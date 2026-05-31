<?php
// Clean output buffer and set headers
ob_clean();
header('Content-Type: application/json');

include_once 'db.php';

// Get all inspectors
$stmt = $conn->prepare("SELECT id, fullname, email, phone_number FROM user WHERE role = 'inspector' OR role = 'Inspector'");
$stmt->execute();
$result = $stmt->get_result();

$res = [];
while ($row = $result->fetch_assoc()) {
  $res[] = $row;
}

// If date and time slot are provided, filter out unavailable inspectors
$inspection_date = isset($_GET['inspection_date']) ? trim($_GET['inspection_date']) : null;
$time_slot = isset($_GET['time_slot']) ? trim($_GET['time_slot']) : null;

if ($inspection_date && $time_slot) {
  $available_inspectors = [];
  
  // Extract start and end times from time_slot (format: "HH:MM-HH:MM")
  $timeparts = explode('-', $time_slot);
  if (count($timeparts) === 2) {
    $start_time = trim($timeparts[0]);
    $end_time = trim($timeparts[1]);
    $dateOnly = substr($inspection_date, 0, 10); // YYYY-MM-DD format
    
    foreach ($res as $inspector) {
      // Check if this inspector has a conflict on the selected date and time slot
      $conflict_stmt = $conn->prepare(
        "SELECT COUNT(*) as conflict_count FROM inspection 
         WHERE (inspector1 = ? OR inspector2 = ?)
         AND DATE(inspection_date) = ?
         AND TIME(inspection_date) BETWEEN TIME(?) AND TIME(DATE_ADD(?, INTERVAL -1 MINUTE))
         AND status NOT IN ('completed', 'cancelled', 'approved')"
      );
      
      $conflict_stmt->bind_param('iisss', $inspector['id'], $inspector['id'], $dateOnly, $start_time, $end_time);
      $conflict_stmt->execute();
      $conflict_result = $conflict_stmt->get_result()->fetch_assoc();
      $conflict_stmt->close();
      
      // If no conflicts, add to available list
      if ($conflict_result['conflict_count'] == 0) {
        $available_inspectors[] = $inspector;
      }
    }
    
    $res = $available_inspectors;
  }
}

echo json_encode($res);

$stmt->close();
$conn->close();
exit;

?>

