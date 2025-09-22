<?php include ('./conn/conn.php') ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System with Email Verification</title>
    <link rel="icon" href="img/FACE-ICON.png"/>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');

        * {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url("img/RPSVCODES.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            height: 100vh;
        }

        .login-form, .registration-form {
            backdrop-filter: blur(100px);
            color: rgb(255, 255, 255);
            padding: 40px;
            width: 500px;
            border: 2px solid;
            border-radius: 10px;
        }
        .switch-form-link {
            text-decoration: underline;
            cursor: pointer;
            color: rgb(100, 100, 200);
        }
        .muted-link {
            text-decoration: underline;
            cursor: pointer;
            color: rgb(200, 200, 220);
        }
    </style>
</head>
<body>
    
    <div class="main">

        <!-- Login Area -->

        <div class="login-container">

            <div class="login-form" id="loginForm">
                <h2 class="text-center">Welcome Back!</h2>
                <p class="text-center">Fill your login details.</p>
                <form action="./endpoint/login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <p>No Account? Register <span  class="switch-form-link" onclick="showRegistrationForm()">Here.</span></p>
                    <p><span class="muted-link" onclick="showForgotForm()">Forgot password?</span></p>
                    <button type="submit" class="btn btn-secondary login-btn form-control" >Login</button>
                </form>
            </div>

        </div>



        <!-- Registration Area -->
        <div class="registration-form" id="registrationForm">
            <h2 class="text-center">Registration Form</h2>
            <p class="text-center">Fill in you personal details.</p>
            <form action="./endpoint/add-user.php" method="POST">
                <div class="form-group registration row">
                    <div class="col-6">
                        <label for="firstName">First Name:</label>
                        <input type="text" class="form-control" id="firstName" name="first_name" required>
                    </div>
                    <div class="col-6">
                        <label for="lastName">Last Name:</label>
                        <input type="text" class="form-control" id="lastName" name="last_name" required>
                    </div>
                </div>
                <div class="form-group registration row">
                    <div class="col-5">
                        <label for="contactNumber">Contact Number:</label>
                        <input type="tel" class="form-control" id="contactNumber" name="contact_number" pattern="^[0-9]{10,13}$" maxlength="13" minlength="10" required>
                    </div>
                    <div class="col-7">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="form-group registration">
                    <label for="registerUsername">Username:</label>
                    <input type="text" class="form-control" id="registerUsername" name="username" required>
                </div>
                <div class="form-group registration">
                    <label for="registerPassword">Password:</label>
                    <input type="password" class="form-control" id="registerPassword" name="password" minlength="6" required>
                </div>
                <p>Already have an account? Login <span class="switch-form-link" onclick="showLoginForm()">Here.</span></p>
                <button type="submit" class="btn btn-dark login-register form-control" name="register">Register</button>

            </form>

        </div>

        <!-- Forgot Password Request Area -->
        <div class="registration-form" id="forgotForm">
            <h2 class="text-center">Forgot Password</h2>
            <p class="text-center">Enter your email to receive a reset code.</p>
            <form action="./endpoint/request-password-reset.php" method="POST">
                <div class="form-group">
                    <label for="forgotUsername">Username:</label>
                    <input type="text" class="form-control" id="forgotUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="forgotEmail">Email:</label>
                    <input type="email" class="form-control" id="forgotEmail" name="email" required>
                </div>
                <!-- <p>Already have a code? Reset <span class="switch-form-link" onclick="showResetForm()">Here.</span></p> -->
                <p>Remembered your password? Login <span class="switch-form-link" onclick="showLoginForm()">Here.</span></p>
                <button type="submit" class="btn btn-info form-control" name="send_reset">Send Reset Code</button>
            </form>
        </div>

        <!-- Reset Password Area -->
        <div class="registration-form" id="resetForm">
            <h2 class="text-center">Reset Password</h2>
            <p class="text-center">Enter the code sent to your email and your new password.</p>
            <form action="./endpoint/reset-password.php" method="POST">
                <div class="form-group">
                    <label for="resetUsername">Username:</label>
                    <input type="text" class="form-control" id="resetUsername" name="username" required readonly>
                </div>
                <div class="form-group">
                    <label for="resetEmail">Email:</label>
                    <input type="email" class="form-control" id="resetEmail" name="email" required readonly>
                </div>
                <div class="form-group">
                    <label for="resetCode">Reset Code:</label>
                    <input type="number" class="form-control" id="resetCode" name="reset_code" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password:</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password:</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                </div>
                <p>Back to Login <span class="switch-form-link" onclick="showLoginForm()">Here.</span></p>
                <button type="submit" class="btn btn-success form-control" name="reset_password">Reset Password</button>
            </form>
        </div>

    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const registrationForm = document.getElementById('registrationForm');
        const forgotForm = document.getElementById('forgotForm');
        const resetForm = document.getElementById('resetForm');

        registrationForm.style.display = "none";
        forgotForm.style.display = "none";
        resetForm.style.display = "none";

        // Check URL parameters to determine which form to show
        const urlParams = new URLSearchParams(window.location.search);
        const formParam = urlParams.get('form');
        const emailParam = urlParams.get('email');
        const usernameParam = urlParams.get('username');

        // Show appropriate form based on URL parameter
        if (formParam === 'register') {
            showRegistrationForm();
        } else if (formParam === 'forgot') {
            showForgotForm();
        } else if (formParam === 'reset') {
            showResetForm();
            // Pre-fill email if provided
            if (emailParam) {
                document.getElementById('resetEmail').value = decodeURIComponent(emailParam);
            }
            if (usernameParam) {
                document.getElementById('resetUsername').value = decodeURIComponent(usernameParam);
            }
        } else {
            showLoginForm();
        }


        function showRegistrationForm() {
            registrationForm.style.display = "";
            loginForm.style.display = "none";
            forgotForm.style.display = "none";
            resetForm.style.display = "none";
        }

        function showLoginForm() {
            registrationForm.style.display = "none";
            loginForm.style.display = "";
            forgotForm.style.display = "none";
            resetForm.style.display = "none";
        }

        function showForgotForm() {
            registrationForm.style.display = "none";
            loginForm.style.display = "none";
            forgotForm.style.display = "";
            resetForm.style.display = "none";
        }

        function showResetForm() {
            registrationForm.style.display = "none";
            loginForm.style.display = "none";
            forgotForm.style.display = "none";
            resetForm.style.display = "";
        }

        function sendVerificationCode() {
            const registrationElements = document.querySelectorAll('.registration');

            registrationElements.forEach(element => {
                element.style.display = 'none';
            });

            const verification = document.querySelector('.verification');
            if (verification) {
                verification.style.display = 'none';
            }
        }

    </script>

    <!-- Bootstrap Js -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

</body>
</html>