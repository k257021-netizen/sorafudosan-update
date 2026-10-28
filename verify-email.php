<?php
require 'db.php';
$token = input_string($_GET['token'] ?? '');
$message = '確認リンクが無効か、有効期限が切れています。';
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = $pdo->prepare("SELECT id, customer_id FROM customer_tokens WHERE purpose='verify' AND token_hash=? AND used_at IS NULL AND expires_at > CURRENT_TIMESTAMP LIMIT 1");
    $stmt->execute([hash('sha256', $token)]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE customers SET email_verified_at=CURRENT_TIMESTAMP WHERE id=?')->execute([(int)$row['customer_id']]);
        $pdo->prepare('UPDATE customer_tokens SET used_at=CURRENT_TIMESTAMP WHERE id=?')->execute([(int)$row['id']]);
        $pdo->commit();
        $message = 'メールアドレスを確認しました。ログインしてください。';
    }
}
?>
<!doctype html><html lang="ja"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>メール確認 | Sora Living</title><link rel="stylesheet" href="style.css"><body><main class="page-shell"><section class="search-section"><h2>メールアドレス確認</h2><p><?= h($message) ?></p><a href="member-login.php">ログインへ</a></section></main></body></html>
