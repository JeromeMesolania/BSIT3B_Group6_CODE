<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer for email sending
require __DIR__ . '/../vendor/autoload.php'; // Correct path to autoload.php

// mailer.php
function sendPasswordResetCode($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // SMTP server address
        $mail->SMTPAuth = true;
        $mail->Username = 'ayesjerome24@gmail.com'; // Your email
        $mail->Password = 'tuxc dqxd iqfz kmqu'; // Your SMTP password or app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('CODE@gmail.com', 'CODE Support');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset Code';
        $mail->Body = "Your password reset code is: <strong>$code</strong>";

        $mail->send();
        return true; // Success
    } catch (Exception $e) {
        return false; // Failure
    }
}

function sendConfirmationCode($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ayesjerome24@gmail.com';
        $mail->Password = 'tuxc dqxd iqfz kmqu'; // App-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('no-reply@code.com', 'CODE Support');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Account Confirmation Code';
        $mail->Body = "Your account confirmation code is: <strong>$code</strong>";

        $mail->send();
        return true; // Success
    } catch (Exception $e) {
        return false; // Failure
    }
}