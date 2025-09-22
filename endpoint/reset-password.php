<?php
include('../conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $resetCode = isset($_POST['reset_code']) ? trim($_POST['reset_code']) : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($email === '' || $username === '' || $resetCode === '' || $newPassword === '' || $confirmPassword === '') {
        $msg = urlencode('All fields are required.');
        header('Location: http://localhost/VISITACION/index.php?form=reset&status=error&message=' . $msg . '&email=' . urlencode($email) . '&username=' . urlencode($username));
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        $msg = urlencode('Passwords do not match.');
        header('Location: http://localhost/VISITACION/index.php?form=reset&status=error&message=' . $msg . '&email=' . urlencode($email) . '&username=' . urlencode($username));
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT verification_code FROM tbl_user WHERE email = :email AND username = :username");
        $stmt->execute([':email' => $email, ':username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || (string)$row['verification_code'] !== (string)$resetCode) {
            $msg = urlencode('Invalid reset code or account.');
            header('Location: http://localhost/VISITACION/index.php?form=reset&status=error&message=' . $msg . '&email=' . urlencode($email) . '&username=' . urlencode($username));
            exit;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE tbl_user SET password = :password, verification_code = NULL WHERE email = :email AND username = :username");
        $update->execute([
            ':password' => $hashedPassword,
            ':email' => $email,
            ':username' => $username,
        ]);

        $msg = urlencode('Password has been reset. You can now login.');
        header('Location: http://localhost/VISITACION/index.php?status=success&message=' . $msg);
        exit;
    } catch (PDOException $e) {
        $msg = urlencode('Server error.');
        header('Location: http://localhost/VISITACION/index.php?form=reset&status=error&message=' . $msg . '&email=' . urlencode($email) . '&username=' . urlencode($username));
        exit;
    }
} else {
    header('Location: http://localhost/VISITACION/index.php?form=forgot');
    exit;
}
?>


