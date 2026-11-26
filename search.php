<?php
require 'db.php';

$where = [];
$params = [];
// Keep every result inside the service area, including unfiltered browse requests.
$serviceAreas = ['鶴見区', '神奈川区', '西区', '中区', '南区', '港南区', '保土ケ谷区', '旭区', '磯子区', '金沢区', '港北区', '緑区', '青葉区', '都筑区', '戸塚区', '栄区', '泉区', '瀬谷区', '川崎区', '幸区', '中原区', '高津区', '宮前区', '多摩区', '麻生区', '横浜市', '川崎市'];
$areaPlaceholders = [];
foreach ($serviceAreas as $index => $serviceArea) {
    $placeholder = ':service_area_' . $index;
    $areaPlaceholders[] = $placeholder;
    $params[$placeholder] = $serviceArea;
}
$where[] = 'area IN (' . implode(', ', $areaPlaceholders) . ')';
$transactionType = input_string($_GET['transaction_type'] ?? '');
if (in_array($transactionType, ['rent', 'sale'], true)) {
    $where[] = 'transaction_type = :transaction_type';
    $params[':transaction_type'] = $transactionType;
} else $transactionType = '';
$query = trim(input_string($_GET['q'] ?? ''));
$query = function_exists('mb_substr') ? mb_substr($query, 0, 100, 'UTF-8') : substr($query, 0, 100);

if ($query !== '') {
    $like = '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $where[] = "(name LIKE :q1 ESCAPE '!' OR station LIKE :q2 ESCAPE '!' OR area LIKE :q3 ESCAPE '!' OR school LIKE :q4 ESCAPE '!')";
    $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = $like;
}

$station = trim(input_string($_GET['station'] ?? ''));
$stations = ['横浜駅', '新横浜駅', '菊名駅', '日吉駅', '鶴見駅', '桜木町駅', '関内駅', '石川町駅', '戸塚駅', '金沢八景駅', '白楽駅', '和田町駅', '川崎駅', '京急川崎駅', '武蔵小杉駅', '溝の口駅', '武蔵溝ノ口駅', '登戸駅', '向ヶ丘遊園駅', '新百合ヶ丘駅'];
if (in_array($station, $stations, true)) {
    $where[] = 'station = :station';
    $params[':station'] = $station;
} else $station = '';

$area = trim(input_string($_GET['area'] ?? ''));
$areas = $serviceAreas;
if (in_array($area, $areas, true)) {
    $where[] = 'area = :area';
    $params[':area'] = $area;
} else $area = '';

$school = trim(input_string($_GET['school'] ?? ''));
$schools = ['yokohama', 'kanagawa', '令和日本語学院', '横浜国立大学', '神奈川大学（横浜キャンパス）', '神奈川大学（みなとみらいキャンパス）', '横浜市立大学（金沢八景キャンパス）', '専修大学（生田キャンパス）', '洗足学園音楽大学'];
if (in_array($school, $schools, true)) {
    $where[] = 'school = :school';
    $params[':school'] = $school;
} else $school = '';

$maxRent = input_string($_GET['max_rent'] ?? '');
if ($maxRent !== '' && filter_var($maxRent, FILTER_VALIDATE_INT) !== false && (int)$maxRent > 0) {
    $where[] = "transaction_type = 'rent' AND rent <= :rent";
    $params[':rent'] = (int)$maxRent;
} else $maxRent = '';
$maxSalePrice = input_string($_GET['max_sale_price'] ?? '');
if ($maxSalePrice !== '' && filter_var($maxSalePrice, FILTER_VALIDATE_INT) !== false && (int)$maxSalePrice > 0) {
    $where[] = "transaction_type = 'sale' AND sale_price <= :sale_price";
    $params[':sale_price'] = (int)$maxSalePrice;
} else $maxSalePrice = '';

$petAllowed = input_string($_GET['pet_allowed'] ?? '');
if ($petAllowed === '0' || $petAllowed === '1') {
    $where[] = 'pet_allowed = :pet_allowed';
    $params[':pet_allowed'] = (int)$petAllowed;
} else $petAllowed = '';

$layout = input_string($_GET['layout'] ?? '');
$layouts = ['1R', '1K', '1DK', '1LDK', '2DK', '2LDK', '3LDK'];
if (in_array($layout, $layouts, true)) {
    $where[] = 'layout = :layout';
    $params[':layout'] = $layout;
} else $layout = '';

