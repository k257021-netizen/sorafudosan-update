<?php
require 'db.php';
header('Content-Type: application/json; charset=UTF-8');

function favorite_response(int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') favorite_response(405, ['ok' => false, 'message' => 'POSTが必要です。']);
if (empty($_SESSION['customer_id'])) favorite_response(401, ['ok' => false, 'message' => 'ログインしてください。']);
if (!csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) favorite_response(403, ['ok' => false, 'message' => 'ページを再読み込みしてください。']);

$payload = json_decode(file_get_contents('php://input'), true);
$propertyId = filter_var($payload['property_id'] ?? null, FILTER_VALIDATE_INT);
if (!$propertyId || $propertyId < 1) favorite_response(422, ['ok' => false, 'message' => '物件を確認できませんでした。']);

$stmt = $pdo->prepare('SELECT id FROM properties WHERE id = ?');
$stmt->execute([$propertyId]);
if (!$stmt->fetchColumn()) favorite_response(404, ['ok' => false, 'message' => '物件が見つかりませんでした。']);

$customerId = (int)$_SESSION['customer_id'];
$stmt = $pdo->prepare('SELECT 1 FROM customer_favorites WHERE customer_id = ? AND property_id = ?');
$stmt->execute([$customerId, $propertyId]);
$isFavorite = (bool)$stmt->fetchColumn();
if ($isFavorite) {
    $pdo->prepare('DELETE FROM customer_favorites WHERE customer_id = ? AND property_id = ?')->execute([$customerId, $propertyId]);
    favorite_response(200, ['ok' => true, 'favorite' => false]);
}

$pdo->prepare('INSERT INTO customer_favorites (customer_id, property_id) VALUES (?, ?)')->execute([$customerId, $propertyId]);
favorite_response(200, ['ok' => true, 'favorite' => true]);
