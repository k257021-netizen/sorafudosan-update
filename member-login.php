<?php
require 'db.php';

if (!empty($_SESSION['customer_id'])) {
    header('Location: account.php');
    exit;
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim(input_string($_POST['email'] ?? '')));
    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'ページの有効期限が切れました。再読み込みして、もう一度お試しください。';
    } else {
        $rateKey = hash('sha256', strtolower($email) . '|' . (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $pdo->exec('DELETE FROM auth_login_attempts WHERE attempted_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)');
        $limit = $pdo->prepare('SELECT COUNT(*) FROM auth_login_attempts WHERE rate_key=? AND attempted_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)');
        $limit->execute([$rateKey]);
        $blocked = (int)$limit->fetchColumn() >= 5;
        $stmt = $pdo->prepare('SELECT id, name, password_hash, email_verified_at FROM customers WHERE email = ?');
        $stmt->execute([$email]);
        $customer = $blocked ? false : $stmt->fetch(PDO::FETCH_ASSOC);
        if ($customer && password_verify(input_string($_POST['password'] ?? ''), $customer['password_hash'])) {
            $pdo->prepare('DELETE FROM auth_login_attempts WHERE rate_key=?')->execute([$rateKey]);
            if (empty($customer['email_verified_at'])) {
                $error = 'メールアドレスの確認が必要です。下のリンクから確認メールを再送してください。';
            } else {
                session_regenerate_id(true);
                $_SESSION['customer_id'] = (int)$customer['id'];
                $_SESSION['customer_name'] = $customer['name'];
                sync_cookie_favorites($pdo, (int)$customer['id']);
                header('Location: index.php');
                exit;
            }
        }
        elseif (!$blocked) { $pdo->prepare('INSERT INTO auth_login_attempts (rate_key) VALUES (?)')->execute([$rateKey]); }
        if ($blocked) $error = 'ログイン試行が多すぎます。15分後にもう一度お試しください。';
        elseif ($error === '') $error = 'メールアドレスまたはパスワードが正しくありません。';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン | Sora Living</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
  <p><a href="index.php">← トップページへ戻る</a></p>
  <section class="search-section account-section">
    <h2>ログイン</h2>
    <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
    <?php if ($error && str_contains($error, '確認が必要')): ?><p><a href="resend-verification.php?email=<?= h(rawurlencode($email)) ?>">確認メールを再送する</a></p><?php endif; ?>
    <form method="POST" class="contact-form">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <label>メールアドレス
        <input type="email" name="email" value="<?= h($email) ?>" required maxlength="150" autocomplete="email">
      </label>
      <label>パスワード
        <input type="password" name="password" required autocomplete="current-password">
      </label>
      <button type="submit">ログイン</button>
    </form>
    <?php if (isset($_GET['verification_sent'])): ?><p class="success-message">確認メールを送信しました。メール内のリンクを開いてください。</p><?php endif; ?>
    <p><a href="forgot-password.php">パスワードをお忘れですか？</a></p>
    <p class="account-switch">はじめての方は<a href="register.php">新規登録</a></p>
  </section>
</div>
</body>
</html>
