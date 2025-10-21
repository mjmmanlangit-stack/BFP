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
    $mail->Username   = 'bfpprofiler@gmail.com';   
    $mail->Password   = 'jyyz qnoy nrjm sorh';   
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // or STARTTLS
    $mail->Port       = 465; // or 587
    // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    // $mail->Port       = 587;

    $mail->setFrom('bfpprofiler@gmail.com', 'BFP Site Profiler');
    $mail->addAddress($r);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();
   
} catch (Exception $e) {
    echo json_encode(['error'=>"error on email"]);
}

}
