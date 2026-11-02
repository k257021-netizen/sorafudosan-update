<?php
require '../db.php';
require 'upload.php';
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$error = '';
$serviceAreas = ['鶴見区', '神奈川区', '西区', '中区', '南区', '港南区', '保土ケ谷区', '旭区', '磯子区', '金沢区', '港北区', '緑区', '青葉区', '都筑区', '戸塚区', '栄区', '泉区', '瀬谷区', '川崎区', '幸区', '中原区', '高津区', '宮前区', '多摩区', '麻生区', '横浜市', '川崎市'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'ページの有効期限が切れました。再読み込みして、もう一度お試しください。';
    } elseif (isset($_POST['delete'])) {
        $pdo->prepare('DELETE FROM properties WHERE id = ?')->execute([(int)$_POST['delete']]);
        header('Location: index.php');
        exit;
    } else {
        $name = trim(input_string($_POST['name'] ?? ''));
        $station = trim(input_string($_POST['station'] ?? ''));
        $area = trim(input_string($_POST['area'] ?? ''));
        $school = trim(input_string($_POST['school'] ?? ''));
        $description = trim(input_string($_POST['description'] ?? ''));
        $transactionType = input_string($_POST['transaction_type'] ?? 'rent');
        $rentInput = input_string($_POST['rent'] ?? '');
        $rent = $rentInput === '' ? null : filter_var($rentInput, FILTER_VALIDATE_INT);
        $salePriceInput = input_string($_POST['sale_price'] ?? '');
        $salePrice = $salePriceInput === '' ? null : filter_var($salePriceInput, FILTER_VALIDATE_INT);
        $managementFeeInput = input_string($_POST['management_fee'] ?? '');
        $managementFee = $managementFeeInput === '' ? null : filter_var($managementFeeInput, FILTER_VALIDATE_INT);
        $depositInput = input_string($_POST['deposit_amount'] ?? '');
        $depositAmount = $depositInput === '' ? null : filter_var($depositInput, FILTER_VALIDATE_INT);
        $keyMoneyInput = input_string($_POST['key_money_amount'] ?? '');
        $keyMoneyAmount = $keyMoneyInput === '' ? null : filter_var($keyMoneyInput, FILTER_VALIDATE_INT);
        $areaRaw = input_string($_POST['area_sqm'] ?? '');
        $areaSqm = $areaRaw === '' ? null : filter_var($areaRaw, FILTER_VALIDATE_FLOAT);
        $floorRaw = input_string($_POST['floor_number'] ?? '');
        $floorNumber = $floorRaw === '' ? null : filter_var($floorRaw, FILTER_VALIDATE_INT);
        $initialRaw = input_string($_POST['initial_cost'] ?? '');
        $initialCost = $initialRaw === '' ? null : filter_var($initialRaw, FILTER_VALIDATE_INT);
        $layout = input_string($_POST['layout'] ?? '');
        $petAllowedInput = input_string($_POST['pet_allowed'] ?? '0');
        $petAllowed = $petAllowedInput === '1' ? 1 : 0;
        $layouts = ['1R', '1K', '1DK', '1LDK', '2DK', '2LDK', '3LDK'];

        if ($name === '' || text_length($name) > 100) {
            $error = '物件名を100文字以内で入力してください。';
        } elseif (!in_array($transactionType, ['rent', 'sale'], true)) {
            $error = '物件種別を選択してください。';
        } elseif (($transactionType === 'rent' && ($rent === null || $rent === false || $rent < 1)) || ($transactionType === 'sale' && ($salePrice === null || $salePrice === false || $salePrice < 1))) {
            $error = $transactionType === 'rent' ? '家賃は1円以上で入力してください。' : '販売価格は1円以上で入力してください。';
        } elseif (in_array(false, [$managementFee, $depositAmount, $keyMoneyAmount], true) || array_filter([$managementFee, $depositAmount, $keyMoneyAmount], static fn($value) => $value !== null && $value < 0)) {
            $error = '管理費、敷金、礼金は0円以上の整数で入力してください。';
        } elseif (($areaRaw !== '' && ($areaSqm === false || $areaSqm < 1 || $areaSqm > 9999)) || ($floorRaw !== '' && ($floorNumber === false || $floorNumber < 1 || $floorNumber > 200)) || ($initialRaw !== '' && ($initialCost === false || $initialCost < 0))) {
            $error = '面積、階数、初期費用を正しく入力してください。';
        } elseif (!in_array($layout, $layouts, true) || !in_array($petAllowedInput, ['0', '1'], true)) {
            $error = '間取りとペット条件を選択してください。';
        } elseif (!in_array($area, $serviceAreas, true)) {
            $error = '地域は横浜市または川崎市の区から選択してください。';
        } elseif (text_length($station) > 50 || text_length($area) > 50 || text_length($school) > 50) {
            $error = '駅名、地域、学校名はそれぞれ50文字以内で入力してください。';
        } else {
            $image = save_upload('image_file');
            if ($image !== null && str_starts_with($image, 'ERROR:')) {
                $error = substr($image, 6);
            } else {
                $stmt = $pdo->prepare('INSERT INTO properties (name, station, area, school, transaction_type, rent, sale_price, management_fee, deposit_amount, key_money_amount, pet_allowed, layout, area_sqm, floor_number, initial_cost, description, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $station, $area, $school, $transactionType, $transactionType === 'rent' ? $rent : null, $transactionType === 'sale' ? $salePrice : null, $transactionType === 'rent' ? $managementFee : null, $transactionType === 'rent' ? $depositAmount : null, $transactionType === 'rent' ? $keyMoneyAmount : null, $petAllowed, $layout, $areaSqm, $floorNumber, $initialCost, $description, $image ?? 'images/property1.jpg', 'available']);
                header('Location: index.php');
                exit;
            }
        }
    }
}

