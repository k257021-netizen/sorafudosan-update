<?php
require '../db.php';
require 'upload.php';
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$error = '';
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
        $rentInput = input_string($_POST['rent'] ?? '');
        $rent = filter_var($rentInput, FILTER_VALIDATE_INT);
        $layout = input_string($_POST['layout'] ?? '');
        $petAllowedInput = input_string($_POST['pet_allowed'] ?? '0');
        $petAllowed = $petAllowedInput === '1' ? 1 : 0;
        $layouts = ['1R', '1K', '1DK', '1LDK', '2DK', '2LDK', '3LDK'];

        if ($name === '' || text_length($name) > 100) {
            $error = '物件名を100文字以内で入力してください。';
        } elseif ($rent === false || $rent < 1) {
            $error = '家賃は1円以上の整数で入力してください。';
        } elseif (!in_array($layout, $layouts, true) || !in_array($petAllowedInput, ['0', '1'], true)) {
            $error = '間取りとペット条件を選択してください。';
        } elseif (text_length($station) > 50 || text_length($area) > 50 || text_length($school) > 50) {
            $error = '駅名、地域、学校名はそれぞれ50文字以内で入力してください。';
        } else {
            $image = save_upload('image_file');
            if ($image !== null && str_starts_with($image, 'ERROR:')) {
                $error = substr($image, 6);
            } else {
                $stmt = $pdo->prepare('INSERT INTO properties (name, station, area, school, rent, pet_allowed, layout, description, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $station, $area, $school, $rent, $petAllowed, $layout, $description, $image ?? 'images/property1.jpg', 'available']);
                header('Location: index.php');
                exit;
            }
        }
    }
}

$list = $pdo->query('SELECT * FROM properties ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
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
<div class="page-shell">
  <nav class="admin-links" aria-label="管理メニュー">
    <a href="../index.php">サイトを見る</a> ・ <a href="inquiries.php">お問い合わせ一覧</a> ・ <a href="../logout.php">ログアウト</a>
  </nav>

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
        <input name="area" maxlength="50" placeholder="例：横浜市" value="<?= h(input_string($_POST['area'] ?? '')) ?>">
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
      <label>家賃（月額・円）
        <input type="number" name="rent" min="1" step="1" required value="<?= h(input_string($_POST['rent'] ?? '')) ?>">
      </label>
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
    <h2>登録物件（<?= count($list) ?>件）</h2>
    <?php if (!$list): ?><p class="empty-state">登録された物件はありません。</p><?php endif; ?>
    <?php foreach ($list as $property): ?>
      <div class="admin-property-row">
        <img src="../<?= h($property['image'] ?: 'images/property1.jpg') ?>" alt="">
        <strong><?= h($property['name']) ?></strong>
        <span>家賃 <?= number_format((int)$property['rent']) ?>円</span>
        <span><?= h($property['layout']) ?>・ペット<?= (int)$property['pet_allowed'] === 1 ? '可' : '不可' ?>・<?= ['available'=>'募集中','reserved'=>'申込あり','rented'=>'契約済み'][$property['status']] ?? '募集中' ?></span>
        <a href="edit.php?id=<?= (int)$property['id'] ?>">編集</a>
        <form method="POST" onsubmit="return confirm('この物件を削除しますか？');">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
          <button type="submit" name="delete" value="<?= (int)$property['id'] ?>">削除</button>
        </form>
      </div>
    <?php endforeach; ?>
  </section>
</div>
</body>
</html>
