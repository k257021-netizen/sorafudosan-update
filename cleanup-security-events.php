<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require 'db.php';

$deletedEvents = $pdo->exec('DELETE FROM auth_security_events WHERE created_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 180 DAY)');
$deletedAttempts = $pdo->exec('DELETE FROM auth_login_attempts WHERE attempted_at < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY)');

fwrite(STDOUT, "Deleted {$deletedEvents} expired security events and {$deletedAttempts} expired login attempts.\n");
