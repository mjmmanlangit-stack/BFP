<?php
ob_start();
ini_set('display_errors', 0);
session_start();
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$ownerId = $_SESSION['user'];
include_once 'db.php';

// Ensure expiry_date column exists (created by authorizeCertificate or updatePaymentStatus)
$conn->query("ALTER TABLE certificates ADD COLUMN IF NOT EXISTS expiry_date DATE DEFAULT NULL");

try {
    $sql = "SELECT
                c.id,
                c.certificate_number,
                c.status,
                c.authorized_at,
                c.expiry_date,
                c.remarks,
                e.name as establishment_name,
                e.address,
                e.type as establishment_type,
                u.fullname as authorized_by_name,
                u_owner.fullname as owner_name
            FROM certificates c
            INNER JOIN establishment e ON c.establishment_id = e.id
            INNER JOIN user u ON c.authorized_by = u.id
            INNER JOIN user u_owner ON e.owner_id = u_owner.id
            WHERE e.owner_id = ?
            ORDER BY c.authorized_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ownerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $certificates = [];
    while ($row = $result->fetch_assoc()) {
        $row['is_expired'] = !empty($row['expiry_date']) && strtotime($row['expiry_date']) < time();
        $certificates[]    = $row;
    }

    echo json_encode(['success' => true, 'certificates' => $certificates]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
