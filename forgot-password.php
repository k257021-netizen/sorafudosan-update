<?php
require 'db.php';
$done = false;
$devLink = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_POST['csrf'] ?? '')) {
    $email = strtolower(trim(input_string($_POST['email'] ?? '')));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare('SELECT id FROM customers WHERE email = ? AND email_verified_at IS NOT NULL');
        $stmt->execute([$email]);
        if ($id = $stmt->fetchColumn()) {
            $token = issue_customer_token($pdo, (int)$id, 'reset');
            $link = site_url('reset-password.php?token=' . urlencode($token));
            if (app_environment() === 'development') {
                $devLink = $link;
            } else {
                send_app_mail($email, 'Sora Living パスワード再設定', "1時間以内に以下のリンクを開いてください:\n" . $link);
            }
        }
    }
    $done = true;
}