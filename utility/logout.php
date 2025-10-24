<?php
session_start();

// Include database connection and activity logger
require_once __DIR__ . '/db.php';

// Log logout before destroying session
if (isset($_SESSION['user'])) {
    $activityLogger->logLogout($_SESSION['user']);
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header("Location: ../html/index.php");
exit();
?>