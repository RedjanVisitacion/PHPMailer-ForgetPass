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
        echo "<script>alert('Email and Username are required.'); window.location.href = 'http://localhost/VISITACION/index.php?form=forgot';</script>";
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT tbl_user_id, username FROM tbl_user WHERE email = :email AND username = :username");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            echo "<script>alert('If the account exists, a reset code has been sent.'); window.location.href = 'http://localhost/VISITACION/index.php?form=reset&email=" . urlencode($email) . "&username=" . urlencode($username) . "';</script>";
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

        echo "<script>alert('Reset code has been sent to your email.'); window.location.href = 'http://localhost/VISITACION/index.php?form=reset&email=" . urlencode($email) . "&username=" . urlencode($username) . "';</script>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Failed to send email. Try again later.'); window.location.href = 'http://localhost/VISITACION/index.php?form=reset&email=" . urlencode($email) . "&username=" . urlencode($username) . "';</script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Server error.'); window.location.href = 'http://localhost/VISITACION/index.php?form=forgot';</script>";
        exit;
    }
} else {
    header('Location: http://localhost/VISITACION/index.php?form=forgot');
    exit;
}
?>


