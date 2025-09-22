<?php
include ('../conn/conn.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

    if ($username === '' || $password === '') {
        $msg = urlencode('Please enter username and password.');
        header('Location: http://localhost/VISITACION/index.php?status=error&message=' . $msg);
        exit;
    }

    $stmt = $conn->prepare("SELECT `tbl_user_id`, `username`, `password` FROM `tbl_user` WHERE `username` = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        $stored_password = $row['password'];

        if (password_verify($password, $stored_password)) {
            $_SESSION['user_id'] = $row['tbl_user_id'];
            $_SESSION['username'] = $row['username'];

            header('Location: http://localhost/VISITACION/home.php');
            exit;
        } else {
            $msg = urlencode('Login failed, incorrect password.');
            header('Location: http://localhost/VISITACION/index.php?status=error&message=' . $msg . '&form=login');
            exit;
        }
    } else {
        $msg = urlencode('Login failed, user not found.');
        header('Location: http://localhost/VISITACION/index.php?status=error&message=' . $msg . '&form=login');
        exit;
    }
} else {
    header('Location: http://localhost/VISITACION/index.php');
    exit;
}
?>
