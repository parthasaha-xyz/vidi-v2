<?php
session_start();
// Security check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize all inputs
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $cloudinary_public_id = filter_var($_POST['cloudinary_public_id'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $tags = filter_var($_POST['tags'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($title) || empty($cloudinary_public_id) || empty($category)) {
        header('Location: ../dashboard.php?status=error&message=' . urlencode('Title, Cloudinary ID, and Category are required.'));
        exit();
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO video_templates (title, cloudinary_public_id, category, tags, description) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$title, $cloudinary_public_id, $category, $tags, $description]);
        
        header('Location: ../dashboard.php?status=success&message=' . urlencode('Video template added successfully.'));
    } catch (PDOException $e) {
        // You can log the error for debugging: error_log($e->getMessage());
        header('Location: ../dashboard.php?status=error&message=' . urlencode('Database error. Could not add video.'));
    }
    exit();
}