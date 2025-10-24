<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Test file works',
    'session' => isset($_SESSION['user']) ? 'Session exists' : 'No session',
    'post_data' => file_get_contents('php://input')
]);
?>
