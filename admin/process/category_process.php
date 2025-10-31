<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

require_once __DIR__ . '/../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    // ACTION: Create a new category
    if ($_POST['action'] == 'create') {
        $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
        if (empty($name)) {
            header('Location: ../categories.php?status=error&message=' . urlencode('Category name cannot be empty.'));
            exit();
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO video_categories (name) VALUES (?)");
            $stmt->execute([$name]);
            header('Location: ../categories.php?status=success&message=' . urlencode('Category created successfully.'));
        } catch (PDOException $e) {
            // Error 1062 is for a duplicate entry
            if ($e->errorInfo[1] == 1062) {
                header('Location: ../categories.php?status=error&message=' . urlencode('This category already exists.'));
            } else {
                header('Location: ../categories.php?status=error&message=' . urlencode('Database error occurred.'));
            }
        }
    }

    // ACTION: Delete an existing category
    if ($_POST['action'] == 'delete') {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: ../categories.php?status=error&message=' . urlencode('Invalid ID.'));
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM video_categories WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: ../categories.php?status=success&message=' . urlencode('Category deleted successfully.'));
    }
    
    exit();
}