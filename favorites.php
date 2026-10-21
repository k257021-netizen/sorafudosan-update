<?php
require 'db.php';

$list = [];
$isCustomer = !empty($_SESSION['customer_id']);
$csrf = csrf_token();
if ($isCustomer) {
    $stmt = $pdo->prepare('SELECT p.id, p.name, p.station, p.area, p.rent, p.image, p.description, p.pet_allowed, p.layout, p.area_sqm, p.floor_number, p.initial_cost, p.status, p.updated_at FROM customer_favorites f JOIN properties p ON p.id = f.property_id WHERE f.customer_id = ? ORDER BY f.created_at DESC');
    $stmt->execute([(int)$_SESSION['customer_id']]);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $rawIds = array_map('intval', explode(',', (string)($_COOKIE['favorites'] ?? '')));
    $ids = array_slice(array_values(array_unique(array_filter($rawIds, static fn($id) => $id > 0))), 0, 100);
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, name, station, area, rent, image, description, pet_allowed, layout, area_sqm, floor_number, initial_cost, status, updated_at FROM properties WHERE id IN ($placeholders) ORDER BY rent ASC");
        $stmt->execute($ids);
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お気に入り | Sora Living</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
  <p><a href="index.php">← トップページへ戻る</a></p>
  <section class="search-section">
    <h2>お気に入り（<span data-favorites-count><?= count($list) ?></span>件）</h2>
    <div id="property-list" data-favorites-page>
      <?php if (!$list): ?><p class="empty-state">お気に入りに登録した物件はありません。</p><?php endif; ?>
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
          <?php if ($p['area_sqm'] !== null): ?><p>専有面積：<?= h($p['area_sqm']) ?>m²</p><?php endif; ?><?php if ($p['floor_number'] !== null): ?><p>所在階：<?= (int)$p['floor_number'] ?>階</p><?php endif; ?><?php if ($p['initial_cost'] !== null): ?><p>初期費用の目安：<?= number_format((int)$p['initial_cost']) ?>円</p><?php endif; ?>
          <p>募集状況：<?= ['available'=>'募集中','reserved'=>'申込あり','rented'=>'契約済み'][$p['status']] ?? '募集中' ?></p>
          <p class="price">家賃 <?= number_format((int)$p['rent']) ?>円</p>
          <button type="button" class="fav-button" data-id="<?= (int)$p['id'] ?>" data-authenticated="<?= $isCustomer ? '1' : '0' ?>" data-favorite="<?= $isCustomer ? '1' : '0' ?>" data-csrf="<?= $isCustomer ? h($csrf) : '' ?>"></button>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</div>
<script src="favorites.js"></script>
</body>
</html>
