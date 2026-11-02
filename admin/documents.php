<?php
require '../db.php';
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }
require '../includes/site-documents.php';

$documents = [
    'terms' => ['title' => '利用規約', 'page' => 'terms.php'],
    'privacy' => ['title' => 'プライバシーポリシー', 'page' => 'privacy.php'],
];
$documentKey = input_string($_GET['document'] ?? $_POST['document'] ?? 'terms');
if (!isset($documents[$documentKey])) {
    $documentKey = 'terms';
}

$error = '';
$saved = isset($_GET['saved']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim(input_string($_POST['content'] ?? ''));
    if (!csrf_valid($_POST['csrf'] ?? '')) {
        $error = 'ページの有効期限が切れました。再読み込みして、もう一度お試しください。';
    } elseif ($content === '' || text_length($content) > 30000) {
        $error = '本文を入力してください（30,000文字以内）。';
    } else {
        $stmt = $pdo->prepare('UPDATE site_documents SET content = ? WHERE document_key = ?');
        $stmt->execute([$content, $documentKey]);
        if ($stmt->rowCount() === 0) {
            $exists = $pdo->prepare('SELECT 1 FROM site_documents WHERE document_key = ?');
            $exists->execute([$documentKey]);
            if (!$exists->fetchColumn()) {
                throw new RuntimeException('The requested site document is not configured.');
            }
        }
        header('Location: documents.php?document=' . rawurlencode($documentKey) . '&saved=1');
        exit;
    }
}

$document = site_document($pdo, $documentKey);
$content = $_SERVER['REQUEST_METHOD'] === 'POST' && $error !== ''
    ? input_string($_POST['content'] ?? '')
    : $document['content'];
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>規約・ポリシー編集 | Sora Living</title>
  <link rel="stylesheet" href="../style.css?v=20261102-7">
</head>
<body>
<?php include __DIR__ . '/../includes/site-brand.php'; ?>
<div class="page-shell">
  <p><a href="index.php">← 物件管理へ戻る</a> ・ <a href="inquiries.php">お問い合わせ一覧</a> ・ <a href="../logout.php">ログアウト</a></p>
  <section class="search-section">
    <h1>規約・ポリシー編集</h1>
    <p>保存すると、サイトに表示される文章がすぐに更新されます。登録済みユーザーに再同意は求めません。</p>
    <?php if ($saved): ?><p class="success-message" role="status">変更を保存しました。</p><?php endif; ?>
    <?php if ($error): ?><p class="error-message" role="alert"><?= h($error) ?></p><?php endif; ?>
    <form method="get" class="filter-row admin-filter">
      <label>編集するページ
        <select name="document">
          <?php foreach ($documents as $key => $item): ?>
            <option value="<?= h($key) ?>" <?= $documentKey === $key ? 'selected' : '' ?>><?= h($item['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button type="submit">ページを選択</button>
      <a href="../<?= h($documents[$documentKey]['page']) ?>" target="_blank" rel="noopener">現在の公開ページを見る</a>
    </form>
    <form method="post" class="contact-form">
      <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
      <input type="hidden" name="document" value="<?= h($documentKey) ?>">
      <label><?= h($documents[$documentKey]['title']) ?>本文
        <textarea name="content" rows="20" required maxlength="30000"><?= h($content) ?></textarea>
      </label>
      <p class="admin-notice-count">見出しは行頭に「## 」を付けます。段落を分けるには空行を入れてください。HTMLタグは表示されず、文章として扱われます。</p>
      <button type="submit">変更を保存</button>
    </form>
  </section>
</div>
</body>
</html>
