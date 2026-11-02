<?php
require 'db.php';
require 'includes/site-documents.php';
$document = site_document($pdo, 'terms');
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>利用規約 | Sora Living</title>
  <link rel="stylesheet" href="style.css?v=20261102-7">
</head>
<body>
<?php include __DIR__ . '/includes/site-brand.php'; ?>
<main class="page-shell">
  <section class="search-section">
    <h1>利用規約</h1>
    <?= render_site_document($document['content']) ?>
    <a href="index.php">トップページへ戻る</a>
  </section>
</main>
</body>
</html>
