<?php
session_start();
include '../db_connect.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the specific columns from your 'users' table
$query = "SELECT full_name, profile_pic FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$display_name = htmlspecialchars($user['full_name']);

// Handle the "Empty Pic" - if DB is empty, use a placeholder icon
$profile_img = !empty($user['profile_pic']) ? $user['profile_pic'] : "../images/default-avatar.png"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPMart | Dashboard</title>
    <link rel="stylesheet" href="main-panel.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <nav class="sidebar">
        <div class="sidebar-brand">
            <img src="logo.png" class="logo-img" alt="UPMart Logo">
        </div>

        <img src="<?= $profile_img ?>" alt="Profile" class="profile-img" 
             style="border-radius: 50%; width: 80px; height: 80px; object-fit: cover; background: #ddd; margin: 10px auto; display: block;">
        
        <div class="profile-info" style="text-align: center;">
            <span class="profile-name" style="color: white; font-weight: bold;"><?= $display_name ?></span>
        </div>

        <ul class="nav-links">
            <li class="active"><a href="dashboard.php"><span class="icon">🏠︎</span> Dashboard</a></li>
            <li><a href="#"><span class="icon">🛒</span> Marketplace</a></li>
            <div class="logout-container">
                <a href="logout.php" class="logout-btn" style="text-decoration: none; display: block; text-align: center; color: white;">Logout</a>
            </div>
        </ul>
    </nav>

    <div class="main-content">
        <nav class="top-nav">
            <h1 style="font-size: 1.4rem;"><span class="icon">🏠︎</span> Dashboard</h1>
        </nav>

        <div class="content-row">
            <div class="about-text" style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <h1 style="color: maroon;">Welcome back, <?= $display_name ?>!</h1>
                <p>Check out the latest listings from your fellow UP Mindanao students.</p>
            </div>
        </div>
        
        </div>
</body>
</html>