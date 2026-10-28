<?php
require 'db.php';

if (empty($_SESSION['customer_id'])) {
    header('Location: member-login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT name, email FROM customers WHERE id = ?');
$stmt->execute([(int)$_SESSION['customer_id']]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$customer) {
    unset($_SESSION['customer_id'], $_SESSION['customer_name']);
    header('Location: member-login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>マイページ | Sora Living</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
  <p><a href="index.php">← トップページへ戻る</a></p>
  <section class="search-section account-section">
    <h2>マイページ</h2>
    <p><?= h($customer['name']) ?> さん、ログイン中です。</p>
    <p>メールアドレス：<?= h($customer['email']) ?></p>
    <p><a class="primary-button account-favorites" href="favorites.php">お気に入りを見る</a></p>
    <p><a href="change-password.php">パスワードを変更</a></p>
    <form method="POST" action="member-logout.php">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <button type="submit" class="secondary-button">ログアウト</button>
    </form>
  </section>
</div>
</body>
</html>