$minArea = input_string($_GET['min_area'] ?? '');
if ($minArea !== '' && is_numeric($minArea) && (float)$minArea > 0 && (float)$minArea <= 9999) {
    $where[] = 'area_sqm >= :min_area'; $params[':min_area'] = (float)$minArea;
} else $minArea = '';
$maxInitial = input_string($_GET['max_initial_cost'] ?? '');
if ($maxInitial !== '' && filter_var($maxInitial, FILTER_VALIDATE_INT) !== false && (int)$maxInitial >= 0) {
    $where[] = 'initial_cost <= :max_initial_cost'; $params[':max_initial_cost'] = (int)$maxInitial;
} else $maxInitial = '';

$searchPrompt = count($where) === 1 && input_string($_GET['browse'] ?? '') !== '1';
$status = input_string($_GET['status'] ?? 'available');
if (!in_array($status, ['available', 'reserved', 'rented', 'all'], true)) $status = 'available';
if ($status !== 'all') { $where[] = 'status = :status'; $params[':status'] = $status; }

$sort = input_string($_GET['sort'] ?? 'rent_asc');
$sorts = ['rent_asc' => 'COALESCE(rent, sale_price) ASC, id DESC', 'rent_desc' => 'COALESCE(rent, sale_price) DESC, id DESC', 'newest' => 'updated_at DESC, id DESC'];
if (!isset($sorts[$sort])) $sort = 'rent_asc';
$page = max(1, min(10000, (int)($_GET['page'] ?? 1)));
$pageSize = 12;
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM properties' . $whereSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $pageSize));
if ($page > $pages) $page = $pages;
$sql = 'SELECT id, name, station, area, transaction_type, rent, sale_price, management_fee, image, description, pet_allowed, layout, area_sqm, floor_number, initial_cost, status, updated_at FROM properties' . $whereSql . ' ORDER BY ' . $sorts[$sort] . ' LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize);

$list = [];
if (!$searchPrompt) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$isCustomer = !empty($_SESSION['customer_id']);
$favoriteIds = $isCustomer ? array_fill_keys(customer_favorite_ids($pdo, (int)$_SESSION['customer_id']), true) : [];
$favoriteCsrf = $isCustomer ? csrf_token() : '';
$searchCriteria = [];
if ($query !== '') $searchCriteria[] = ['キーワード', '「' . $query . '」'];
if ($transactionType !== '') $searchCriteria[] = ['物件種別', $transactionType === 'sale' ? '売買' : '賃貸'];
if ($school !== '') {
    $schoolLabels = ['yokohama' => '横浜エリア', 'kanagawa' => '神奈川県内'];
    $searchCriteria[] = ['学校', $schoolLabels[$school] ?? $school];
}
if ($station !== '') $searchCriteria[] = ['駅', $station];
if ($area !== '') $searchCriteria[] = ['地域', $area];
if ($maxRent !== '' && filter_var($maxRent, FILTER_VALIDATE_INT) !== false && (int)$maxRent > 0) $searchCriteria[] = ['家賃上限', number_format((int)$maxRent) . '円'];
if ($maxSalePrice !== '' && filter_var($maxSalePrice, FILTER_VALIDATE_INT) !== false && (int)$maxSalePrice > 0) $searchCriteria[] = ['販売価格上限', number_format((int)$maxSalePrice) . '円'];
if ($layout !== '') $searchCriteria[] = ['間取り', $layout];
if ($petAllowed === '0' || $petAllowed === '1') $searchCriteria[] = ['ペット', $petAllowed === '1' ? '可' : '不可'];
if ($minArea !== '' && is_numeric($minArea) && (float)$minArea > 0 && (float)$minArea <= 9999) $searchCriteria[] = ['専有面積', $minArea . 'm²以上'];
if ($maxInitial !== '' && filter_var($maxInitial, FILTER_VALIDATE_INT) !== false && (int)$maxInitial >= 0) $searchCriteria[] = ['初期費用上限', number_format((int)$maxInitial) . '円'];
$statusLabels = ['available' => '募集中', 'reserved' => '申込あり', 'rented' => '契約済み', 'all' => 'すべて'];
$searchCriteria[] = ['募集状況', $statusLabels[$status]];
$sortLabels = ['rent_asc' => '価格が安い順', 'rent_desc' => '価格が高い順', 'newest' => '更新が新しい順'];
$searchCriteria[] = ['並び順', $sortLabels[$sort]];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>検索結果 | Sora Living</title>
  <link rel="stylesheet" href="style.css?v=20261126-11">
