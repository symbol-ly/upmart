<?php
include '../db_connect.php';    
session_start();

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. LOGIN LOGIC (For the sidebar)
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $email = trim($_POST['up_email']);
        $password = trim($_POST['password']);

        try {
            $stmt = $conn->prepare("SELECT user_id, full_name, password FROM Users WHERE up_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "Database Error: " . $e->getMessage();
        }
    }
    
    // 2. CONTACT FORM LOGIC (For the message form on the right)
    if (isset($_POST['contact_name'])) {
        // Here you could save the message to a 'Contacts' table
        $success_message = "Thank you, " . htmlspecialchars($_POST['contact_name']) . "! Your message has been sent.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | UPMart</title>
    <link rel="stylesheet" href="loginpanel.css">
</head>
<body>
    <div class="main-container">
        <div class="form-container">
            <div class="forms-slider" id="formsSlider">
               <form class="form-section" action="about.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="brand">
                        <img src="../images/logo.png" class="logo-image" alt="UPMart Logo">
                    </div>

                    <?php if($error_message): ?>
                        <p style="color:red; font-size:0.8rem; text-align:center;"><?php echo $error_message; ?></p>
                    <?php endif; ?>

                    <?php if($success_message): ?>
                        <p style="color:green; font-weight:bold;"><?php echo $success_message; ?></p>
                    <?php endif; ?>
                    
                    <div class="input-group">
                        <input type="email" name="up_email" placeholder="UP Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="login-btn">LOGIN</button>    

                    <div class="form-footer">
                        <label class="remember-me"><input type="checkbox"> Remember me</label><br><br>
                        <a href="#" class="forgot-link">Forgot password?</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="visual-container">
            <nav class="top-nav">
                <a href="about.php" class="nav-link" style="opacity: 1; border: 1px solid white; padding: 4px 12px; border-radius: 4px;">ABOUT</a>
                <a href="contact.php" class="nav-link">CONTACT</a>
                <a href="login.php" class="nav-link">SIGN UP</a>
            </nav>

            <div class="content-row">
                <div class="about-text">
                    <h1>What is UPMart?</h1>
                    <p>UP Mindanao students currently navigate a disorganized Facebook-based marketplace that makes finding essential goods difficult and necessitates expensive trips to downtown Davao. The UPMART project will resolve this by launching a dedicated web application featuring categorized listings and dynamic filters to create a streamlined, efficient, and exclusive campus trade experience.</p>
                </div>
                <div class="cart-wrapper">
                    <img src=../images/cart-icon.png alt="Shopping Cart" class="cart-image">
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleForm() {
            const slider = document.getElementById('formsSlider');
            slider.classList.toggle('slide-active');
        }
    </script>
</body>
</html>