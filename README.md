# PHPMailer-ForgetPass
![App Preview](img/RPSVCODES.jpg)

Simple password reset flow using PHP, MySQL, and PHPMailer. Users can request a reset code via email and then set a new password using the code they receive.

## Features
- Forgot Password with email verification code
- Reset password form with validation
- PHPMailer for reliable SMTP email delivery

## Quick Start
1. Import the provided SQL schema: `conn/rpsv.sql` (or your own schema) into your MySQL database.
2. Update database credentials in `conn/conn.php`.
3. Configure PHPMailer SMTP settings in `rpsv_codes/phpmailer` usage or your email-sending script(s).
4. Run the project on your local server (e.g., XAMPP) and open `index.php`.

## File Highlights
- `index.php` – login, forgot/reset forms UI
- `verification.php` – code verification handling
- `home.php` – post-login landing page
- `conn/conn.php` – database connection
- `endpoint/` – server-side endpoints and PHPMailer library

## Credits
- PHPMailer by the PHPMailer team: https://github.com/PHPMailer/PHPMailer
- Built for the Information Assurance Security (Activity)
- Created by: Redjan Phil S. Visitacion
