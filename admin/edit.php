<?php
require '../db.php';
require 'upload.php';
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$id = filter_var(input_string($_GET['id'] ?? $_POST['id'] ?? ''), FILTER_VALIDATE_INT);
if (!$id || $id < 1) { http_response_code(404); exit('物件が見つかりませんでした。'); }
$stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ?');
$stmt->execute([$id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$property) { http_response_code(404); exit('物件が見つかりませんでした。'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'ページの有効期限が切れました。再読み込みして、もう一度お試しください。';
    } else {
        $name = trim(input_string($_POST['name'] ?? ''));
        $station = trim(input_string($_POST['station'] ?? ''));
        $area = trim(input_string($_POST['area'] ?? ''));
        $school = trim(input_string($_POST['school'] ?? ''));
        $description = trim(input_string($_POST['description'] ?? ''));
        $rent = filter_var(input_string($_POST['rent'] ?? ''), FILTER_VALIDATE_INT);
        $areaRaw = input_string($_POST['area_sqm'] ?? '');
        $areaSqm = $areaRaw === '' ? null : filter_var($areaRaw, FILTER_VALIDATE_FLOAT);
        $floorRaw = input_string($_POST['floor_number'] ?? '');
        $floorNumber = $floorRaw === '' ? null : filter_var($floorRaw, FILTER_VALIDATE_INT);
        $initialRaw = input_string($_POST['initial_cost'] ?? '');
        $initialCost = $initialRaw === '' ? null : filter_var($initialRaw, FILTER_VALIDATE_INT);
        $layout = input_string($_POST['layout'] ?? '');
        $petAllowedInput = input_string($_POST['pet_allowed'] ?? '0');
        $petAllowed = $petAllowedInput === '1' ? 1 : 0;
        $status = input_string($_POST['status'] ?? 'available');
        $layouts = ['1R', '1K', '1DK', '1LDK', '2DK', '2LDK', '3LDK'];

        if ($name === '' || text_length($name) > 100) {
            $error = '物件名を100文字以内で入力してください。';
        } elseif ($rent === false || $rent < 1) {
            $error = '家賃は1円以上の整数で入力してください。';
        } elseif (($areaRaw !== '' && ($areaSqm === false || $areaSqm < 1 || $areaSqm > 9999)) || ($floorRaw !== '' && ($floorNumber === false || $floorNumber < 1 || $floorNumber > 200)) || ($initialRaw !== '' && ($initialCost === false || $initialCost < 0))) {
            $error = '面積、階数、初期費用を正しく入力してください。';
        } elseif (!in_array($layout, $layouts, true) || !in_array($petAllowedInput, ['0', '1'], true) || !in_array($status, ['available','reserved','rented'], true)) {
            $error = '間取りとペット条件を選択してください。';
        } elseif (text_length($station) > 50 || text_length($area) > 50 || text_length($school) > 50) {
            $error = '駅名、地域、学校名はそれぞれ50文字以内で入力してください。';
        } else {
            $image = save_upload('image_file');
            if ($image !== null && str_starts_with($image, 'ERROR:')) {
                $error = substr($image, 6);
            } else {
                $stmt = $pdo->prepare('UPDATE properties SET name=?, station=?, area=?, school=?, rent=?, pet_allowed=?, layout=?, area_sqm=?, floor_number=?, initial_cost=?, description=?, image=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?');
                $stmt->execute([$name, $station, $area, $school, $rent, $petAllowed, $layout, $areaSqm, $floorNumber, $initialCost, $description, $image ?? $property['image'], $status, $id]);
                header('Location: index.php');
                exit;
            }
        }
    }
}

$currentName = input_string($_POST['name'] ?? $property['name']);
$currentStation = input_string($_POST['station'] ?? $property['station']);
$currentArea = input_string($_POST['area'] ?? $property['area']);
$currentSchool = input_string($_POST['school'] ?? $property['school']);
$currentRent = input_string($_POST['rent'] ?? $property['rent']);
$currentLayout = input_string($_POST['layout'] ?? $property['layout']);
$currentPetAllowed = input_string($_POST['pet_allowed'] ?? (string)$property['pet_allowed']);
$currentDescription = input_string($_POST['description'] ?? $property['description']);
$currentAreaSqm = input_string($_POST['area_sqm'] ?? $property['area_sqm']);
$currentFloor = input_string($_POST['floor_number'] ?? $property['floor_number']);
$currentInitialCost = input_string($_POST['initial_cost'] ?? $property['initial_cost']);
$currentStatus = input_string($_POST['status'] ?? $property['status']);
$schools = [
    '' => '指定なし',
    '横浜国立大学' => '横浜国立大学',
    '神奈川大学（横浜キャンパス）' => '神奈川大学（横浜キャンパス）',
    '神奈川大学（みなとみらいキャンパス）' => '神奈川大学（みなとみらいキャンパス）',
    '横浜市立大学（金沢八景キャンパス）' => '横浜市立大学（金沢八景キャンパス）',
    '専修大学（生田キャンパス）' => '専修大学（生田キャンパス）',
    '洗足学園音楽大学' => '洗足学園音楽大学',
    '令和日本語学院' => '令和日本語学院',
    'yokohama' => '横浜エリア（既存登録）',
    'kanagawa' => '神奈川県内（既存登録）',
];
if ($currentSchool && !isset($schools[$currentSchool])) $schools[$currentSchool] = $currentSchool;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>物件を編集 | Sora Living</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="page-shell">
  <p><a href="index.php">← 物件管理へ戻る</a></p>
  <section class="search-section">
    <h2>物件を編集</h2>
    <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="contact-form">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int)$id ?>">
      <label>物件名
        <input name="name" value="<?= h($currentName) ?>" required maxlength="100">
      </label>
      <label>最寄り駅
        <input name="station" value="<?= h($currentStation) ?>" maxlength="50">
      </label>
      <label>地域
        <input name="area" value="<?= h($currentArea) ?>" maxlength="50">
      </label>
      <label>学校・通学エリア
        <select name="school" class="form-control">
          <?php foreach ($schools as $value => $label): ?>
            <option value="<?= h($value) ?>" <?= $currentSchool === $value ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>家賃（月額・円）
        <input type="number" name="rent" value="<?= h($currentRent) ?>" min="1" step="1" required>
      </label>
      <label>間取り
        <select name="layout" class="form-control" required>
          <?php foreach (['1R', '1K', '1DK', '1LDK', '2DK', '2LDK', '3LDK'] as $option): ?>
            <option value="<?= h($option) ?>" <?= $currentLayout === $option ? 'selected' : '' ?>><?= h($option) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>ペット
        <select name="pet_allowed" class="form-control" required>
          <option value="0" <?= $currentPetAllowed === '0' ? 'selected' : '' ?>>不可</option>
          <option value="1" <?= $currentPetAllowed === '1' ? 'selected' : '' ?>>可</option>
        </select>
      </label>
      <label>募集状況<select name="status" class="form-control"><option value="available" <?=$currentStatus==='available'?'selected':''?>>募集中</option><option value="reserved" <?=$currentStatus==='reserved'?'selected':''?>>申込あり</option><option value="rented" <?=$currentStatus==='rented'?'selected':''?>>契約済み</option></select></label>
      <div class="admin-fields-grid"><label>専有面積（m²・任意）<input type="number" name="area_sqm" min="1" max="9999" step="0.01" value="<?=h($currentAreaSqm)?>"></label><label>所在階（任意）<input type="number" name="floor_number" min="1" max="200" value="<?=h($currentFloor)?>"></label><label>初期費用の目安（円・任意）<input type="number" name="initial_cost" min="0" step="1000" value="<?=h($currentInitialCost)?>"></label></div>
      <label>現在の写真
        <img src="../<?= h($property['image'] ?: 'images/property1.jpg') ?>" alt="<?= h($property['name']) ?>の現在の写真" style="display:block;width:min(100%,360px);max-height:240px;object-fit:cover;border-radius:12px;margin-top:8px;">
      </label>
      <label>写真を変更（JPG・PNG・WebP、5MBまで）
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
      </label>
      <label>物件の説明
        <textarea name="description" rows="5"><?= h($currentDescription) ?></textarea>
      </label>
      <button type="submit">変更を保存</button>
    </form>
  </section>
</div>
</body>
</html>
