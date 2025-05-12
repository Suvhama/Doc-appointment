<?php
// Include PHPMailer classes
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipientEmail = $_POST['email'];
    $recipientName = $_POST['name'];
    $userMessage = $_POST['message'];

    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username   = 'saritakawan46@gmail.com';
        $mail->Password   = 'swxt mrcy xxmp guuu';  
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
    
        // Recipients
        $mail->setFrom('saritakawan46@gmail.com', 'Medisync');
        $mail->addAddress($recipientEmail, $recipientName);
        $meetLink = 'https://meet.google.com/abc-defg-hij';

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Message with Meet Link';
        $mail->Body = "
            <strong>Message from {$recipientName}:</strong><br><br>
            " . nl2br($userMessage) . "<br><br>
            <strong>Join the meeting:</strong> <a href='{$meetLink}' target='_blank'>{$meetLink}</a>
        ";
        $mail->AltBody = "Message from {$recipientName}:\n\n{$userMessage}\n\nJoin the meeting: {$meetLink}";

        $mail->send();
        echo 'Email sent successfully.';
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request.";
}