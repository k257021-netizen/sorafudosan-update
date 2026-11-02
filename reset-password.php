<?php
require 'db.php';

$token = input_string($_GET['token'] ?? $_POST['token'] ?? '');
$error = '';
$done = false;
$notificationWarning = false;
$customerId = 0;
$tokenId = 0;

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = $pdo->prepare("SELECT id, customer_id FROM customer_tokens WHERE purpose = 'reset' AND token_hash = ? AND used_at IS NULL AND expires_at > CURRENT_TIMESTAMP LIMIT 1");
    $stmt->execute([hash('sha256', $token)]);
    if ($row = $stmt->fetch()) {
        $tokenId = (int)$row['id'];
        $customerId = (int)$row['customer_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = input_string($_POST['password'] ?? '');
    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'ページの有効期限が切れました。';
    } elseif (!$customerId || !$tokenId) {
        $error = 'リンクが無効か、有効期限が切れています。';
    } elseif (!valid_password_length($password)) {
        $error = 'パスワードは8文字以上、UTF-8で72バイト以内で入力してください。';
    } elseif ($password !== input_string($_POST['password_confirm'] ?? '')) {
        $error = '確認用パスワードが一致しません。';
    } else {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT c.email, c.password_hash FROM customer_tokens t JOIN customers c ON c.id = t.customer_id WHERE t.id = ? AND t.customer_id = ? AND t.purpose = 'reset' AND t.token_hash = ? AND t.used_at IS NULL AND t.expires_at > CURRENT_TIMESTAMP FOR UPDATE");
        $stmt->execute([$tokenId, $customerId, hash('sha256', $token)]);
        $customer = $stmt->fetch();

        if (!$customer) {
            $pdo->rollBack();
            $error = 'リンクが無効か、有効期限が切れています。';
        } elseif (password_verify($password, $customer['password_hash'])) {
            $pdo->rollBack();
            $error = '新しいパスワードは現在のパスワードと異なるものにしてください。';
        } else {
            $stmt = $pdo->prepare('UPDATE customers SET password_hash = ?, password_version = password_version + 1 WHERE id = ?');
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $customerId]);
            $stmt = $pdo->prepare("UPDATE customer_tokens SET used_at = CURRENT_TIMESTAMP WHERE customer_id = ? AND purpose = 'reset' AND used_at IS NULL");
            $stmt->execute([$customerId]);
            log_customer_security_event($pdo, $customerId, 'password_reset_succeeded');
            $pdo->commit();

            $done = true;
            if (!send_app_mail(
                $customer['email'],
                'Sora Living パスワード変更のお知らせ',
                "アカウントのパスワードが変更されました。\nお心当たりがない場合は、すぐにパスワードを再設定し、サイトのお問い合わせ窓口までご連絡ください。"
            )) {
                log_customer_security_event($pdo, $customerId, 'password_notice_failed');
                error_log('Password reset notification email could not be sent.');
                $notificationWarning = true;
            }
        }
    }
}
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>新しいパスワード | Sora Living</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include ((basename(__DIR__) === 'admin' ? dirname(__DIR__) : __DIR__) . '/includes/site-brand.php'); ?>
<main class="page-shell">
    <section class="search-section">
        <h2>新しいパスワード</h2>
        <?php if ($done): ?>
            <p class="success-message" role="status">パスワードを変更しました。</p>
            <?php if ($notificationWarning): ?><p class="error-message" role="alert">変更は完了しましたが、確認メールを送信できませんでした。</p><?php endif; ?>
            <a href="member-login.php">ログイン</a>
        <?php elseif (!$customerId): ?>
            <p class="error-message" role="alert">リンクが無効か、有効期限が切れています。</p>
        <?php else: ?>
            <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
            <form method="post" class="contact-form" data-password-form>
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <p id="password-requirements">8文字以上。UTF-8で72バイト以内（半角英数字の場合は最大72文字）で入力してください。</p>
                <label for="password">新しいパスワード</label>
                <div class="password-field">
                    <input id="password" type="password" name="password" minlength="8" maxlength="72" required autocomplete="new-password" aria-describedby="password-requirements password-strength-text">
                    <button class="password-toggle" type="button" aria-controls="password" aria-pressed="false">表示</button>
                </div>
                <meter data-password-strength class="password-strength" min="0" max="4" value="0" aria-label="パスワード強度の目安"></meter>
                <p data-password-strength-text class="password-hint" aria-live="polite">パスワードを入力してください。</p>
                <label for="password_confirm">新しいパスワード（確認）</label>
                <div class="password-field">
                    <input id="password_confirm" type="password" name="password_confirm" minlength="8" maxlength="72" required autocomplete="new-password" aria-describedby="password-match">
                    <button class="password-toggle" type="button" aria-controls="password_confirm" aria-pressed="false">表示</button>
                </div>
                <p id="password-match" class="password-hint" data-password-match aria-live="polite"></p>
                <button type="submit">変更する</button>
            </form>
        <?php endif; ?>
    </section>
</main>
<script src="password-form.js" defer></script>
</body>
</html>
