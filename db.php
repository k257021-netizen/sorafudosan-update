<?php
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$dbHost = getenv('SORA_DB_HOST') ?: 'localhost';
$dbName = getenv('SORA_DB_NAME') ?: 'sora';
$dbUser = getenv('SORA_DB_USER') ?: 'root';
$dbPassword = getenv('SORA_DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPassword, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function input_string($value): string {
    return is_string($value) || is_numeric($value) ? (string)$value : '';
}

function text_length(string $value): int {
    if (function_exists('mb_strlen')) return mb_strlen($value, 'UTF-8');
    $length = preg_match_all('/./us', $value, $matches);
    return $length === false ? strlen($value) : $length;
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}

function csrf_valid($token): bool {
    return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], (string)$token);
}

function customer_favorite_ids(PDO $pdo, int $customerId): array {
    $stmt = $pdo->prepare('SELECT property_id FROM customer_favorites WHERE customer_id = ? ORDER BY created_at DESC');
    $stmt->execute([$customerId]);
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

function sync_cookie_favorites(PDO $pdo, int $customerId): void {
    $rawIds = array_map('intval', explode(',', (string)($_COOKIE['favorites'] ?? '')));
    $ids = array_slice(array_values(array_unique(array_filter($rawIds, static fn($id) => $id > 0))), 0, 100);
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id FROM properties WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $insert = $pdo->prepare('INSERT IGNORE INTO customer_favorites (customer_id, property_id) VALUES (?, ?)');
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $propertyId) {
            $insert->execute([$customerId, (int)$propertyId]);
        }
    }
    if (isset($_COOKIE['favorites'])) {
        $secure = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
        setcookie('favorites', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
        unset($_COOKIE['favorites']);
    }
}

function app_environment(): string {
    return strtolower((string)(getenv('SORA_APP_ENV') ?: 'development'));
}

function send_app_mail(string $to, string $subject, string $body): bool {
    $from = trim((string)getenv('SORA_MAIL_FROM'));
    if ($from === '' || !filter_var($from, FILTER_VALIDATE_EMAIL)) return false;
    $encodedSubject = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8') : $subject;
    return mail($to, $encodedSubject, $body, "From: {$from}\r\nContent-Type: text/plain; charset=UTF-8");
}

function issue_customer_token(PDO $pdo, int $customerId, string $purpose): string {
    $token = bin2hex(random_bytes(32));
    $hours = $purpose === 'verify' ? 24 : 1;
    $pdo->prepare('UPDATE customer_tokens SET used_at = CURRENT_TIMESTAMP WHERE customer_id = ? AND purpose = ? AND used_at IS NULL')->execute([$customerId, $purpose]);
    $stmt = $pdo->prepare("INSERT INTO customer_tokens (customer_id, purpose, token_hash, expires_at) VALUES (?, ?, ?, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL {$hours} HOUR))");
    $stmt->execute([$customerId, $purpose, hash('sha256', $token)]);
    return $token;
}

function site_url(string $path): string {
    $configured = trim((string)getenv('SORA_BASE_URL'));
    if ($configured !== '') return rtrim($configured, '/') . '/' . ltrim($path, '/');
    $https = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    if (!preg_match('/^[a-z0-9.-]+(?::[0-9]{1,5})?$/i', $host)) $host = 'localhost';
    $base = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/'))), '/.');
    return $scheme . '://' . $host . ($base ? $base . '/' : '/') . ltrim($path, '/');
}