</head>
<body>
<?php include ((basename(__DIR__) === 'admin' ? dirname(__DIR__) : __DIR__) . '/includes/site-brand.php'); ?>
<a class="skip-link" href="#main-content">検索結果へ移動</a>
<div class="page-shell">
  <p><a href="index.php">← トップページへ戻る</a></p>
  <main id="main-content"><section class="search-section" aria-labelledby="results-title">
    <h1 class="page-title" id="results-title"><?= $searchPrompt ? '検索条件を入力してください' : '検索結果（' . $total . '件）' ?></h1>
    <?php if (!$searchPrompt): ?>
      <section class="search-criteria-summary" aria-label="検索した条件">
        <strong>検索した条件</strong>
        <ul>
          <?php foreach ($searchCriteria as [$label, $value]): ?><li><span><?= h($label) ?></span><?= h($value) ?></li><?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
    <?php
function search_opt(string $value, string $label, string $current): string {
    return '<option value="' . h($value) . '"' . ($value === $current ? ' selected' : '') . '>' . h($label) . '</option>';
}
?>
<form method="get" class="filter-row search-refine">
  <input type="hidden" name="browse" value="1">
  <label>キーワード <input type="search" name="q" value="<?= h($query) ?>" maxlength="100" placeholder="学校・駅・地域・物件名"></label>
  <label>学校 <select name="school">
    <?= search_opt('', '指定なし', $school) ?>
    <?php foreach ($schools as $schoolValue): ?>
      <?php $schoolLabel = ['yokohama' => '横浜エリア', 'kanagawa' => '神奈川県内'][$schoolValue] ?? $schoolValue; ?>
      <?= search_opt($schoolValue, $schoolLabel, $school) ?>
    <?php endforeach; ?>
  </select></label>
  <label>駅 <select name="station">
    <?= search_opt('', '指定なし', $station) ?>
    <?php foreach ($stations as $stationValue): ?><?= search_opt($stationValue, $stationValue, $station) ?><?php endforeach; ?>
  </select></label>
  <label>地域 <select name="area">
    <?= search_opt('', '指定なし', $area) ?>
    <?php foreach ($areas as $areaValue): ?><?= search_opt($areaValue, $areaValue, $area) ?><?php endforeach; ?>
  </select></label>
  <label>物件種別 <select name="transaction_type"><?= search_opt('', '賃貸・売買どちらも', $transactionType) . search_opt('rent', '賃貸', $transactionType) . search_opt('sale', '売買', $transactionType) ?></select></label>
  <label>家賃の上限 <select name="max_rent"><?= search_opt('', '指定なし', $maxRent) . search_opt('50000', '50,000円まで', $maxRent) . search_opt('60000', '60,000円まで', $maxRent) . search_opt('70000', '70,000円まで', $maxRent) . search_opt('80000', '80,000円まで', $maxRent) ?></select></label>
  <label>販売価格の上限 <select name="max_sale_price"><?= search_opt('', '指定なし', $maxSalePrice) . search_opt('30000000', '3,000万円まで', $maxSalePrice) . search_opt('50000000', '5,000万円まで', $maxSalePrice) . search_opt('80000000', '8,000万円まで', $maxSalePrice) ?></select></label>
  <label>間取り <select name="layout"><?= search_opt('', '指定なし', $layout) . search_opt('1R', '1R', $layout) . search_opt('1K', '1K', $layout) . search_opt('1DK', '1DK', $layout) . search_opt('1LDK', '1LDK', $layout) . search_opt('2DK', '2DK', $layout) . search_opt('2LDK', '2LDK', $layout) . search_opt('3LDK', '3LDK', $layout) ?></select></label>
  <label>ペット <select name="pet_allowed"><?= search_opt('', '指定なし', $petAllowed) . search_opt('1', 'ペット可', $petAllowed) . search_opt('0', 'ペット不可', $petAllowed) ?></select></label>
  <label>専有面積の下限 <select name="min_area"><?= search_opt('', '指定なし', $minArea) . search_opt('20', '20m²以上', $minArea) . search_opt('30', '30m²以上', $minArea) . search_opt('40', '40m²以上', $minArea) ?></select></label>
  <label>初期費用の上限 <select name="max_initial_cost"><?= search_opt('', '指定なし', $maxInitial) . search_opt('150000', '150,000円まで', $maxInitial) . search_opt('250000', '250,000円まで', $maxInitial) . search_opt('350000', '350,000円まで', $maxInitial) ?></select></label>
  <label>募集状況 <select name="status"><?= search_opt('available', '募集中', $status) . search_opt('rented', '契約済み', $status) ?></select></label>
  <label>並び順 <select name="sort"><?= search_opt('rent_asc', '価格が安い順', $sort) . search_opt('rent_desc', '価格が高い順', $sort) . search_opt('newest', '更新が新しい順', $sort) ?></select></label>
  <button type="submit">条件で再検索</button>
  <a class="reset-filter" href="search.php?browse=1">条件をクリア</a>
