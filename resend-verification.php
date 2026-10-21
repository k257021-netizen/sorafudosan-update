<?php
require 'db.php';
$email = strtolower(trim(input_string($_GET['email'] ?? $_POST['email'] ?? '')));
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_POST['csrf'] ?? '') && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $stmt = $pdo->prepare('SELECT id FROM customers WHERE email=? AND email_verified_at IS NULL');
    $stmt->execute([$email]);
    if ($id = $stmt->fetchColumn()) {
        $token = issue_customer_token($pdo, (int)$id, 'verify');
        $sent = send_app_mail($email, 'Sora Living メール確認', "確認リンク（24時間有効）:\n" . site_url('verify-email.php?token=' . urlencode($token)));
    }
    $done = true;
}
?>
<!doctype html><html lang="ja"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>確認メール再送 | Sora Living</title><link rel="stylesheet" href="style.css"><body><main class="page-shell"><section class="search-section"><h2>確認メール再送</h2><?php if (!empty($done)): ?><p>該当する未確認アカウントがある場合、確認メールを送信しました。</p><?php else: ?><form method="post" class="contact-form"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><label>メールアドレス<input type="email" name="email" required value="<?= h($email) ?>"></label><button>再送する</button></form><?php endif; ?><a href="member-login.php">ログインへ戻る</a></section></main></body></html>
