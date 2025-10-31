<?php
session_start();
include '../config/db.php';

if (isset($_POST['verify'])) {
    $otp = $_POST['otp'];
    $email = $_SESSION['email'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp = ?");
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
        $stmt->execute([$email]);
        $_SESSION['user_id'] = $user['id'];
        header('Location: ../dashboard.php');
        exit();
    } else {
        // Handle incorrect OTP
        header('Location: ../verify_otp.php?error=1');
        exit();
    }
}
?>