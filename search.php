<?php
require 'db.php';

$where = [];
$params = [];
$query = trim(input_string($_GET['q'] ?? ''));
$query = function_exists('mb_substr') ? mb_substr($query, 0, 100, 'UTF-8') : substr($query, 0, 100);

if ($query !== '') {
    $like = '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
    $where[] = "(name LIKE :q1 ESCAPE '!' OR station LIKE :q2 ESCAPE '!' OR area LIKE :q3 ESCAPE '!' OR school LIKE :q4 ESCAPE '!')";
    $params[':q1'] = $params[':q2'] = $params[':q3'] = $params[':q4'] = $like;
}

$station = trim(input_string($_GET['station'] ?? ''));
$stations = ['横浜駅', '新横浜駅', '菊名駅', '日吉駅', '鶴見駅', '桜木町駅', '関内駅', '石川町駅', '戸塚駅', '金沢八景駅', '白楽駅', '和田町駅', '川崎駅', '京急川崎駅', '武蔵小杉駅', '溝の口駅', '武蔵溝ノ口駅', '登戸駅', '向ヶ丘遊園駅', '新百合ヶ丘駅', '鎌倉駅', '藤沢駅', '逗子駅', '横須賀中央駅', '大和駅', '町田駅', '蒲田駅', '品川駅'];
if (in_array($station, $stations, true)) {
    $where[] = 'station = :station';
    $params[':station'] = $station;
}

$area = trim(input_string($_GET['area'] ?? ''));
$areas = ['鶴見区', '神奈川区', '西区', '中区', '南区', '港南区', '保土ケ谷区', '旭区', '磯子区', '金沢区', '港北区', '緑区', '青葉区', '都筑区', '戸塚区', '栄区', '泉区', '瀬谷区', '川崎区', '幸区', '中原区', '高津区', '宮前区', '多摩区', '麻生区', '鎌倉市', '藤沢市', '逗子市', '横須賀市', '大和市', '町田市', '大田区', '品川区', '横浜市', '川崎市'];
if (in_array($area, $areas, true)) {
    $where[] = 'area = :area';
    $params[':area'] = $area;
}

$school = trim(input_string($_GET['school'] ?? ''));
$schools = ['yokohama', 'kanagawa', '令和日本語学院', '横浜国立大学', '神奈川大学（横浜キャンパス）', '神奈川大学（みなとみらいキャンパス）', '横浜市立大学（金沢八景キャンパス）', '専修大学（生田キャンパス）', '洗足学園音楽大学'];
if (in_array($school, $schools, true)) {
    $where[] = 'school = :school';
    $params[':school'] = $school;
}

$maxRent = input_string($_GET['max_rent'] ?? '');
if ($maxRent !== '' && filter_var($maxRent, FILTER_VALIDATE_INT) !== false && (int)$maxRent > 0) {
    $where[] = 'rent <= :rent';
    $params[':rent'] = (int)$maxRent;
}

$petAllowed = input_string($_GET['pet_allowed'] ?? '');
if ($petAllowed === '0' || $petAllowed === '1') {
    $where[] = 'pet_allowed = :pet_allowed';
    $params[':pet_allowed'] = (int)$petAllowed;
}

$layout = input_string($_GET['layout'] ?? '');
$layouts = ['1R', '1K', '1DK', '1LDK', '2DK', '2LDK', '3LDK'];
if (in_array($layout, $layouts, true)) {
    $where[] = 'layout = :layout';
    $params[':layout'] = $layout;
}

$minArea = input_string($_GET['min_area'] ?? '');
if ($minArea !== '' && is_numeric($minArea) && (float)$minArea > 0 && (float)$minArea <= 9999) {
    $where[] = 'area_sqm >= :min_area'; $params[':min_area'] = (float)$minArea;
}
$maxInitial = input_string($_GET['max_initial_cost'] ?? '');
if ($maxInitial !== '' && filter_var($maxInitial, FILTER_VALIDATE_INT) !== false && (int)$maxInitial >= 0) {
    $where[] = 'initial_cost <= :max_initial_cost'; $params[':max_initial_cost'] = (int)$maxInitial;
}

