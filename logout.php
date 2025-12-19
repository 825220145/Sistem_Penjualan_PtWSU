<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/funcs.php';

if (isset($_SESSION['user_id'])) {
    log_activity($pdo, $_SESSION['user_id'], 'Logout', 'Keluar sistem');
}

session_unset();
session_destroy();
header("Location: index.php");
exit;
