<?php

require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;



if (isset($_POST['token']) && isset($_POST['email'])) {
    $token = $_POST['token'];

    $message = "Для восстановления пароля перейдите по ссылке: <a href='http://my_cloud/resetpassword.php?token=" . urlencode($token) . "'>Восстановить пароль</a>";

    $mail = new PHPMailer();
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        try {

            $mail->CharSet = 'UTF-8';
            $mail->setFrom('');
            $mail->addAddress($_POST['email']);
            $mail->isHTML(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.yandex.ru';
            $mail->SMTPAuth   = true;
            $mail->Username   = '';
            $mail->Password   = '';
            $mail->Subject = 'Восстановление пароля: my_cloud';
            $mail->Port       = 465;
            $mail->SMTPSecure = 'ssl';
            $mail->Body = $message;

            if ($mail->send()) {
                $response = array('status' => 'success', 'message' => 'A password recovery link has been sent to your email');
                echo json_encode($response);
            } else {
                throw new Exception('Error send mail!');
            }
        } catch (Exception $error) {
            $myError = $error->getMessage();
        }
    }
}
