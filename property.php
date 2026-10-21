<?php
require 'db.php';

$id = filter_var(input_string($_GET['id'] ?? ''), FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('物件が見つかりませんでした。');
}

$stmt = $pdo->prepare('SELECT * FROM properties WHERE id = :id');
$stmt->execute([':id' => $id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$property) {
    http_response_code(404);
    exit('物件が見つかりませんでした。');
}
$isCustomer = !empty($_SESSION['customer_id']);
$isFavorite = $isCustomer && in_array($id, customer_favorite_ids($pdo, (int)$_SESSION['customer_id']), true);
$favoriteCsrf = $isCustomer ? csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($property['name']) ?> | Sora Living</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
  <p><a href="index.php">← お部屋探しに戻る</a></p>
  <article class="search-section">
    <h2><?= h($property['name']) ?></h2>
    <img src="<?= h($property['image'] ?: 'images/property1.jpg') ?>" alt="<?= h($property['name']) ?>の物件写真" style="display:block;width:min(100%,760px);max-height:480px;object-fit:cover;margin:0 auto 20px;border-radius:16px;">
    <?php if ($property['station']): ?><p><strong>最寄り駅：</strong><?= h($property['station']) ?></p><?php endif; ?>
    <?php if ($property['area']): ?><p><strong>地域：</strong><?= h($property['area']) ?></p><?php endif; ?>
    <p><strong>間取り：</strong><?= h($property['layout']) ?></p>
    <p><strong>ペット：</strong><?= (int)$property['pet_allowed'] === 1 ? '相談可' : '不可' ?></p>
    <p class="price">家賃 <?= number_format((int)$property['rent']) ?>円</p>
    <p><strong>募集状況：</strong><?= ['available'=>'募集中','reserved'=>'申込あり','rented'=>'契約済み'][$property['status']] ?? '募集中' ?> ・ 更新 <?= h(substr((string)$property['updated_at'],0,10)) ?></p>
    <?php if (trim((string)$property['description']) !== ''): ?>
      <p><?= nl2br(h($property['description'])) ?></p>
    <?php endif; ?>
    <p><button type="button" class="fav-button" data-id="<?= (int)$property['id'] ?>" data-authenticated="<?= $isCustomer ? '1' : '0' ?>" data-favorite="<?= $isFavorite ? '1' : '0' ?>" data-csrf="<?= h($favoriteCsrf) ?>"></button></p>
    <?php if ($property['status'] !== 'rented'): ?><p><a class="contact-link" href="contact.php?property_id=<?= (int)$property['id'] ?>">この物件について問い合わせる</a></p><?php else: ?><p>こちらの物件は契約済みです。</p><?php endif; ?>
  </article>
</div>
<script src="favorites.js"></script>
</body>
</html>
