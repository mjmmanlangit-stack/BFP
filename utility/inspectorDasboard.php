<?php
include_once 'db.php';
session_start();

$inspector = $_SESSION['user']; // adjust as needed
$currentDate = date('Y-m-d');

// One single query to get all counts
$sql = "
SELECT 
    COUNT(*) AS inspection,
    SUM(CASE WHEN inspection_date < '$currentDate' AND status != 'completed' THEN 1 ELSE 0 END) AS ongoing,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
    SUM(CASE WHEN status = 'status-pen' THEN 1 ELSE 0 END) AS pending
FROM inspection
WHERE inspector = '$inspector'
";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);


