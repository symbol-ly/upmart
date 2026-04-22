<?php
include 'db_connect.php';
include 'functions.php';
session_start();

// Security check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $new_name = $_POST['full_name'];
    $new_role = $_POST['user_role'];

    $result = user_update_profile($conn, $user_id, $full_name, $role);

    if ($result['success']) {
        // Update the session name so the UI reflects the change immediately
        $_SESSION['full_name'] = $new_name;
        header("Location: profile.php?status=success");
    } else {
        header("Location: profile.php?status=error&msg=" . urlencode($result['message']));
    }
    exit();
}
?>