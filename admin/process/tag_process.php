<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    // ACTION: Create a new tag
    if ($_POST['action'] == 'create') {
        $name = strtolower(filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING));
        if (empty($name)) {
            header('Location: ../tags.php?status=error&message=Tag name cannot be empty.');
            exit();
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO video_tags (name) VALUES (?)");
            $stmt->execute([$name]);
            header('Location: ../tags.php?status=success&message=Tag created successfully.');
        } catch (PDOException $e) {
            header('Location: ../tags.php?status=error&message=This tag already exists.');
        }
    }

    // ACTION: Delete an existing tag
    if ($_POST['action'] == 'delete') {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $stmt = $pdo->prepare("DELETE FROM video_tags WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: ../tags.php?status=success&message=Tag deleted successfully.');
    }
    
    exit();
}