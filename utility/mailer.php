<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/PHPMailer/Exception.php';



function sendEmail($r, $body, $subject){
    try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'profilerbfp@gmail.com';   
    $mail->Password   = 'nlll djbr dmil kaxu';   
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // or STARTTLS
    $mail->Port       = 465; // or 587
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    // $mail->Port       = 587;

    $mail->setFrom('profilerbfp@gmail.com', 'BFP Site Profiler');
    $mail->addAddress($r);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
    return true;
   
} catch (Exception $e) {
    // Don't echo here - just log the error
    error_log("Email sending failed: " . $e->getMessage());
    return false;
}

}

// ---------------------------------------------------------------------------
// SMS — replace the placeholder URL with your actual API endpoint
// ---------------------------------------------------------------------------
define('SMS_API_ENDPOINT', 'https://YOUR_SMS_API_ENDPOINT_HERE/send');

/**
 * Send an SMS via the custom SMS gateway API.
 *
 * @param string $recipient  Phone number of the recipient
 * @param string $message    Plain-text message body
 * @return bool              true on success, false on failure (error is logged)
 */
function sendSMS(string $recipient, string $message): bool {
    if (empty($recipient)) {
        return false;
    }
    $payload = json_encode(['recipient' => $recipient, 'message' => $message]);
    $ch = curl_init(SMS_API_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);
    if ($error) {
        error_log("SMS sending failed to {$recipient}: " . $error);
        return false;
    }
    return true;
}
