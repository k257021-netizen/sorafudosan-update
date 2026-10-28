<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid($_POST['csrf'] ?? '')) {
    unset($_SESSION['customer_id'], $_SESSION['customer_name']);
    session_regenerate_id(true);
}

header('Location: index.php');
exit;
