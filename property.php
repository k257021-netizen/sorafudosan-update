<<<<<<< HEAD
<?php
require 'db.php';

$id = filter_var(input_string($_GET['id'] ?? ''), FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('物件が見つかりませんでした。');
}

$serviceAreas = ['鶴見区', '神奈川区', '西区', '中区', '南区', '港南区', '保土ケ谷区', '旭区', '磯子区', '金沢区', '港北区', '緑区', '青葉区', '都筑区', '戸塚区', '栄区', '泉区', '瀬谷区', '川崎区', '幸区', '中原区', '高津区', '宮前区', '多摩区', '麻生区', '横浜市', '川崎市'];
$stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ? AND area IN (' . implode(',', array_fill(0, count($serviceAreas), '?')) . ')');
$stmt->execute(array_merge([$id], $serviceAreas));
$property = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$property) {
    http_response_code(404);
    exit('物件が見つかりませんでした。');
}
$isCustomer = !empty($_SESSION['customer_id']);
$isFavorite = $isCustomer && in_array($id, customer_favorite_ids($pdo, (int)$_SESSION['customer_id']), true);
$favoriteCsrf = $isCustomer ? csrf_token() : '';
$galleryImages = [
    ['src' => $property['image'] ?: 'images/property1.jpg', 'label' => '登録されている物件写真', 'alt' => $property['name'] . 'の登録写真'],
    ['src' => 'images/unsplash-exterior.jpg', 'label' => '外観の参考写真', 'alt' => '集合住宅の外観参考写真', 'credit' => 'Photo: Anthony DELANOIX / Unsplash', 'source_url' => 'https://unsplash.com/photos/apartment-building-JTcLVcE80B8'],
    ['src' => 'images/unsplash-living.jpg', 'label' => 'リビング・キッチンの参考写真', 'alt' => 'リビングとキッチンの参考写真', 'credit' => 'Photo: Danny Greenberg / Unsplash', 'source_url' => 'https://unsplash.com/photos/an-interior-shot-shows-a-modern-kitchen-and-living-room-QbdgGRQOHdM'],
    ['src' => 'images/unsplash-open-plan.jpg', 'label' => 'オープンキッチンの参考写真', 'alt' => '明るいオープンキッチンの参考写真', 'credit' => 'Photo: Louie A / Unsplash', 'source_url' => 'https://unsplash.com/photos/a-modern-kitchen-and-living-space-are-shown-e5yEXmGWjKo'],
    ['src' => 'images/unsplash-bedroom.jpg', 'label' => '寝室の参考写真', 'alt' => 'モダンな寝室の参考写真', 'credit' => 'Photo: Lukáš Parničan / Unsplash', 'source_url' => 'https://unsplash.com/photos/a-modern-bedroom-with-a-bed-and-built-in-lighting-LZ3IQuhGbQE'],
    ['src' => 'images/unsplash-bathroom.jpg', 'label' => '浴室の参考写真', 'alt' => 'モダンな浴室の参考写真', 'credit' => 'Photo: Billy Jo Catbagan / Unsplash', 'source_url' => 'https://unsplash.com/photos/a-bathroom-with-a-mirror-and-a-sink-o9tSmffkg7w'],
    ['src' => 'images/unsplash-kitchen.jpg', 'label' => 'キッチンの参考写真', 'alt' => 'キッチンの参考写真', 'credit' => 'Photo: Y K / Unsplash', 'source_url' => 'https://unsplash.com/photos/kitchen-interior-bn4Ve3NjQ8g'],
    ['src' => 'images/floor-plan-1k.svg', 'label' => '1K間取りイメージ（実際の間取りではありません）', 'alt' => '玄関、キッチン、浴室、居室を描いた1K間取りのイメージ'],
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($property['name']) ?> | Sora Living</title>
  <link rel="stylesheet" href="style.css?v=4">
</head>
<body>
<?php include ((basename(__DIR__) === 'admin' ? dirname(__DIR__) : __DIR__) . '/includes/site-brand.php'); ?>
<div class="page-shell">
  <p><a href="index.php">← お部屋探しに戻る</a></p>
  <article class="search-section">
    <h2><?= h($property['name']) ?></h2>
    <p class="transaction-badge"><?= $property['transaction_type'] === 'sale' ? '売買物件' : '賃貸物件' ?></p>
    <section class="property-gallery" aria-label="物件写真">
      <div class="gallery-stage">
        <div class="gallery-track" data-gallery-track>
          <?php foreach ($galleryImages as $index => $image): ?>
            <figure class="gallery-slide<?= $index === 0 ? ' is-active' : '' ?>" data-gallery-slide="<?= $index ?>" aria-hidden="<?= $index === 0 ? 'false' : 'true' ?>">
              <img src="<?= h($image['src']) ?>" alt="<?= h($image['alt']) ?>" <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
              <figcaption><?= h($image['label']) ?><?php if (!empty($image['credit'])): ?> <a href="<?= h($image['source_url']) ?>" target="_blank" rel="noopener noreferrer"><?= h($image['credit']) ?></a><?php endif; ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
        <button class="gallery-arrow gallery-prev" type="button" aria-label="前の写真へ"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></button>
        <button class="gallery-arrow gallery-next" type="button" aria-label="次の写真へ"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>
      </div>
      <div class="gallery-meta"><span data-gallery-count aria-live="polite">1 / <?= count($galleryImages) ?></span><button class="gallery-toggle" type="button" data-gallery-toggle aria-pressed="false">自動再生を停止</button><span>一部は室内・間取りのイメージ画像です</span></div>
      <div class="gallery-thumbnails" aria-label="写真を選択">
        <?php foreach ($galleryImages as $index => $image): ?>
          <button class="gallery-thumb<?= $index === 0 ? ' is-active' : '' ?>" type="button" data-gallery-thumb="<?= $index ?>" aria-label="<?= h($image['label']) ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>">
            <img src="<?= h($image['src']) ?>" alt="" loading="lazy">
          </button>
        <?php endforeach; ?>
      </div>
    </section>
    <?php if ($property['station']): ?><p><strong>最寄り駅：</strong><?= h($property['station']) ?></p><?php endif; ?>
    <?php if ($property['area']): ?><p><strong>地域：</strong><?= h($property['area']) ?></p><?php endif; ?>
    <p><strong>間取り：</strong><?= h($property['layout']) ?></p>
    <?php if ($property['area_sqm'] !== null): ?><p><strong>専有面積：</strong><?= h($property['area_sqm']) ?>m²</p><?php endif; ?>
    <?php if ($property['floor_number'] !== null): ?><p><strong>所在階：</strong><?= (int)$property['floor_number'] ?>階</p><?php endif; ?>
    <?php if ($property['transaction_type'] === 'rent'): ?>
      <p class="price"><strong>家賃（月額）：</strong><?= number_format((int)$property['rent']) ?>円</p>
      <?php if ($property['management_fee'] !== null): ?><p><strong>管理費・共益費：</strong><?= number_format((int)$property['management_fee']) ?>円／月</p><?php endif; ?>
      <?php if ($property['deposit_amount'] !== null): ?><p><strong>敷金：</strong><?= number_format((int)$property['deposit_amount']) ?>円</p><?php endif; ?>
      <?php if ($property['key_money_amount'] !== null): ?><p><strong>礼金：</strong><?= number_format((int)$property['key_money_amount']) ?>円</p><?php endif; ?>
      <?php if ($property['initial_cost'] !== null): ?><p><strong>初期費用の目安：</strong><?= number_format((int)$property['initial_cost']) ?>円</p><?php endif; ?>
    <?php else: ?>
      <p class="price"><strong>販売価格：</strong><?= number_format((int)$property['sale_price']) ?>円</p>
      <?php if ($property['initial_cost'] !== null): ?><p><strong>諸費用の目安：</strong><?= number_format((int)$property['initial_cost']) ?>円</p><?php endif; ?>
    <?php endif; ?>
    <p><strong>ペット：</strong><?= (int)$property['pet_allowed'] === 1 ? '相談可' : '不可' ?></p>
    <p><strong>募集状況：</strong><?= ['available'=>'募集中','reserved'=>'申込あり','rented'=>'契約済み'][$property['status']] ?? '募集中' ?> ・ 更新 <?= h(substr((string)$property['updated_at'],0,10)) ?></p>
    <?php if (trim((string)$property['description']) !== ''): ?>
      <p><?= nl2br(h($property['description'])) ?></p>
    <?php endif; ?>
    <p><button type="button" class="fav-button" data-id="<?= (int)$property['id'] ?>" data-authenticated="<?= $isCustomer ? '1' : '0' ?>" data-favorite="<?= $isFavorite ? '1' : '0' ?>" data-csrf="<?= h($favoriteCsrf) ?>"></button></p>
    <?php if ($property['status'] !== 'rented'): ?><p><a class="contact-link" href="contact.php?property_id=<?= (int)$property['id'] ?>">この物件について問い合わせる</a></p><?php else: ?><p>こちらの物件は契約済みです。</p><?php endif; ?>
  </article>
</div>
<script src="favorites.js"></script>
<script src="property-gallery.js?v=5"></script>
</body>
</html>
=======
<?php
require 'db.php';

$id = filter_var(input_string($_GET['id'] ?? ''), FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('物件が見つかりませんでした。');
}

$serviceAreas = ['鶴見区', '神奈川区', '西区', '中区', '南区', '港南区', '保土ケ谷区', '旭区', '磯子区', '金沢区', '港北区', '緑区', '青葉区', '都筑区', '戸塚区', '栄区', '泉区', '瀬谷区', '川崎区', '幸区', '中原区', '高津区', '宮前区', '多摩区', '麻生区', '横浜市', '川崎市'];
$stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ? AND area IN (' . implode(',', array_fill(0, count($serviceAreas), '?')) . ')');
$stmt->execute(array_merge([$id], $serviceAreas));
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
    <?php if ($property['area_sqm'] !== null): ?><p><strong>専有面積：</strong><?= h($property['area_sqm']) ?>m²</p><?php endif; ?>
    <?php if ($property['floor_number'] !== null): ?><p><strong>所在階：</strong><?= (int)$property['floor_number'] ?>階</p><?php endif; ?>
    <?php if ($property['initial_cost'] !== null): ?><p><strong>初期費用の目安：</strong><?= number_format((int)$property['initial_cost']) ?>円</p><?php endif; ?>
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
>>>>>>> e6eade3b54b3300cc2f0ae874be96c5ac57f6c25
