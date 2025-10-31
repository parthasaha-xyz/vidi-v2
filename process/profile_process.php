<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require '../config/db.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_photo'])) {
    
    // Check for upload errors
    if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        header('Location: ../profile.php?status=error&message=' . urlencode('File upload error.'));
        exit();
    }

    $file = $_FILES['profile_photo'];
    $file_name = $file['name'];
    $file_tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    
    // --- Validation ---
    // Check file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_ext, $allowed_exts)) {
        header('Location: ../profile.php?status=error&message=' . urlencode('Invalid file type. Only JPG, JPEG, & PNG are allowed.'));
        exit();
    }
    // Check file size (e.g., max 2MB)
    if ($file_size > 2097152) {
        header('Location: ../profile.php?status=error&message=' . urlencode('File is too large. Maximum size is 2MB.'));
        exit();
    }

    // --- Process Upload ---
    // Create a new, unique filename to prevent overwrites
    $new_file_name = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
    $target_dir = '../uploads/profiles/';
    $target_file = $target_dir . $new_file_name;
    
    // The relative path to store in the database
    $db_path = 'uploads/profiles/' . $new_file_name;

    if (move_uploaded_file($file_tmp_name, $target_file)) {
        // File uploaded successfully, now update the database
        try {
            // First, get the old photo path to delete it
            $stmt_old = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
            $stmt_old->execute([$user_id]);
            $old_photo = $stmt_old->fetchColumn();
            
            // Update the database with the new path
            $stmt_update = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $stmt_update->execute([$db_path, $user_id]);

            // If an old photo existed and it's a real file, delete it from the server
            if ($old_photo && file_exists('../' . $old_photo)) {
                unlink('../' . $old_photo);
            }

            header('Location: ../profile.php?status=success&message=' . urlencode('Profile photo updated successfully.'));
        } catch (PDOException $e) {
            header('Location: ../profile.php?status=error&message=' . urlencode('Database error.'));
        }
    } else {
        header('Location: ../profile.php?status=error&message=' . urlencode('Failed to move uploaded file.'));
    }
    exit();
}