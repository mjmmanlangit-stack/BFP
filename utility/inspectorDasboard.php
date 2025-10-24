<?php
// Start output buffering
ob_start();

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

include_once 'db.php';
session_start();

$inspector = $_SESSION['user']; // adjust as needed
$currentDate = date('Y-m-d');

// Query to get all counts where user is either inspector1 OR inspector2
$sql = "
SELECT 
    COUNT(*) AS inspection,
    SUM(CASE WHEN inspection_date < ? AND status != 'completed' THEN 1 ELSE 0 END) AS ongoing,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
    SUM(CASE WHEN status = 'status-pen' THEN 1 ELSE 0 END) AS pending
FROM inspection
WHERE inspector1 = ? OR inspector2 = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $currentDate, $inspector, $inspector);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$stmt->close();


