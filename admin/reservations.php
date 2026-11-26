<?php
require '../db.php';
if (empty($_SESSION['user_id'])) { header('Location: ../login.php'); exit; }

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var(input_string($_POST['id'] ?? ''), FILTER_VALIDATE_INT);
    $decision = input_string($_POST['decision'] ?? '');
    $note = trim(input_string($_POST['admin_note'] ?? ''));
    if (!csrf_valid($_POST['csrf'] ?? '') || !$id || $id < 1 || !in_array($decision, ['approve', 'reject'], true)) {
        $notice = 'invalid';
    } elseif (text_length($note) > 2000) {
        $notice = 'note';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT r.id, r.property_id, r.status, c.name AS customer_name, c.email, p.name AS property_name, p.status AS property_status
                FROM reservations r
                JOIN customers c ON c.id = r.customer_id
                JOIN properties p ON p.id = r.property_id
                WHERE r.id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$reservation || $reservation['status'] !== 'pending' || $reservation['property_status'] === 'rented') {
                $pdo->rollBack();
                $notice = 'unavailable';
            } else {
                $nextStatus = $decision === 'approve' ? 'active' : 'rejected';
                $pdo->prepare('UPDATE reservations SET status = ?, admin_note = ?, decided_at = CURRENT_TIMESTAMP WHERE id = ? AND status = \'pending\'')
                    ->execute([$nextStatus, $note !== '' ? $note : null, $id]);
                if ($decision === 'approve') {
                    $pdo->prepare("UPDATE properties SET status = 'reserved' WHERE id = ? AND status = 'available'")
                        ->execute([(int)$reservation['property_id']]);
                } else {
                    $open = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE property_id = ? AND id <> ? AND status IN ('pending', 'active')");
                    $open->execute([(int)$reservation['property_id'], $id]);
                    if ((int)$open->fetchColumn() === 0) {
                        $pdo->prepare("UPDATE properties SET status = 'available' WHERE id = ? AND status = 'reserved'")
                            ->execute([(int)$reservation['property_id']]);
                    }
                }
                $pdo->commit();
                $mailSent = reservation_decision_mail(
                    (string)$reservation['email'],
                    (string)$reservation['customer_name'],
                    (string)$reservation['property_name'],
                    $nextStatus
                );
                $notice = $mailSent ? $nextStatus : $nextStatus . '_nomail';
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('Reservation review failed: ' . $e->getMessage());
            $notice = 'error';
        }
    }
    $filter = input_string($_POST['filter'] ?? 'pending');
    header('Location: reservations.php?status=' . rawurlencode($filter) . '&notice=' . rawurlencode($notice));
    exit;
}

function reservation_decision_mail(string $email, string $name, string $propertyName, string $status): bool {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    if ($status === 'active') {
        $subject = 'Sora Living お申込みを承認しました';
        $body = $name . " 様\n\n「" . $propertyName . "」へのお申込みを承認しました。担当者からご連絡します。\nSora Living";
    } else {
        $subject = 'Sora Living お申込みについて';
        $body = $name . " 様\n\n「" . $propertyName . "」へのお申込みは見送られました。他の物件もご覧ください。\nSora Living";
    }
    return send_app_mail($email, $subject, $body);
}

$statusFilter = input_string($_GET['status'] ?? 'pending');
if (!in_array($statusFilter, ['pending', 'active', 'rejected', 'cancelled', 'all'], true)) $statusFilter = 'pending';
$sql = 'SELECT r.*, c.name AS customer_name, c.email, p.name AS property_name, p.status AS property_status
    FROM reservations r
    JOIN customers c ON c.id = r.customer_id
    JOIN properties p ON p.id = r.property_id';
if ($statusFilter !== 'all') $sql .= ' WHERE r.status = ?';
$sql .= ' ORDER BY r.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($statusFilter === 'all' ? [] : [$statusFilter]);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();
$csrf = csrf_token();
$notice = input_string($_GET['notice'] ?? '');
$noticeText = [
    'active' => '申込を承認し、お客様へメールを送信しました。',
    'active_nomail' => '申込を承認しました。メール送信の設定がないため、お客様への通知は送られていません。',
    'rejected' => '申込を見送り、お客様へメールを送信しました。',
    'rejected_nomail' => '申込を見送りました。メール送信の設定がないため、お客様への通知は送られていません。',
    'unavailable' => 'この申込はすでに処理済みか、物件が契約済みです。',
    'note' => '管理メモは2,000文字以内で入力してください。',
    'invalid' => '操作を確認できませんでした。ページを再読み込みしてください。',
    'error' => '処理できませんでした。もう一度お試しください。',
][$notice] ?? '';
$statusLabels = ['pending' => '確認中', 'active' => '承認済み', 'rejected' => '見送り', 'cancelled' => '取消'];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>申込確認 | Sora Living</title>
  <link rel="stylesheet" href="../style.css?v=20261126-11">
</head>
<body>
<?php include ((basename(__DIR__) === 'admin' ? dirname(__DIR__) : __DIR__) . '/includes/site-brand.php'); ?>
<div class="page-shell">
  <p><a href="index.php">← 物件管理へ戻る</a> ・ <a href="inquiries.php">お問い合わせ一覧</a> ・ <a href="documents.php">規約・ポリシー編集</a> ・ <a href="../logout.php">ログアウト</a></p>
  <section class="search-section">
    <h2>申込確認（<?= count($list) ?>件）</h2>
    <p class="admin-notice-count">確認待ち：<strong><?= $pendingCount ?></strong> 件</p>
    <?php if ($noticeText !== ''): ?><p class="<?= str_contains($notice, 'nomail') || in_array($notice, ['unavailable', 'note', 'invalid', 'error'], true) ? 'error-message' : 'success-message' ?>" role="status"><?= h($noticeText) ?></p><?php endif; ?>
    <form method="get" class="filter-row admin-filter">
      <label>状態
        <select name="status">
          <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>確認中</option>
          <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>承認済み</option>
          <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>見送り</option>
          <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>取消</option>
          <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>すべて</option>
        </select>
      </label>
      <button>絞り込む</button><a href="reservations.php">解除</a>
    </form>
    <?php if (!$list): ?><p class="empty-state">該当する申込はありません。</p><?php endif; ?>
    <?php foreach ($list as $reservation): ?>
      <article class="inquiry">
        <p><?= h($reservation['created_at']) ?> ・ <?= h($statusLabels[$reservation['status']] ?? $reservation['status']) ?></p>
        <p>物件：<a href="../property.php?id=<?= (int)$reservation['property_id'] ?>"><?= h($reservation['property_name']) ?></a></p>
        <p><?= h($reservation['customer_name']) ?> ・ <a href="mailto:<?= h($reservation['email']) ?>"><?= h($reservation['email']) ?></a></p>
        <?php if (!empty($reservation['admin_note'])): ?><p class="admin-note"><strong>管理メモ：</strong><?= nl2br(h($reservation['admin_note'])) ?></p><?php endif; ?>
        <?php if ($reservation['status'] === 'pending'): ?>
          <form method="POST" class="contact-form">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int)$reservation['id'] ?>">
            <input type="hidden" name="filter" value="<?= h($statusFilter) ?>">
            <label>管理メモ（任意）<textarea name="admin_note" maxlength="2000" rows="3"></textarea></label>
            <button type="submit" name="decision" value="approve">承認する</button>
            <button type="submit" name="decision" value="reject">見送る</button>
          </form>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </section>
</div>
</body>
</html>
