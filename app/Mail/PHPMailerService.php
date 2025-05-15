<?php

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PHPMailerService
{
    public function sendMail($toEmail, $toName, $subject, $body)
    {
        if(empty($toEmail)){
            return 'User dont have any email';
        }
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';  // Correct SMTP host for Hostinger
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tribo.corp@tribo.uno';  // Your email address
            $mail->Password   = '@Zin090400';  // Use your app password if applicable
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            //$mail->SMTPDebug = 2;  // Show debugging output
            //$mail->Debugoutput = 'html';

            // Sender & Recipient
            $mail->setFrom('tribo.corp@tribo.uno', 'Tribo Corp');  // Set the sender's email
            $mail->addAddress($toEmail, $toName);  // Add recipient

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;


            // Send email
            $mail->send();
            return 'Message has been sent successfully';
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
