<?php
session_start();
require '../config/db.php';

// --- SIGNUP LOGIC (from previous step) ---
if (isset($_POST['signup'])) {
    // Sanitize and validate input
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $business_name = filter_var($_POST['business_name'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $otp = rand(100000, 999999);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        header('Location: ../signup.php?error=email_exists');
        exit();
    }

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (name, business_name, phone, email, password, otp) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $business_name, $phone, $email, $password, $otp]);

    // Send OTP email (for production, use a library like PHPMailer)
    $to = $email;
    $subject = 'Your OTP for Clinic Management System';
    $message = "Your OTP is: $otp";
    $headers = 'From: no-reply@yourdomain.com' . "\r\n" .
               'Reply-To: no-reply@yourdomain.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    // mail($to, $subject, $message, $headers); // Uncomment for live server

    $_SESSION['email'] = $email;
    header('Location: ../verify_otp.php');
    exit();
}


// --- NEW LOGIN LOGIC ---
if (isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Find user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify user and password
    if ($user && password_verify($password, $user['password'])) {
        // Check if account is verified
        if ($user['is_verified'] == 1) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['business_name'] = $user['business_name'];
            header('Location: ../dashboard.php');
            exit();
        } else {
            // Redirect if account is not verified
            $_SESSION['email'] = $email; // Set email in session to allow re-verification
            header('Location: ../index.php?error=not_verified');
            exit();
        }
    } else {
        // Redirect on invalid credentials
        header('Location: ../index.php?error=invalid_credentials');
        exit();
    }
}
?>