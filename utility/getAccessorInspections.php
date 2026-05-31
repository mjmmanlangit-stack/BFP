<?php
session_start();
header('Content-Type: application/json');

// Check authentication - allow CRO and admin (Accessor role merged into CRO)
if (!isset($_SESSION['user'])) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}
$role = strtolower($_SESSION['role']);
if (!in_array($role, ['cro', 'admin'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bfpprofiler');
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get all establishments with inspection and payment info
// Changed to use establishment as main source (LEFT JOIN inspection)
// This allows new establishments without inspections to appear as "Unpaid"
$query = "SELECT 
            COALESCE(i.id, 0) as inspection_id,
            i.inspection_date,
            COALESCE(i.payment, 0) as payment,
            i.inspection_type,
            e.id as establishment_id,
            e.name as business_name,
            e.type as business_type,
            e.address,
            owner.fullname as owner_name,
            owner.email as owner_email,
            owner.phone_number as owner_phone,
            u1.fullname as inspector1_name,
            u2.fullname as inspector2_name,
            r.id as report_id,
            r.finalized_at,
            r.compliance_status
          FROM establishment e
          LEFT JOIN user owner ON e.owner_id = owner.id
          LEFT JOIN inspection i ON i.establishment_id = e.id
            AND i.id = (SELECT MAX(id) FROM inspection WHERE establishment_id = e.id)
          LEFT JOIN user u1 ON i.inspector1 = u1.id
          LEFT JOIN user u2 ON i.inspector2 = u2.id
          LEFT JOIN reports r ON i.id = r.inspection_id
          ORDER BY COALESCE(i.inspection_date, e.createdAt) DESC";

$result = $conn->query($query);
$inspections = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $inspection_id = $row['inspection_id'];
        
        // Get defects for this inspection
        $defectQuery = "SELECT d.defects_details, d.grace_period, d.status 
                        FROM defects d
                        INNER JOIN reports r ON d.report_id = r.id
                        WHERE r.inspection_id = ?";
        $stmt = $conn->prepare($defectQuery);
        $stmt->bind_param("i", $inspection_id);
        $stmt->execute();
        $defectResult = $stmt->get_result();
        
        $defects = [];
        $gracePeriods = [];
        while ($defect = $defectResult->fetch_assoc()) {
            $defects[] = $defect['defects_details'];
            if (!in_array($defect['grace_period'], $gracePeriods)) {
                $gracePeriods[] = $defect['grace_period'];
            }
        }
        $stmt->close();
        
        $inspections[] = [
            'inspectionId' => $row['inspection_id'],
            'establishmentId' => $row['establishment_id'],
            'ownerName' => $row['owner_name'],
            'ownerEmail' => $row['owner_email'],
            'ownerPhone' => $row['owner_phone'],
            'businessName' => $row['business_name'],
            'businessType' => $row['business_type'],
            'address' => $row['address'],
            'inspector1' => $row['inspector1_name'],
            'inspector2' => $row['inspector2_name'],
            'inspectionDate' => $row['inspection_date'],
            'inspectionType' => $row['inspection_type'],
            'defects' => count($defects) > 0 ? implode('; ', $defects) : 'No defects found',
            'gracePeriod' => count($gracePeriods) > 0 ? implode(', ', $gracePeriods) : 'N/A',
            'payment' => $row['payment'] == 1 ? 'paid' : 'unpaid',
            'complianceStatus' => $row['compliance_status'],
            'finalizedAt' => $row['finalized_at']
        ];
    }
}

$conn->close();

echo json_encode([
    'success' => true,
    'inspections' => $inspections,
    'total' => count($inspections)
]);
?>
