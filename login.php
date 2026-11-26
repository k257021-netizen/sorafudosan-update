<?php
require 'db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'ページの有効期限が切れました。再読み込みして、もう一度お試しください。';
    } else {
        $username = trim(input_string($_POST['username'] ?? ''));
        $ipAddress = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: 'unknown';
        $userRateKey = hash('sha256', 'admin-user|' . strtolower($username) . '|' . $ipAddress);
        $ipRateKey = hash('sha256', 'admin-ip|' . $ipAddress);
        $pdo->exec('DELETE FROM auth_login_attempts WHERE attempted_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)');
        $userLimit = $pdo->prepare('SELECT COUNT(*) FROM auth_login_attempts WHERE rate_key = ? AND attempted_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)');
        $userLimit->execute([$userRateKey]);
        $ipLimit = $pdo->prepare('SELECT COUNT(*) FROM auth_login_attempts WHERE rate_key = ? AND attempted_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)');
        $ipLimit->execute([$ipRateKey]);

        if ((int)$userLimit->fetchColumn() >= 5 || (int)$ipLimit->fetchColumn() >= 20) {
            $error = 'ログイン試行が多すぎます。15分後にもう一度お試しください。';
        } else {
            $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify(input_string($_POST['password'] ?? ''), $user['password_hash'])) {
                $pdo->prepare('DELETE FROM auth_login_attempts WHERE rate_key = ?')->execute([$userRateKey]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                header('Location: admin/index.php');
                exit;
            }

            $attempt = $pdo->prepare('INSERT INTO auth_login_attempts (rate_key) VALUES (?)');
            $attempt->execute([$userRateKey]);
            $attempt->execute([$ipRateKey]);
            $error = 'ユーザー名またはパスワードが正しくありません。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>管理者ログイン | Sora Living</title>
  <link rel="stylesheet" href="style.css?v=20261102-7">
</head>
<body>
<?php include ((basename(__DIR__) === 'admin' ? dirname(__DIR__) : __DIR__) . '/includes/site-brand.php'); ?>
<div class="page-shell">
  <p><a href="index.php">← トップページへ戻る</a></p>
  <section class="search-section">
    <h2>管理者ログイン</h2>
    <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
    <form method="POST" class="contact-form">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <label>ユーザー名
        <input type="text" name="username" required maxlength="50" autocomplete="username">
      </label>
      <label>パスワード
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <button type="submit">ログイン</button>
    </form>
  </section>
</div>
</body>
</html>
