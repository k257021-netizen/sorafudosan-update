<?php
require 'db.php';
$done = false; $devLink = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_POST['csrf'] ?? '')) {
    $email = strtolower(trim(input_string($_POST['email'] ?? '')));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt=$pdo->prepare('SELECT id FROM customers WHERE email=? AND email_verified_at IS NOT NULL'); $stmt->execute([$email]);
        if ($id=$stmt->fetchColumn()) {
            $token=issue_customer_token($pdo,(int)$id,'reset');
            $devLink=site_url('reset-password.php?token='.urlencode($token));
            if (app_environment() !== 'development') send_app_mail($email,'Sora Living パスワード再設定',"1時間以内に以下のリンクを開いてください:\n".$devLink);
        }
    }
    $done=true;
}
?>
<!doctype html><html lang="ja"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>パスワード再設定 | Sora Living</title><link rel="stylesheet" href="style.css"><body><main class="page-shell"><section class="search-section"><h2>パスワード再設定</h2><?php if($done): ?><p>該当するアカウントがある場合、再設定リンクを送信しました。</p><?php if($devLink): ?><p>開発用リンク: <a href="<?= h($devLink) ?>">パスワードを再設定</a></p><?php endif; ?><?php else: ?><form method="post" class="contact-form"><input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>"><label>登録メールアドレス<input type="email" name="email" required></label><button>再設定リンクを送る</button></form><?php endif; ?><a href="member-login.php">ログインへ戻る</a></section></main></body></html>
