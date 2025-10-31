<?php
session_start();

// --- Hardcoded credentials ---
// In a real production environment, fetch this from a database and use hashed passwords.
$admin_email = 'admin@gmail.com';
$admin_password = 'admin';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if credentials match
    if ($email === $admin_email && $password === $admin_password) {
        // Set a specific session variable for the admin
        $_SESSION['admin_logged_in'] = true;
        header('Location: ../dashboard.php'); // Redirect to admin dashboard
        exit();
    } else {
        // If they don't match, redirect back to login with an error
        header('Location: ../index.php?error=1');
        exit();
    }
} else {
    // If accessed directly, just redirect to login
    header('Location: ../index.php');
    exit();
}