$searchPrompt = !$where && input_string($_GET['browse'] ?? '') !== '1';
$status = input_string($_GET['status'] ?? 'available');
if (!in_array($status, ['available', 'reserved', 'rented', 'all'], true)) $status = 'available';
if ($status !== 'all') { $where[] = 'status = :status'; $params[':status'] = $status; }

$sort = input_string($_GET['sort'] ?? 'rent_asc');
$sorts = ['rent_asc' => 'rent ASC, id DESC', 'rent_desc' => 'rent DESC, id DESC', 'newest' => 'updated_at DESC, id DESC'];
if (!isset($sorts[$sort])) $sort = 'rent_asc';
$page = max(1, min(10000, (int)($_GET['page'] ?? 1)));
$pageSize = 12;
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM properties' . $whereSql);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$pages = max(1, (int)ceil($total / $pageSize));
if ($page > $pages) $page = $pages;
$sql = 'SELECT id, name, station, area, rent, image, description, pet_allowed, layout, area_sqm, floor_number, initial_cost, status, updated_at FROM properties' . $whereSql . ' ORDER BY ' . $sorts[$sort] . ' LIMIT ' . $pageSize . ' OFFSET ' . (($page - 1) * $pageSize);

$list = [];
if (!$searchPrompt) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$isCustomer = !empty($_SESSION['customer_id']);
$favoriteIds = $isCustomer ? array_fill_keys(customer_favorite_ids($pdo, (int)$_SESSION['customer_id']), true) : [];
$favoriteCsrf = $isCustomer ? csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>検索結果 | Sora Living</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<a class="skip-link" href="#main-content">検索結果へ移動</a>
<div class="page-shell">
  <p><a href="index.php">← トップページへ戻る</a></p>
  <main id="main-content"><section class="search-section" aria-labelledby="results-title">
    <h1 class="page-title" id="results-title"><?= $searchPrompt ? '検索条件を入力してください' : '検索結果（' . $total . '件）' ?></h1>
    <?php if (!$searchPrompt): ?><form method="get" class="filter-row search-refine"><?php if (input_string($_GET['browse'] ?? '') === '1'): ?><input type="hidden" name="browse" value="1"><?php endif; ?><?php foreach (['q','station','area','school','max_rent','pet_allowed','layout','min_area','max_initial_cost'] as $key): if (isset($_GET[$key]) && $_GET[$key] !== ''): ?><input type="hidden" name="<?= h($key) ?>" value="<?= h(input_string($_GET[$key])) ?>"><?php endif; endforeach; ?><label>募集状況 <select name="status"><option value="available" <?= $status==='available'?'selected':'' ?>>募集中</option><option value="reserved" <?= $status==='reserved'?'selected':'' ?>>申込あり</option><option value="rented" <?= $status==='rented'?'selected':'' ?>>契約済み</option><option value="all" <?= $status==='all'?'selected':'' ?>>すべて</option></select></label><label>並び順 <select name="sort"><option value="rent_asc" <?=$sort==='rent_asc'?'selected':''?>>家賃が安い順</option><option value="rent_desc" <?=$sort==='rent_desc'?'selected':''?>>家賃が高い順</option><option value="newest" <?=$sort==='newest'?'selected':''?>>更新が新しい順</option></select></label><button>適用</button><?php if ($where): ?><a class="reset-filter" href="search.php?browse=1">条件をクリアして物件を見る</a><?php endif; ?></form><?php endif; ?>
    <div id="property-list">
      <?php if ($searchPrompt): ?>
        <p class="empty-state" role="status">学校・駅・地域・物件名、家賃・間取り・ペット・面積・初期費用の条件を1つ以上選んで検索してください。</p>
      <?php elseif (!$list): ?>
        <div class="empty-state" role="status"><p>条件に合う物件が見つかりませんでした。</p><p>家賃や面積の条件を広げるか、募集状況を「すべて」に変えてみてください。</p><a class="primary-button" href="search.php?browse=1">条件をクリアして物件を見る</a></div>
      <?php endif; ?>
      <?php foreach ($list as $p): ?>
        <article class="property-card">
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
          <p class="price">家賃 <?= number_format((int)$p['rent']) ?>円</p>
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
