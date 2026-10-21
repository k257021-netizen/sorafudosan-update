<?php
require '../db.php';
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_valid($_POST['csrf'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && isset($_POST['read'])) {
            $pdo->prepare('UPDATE inquiries SET is_read = 1 WHERE id = ?')->execute([$id]);
        } elseif ($id > 0 && isset($_POST['save'])) {
            $status = input_string($_POST['status'] ?? 'new');
            if (in_array($status, ['new','in_progress','resolved'], true)) {
                $note = trim(input_string($_POST['admin_note'] ?? ''));
                if (text_length($note) <= 2000) $pdo->prepare('UPDATE inquiries SET status=?, admin_note=?, updated_at=CURRENT_TIMESTAMP WHERE id=?')->execute([$status, $note, $id]);
            }
        } elseif ($id > 0 && isset($_POST['delete'])) {
            $pdo->prepare('DELETE FROM inquiries WHERE id = ?')->execute([$id]);
        }
    }
    header('Location: inquiries.php');
    exit;
}

$statusFilter = input_string($_GET['status'] ?? 'all');
if (!in_array($statusFilter, ['all','new','in_progress','resolved'], true)) $statusFilter = 'all';
$sql = 'SELECT i.*, p.name AS property_name FROM inquiries i LEFT JOIN properties p ON p.id = i.property_id' . ($statusFilter === 'all' ? '' : ' WHERE i.status=?') . ' ORDER BY i.id DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($statusFilter === 'all' ? [] : [$statusFilter]);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
$unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM inquiries WHERE is_read=0')->fetchColumn();
$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お問い合わせ一覧 | Sora Living</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="page-shell">
  <p><a href="index.php">← 物件管理へ戻る</a> ・ <a href="../logout.php">ログアウト</a></p>
  <section class="search-section">
    <h2>お問い合わせ一覧（<?= count($list) ?>件）</h2>
    <p class="admin-notice-count">未読：<strong><?= $unreadCount ?></strong> 件</p>
    <form method="get" class="filter-row admin-filter"><label>対応状況<select name="status"><option value="all" <?=$statusFilter==='all'?'selected':''?>>すべて</option><option value="new" <?=$statusFilter==='new'?'selected':''?>>新着</option><option value="in_progress" <?=$statusFilter==='in_progress'?'selected':''?>>対応中</option><option value="resolved" <?=$statusFilter==='resolved'?'selected':''?>>完了</option></select></label><button>絞り込む</button><a href="inquiries.php">解除</a></form>
    <?php if (!$list): ?><p class="empty-state">お問い合わせはまだありません。</p><?php endif; ?>

    <?php foreach ($list as $inquiry): ?>
      <article class="inquiry<?= $inquiry['is_read'] ? '' : ' unread' ?>">
        <p>
          <?php if (!$inquiry['is_read']): ?><strong>未読</strong> ・ <?php endif; ?>
          <?= h($inquiry['created_at']) ?> ・ <?= h($inquiry['name']) ?> ・
          <a href="mailto:<?= h($inquiry['email']) ?>"><?= h($inquiry['email']) ?></a>
          <?php if ($inquiry['phone']): ?>・ <?= h($inquiry['phone']) ?><?php endif; ?>
        </p>
        <?php if ($inquiry['property_name']): ?><p>対象物件：<?= h($inquiry['property_name']) ?></p><?php endif; ?>
        <p><?= nl2br(h($inquiry['message'])) ?></p>
        <?php if (!empty($inquiry['admin_note'])): ?><p class="admin-note"><strong>管理メモ：</strong><?= nl2br(h($inquiry['admin_note'])) ?></p><?php endif; ?>
        <form method="POST" class="contact-form">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="id" value="<?= (int)$inquiry['id'] ?>">
          <label>対応状況<select name="status"><option value="new" <?=$inquiry['status']==='new'?'selected':''?>>新着</option><option value="in_progress" <?=$inquiry['status']==='in_progress'?'selected':''?>>対応中</option><option value="resolved" <?=$inquiry['status']==='resolved'?'selected':''?>>完了</option></select></label>
          <label>管理メモ<textarea name="admin_note" maxlength="2000" rows="3"><?=h($inquiry['admin_note'])?></textarea></label><button name="save" value="1">対応状況を保存</button>
        </form>
        <form method="POST" onsubmit="return confirm('この操作を実行しますか？');">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
          <input type="hidden" name="id" value="<?= (int)$inquiry['id'] ?>">
          <?php if (!$inquiry['is_read']): ?><button name="read" value="1">既読にする</button><?php endif; ?>
          <button name="delete" value="1">削除</button>
        </form>
      </article>
    <?php endforeach; ?>
  </section>
</div>
</body>
</html>
