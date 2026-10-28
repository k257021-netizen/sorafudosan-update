<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_POST['csrf'] ?? '')) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
    }
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログアウト | Sora Living</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
  <section class="search-section">
    <h2>ログアウトしますか？</h2>
    <form method="POST" class="contact-form">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <button type="submit">ログアウト</button>
      <a href="admin/index.php">管理画面に戻る</a>
    </form>
  </section>
</div>
</body>
</html>
