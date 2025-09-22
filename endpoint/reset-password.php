<?php
include('../conn/conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $resetCode = isset($_POST['reset_code']) ? trim($_POST['reset_code']) : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($email === '' || $username === '' || $resetCode === '' || $newPassword === '' || $confirmPassword === '') {
        echo "<script>alert('All fields are required.'); window.location.href = 'http://localhost/VISITACION/index.php?form=reset&email=" . urlencode($email) . "&username=" . urlencode($username) . "';</script>";
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords do not match.'); window.location.href = 'http://localhost/VISITACION/index.php?form=reset&email=" . urlencode($email) . "&username=" . urlencode($username) . "';</script>";
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT verification_code FROM tbl_user WHERE email = :email AND username = :username");
        $stmt->execute([':email' => $email, ':username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || (string)$row['verification_code'] !== (string)$resetCode) {
            echo "<script>alert('Invalid reset code or account.'); window.location.href = 'http://localhost/VISITACION/index.php?form=reset&email=" . urlencode($email) . "&username=" . urlencode($username) . "';</script>";
            exit;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE tbl_user SET password = :password, verification_code = NULL WHERE email = :email AND username = :username");
        $update->execute([
            ':password' => $hashedPassword,
            ':email' => $email,
            ':username' => $username,
        ]);

        echo "<script>alert('Password has been reset. You can now login.'); window.location.href = 'http://localhost/VISITACION/index.php';</script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Server error.'); window.location.href = 'http://localhost/VISITACION/index.php?form=reset&email=" . urlencode($email) . "&username=" . urlencode($username) . "';</script>";
        exit;
    }
} else {
    header('Location: http://localhost/VISITACION/index.php?form=forgot');
    exit;
}
?>


