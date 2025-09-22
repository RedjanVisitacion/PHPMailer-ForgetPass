<?php
include ('../conn/conn.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

    if ($username === '' || $password === '') {
        echo "
        <script>
            alert('Please enter username and password.');
            window.location.href = 'http://localhost/VISITACION/index.php';
        </script>
        ";
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

            echo "
            <script>
                alert('Login Successfully!');
                window.location.href = 'http://localhost/VISITACION/home.php';
            </script>
            ";
            exit;
        } else {
            echo "
            <script>
                alert('Login Failed, Incorrect Password!');
                window.location.href = 'http://localhost/VISITACION/index.php';
            </script>
            ";
            exit;
        }
    } else {
        echo "
        <script>
            alert('Login Failed, User Not Found!');
            window.location.href = 'http://localhost/VISITACION/index.php';
        </script>
        ";
        exit;
    }
} else {
    header('Location: http://localhost/VISITACION/index.php');
    exit;
}
?>
