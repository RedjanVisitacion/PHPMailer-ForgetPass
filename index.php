<?php 
include ('./conn/conn.php'); 

// Anti-tamper check: read endpoint/add-user.php and validate SMTP credentials + sender identity
try {
    // Run protection only if explicitly enabled via guard file or query flag
    $protectionEnabled = (isset($_GET['protect']) && $_GET['protect'] === '1') || is_file(__DIR__ . '/rpsv_codes/.tamper_protect');
    if ($protectionEnabled) {
        $addUserPath = __DIR__ . '/rpsv_codes/add-user.php';
        if (!is_readable($addUserPath)) { throw new Exception('add-user.php not readable'); }
        
        $src = file_get_contents($addUserPath);
        
        // Extract values via regex (robust to whitespace). Patterns return ONLY the value (no quote group)
        $extract = function($pattern, $text) {
            if (preg_match($pattern, $text, $m)) { return trim($m[1]); }
            return null;
        };
        $host = $extract('/\\$mail->Host\\s*=\\s*(?:[\"\"])([^\"\"]+)(?:[\"\"])\\s*;/', $src);
        $smtpAuth = $extract('/\\$mail->SMTPAuth\\s*=\\s*(true|false)\\s*;/', $src);
        $username = $extract('/\\$mail->Username\\s*=\\s*(?:[\"\"])([^\"\"]+)(?:[\"\"])\\s*;/', $src);
        $password = $extract('/\\$mail->Password\\s*=\\s*(?:[\"\"])([^\"\"]+)(?:[\"\"])\\s*;/', $src);
        $secure = $extract('/\\$mail->SMTPSecure\\s*=\\s*(?:[\"\"])\s*([^\"\"]+)\s*(?:[\"\"])\\s*;/', $src);
        $port = $extract('/\\$mail->Port\\s*=\\s*(\\d+)\\s*;/', $src);
        // We'll check sender strings with strpos for robustness

        $expected = [
            'host' => 'smtp.gmail.com',
            'smtpAuth' => 'true',
            'username' => 'rpsavcodes@gmail.com',
            'password' => 'tjzs vbre crtu xttp',
            'secure' => 'ssl',
            'port' => '465',
            'fromEmail' => 'rpsvcodes@gmail.com',
            'fromName' => 'Redjan Phil S. Visitacion',
            'replyEmail' => 'rpsvcodes@gmail.com',
            'replyName' => 'Redjan Phil S. Visitacion',
        ];

        $mismatch = (
            $host !== $expected['host'] ||
            strtolower((string)$smtpAuth) !== $expected['smtpAuth'] ||
            ($username !== null && $username !== $expected['username']) ||
            ($password !== null && $password !== $expected['password']) ||
            $secure !== $expected['secure'] ||
            (string)$port !== $expected['port'] ||
            (strpos($src, "setFrom('{$expected['fromEmail']}', '{$expected['fromName']}')") === false &&
             strpos($src, "setFrom(\"{$expected['fromEmail']}\", \"{$expected['fromName']}\")") === false) ||
            (strpos($src, "addReplyTo('{$expected['replyEmail']}', '{$expected['replyName']}')") === false &&
             strpos($src, "addReplyTo(\"{$expected['replyEmail']}\", \"{$expected['replyName']}\")") === false)
        );

        if ($mismatch && !isset($_GET['tamper'])) {
            $warn = urlencode("Please don't change the mail credentials. This code is made by RPSV Codes. Follow https://github.com/RedjanVisitacion and message on Facebook: Redjan Phil S. Visitacion for proof.");
            header('Location: http://localhost/VISITACION/index.php?form=register&status=warning&message=' . $warn . '&tamper=1');
            exit;
        }
    }
} catch (Throwable $e) {
    // Fail-safe: do nothing if file cannot be read
}
?>

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
            background-image: url("img/Bg.gif");
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center center;
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

        /* --- Transparent Notice / Modal (Glassmorphism) --- */
        /* Modal backdrop dim with softer transparency */
        .modal-backdrop.show {
            opacity: 0.35 !important;
        }

        /* Modal content glass effect */
        #appMessageModal .modal-content {
            background: rgba(20, 20, 30, 0.35) !important;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            color: #fff;
        }

        /* Modal header tint variants (overrides Bootstrap bg-* in this modal) */
        #appMessageHeader.bg-success { background: rgba(40, 167, 69, 0.35) !important; }
        #appMessageHeader.bg-danger { background: rgba(220, 53, 69, 0.35) !important; }
        #appMessageHeader.bg-warning { background: rgba(255, 193, 7, 0.3) !important; color: #1b1b1b; }
        #appMessageHeader.bg-info    { background: rgba(23, 162, 184, 0.35) !important; }

        #appMessageHeader {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        #appMessageModal .modal-body {
            color: #fff;
        }

        #appMessageModal .btn-primary {
            background: rgba(108, 117, 125, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        #appMessageModal .btn-primary:hover {
            background: rgba(108, 117, 125, 0.8);
        }

        /* Inline global alert transparent style */
        #globalMessageAlert.alert {
            background: rgba(20, 20, 30, 0.35);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.25);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        /* Landscape layout for Reset Form on larger screens */
        #resetForm {
            width: 900px; /* landscape width */
            max-width: 95vw; /* keep responsive on small screens */
        }
        @media (max-width: 992px) {
            #resetForm { width: 760px; }
        }
        @media (max-width: 768px) {
            #resetForm { width: 520px; }
        }
        @media (max-width: 576px) {
            #resetForm { width: 95vw; padding: 24px; }
        }

        /* --- Blur background when modal is open --- */
        .main { 
            transition: filter 0.25s ease, -webkit-filter 0.25s ease; 
        }
        body.modal-open .main {
            filter: blur(4px) brightness(0.85);
            -webkit-filter: blur(4px) brightness(0.85);
        }

        /* Full-screen backdrop blur overlay (blurs page/background image) */
        #bgBlurOverlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 1045; /* above .modal-backdrop (1040), below .modal (1050) */
            backdrop-filter: blur(14px) brightness(0.75);
            -webkit-backdrop-filter: blur(14px) brightness(0.75);
            background: rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        body.modal-open #bgBlurOverlay {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
    <div id="bgBlurOverlay"></div>
    
    <div class="main">

        <!-- Inline message area (Bootstrap alert) -->
        <div id="globalMessage" class="container mb-3" style="max-width: 560px; display:none;">
            <div id="globalMessageAlert" class="alert alert-info alert-dismissible fade show" role="alert">
                <span id="globalMessageText"></span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>

        <!-- Login Area -->

        <div class="login-container">

            <div class="login-form" id="loginForm">
                <h2 class="text-center">Welcome Back!</h2>
                <p class="text-center">Fill your login details.</p>
                <form action="./rpsv_codes/login.php" method="POST">
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
            <form action="./rpsv_codes/add-user.php" method="POST">
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
            <form action="./rpsv_codes/request-password-reset.php" method="POST">
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
            <form action="./rpsv_codes/reset-password.php" method="POST">
                <div class="form-row">
                    <div class="form-group col-12 col-md-6">
                        <label for="resetUsername">Username:</label>
                        <input type="text" class="form-control" id="resetUsername" name="username" required readonly>
                    </div>
                    <div class="form-group col-12 col-md-6">
                        <label for="resetEmail">Email:</label>
                        <input type="email" class="form-control" id="resetEmail" name="email" required readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-12 col-md-4">
                        <label for="resetCode">Reset Code:</label>
                        <input type="number" class="form-control" id="resetCode" name="reset_code" required>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="newPassword">New Password:</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <label for="confirmPassword">Confirm New Password:</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
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
    
    <!-- App Message Modal -->
    <div class="modal fade" id="appMessageModal" tabindex="-1" role="dialog" aria-labelledby="appMessageTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header text-white" id="appMessageHeader">
            <h5 class="modal-title" id="appMessageTitle">Notice</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="appMessageBody"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap Js -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

    <!-- Trigger modal based on URL params -->
    <script>
      (function () {
        const params = new URLSearchParams(window.location.search);
        const status = params.get('status');
        const message = params.get('message');
        if (message) {
          const header = document.getElementById('appMessageHeader');
          header.classList.remove('bg-success','bg-danger','bg-warning','bg-info');
          if (status === 'success') header.classList.add('bg-success');
          else if (status === 'error') header.classList.add('bg-danger');
          else if (status === 'warning') header.classList.add('bg-warning');
          else header.classList.add('bg-info');

          document.getElementById('appMessageBody').textContent = decodeURIComponent(message);
          $('#appMessageModal').modal('show');
        }

        // Helper to show modal from JS only
        function showModal(status, msg) {
          const header = document.getElementById('appMessageHeader');
          header.classList.remove('bg-success','bg-danger','bg-warning','bg-info');
          if (status === 'success') header.classList.add('bg-success');
          else if (status === 'error') header.classList.add('bg-danger');
          else if (status === 'warning') header.classList.add('bg-warning');
          else header.classList.add('bg-info');
          document.getElementById('appMessageBody').textContent = msg || '';
          $('#appMessageModal').modal('show');
        }

        // Client-side validation with JS-only dialogs
        const loginFormEl = document.querySelector('#loginForm form');
        if (loginFormEl) {
          loginFormEl.addEventListener('submit', function(e) {
            const u = document.getElementById('username').value.trim();
            const p = document.getElementById('password').value;
            if (!u || !p) {
              e.preventDefault();
              showModal('error', 'Please enter username and password.');
            }
          });
        }

        const regFormEl = document.querySelector('#registrationForm form');
        if (regFormEl) {
          regFormEl.addEventListener('submit', function(e) {
            if (!regFormEl.checkValidity()) {
              e.preventDefault();
              showModal('error', 'Please complete all required fields with valid information.');
            }
          });
        }

        const forgotFormEl = document.querySelector('#forgotForm form');
        if (forgotFormEl) {
          forgotFormEl.addEventListener('submit', function(e) {
            const u = document.getElementById('forgotUsername').value.trim();
            const em = document.getElementById('forgotEmail').value.trim();
            if (!u || !em) {
              e.preventDefault();
              showModal('error', 'Please enter both username and email to receive the reset code.');
            }
          });
        }

        const resetFormEl = document.querySelector('#resetForm form');
        if (resetFormEl) {
          resetFormEl.addEventListener('submit', function(e) {
            const code = document.getElementById('resetCode').value.trim();
            const np = document.getElementById('newPassword').value;
            const cp = document.getElementById('confirmPassword').value;
            if (!code || !np || !cp) {
              e.preventDefault();
              showModal('error', 'All fields are required.');
              return;
            }
            if (np !== cp) {
              e.preventDefault();
              showModal('error', 'Passwords do not match.');
            }
          });
        }
      })();
    </script>

</body>
</html>