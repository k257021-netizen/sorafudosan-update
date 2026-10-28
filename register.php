<?php
require 'db.php';

if (!empty($_SESSION['customer_id'])) {
    header('Location: account.php');
    exit;
}

$error = '';
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(input_string($_POST['name'] ?? ''));
    $email = strtolower(trim(input_string($_POST['email'] ?? '')));
    $password = input_string($_POST['password'] ?? '');
    $passwordConfirm = input_string($_POST['password_confirm'] ?? '');

    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'ページの有効期限が切れました。再読み込みして、もう一度お試しください。';
    } elseif ($name === '' || text_length($name) > 100) {
        $error = 'お名前を100文字以内で入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $error = '有効なメールアドレスを入力してください。';
    } elseif (strlen($password) < 8 || strlen($password) > 72) {
        $error = 'パスワードは8〜72文字で入力してください。';
    } elseif ($password !== $passwordConfirm) {
        $error = '確認用パスワードが一致しません。';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM customers WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'このメールアドレスはすでに登録されています。';
        } else {
            if (empty($_POST['accept_terms'])) {
                $error = '利用規約とプライバシーポリシーへの同意が必要です。';
            } else try {
                $verifiedAt = app_environment() === 'development' ? date('Y-m-d H:i:s') : null;
                $stmt = $pdo->prepare('INSERT INTO customers (name, email, password_hash, email_verified_at, terms_accepted_at, privacy_accepted_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $verifiedAt]);
                $customerId = (int)$pdo->lastInsertId();
                if ($verifiedAt === null) {
                    $token = issue_customer_token($pdo, $customerId, 'verify');
                    $mailBody = "以下のリンクからメールアドレスを確認してください（24時間有効です）。\n" . site_url('verify-email.php?token=' . urlencode($token));
                    if (!send_app_mail($email, 'Sora Living メール確認', $mailBody)) {
                        $pdo->prepare('DELETE FROM customers WHERE id = ?')->execute([$customerId]);
                        $error = '確認メールを送信できませんでした。メール送信設定を確認して、もう一度お試しください。';
                    } else {
                        header('Location: member-login.php?verification_sent=1');
                        exit;
                    }
                } else {
                    session_regenerate_id(true);
                    $_SESSION['customer_id'] = $customerId;
                    $_SESSION['customer_name'] = $name;
                    sync_cookie_favorites($pdo, $customerId);
                    header('Location: account.php');
                    exit;
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $error = 'このメールアドレスはすでに登録されています。';
                } else {
                    throw $e;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>新規登録 | Sora Living</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
  <p><a href="index.php">← トップページへ戻る</a></p>
  <section class="search-section account-section">
    <h2>新規登録</h2>
    <p class="account-intro">お気に入りのお部屋探しに使うアカウントを作成します。</p>
    <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
    <form method="POST" class="contact-form">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <label>お名前
        <input type="text" name="name" value="<?= h($name) ?>" required maxlength="100" autocomplete="name">
      </label>
      <label>メールアドレス
        <input type="email" name="email" value="<?= h($email) ?>" required maxlength="150" autocomplete="email">
      </label>
      <label>パスワード（8〜72文字）
        <input type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
      </label>
      <label>パスワード（確認）
        <input type="password" name="password_confirm" required minlength="8" maxlength="72" autocomplete="new-password">
      </label>
      <label class="consent-check"><input type="checkbox" name="accept_terms" value="1" required> <a href="terms.php" target="_blank">利用規約</a>と<a href="privacy.php" target="_blank">プライバシーポリシー</a>に同意します</label>
      <button type="submit">アカウントを作成</button>
    </form>
    <p class="account-switch">登録済みの方は<a href="member-login.php">ログイン</a></p>
  </section>
</div>
</body>
</html>
