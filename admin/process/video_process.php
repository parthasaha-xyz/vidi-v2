<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    // --- CREATE ACTION ---
    if ($_POST['action'] == 'create') {
        // ... (Code for creating a video)
        handle_video_save($pdo);
    }

    // --- UPDATE ACTION ---
    if ($_POST['action'] == 'update') {
        // ... (Code for updating a video)
        handle_video_save($pdo, true);
    }
    
    // --- DELETE ACTION ---
    if ($_POST['action'] == 'delete') {
        $video_id = filter_var($_POST['video_id'], FILTER_VALIDATE_INT);
        $stmt = $pdo->prepare("DELETE FROM video_templates WHERE id = ?");
        $stmt->execute([$video_id]);
        header('Location: ../videos.php?status=success&message=' . urlencode('Video deleted successfully.'));
        exit();
    }
}

// --- Reusable function to handle both CREATE and UPDATE ---
// In admin/process/video_process.php

function handle_video_save($pdo, $is_update = false) {
    // 1. Sanitize main inputs
    $title = trim(filter_var($_POST['title'], FILTER_SANITIZE_STRING));
    $video_link = filter_var($_POST['video_link'], FILTER_VALIDATE_URL) ? $_POST['video_link'] : null;
    $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
    $status = in_array($_POST['status'], ['Active', 'Inactive']) ? $_POST['status'] : 'Active';
    $description = trim(filter_var($_POST['description'], FILTER_SANITIZE_STRING));
    
    // The 'tags' input is now a comma-separated string of tag names from the chip input
    $tags_string = trim($_POST['tags']);

    // Basic Validation: Title and Category are mandatory.
    if (empty($title) || empty($category_id)) {
        header('Location: ../videos.php?status=error&message=' . urlencode('Title and Category are required fields.'));
        exit();
    }

    $pdo->beginTransaction();
    try {
        if ($is_update) {
            $video_id = filter_var($_POST['video_id'], FILTER_VALIDATE_INT);
            $stmt = $pdo->prepare("UPDATE video_templates SET title=?, video_link=?, category_id=?, description=?, status=? WHERE id=?");
            $stmt->execute([$title, $video_link, $category_id, $description, $status, $video_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO video_templates (title, video_link, category_id, description, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $video_link, $category_id, $description, $status]);
            $video_id = $pdo->lastInsertId();
        }

        // --- CORRECTED TAG PROCESSING LOGIC ---
        
        // 1. First, clear all existing tag associations for this video.
        $stmt_delete_tags = $pdo->prepare("DELETE FROM template_tags WHERE template_id = ?");
        $stmt_delete_tags->execute([$video_id]);

        // 2. Process the new tag string if it's not empty.
        if (!empty($tags_string)) {
            // Create a clean array of unique, non-empty, lowercase tag names
            $tags_array = array_unique(array_filter(array_map('trim', explode(',', $tags_string))));
            
            // Prepare the SQL statements once for efficiency inside the loop
            $stmt_find_tag = $pdo->prepare("SELECT id FROM video_tags WHERE name = ?");
            $stmt_add_tag = $pdo->prepare("INSERT INTO video_tags (name) VALUES (?)");
            $stmt_link_tag = $pdo->prepare("INSERT INTO template_tags (template_id, tag_id) VALUES (?, ?)");

            foreach ($tags_array as $tag_name) {
                // Check if the tag already exists in the `video_tags` table
                $stmt_find_tag->execute([$tag_name]);
                $tag = $stmt_find_tag->fetch();
                
                if ($tag) {
                    // If it exists, use its ID
                    $tag_id = $tag['id'];
                } else {
                    // If it's a new tag, insert it into `video_tags` and get the new ID
                    $stmt_add_tag->execute([$tag_name]);
                    $tag_id = $pdo->lastInsertId();
                }
                
                // Finally, link the video and the tag in the pivot table `template_tags`
                $stmt_link_tag->execute([$video_id, $tag_id]);
            }
        }
        
        $pdo->commit();
        header('Location: ../videos.php?status=success&message=' . urlencode('Video saved successfully.'));
    } catch (PDOException $e) {
        $pdo->rollBack();
        // It's helpful to see the specific error during development
        $error_message = 'Database error: ' . $e->getMessage();
        header('Location: ../videos.php?status=error&message=' . urlencode($error_message));
    }
    exit();
}