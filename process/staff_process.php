<?php
session_start();
require '../config/db.php';

// This entire file is for staff processing, so we can check the request method.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ensure the main clinic admin is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login if not authenticated
        header('Location: ../index.php');
        exit();
    }
    
    // Sanitize and retrieve POST data
    $user_id = $_SESSION['user_id'];
    $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Validate the role to ensure it's one of the allowed options
    $allowed_roles = ['Receptionist', 'Patient Cordinator', 'Helping Hand'];
    if (!in_array($role, $allowed_roles)) {
        header('Location: ../clinic_staff.php?status=error&message=' . urlencode('Invalid role selected.'));
        exit();
    }

    // Perform server-side password validation
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $symbol    = preg_match('@[^\w]@', $password); // Matches any non-alphanumeric character

    if (!$uppercase || !$lowercase || !$number || !$symbol || strlen($password) < 8) {
        header('Location: ../clinic_staff.php?status=error&message=' . urlencode('Password does not meet the security criteria.'));
        exit();
    }

    // Check if a staff member with this email already exists
    $stmt = $pdo->prepare("SELECT id FROM clinic_staff WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: ../clinic_staff.php?status=error&message=' . urlencode('A staff member with this email is already registered.'));
        exit();
    }

    // Hash the password for secure storage
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare and execute the insertion query
    $stmt = $pdo->prepare("INSERT INTO clinic_staff (user_id, full_name, phone, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$user_id, $full_name, $phone, $email, $hashed_password, $role])) {
        // Redirect on success
        header('Location: ../clinic_staff.php?status=success');
    } else {
        // Redirect on failure
        header('Location: ../clinic_staff.php?status=error&message=' . urlencode('An unknown database error occurred.'));
    }
    exit();
} else {
    // If someone tries to access this file directly, redirect them
    header('Location: ../dashboard.php');
    exit();
}