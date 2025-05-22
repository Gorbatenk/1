<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Налаштування сервера
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'm.o.horbatenko@student.khai.edu';
    $mail->Password = 'wqilauivcaqmavnb';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Від кого і кому
    $mail->setFrom('m.o.horbatenko@student.khai.edu', 'Mykyta Horbatenko');
    $mail->addAddress('m.o.horbatenko@student.khai.edu', 'Я');

    // Контент
    $mail->isHTML(false);
    $mail->Subject = 'MY TEST EMAIL';

    $firstName = 'Mykyta';
    $lastName = 'Horbatenko';
    $group = '539а';
    $date = date('d.m.Y');
    $time = date('H:i:s');

    $message = "firstName: {$firstName}\n";
    $message .= "lastName: {$lastName}\n";
    $message .= "group: {$group}\n";
    $message .= "date: {$date}\n";
    $message .= "time: {$time}\n";
    $message .= "\nЭто тестовое письмо, отправленное с помощью PHP-скрипта\n";

    $mail->Body = $message;

    $mail->send();
    echo "Email sent successfully!\n";
} catch (Exception $e) {
    echo "Failed to send email. Mailer Error: {$mail->ErrorInfo}\n";
}
