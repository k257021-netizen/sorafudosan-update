<?php
require 'db.php';

if (empty($_SESSION['customer_id'])) {
    header('Location: member-login.php');
    exit;
}

$error = '';
$notificationWarning = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'ページの有効期限が切れました。';
    } else {
        $currentPassword = input_string($_POST['current_password'] ?? '');
        $newPassword = input_string($_POST['password'] ?? '');

        if (!valid_password_length($newPassword)) {
            $error = '新しいパスワードは8文字以上、UTF-8で72バイト以内で入力してください。';
        } elseif ($newPassword !== input_string($_POST['password_confirm'] ?? '')) {
            $error = '確認用パスワードが一致しません。';
        } elseif ($newPassword === $currentPassword) {
            $error = '新しいパスワードは現在のパスワードと異なるものにしてください。';
        } else {
            $customerId = (int)$_SESSION['customer_id'];
            $ipAddress = filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP);
            $rateKey = hash('sha256', 'password-change|' . $customerId . '|' . ($ipAddress ?: 'unknown'));
            $pdo->exec('DELETE FROM auth_login_attempts WHERE attempted_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)');
            $pdo->exec('DELETE FROM auth_security_events WHERE created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 180 DAY)');
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM auth_login_attempts WHERE rate_key = ? AND attempted_at > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 15 MINUTE)');
            $stmt->execute([$rateKey]);

            if ((int)$stmt->fetchColumn() >= 5) {
                log_customer_security_event($pdo, $customerId, 'password_change_rate_limited');
                $error = '試行回数が多すぎます。15分後にもう一度お試しください。';
            } else {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('SELECT email, password_hash, password_version FROM customers WHERE id = ? FOR UPDATE');
                $stmt->execute([$customerId]);
                $customer = $stmt->fetch();

                if (!$customer) {
                    $pdo->rollBack();
                    unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_password_version']);
                    header('Location: member-login.php');
                    exit;
                }

                if (!password_verify($currentPassword, $customer['password_hash'])) {
                    $pdo->rollBack();
                    $stmt = $pdo->prepare('INSERT INTO auth_login_attempts (rate_key) VALUES (?)');
                    $stmt->execute([$rateKey]);
                    log_customer_security_event($pdo, $customerId, 'password_change_failed');
                    $error = '現在のパスワードが正しくありません。';
                } else {
                    $stmt = $pdo->prepare('UPDATE customers SET password_hash = ?, password_version = password_version + 1 WHERE id = ?');
                    $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $customerId]);
                    $stmt = $pdo->prepare("UPDATE customer_tokens SET used_at = CURRENT_TIMESTAMP WHERE customer_id = ? AND purpose = 'reset' AND used_at IS NULL");
                    $stmt->execute([$customerId]);
                    $pdo->prepare('DELETE FROM auth_login_attempts WHERE rate_key = ?')->execute([$rateKey]);
                    log_customer_security_event($pdo, $customerId, 'password_change_succeeded');
                    $pdo->commit();

                    $notificationWarning = !send_app_mail(
                        $customer['email'],
                        'Sora Living パスワード変更のお知らせ',
                        "アカウントのパスワードが変更されました。\nお心当たりがない場合は、すぐにパスワードを再設定し、サイトのお問い合わせ窓口までご連絡ください。"
                    );
                    if ($notificationWarning) {
                        log_customer_security_event($pdo, $customerId, 'password_notice_failed');
                        error_log('Password change notification email could not be sent.');
                    }

                    session_regenerate_id(true);
                    unset($_SESSION['_csrf']);
                    $_SESSION['customer_password_version'] = (int)$customer['password_version'] + 1;
                    $_SESSION['password_changed'] = true;
                    $_SESSION['password_notification_warning'] = $notificationWarning;
                    header('Location: change-password.php', true, 303);
                    exit;
                }
            }
        }
    }
}
$passwordChanged = !empty($_SESSION['password_changed']);
$passwordNotificationWarning = !empty($_SESSION['password_notification_warning']);
unset($_SESSION['password_changed']);
unset($_SESSION['password_notification_warning']);
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>パスワード変更 | Sora Living</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include ((basename(__DIR__) === 'admin' ? dirname(__DIR__) : __DIR__) . '/includes/site-brand.php'); ?>
<main class="page-shell">
    <section class="search-section">
        <h2>パスワード変更</h2>
        <?php if ($passwordChanged): ?><p class="success-message" role="status">パスワードを変更しました。</p><?php endif; ?>
        <?php if ($passwordChanged && $passwordNotificationWarning): ?><p class="error-message" role="alert">変更は完了しましたが、確認メールを送信できませんでした。</p><?php endif; ?>
        <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
        <form method="post" class="contact-form" data-password-form>
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            <p id="password-requirements">8文字以上。UTF-8で72バイト以内（半角英数字の場合は最大72文字）で入力してください。</p>
            <label for="current_password">現在のパスワード</label>
            <div class="password-field">
                <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                <button class="password-toggle" type="button" aria-controls="current_password" aria-pressed="false">表示</button>
            </div>
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
        <a href="account.php">マイページへ</a>
    </section>
</main>
<script src="password-form.js" defer></script>
</body>
</html>
