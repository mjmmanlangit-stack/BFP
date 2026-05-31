<?php
session_start();
$_SESSION['user'] = 5;  // CRO user ID
$_SESSION['role'] = 'CRO';
$_SESSION['email'] = 'cro@bfp.gov.ph';
$_SESSION['fullname'] = 'Pedro CRO';

echo "Session set for CRO user. Now redirecting to payment-verification.php...";
header('Location: ./html/CRO/payment-verification.php');
exit;
?>
