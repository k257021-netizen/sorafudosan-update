<?php
require 'db.php';

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

$rawPropertyId = input_string($_GET['property_id'] ?? $_POST['property_id'] ?? 0);
$propertyId = filter_var($rawPropertyId, FILTER_VALIDATE_INT);
if ($propertyId === false || $propertyId < 0) $propertyId = 0;
$property = null;
if ($propertyId > 0) {
    $stmt = $pdo->prepare('SELECT id, name FROM properties WHERE id = ?');
    $stmt->execute([$propertyId]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$property) {
        http_response_code(404);
        exit('対象の物件が見つかりませんでした。');
    }
}

$error = '';
$old = ['name' => '', 'email' => '', 'phone' => '', 'message' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $_) $old[$key] = trim(input_string($_POST[$key] ?? ''));

    $token = (string)($_POST['csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'], $token)) {
        $error = '送信内容を確認できませんでした。ページを再読み込みして、もう一度お試しください。';
    } elseif (empty($_POST['privacy_consent'])) {
        $error = 'プライバシーポリシーへの同意が必要です。';
    } elseif ($old['name'] === '' || $old['message'] === '') {
        $error = 'お名前とお問い合わせ内容を入力してください。';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'メールアドレスの形式をご確認ください。';
    } elseif (text_length($old['name']) > 100
        || strlen($old['email']) > 150 || strlen($old['phone']) > 30
        || text_length($old['message']) > 2000) {
        $error = '入力できる文字数を超えています。内容を短くしてお試しください。';
    } else {
        $stmt = $pdo->prepare('INSERT INTO inquiries (property_id, name, email, phone, message, privacy_accepted_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)');
        $stmt->execute([$property ? $property['id'] : null, $old['name'], $old['email'], $old['phone'], $old['message']]);
        send_app_mail($old['email'], 'お問い合わせを受け付けました', "お問い合わせを受け付けました。担当者が内容を確認してご連絡します。\nSora Living");
        $adminEmail = trim((string)getenv('SORA_ADMIN_EMAIL'));
        if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) send_app_mail($adminEmail, '新しいお問い合わせ', "名前: {$old['name']}\nメール: {$old['email']}\n電話: {$old['phone']}\n\n{$old['message']}");
        header('Location: contact.php?sent=1');
        exit;
    }
}
$sent = isset($_GET['sent']);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お問い合わせ | Sora Living</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-shell">
  <p><a href="index.php">← トップページへ戻る</a></p>
  <section class="search-section">
    <h2>お問い合わせ</h2>
    <?php if ($sent): ?>
      <p class="success-message">お問い合わせを受け付けました。内容を確認のうえ、担当者よりご連絡いたします。</p>
      <p><a href="index.php">トップページへ戻る</a></p>
    <?php else: ?>
      <?php if ($property): ?><p>お問い合わせ物件：<strong><?= h($property['name']) ?></strong></p><?php endif; ?>
      <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
      <form method="POST" class="contact-form">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <input type="hidden" name="property_id" value="<?= (int)$propertyId ?>">
        <label>お名前 <span aria-hidden="true">（必須）</span>
          <input type="text" name="name" value="<?= h($old['name']) ?>" required maxlength="100" autocomplete="name">
        </label>
        <label>メールアドレス <span aria-hidden="true">（必須）</span>
          <input type="email" name="email" value="<?= h($old['email']) ?>" required maxlength="150" autocomplete="email">
        </label>
        <label>電話番号（任意）
          <input type="tel" name="phone" value="<?= h($old['phone']) ?>" maxlength="30" autocomplete="tel">
        </label>
        <label>お問い合わせ内容 <span aria-hidden="true">（必須）</span>
          <textarea name="message" rows="6" required maxlength="2000"><?= h($old['message']) ?></textarea>
        </label>
        <label class="consent-check"><input type="checkbox" name="privacy_consent" value="1" required> <a href="privacy.php" target="_blank">プライバシーポリシー</a>に同意します</label>
        <button type="submit">送信する</button>
      </form>
    <?php endif; ?>
  </section>
</div>
</body>
</html>
