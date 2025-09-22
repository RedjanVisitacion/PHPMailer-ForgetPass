<?php
include('../conn/conn.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reset'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';

    if ($email === '' || $username === '') {
        $msg = urlencode('Email and Username are required.');
        header('Location: http://localhost/VISITACION/index.php?form=forgot&status=error&message=' . $msg);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT tbl_user_id, username FROM tbl_user WHERE email = :email AND username = :username");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $msg = urlencode('Account does not exist. Please check your username and email.');
            header('Location: http://localhost/VISITACION/index.php?form=forgot&status=error&message=' . $msg . '&email=' . urlencode($email) . '&username=' . urlencode($username));
            exit;
        }

        $resetCode = rand(100000, 999999);

        // Reuse verification_code column to store the password reset code
        $update = $conn->prepare("UPDATE tbl_user SET verification_code = :code WHERE email = :email AND username = :username");
        $update->execute([
            ':code' => $resetCode,
            ':email' => $email,
            ':username' => $username,
        ]);

        // Configure mail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rpsvcodes@gmail.com';
        $mail->Password   = 'tjzs vbre crtu xttp';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('rpsvcodes@gmail.com', 'Redjan Phil S. Visitacion');
        $mail->addAddress($email);
        $mail->addReplyTo('rpsvcodes@gmail.com', 'Redjan Phil S. Visitacion');

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code';
        $mail->Body    = 'Use this code to reset your password: <b>' . $resetCode . '</b>';

        $mail->send();

        $msg = urlencode('Reset code has been sent to your email.');
        header('Location: http://localhost/VISITACION/index.php?form=reset&status=success&message=' . $msg . '&email=' . urlencode($email) . '&username=' . urlencode($username));
        exit;
    } catch (Exception $e) {
        $msg = urlencode('Failed to send email. Try again later.');
        header('Location: http://localhost/VISITACION/index.php?form=forgot&status=error&message=' . $msg . '&email=' . urlencode($email) . '&username=' . urlencode($username));
        exit;
    } catch (PDOException $e) {
        $msg = urlencode('Server error.');
        header('Location: http://localhost/VISITACION/index.php?form=forgot&status=error&message=' . $msg);
        exit;
    }
} else {
    header('Location: http://localhost/VISITACION/index.php?form=forgot');
    exit;
}
?>


