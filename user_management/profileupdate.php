<?php
include 'db_connect.php';
include 'functions.php'; // This gives you access to user_update_profile()
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // Get who is logged in
    $new_name = $_POST['full_name']; // Get data from the form
    $new_role = $_POST['user_role'];

    // CALL THE FUNCTION FROM functions.php
    $success = user_update_profile($conn, $user_id, $new_name, $new_role);

    if ($success) {
        $_SESSION['full_name'] = $new_name; // Update current session name
        header("Location: profile_view.php?msg=updated"); // Send them back to their profile
    } else {
        echo "Something went wrong!";
    }
}
?>