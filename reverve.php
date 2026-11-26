<?php
require 'db.php';

$serviceAreas = ['鶴見区', '神奈川区', '西区', '中区', '南区', '港南区', '保土ケ谷区', '旭区', '磯子区', '金沢区', '港北区', '緑区', '青葉区', '都筑区', '戸塚区', '栄区', '泉区', '瀬谷区', '川崎区', '幸区', '中原区', '高津区', '宮前区', '多摩区', '麻生区', '横浜市', '川崎市'];

function reserve_redirect(int $propertyId, string $result): never {
    header('Location: property.php?id=' . $propertyId . '&reservation=' . rawurlencode($result));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}
if (empty($_SESSION['customer_id'])) {
    header('Location: member-login.php');
    exit;
}

$propertyId = filter_var(input_string($_POST['property_id'] ?? ''), FILTER_VALIDATE_INT);
if (!$propertyId || $propertyId < 1) {
    http_response_code(422);
    exit('物件を確認できませんでした。');
}
if (!csrf_valid($_POST['csrf'] ?? '')) {
    reserve_redirect($propertyId, 'error');
}

$customerId = (int)$_SESSION['customer_id'];
$action = input_string($_POST['action'] ?? '');

// 物件が存在し、対象エリア内であることを確認
$areaPlaceholders = implode(',', array_fill(0, count($serviceAreas), '?'));
$check = $pdo->prepare("SELECT id FROM properties WHERE id = ? AND area IN ($areaPlaceholders)");
$check->execute(array_merge([$propertyId], $serviceAreas));
if (!$check->fetchColumn()) {
    http_response_code(404);
    exit('物件が見つかりませんでした。');
}

try {
    $pdo->beginTransaction();

    if ($action === 'reserve') {
        // 申込中は物件を確保し、管理者の承認後に申込を確定する。
        $stmt = $pdo->prepare("UPDATE properties SET status = 'reserved' WHERE id = ? AND status = 'available'");
        $stmt->execute([$propertyId]);
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            reserve_redirect($propertyId, 'unavailable');
        }

        $pdo->prepare("INSERT INTO reservations (property_id, customer_id, status) VALUES (?, ?, 'pending')")
            ->execute([$propertyId, $customerId]);
        $pdo->commit();
        reserve_redirect($propertyId, 'pending');
    }

    if ($action === 'cancel') {
        // 本人の審査中または承認済みの申込のみキャンセル可能
        $stmt = $pdo->prepare("SELECT id FROM reservations WHERE property_id = ? AND customer_id = ? AND status IN ('pending', 'active') FOR UPDATE");
        $stmt->execute([$propertyId, $customerId]);
        $reservationId = $stmt->fetchColumn();
        if (!$reservationId) {
            $pdo->rollBack();
            reserve_redirect($propertyId, 'error');
        }

        $pdo->prepare("UPDATE reservations SET status = 'cancelled', cancelled_at = CURRENT_TIMESTAMP WHERE id = ?")
            ->execute([$reservationId]);
        $otherRequests = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE property_id = ? AND id <> ? AND status IN ('pending', 'active')");
        $otherRequests->execute([$propertyId, (int)$reservationId]);
        if ((int)$otherRequests->fetchColumn() === 0) {
            $pdo->prepare("UPDATE properties SET status = 'available' WHERE id = ? AND status = 'reserved'")
                ->execute([$propertyId]);
        }
        $pdo->commit();
        reserve_redirect($propertyId, 'cancelled');
    }

    $pdo->rollBack();
    reserve_redirect($propertyId, 'error');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Reservation failed: ' . $e->getMessage());
    reserve_redirect($propertyId, 'error');
}
