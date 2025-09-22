<?php
include('../conn/conn.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

if (isset($_POST['register'])) {
    try {
        $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
        $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
        $contactNumber = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        // Required checks for all fields
        if ($firstName === '' || $lastName === '' || $contactNumber === '' || $email === '' || $username === '' || $password === '') {
            $msg = urlencode('All fields are required.');
            header('Location: http://localhost/VISITACION/index.php?form=register&status=error&message=' . $msg);
            exit;
        }
        // Email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = urlencode('Please enter a valid email address.');
            header('Location: http://localhost/VISITACION/index.php?form=register&status=error&message=' . $msg);
            exit;
        }
        // Password strength
        if (strlen($password) < 6) {
            $msg = urlencode('Password must be at least 6 characters.');
            header('Location: http://localhost/VISITACION/index.php?form=register&status=error&message=' . $msg);
            exit;
        }

        // HASH THE PASSWORD HERE
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $conn->beginTransaction();

        // Check if username already exists (allow duplicate emails)
        $stmt = $conn->prepare("SELECT 1 FROM `tbl_user` WHERE `username` = :username LIMIT 1");
        $stmt->execute([
            'username' => $username
        ]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($exists)) {
            $verificationCode = rand(100000, 999999);

            $insertStmt = $conn->prepare("INSERT INTO `tbl_user` (`tbl_user_id`, `first_name`, `last_name`, `contact_number`, `email`, `username`, `password`, `verification_code`) VALUES (NULL, :first_name, :last_name, :contact_number, :email, :username, :password, :verification_code)");
            $insertStmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
            $insertStmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
            $insertStmt->bindParam(':contact_number', $contactNumber, PDO::PARAM_STR); // changed to STR in case of leading zeros
            $insertStmt->bindParam(':email', $email, PDO::PARAM_STR);
            $insertStmt->bindParam(':username', $username, PDO::PARAM_STR);
            $insertStmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR); // use hashed password here
            $insertStmt->bindParam(':verification_code', $verificationCode, PDO::PARAM_INT);
            $insertStmt->execute();
    
            //Server settings
            $mail->isSMTP(); 
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true; 
            $mail->Username   = 'rpsvcodes@gmail.com';
            $mail->Password   = 'tjzs vbre crtu xttp';
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;                                    
        
            //Recipients
            $mail->setFrom('rpsvcodes@gmail.com', 'Redjan Phil S. Visitacion');
            $mail->addAddress($email);   
            $mail->addReplyTo('rpsvcodes@gmail.com', 'Redjan Phil S. Visitacion'); 
        
            //Content
            $mail->isHTML(true);  
            $mail->Subject = 'Verification Code';
            $mail->Body    = 'Your verification code is: ' . $verificationCode; 
            
            // Send verification email
            $mail->send();

            session_start();
            $userVerificationID = $conn->lastInsertId();
            $_SESSION['user_verification_id'] = $userVerificationID;

            $conn->commit();

            $msg = urlencode('Check your email for verification code.');
            header('Location: http://localhost/VISITACION/verification.php?status=info&message=' . $msg);
            exit;
        } else {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            $msg = urlencode('Username already exists.');
            header('Location: http://localhost/VISITACION/index.php?form=register&status=error&message=' . $msg);
            exit;
        }
    } catch (Exception $e) { // PHPMailer exception
        if ($conn->inTransaction()) { $conn->rollBack(); }
        $msg = urlencode('Failed to send verification email. Please try again.');
        header('Location: http://localhost/VISITACION/index.php?form=register&status=error&message=' . $msg);
        exit;
    } catch (PDOException $e) {
        if ($conn->inTransaction()) { $conn->rollBack(); }
        $msg = urlencode('Server error during registration.');
        header('Location: http://localhost/VISITACION/index.php?form=register&status=error&message=' . $msg);
        exit;
    }
}

if (isset($_POST['verify'])) {
    try {
        $userVerificationID = isset($_POST['user_verification_id']) ? trim($_POST['user_verification_id']) : '';
        $verificationCode = isset($_POST['verification_code']) ? trim($_POST['verification_code']) : '';

        if ($userVerificationID === '' || $verificationCode === '') {
            $msg = urlencode('Both verification fields are required.');
            header('Location: http://localhost/VISITACION/verification.php?status=error&message=' . $msg);
            exit;
        }

        $stmt = $conn->prepare("SELECT `verification_code` FROM `tbl_user` WHERE `tbl_user_id` = :user_verification_id");
        $stmt->execute([
            'user_verification_id' => $userVerificationID,
        ]);
        $codeExist = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($codeExist && (string)$codeExist['verification_code'] === (string)$verificationCode) {
            session_destroy();
            $msg = urlencode('Registered successfully. You can now log in.');
            header('Location: http://localhost/VISITACION/index.php?status=success&message=' . $msg);
            exit;
        } else {
            $conn->prepare("DELETE FROM `tbl_user` WHERE `tbl_user_id` = :user_verification_id")->execute([
                'user_verification_id' => $userVerificationID
            ]);

            $msg = urlencode('Incorrect Verification Code. Register Again.');
            header('Location: http://localhost/VISITACION/index.php?form=register&status=error&message=' . $msg);
            exit;
        }
    } catch (PDOException $e) {
        $msg = urlencode('Server error during verification.');
        header('Location: http://localhost/VISITACION/index.php?form=register&status=error&message=' . $msg);
        exit;
    }
}
?>
