<?php
// Start session FIRST before any output
session_start();

// Then start output buffering
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set headers for JSON response
header('Content-Type: application/json');

try {
    include_once 'db.php';
    include_once 'mailer.php';
    
    // Get JSON data from request body
    $data = json_decode(file_get_contents("php://input"), true);

    // Verify user is authenticated
    if (!isset($_SESSION['user'])) {
        throw new Exception('Not authenticated');
    }

    $user_id = $_SESSION['user'];
    
    if (!$data) {
        throw new Exception('No data received');
    }

    // Extract and sanitize input data
    $business_name   = trim($data['business_name'] ?? '');
    $registration_no = trim($data['registration_no'] ?? '');
    $type            = trim($data['type'] ?? '');
    $ownership_type  = trim($data['ownership_type'] ?? '');
    $tin_number      = trim($data['tin_number'] ?? '');
    $contact_number  = trim($data['contact_number'] ?? '');
    $contact_email   = trim($data['contact_email'] ?? '');
    $address         = trim($data['address'] ?? '');
    $x_coordinate    = $data['x_coordinate'] ?? '';
    $y_coordinate    = $data['y_coordinate'] ?? '';
    $status          = 'active';

    // Validate required fields
    if (!$business_name || !$type || !$address) {
        throw new Exception('Business name, type, and address are required');
    }

    // Check for duplicate registration number
    if ($registration_no !== '') {
        $dup = $conn->prepare("SELECT id FROM establishment WHERE registration_no = ?");
        if (!$dup) {
            throw new Exception('Database error: ' . $conn->error);
        }
        $dup->bind_param('s', $registration_no);
        $dup->execute();
        $dup->store_result();
        if ($dup->num_rows > 0) {
            $dup->close();
            throw new Exception('An establishment with this BFP Registration Number already exists');
        }
        $dup->close();
    }

    // Insert establishment into database
    $stmt = $conn->prepare("INSERT INTO `establishment`(`owner_id`, `address`, `type`, `ownership_type`, `tin_number`, `contact_number`, `contact_email`, `name`, `status`, `x_coordinate`, `y_coordinate`, `registration_no`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    if (!$stmt) {
        throw new Exception('Database error: ' . $conn->error);
    }

    $stmt->bind_param('isssssssssss', $user_id, $address, $type, $ownership_type, $tin_number, $contact_number, $contact_email, $business_name, $status, $x_coordinate, $y_coordinate, $registration_no);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create establishment: ' . $stmt->error);
    }

    $establishment_id = $conn->insert_id;
    $stmt->close();

    // Log the establishment creation (wrapped in try-catch to not break the response)
    try {
        if (isset($activityLogger)) {
            $activityLogger->logCreate(
                $user_id,
                'establishment_created',
                'establishment',
                'Created new establishment: ' . $business_name . ' (' . $type . ')',
                [
                    'establishment_id' => $establishment_id,
                    'business_name'    => $business_name,
                    'type'             => $type,
                    'registration_no'  => $registration_no,
                    'address'          => $address
                ]
            );
        }
    } catch (Exception $logErr) {
        // Log errors but don't break the response
        error_log('Activity logging failed: ' . $logErr->getMessage());
    }

    // Notify CRO users about the new establishment (wrapped in try-catch)
    try {
        $croStmt = $conn->prepare("SELECT email, fullname, phone_number FROM user WHERE role = 'cro' AND status = 'active'");
        if ($croStmt) {
            $croStmt->execute();
            $croResult = $croStmt->get_result();
            
            while ($cro = $croResult->fetch_assoc()) {
                $emailBody = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <div style="background: linear-gradient(135deg,#dc3545,#a02834); padding:20px; border-radius:10px 10px 0 0; color:white;">
                        <h2 style="margin:0;">New Establishment Registered</h2>
                    </div>
                    <div style="background:#f8f9fa; padding:20px; border-radius:0 0 10px 10px;">
                        <p>Dear ' . htmlspecialchars($cro['fullname']) . ',</p>
                        <p>A new establishment has been registered and requires document review:</p>
                        <ul>
                            <li><strong>Business Name:</strong> ' . htmlspecialchars($business_name) . '</li>
                            <li><strong>Type:</strong> ' . htmlspecialchars($type) . '</li>
                            <li><strong>Address:</strong> ' . htmlspecialchars($address) . '</li>
                            <li><strong>Registration No:</strong> ' . htmlspecialchars($registration_no ?: 'N/A') . '</li>
                        </ul>
                        <p>Please log in to the BFP Site Profiler to review the submitted documents.</p>
                    </div>
                </div>';
                
                // Send email - wrapped in try-catch
                try {
                    if (function_exists('sendEmail')) {
                        sendEmail($cro['email'], $emailBody, 'New Establishment Pending Review — BFP Site Profiler');
                    }
                } catch (Exception $emailErr) {
                    error_log('Email sending failed for ' . $cro['email'] . ': ' . $emailErr->getMessage());
                }
                
                // Send SMS - wrapped in try-catch
                if (!empty($cro['phone_number'])) {
                    try {
                        if (function_exists('sendSMS')) {
                            sendSMS($cro['phone_number'],
                                "BFP Site Profiler: A new establishment \"{$business_name}\" ({$type}) has been registered and requires document review."
                            );
                        }
                    } catch (Exception $smsErr) {
                        error_log('SMS sending failed for ' . $cro['phone_number'] . ': ' . $smsErr->getMessage());
                    }
                }
            }
            $croStmt->close();
        }
    } catch (Exception $croErr) {
        error_log('CRO notification failed: ' . $croErr->getMessage());
    }

    // Success response
    ob_clean();
    echo json_encode(['success' => true, 'establishment_id' => $establishment_id]);

} catch (Exception $e) {
    // Catch any errors and return as JSON
    ob_clean();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>