</form>
    <div id="property-list">
      <?php if ($searchPrompt): ?>
        <p class="empty-state" role="status">学校・駅・地域・物件名、家賃・間取り・ペット・面積・初期費用の条件を1つ以上選んで検索してください。</p>
      <?php elseif (!$list): ?>
        <div class="empty-state" role="status"><p>条件に合う物件が見つかりませんでした。</p><p>家賃や面積の条件を広げるか、募集状況を「すべて」に変えてみてください。</p><a class="primary-button" href="search.php?browse=1">条件をクリアして物件を見る</a></div>
      <?php endif; ?>
      <?php foreach ($list as $p): ?>
        <article class="property-card">
          <p class="transaction-badge"><?= $p['transaction_type'] === 'sale' ? '売買' : '賃貸' ?></p>
          <a href="property.php?id=<?= (int)$p['id'] ?>">
            <img src="<?= h($p['image'] ?: 'images/property1.jpg') ?>" alt="<?= h($p['name']) ?>の物件写真" loading="lazy">
            <h3><?= h($p['name']) ?></h3>
          </a>
          <?php if (str_starts_with((string)$p['description'], '【架空のサンプル物件】')): ?><p class="sample-badge">架空のサンプル</p><?php endif; ?>
          <?php if ($p['station']): ?><p>最寄り駅：<?= h($p['station']) ?></p><?php endif; ?>
          <?php if ($p['area']): ?><p><?= h($p['area']) ?></p><?php endif; ?>
          <p>間取り：<?= h($p['layout']) ?> ・ ペット<?= (int)$p['pet_allowed'] === 1 ? '可' : '不可' ?></p>
          <?php if ($p['area_sqm'] !== null || $p['floor_number'] !== null): ?><p><?php if ($p['area_sqm'] !== null): ?>専有面積 <?= h($p['area_sqm']) ?>m²<?php endif; ?><?php if ($p['floor_number'] !== null): ?><?= $p['area_sqm'] !== null ? ' ・ ' : '' ?><?= (int)$p['floor_number'] ?>階<?php endif; ?></p><?php endif; ?>
          <?php if ($p['initial_cost'] !== null): ?><p>初期費用の目安 <?= number_format((int)$p['initial_cost']) ?>円</p><?php endif; ?>
          <p class="price"><?= $p['transaction_type'] === 'sale' ? '販売価格' : '家賃（月額）' ?> <?= number_format((int)($p['sale_price'] ?? $p['rent'] ?? 0)) ?>円</p>
          <?php if ($p['transaction_type'] === 'rent' && $p['management_fee'] !== null): ?><p>管理費・共益費 <?= number_format((int)$p['management_fee']) ?>円／月</p><?php endif; ?>
          <p>募集状況：<?= ['available'=>'募集中','reserved'=>'申込あり','rented'=>'契約済み'][$p['status']] ?? '募集中' ?> ・ 更新 <?= h(substr((string)$p['updated_at'],0,10)) ?></p>
          <button type="button" class="fav-button" data-id="<?= (int)$p['id'] ?>" data-authenticated="<?= $isCustomer ? '1' : '0' ?>" data-favorite="<?= isset($favoriteIds[(int)$p['id']]) ? '1' : '0' ?>" data-csrf="<?= h($favoriteCsrf) ?>"></button>
        </article>
      <?php endforeach; ?>
      <?php if (!$searchPrompt && $pages > 1): ?><nav class="pagination" aria-label="検索結果ページ"><?php for($n=max(1,$page-2);$n<=min($pages,$page+2);$n++): $queryParams=$_GET; $queryParams['page']=$n; ?><a class="<?= $n===$page?'current':'' ?>" href="?<?= h(http_build_query($queryParams)) ?>" <?= $n===$page?'aria-current="page"':'' ?>><?= $n ?></a><?php endfor; ?></nav><?php endif; ?>
    </div>
  </section></main>
</div>
<script src="favorites.js"></script>
</body>
</html>
