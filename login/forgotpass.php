<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to Composer's autoload
include '../db_connect.php';

if (isset($_POST['check-email'])) {
    $email = trim($_POST['up_email']);
    
    // 1. Generate a 6-digit OTP
    $otp = rand(100000, 999999);
    $expires = date("Y-m-d H:i:s", strtotime('+15 minutes'));

    // 2. Save OTP to Database
    $stmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_expires = ? WHERE up_email = ?");
    $stmt->bind_param("iss", $otp, $expires, $email);
    
    if ($stmt->execute()) {
        // 3. Send Email via PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings (using Gmail as an example)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your-gmail@gmail.com'; 
            $mail->Password   = 'your-app-password'; // Use Google App Password, not your real password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('upmart-admin@up.edu.ph', 'UPMart Admin');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your UPMart Password Reset Code';
            $mail->Body    = "Your verification code is: <b>$otp</b>. It expires in 15 minutes.";

            $mail->send();
            session_start();
            $_SESSION['reset_email'] = $email;
            header("Location: reset_password.php"); // Redirect to the page where they type the code
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | UPMart</title>
    <link rel="stylesheet" href="loginpanel.css">
</head>
<body>
    <div class="main-container">
        <div class="form-container" style="padding: 40px; background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div class="brand">
                <img src="../images/logo.png" style="width: 80px; margin-bottom: 20px;" alt="UPMart Logo">
            </div>
            
            <h2 style="color: #1a1a2e; margin-bottom: 10px;">Forgot Password?</h2>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 20px;">
                Enter your UP email and we'll send you a secure link to reset your password.
            </p>
            
            <form method="post" action="send-password-reset.php">
                <div class="input-group">
                    <input 
                        type="email" 
                        name="up_email" 
                        placeholder="UP Email (@up.edu.ph)" 
                        pattern=".+@up\.edu\.ph" 
                        required
                    >
                </div>
                <button type="submit" class="login-btn">Send Reset Link</button>
            </form>

            <p style="margin-top: 20px; font-size: 0.9rem;">
                Remembered it? <a href="login.php" style="color: #1a1a2e; font-weight: bold;">Back to Login</a>
            </p>
        </div>
    </div>
</body>
</html>