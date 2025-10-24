<?php
$host = "localhost";      
$user = "root";          
$pass = "";               
$dbname = "bfpprofiler";  

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to prevent encoding issues
$conn->set_charset("utf8mb4");

// Include and initialize Activity Logger
require_once __DIR__ . '/ActivityLogger.php';
$activityLogger = new ActivityLogger($conn);
?>