$filterStatus = input_string($_GET['status'] ?? 'all');
if (!in_array($filterStatus, ['all','available','reserved','rented'], true)) $filterStatus = 'all';
$listSql = 'SELECT * FROM properties WHERE area IN (' . implode(',', array_fill(0, count($serviceAreas), '?')) . ')' . ($filterStatus === 'all' ? '' : ' AND status = ?') . ' ORDER BY id DESC';
$listStmt = $pdo->prepare($listSql);
$listStmt->execute($filterStatus === 'all' ? $serviceAreas : array_merge($serviceAreas, [$filterStatus]));
$list = $listStmt->fetchAll(PDO::FETCH_ASSOC);
$areaPlaceholders = implode(',', array_fill(0, count($serviceAreas), '?'));
$dashboardStmt = $pdo->prepare('SELECT COUNT(*), SUM(status = \'available\') FROM properties WHERE area IN (' . $areaPlaceholders . ')');
$dashboardStmt->execute($serviceAreas);
$dashboardCounts = $dashboardStmt->fetch(PDO::FETCH_NUM);
$dashboard = [
    'total_properties' => (int)$dashboardCounts[0],
    'available' => (int)$dashboardCounts[1],
    'unread_inquiries' => (int)$pdo->query('SELECT COUNT(*) FROM inquiries WHERE is_read=0')->fetchColumn(),
];
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>物件管理 | Sora Living</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include ((basename(__DIR__) === 'admin' ? dirname(__DIR__) : __DIR__) . '/includes/site-brand.php'); ?>
<div class="page-shell">
  <nav class="admin-links" aria-label="管理メニュー">
    <a href="../index.php">サイトを見る</a> ・ <a href="inquiries.php">お問い合わせ一覧</a> ・ <a href="../logout.php">ログアウト</a>
  </nav>
  <section class="admin-dashboard" aria-label="管理サマリー"><a href="index.php"><strong><?= $dashboard['total_properties'] ?></strong><span>登録物件</span></a><a href="index.php?status=available"><strong><?= $dashboard['available'] ?></strong><span>募集中</span></a><a href="inquiries.php"><strong><?= $dashboard['unread_inquiries'] ?></strong><span>未読お問い合わせ</span></a></section>

  <section class="search-section">
    <h2>物件を追加</h2>
    <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="contact-form">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <label>物件名
        <input name="name" required maxlength="100" value="<?= h(input_string($_POST['name'] ?? '')) ?>">
      </label>
      <label>最寄り駅
        <input name="station" maxlength="50" placeholder="例：横浜駅" value="<?= h(input_string($_POST['station'] ?? '')) ?>">
      </label>
      <label>地域
        <select name="area" class="form-control" required><option value="">地域を選択</option><?php foreach ($serviceAreas as $serviceArea): ?><option value="<?= h($serviceArea) ?>" <?= input_string($_POST['area'] ?? '') === $serviceArea ? 'selected' : '' ?>><?= h($serviceArea) ?></option><?php endforeach; ?></select>
      </label>
      <label>学校・通学エリア
        <select name="school" class="form-control">
          <option value="">指定なし</option>
          <option value="横浜国立大学" <?= (input_string($_POST['school'] ?? '') === '横浜国立大学') ? 'selected' : '' ?>>横浜国立大学</option>
          <option value="神奈川大学（横浜キャンパス）" <?= (input_string($_POST['school'] ?? '') === '神奈川大学（横浜キャンパス）') ? 'selected' : '' ?>>神奈川大学（横浜キャンパス）</option>
          <option value="神奈川大学（みなとみらいキャンパス）" <?= (input_string($_POST['school'] ?? '') === '神奈川大学（みなとみらいキャンパス）') ? 'selected' : '' ?>>神奈川大学（みなとみらいキャンパス）</option>
          <option value="横浜市立大学（金沢八景キャンパス）" <?= (input_string($_POST['school'] ?? '') === '横浜市立大学（金沢八景キャンパス）') ? 'selected' : '' ?>>横浜市立大学（金沢八景キャンパス）</option>
          <option value="専修大学（生田キャンパス）" <?= (input_string($_POST['school'] ?? '') === '専修大学（生田キャンパス）') ? 'selected' : '' ?>>専修大学（生田キャンパス）</option>
          <option value="洗足学園音楽大学" <?= (input_string($_POST['school'] ?? '') === '洗足学園音楽大学') ? 'selected' : '' ?>>洗足学園音楽大学</option>
          <option value="令和日本語学院" <?= (input_string($_POST['school'] ?? '') === '令和日本語学院') ? 'selected' : '' ?>>令和日本語学院</option>
          <option value="yokohama" <?= (input_string($_POST['school'] ?? '') === 'yokohama') ? 'selected' : '' ?>>横浜エリア（既存登録）</option>
          <option value="kanagawa" <?= (input_string($_POST['school'] ?? '') === 'kanagawa') ? 'selected' : '' ?>>神奈川県内（既存登録）</option>
        </select>
      </label>
      <label>取引種別<select name="transaction_type" data-transaction-type class="form-control"><option value="rent" <?= input_string($_POST['transaction_type'] ?? 'rent') === 'rent' ? 'selected' : '' ?>>賃貸</option><option value="sale" <?= input_string($_POST['transaction_type'] ?? '') === 'sale' ? 'selected' : '' ?>>売買</option></select></label>
      <label data-type-fields="rent">家賃（月額・円）<input type="number" name="rent" data-type-input min="1" step="1" value="<?= h(input_string($_POST['rent'] ?? '')) ?>"></label>
      <label data-type-fields="sale" hidden>販売価格（円）<input type="number" name="sale_price" data-type-input min="1" step="10000" value="<?= h(input_string($_POST['sale_price'] ?? '')) ?>"></label>
      <div class="admin-fields-grid" data-type-fields="rent"><label>管理費・共益費（月額・円）<input type="number" name="management_fee" min="0" step="100" value="<?= h(input_string($_POST['management_fee'] ?? '')) ?>"></label><label>敷金（円）<input type="number" name="deposit_amount" min="0" step="1000" value="<?= h(input_string($_POST['deposit_amount'] ?? '')) ?>"></label><label>礼金（円）<input type="number" name="key_money_amount" min="0" step="1000" value="<?= h(input_string($_POST['key_money_amount'] ?? '')) ?>"></label></div>
      <div class="admin-fields-grid"><label>専有面積（m²・任意）<input type="number" name="area_sqm" min="1" max="9999" step="0.01" value="<?= h(input_string($_POST['area_sqm'] ?? '')) ?>"></label><label>所在階（任意）<input type="number" name="floor_number" min="1" max="200" step="1" value="<?= h(input_string($_POST['floor_number'] ?? '')) ?>"></label><label>初期費用・諸費用の目安（円・任意）<input type="number" name="initial_cost" min="0" step="1000" value="<?= h(input_string($_POST['initial_cost'] ?? '')) ?>"></label></div>
      <label>間取り
        <select name="layout" class="form-control" required>
          <?php foreach (['1R', '1K', '1DK', '1LDK', '2DK', '2LDK', '3LDK'] as $option): ?>
            <option value="<?= h($option) ?>" <?= input_string($_POST['layout'] ?? '1K') === $option ? 'selected' : '' ?>><?= h($option) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>ペット
        <select name="pet_allowed" class="form-control" required>
          <option value="0" <?= input_string($_POST['pet_allowed'] ?? '0') === '0' ? 'selected' : '' ?>>不可</option>
          <option value="1" <?= input_string($_POST['pet_allowed'] ?? '0') === '1' ? 'selected' : '' ?>>可</option>
        </select>
      </label>
      <label>物件写真（JPG・PNG・WebP、5MBまで）
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
      </label>
      <label>物件の説明
        <textarea name="description" rows="4"><?= h(input_string($_POST['description'] ?? '')) ?></textarea>
      </label>
      <button type="submit">物件を追加</button>
    </form>
  </section>

  <section class="search-section">
    <h2>登録物件（<?= count($list) ?>件<?= $filterStatus !== 'all' ? '・絞り込み中' : '' ?>）</h2>
    <?php if (!$list): ?><p class="empty-state">条件に合う物件はありません。<a href="index.php">絞り込みを解除</a></p><?php endif; ?>
    <form method="get" class="filter-row admin-filter"><label>募集状況<select name="status"><option value="all">すべて</option><option value="available" <?= input_string($_GET['status'] ?? '')==='available'?'selected':'' ?>>募集中</option><option value="reserved" <?= input_string($_GET['status'] ?? '')==='reserved'?'selected':'' ?>>申込あり</option><option value="rented" <?= input_string($_GET['status'] ?? '')==='rented'?'selected':'' ?>>契約済み</option></select></label><button>絞り込む</button><a href="index.php">解除</a></form>
    <?php foreach ($list as $property): ?>
      <div class="admin-property-row">
        <img src="../<?= h($property['image'] ?: 'images/property1.jpg') ?>" alt="">
        <strong><?= h($property['name']) ?></strong>
        <span><?= $property['transaction_type'] === 'sale' ? '販売価格' : '家賃（月額）' ?> <?= number_format((int)($property['sale_price'] ?? $property['rent'] ?? 0)) ?>円</span>
        <span><?= h($property['layout']) ?>・ペット<?= (int)$property['pet_allowed'] === 1 ? '可' : '不可' ?>・<?= ['available'=>'募集中','reserved'=>'申込あり','rented'=>'契約済み'][$property['status']] ?? '募集中' ?><?php if($property['area_sqm']): ?>・<?= h($property['area_sqm']) ?>m²<?php endif; ?></span>
        <a href="edit.php?id=<?= (int)$property['id'] ?>">編集</a>
        <form method="POST" onsubmit="return confirm('この物件を削除しますか？');">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
          <button type="submit" name="delete" value="<?= (int)$property['id'] ?>">削除</button>
        </form>
      </div>
    <?php endforeach; ?>
  </section>
</div>
<script src="../property-form.js?v=3"></script>
</body>
</html>
