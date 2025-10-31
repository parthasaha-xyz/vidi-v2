<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    switch ($_POST['action']) {
        case 'create':
            handle_avatar_save($pdo, false);
            break;
        case 'update':
            handle_avatar_save($pdo, true);
            break;
        case 'delete':
            handle_avatar_delete($pdo);
            break;
        default:
            header('Location: ../avatars.php?status=error&message=Unknown action.');
            exit();
    }
}

function handle_avatar_save($pdo, $is_update = false) {
    // 1. Sanitize all main inputs from the form
    $name = trim(filter_var($_POST['name'], FILTER_SANITIZE_STRING));
    $video_link = filter_var($_POST['video_link'], FILTER_VALIDATE_URL) ? $_POST['video_link'] : null;
    $category_id = !empty($_POST['category_id']) ? filter_var($_POST['category_id'], FILTER_VALIDATE_INT) : null;
    $type = in_array($_POST['type'], ['Free', 'Premium']) ? $_POST['type'] : 'Free';
    $gender = in_array($_POST['gender'], ['Male', 'Female', 'Other']) ? $_POST['gender'] : 'Other';
    $age_group = in_array($_POST['age_group'], ['Young', 'Middle-aged', 'Senior']) ? $_POST['age_group'] : 'Young';
    $description = trim(filter_var($_POST['description'], FILTER_SANITIZE_STRING));
    
    // Get the comma-separated string of tag names from the hidden input
    $tags_string = trim($_POST['tags']);

    if (empty($name)) {
        header('Location: ../avatars.php?status=error&message=Avatar Name is required.');
        exit();
    }

    $pdo->beginTransaction();
    try {
        if ($is_update) {
            $avatar_id = filter_var($_POST['avatar_id'], FILTER_VALIDATE_INT);
            $stmt = $pdo->prepare("UPDATE avatars SET name=?, video_link=?, category_id=?, description=?, type=?, gender=?, age_group=? WHERE id=?");
            $stmt->execute([$name, $video_link, $category_id, $description, $type, $gender, $age_group, $avatar_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO avatars (name, video_link, category_id, description, type, gender, age_group) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $video_link, $category_id, $description, $type, $gender, $age_group]);
            $avatar_id = $pdo->lastInsertId();
        }

        // =================================================================
        // THIS IS THE FULL, CORRECTED TAG PROCESSING LOGIC
        // =================================================================
        
        // 1. First, clear all existing tag associations for this avatar.
        $stmt_delete_tags = $pdo->prepare("DELETE FROM avatar_tags WHERE avatar_id = ?");
        $stmt_delete_tags->execute([$avatar_id]);

        // 2. Process the new tag string if it's not empty.
        if (!empty($tags_string)) {
            // Create a clean array of unique, non-empty, lowercase tag names
            $tags_array = array_unique(array_filter(array_map('trim', explode(',', $tags_string))));
            
            // Prepare the SQL statements once for efficiency inside the loop
            $stmt_find_tag = $pdo->prepare("SELECT id FROM video_tags WHERE name = ?");
            $stmt_add_tag = $pdo->prepare("INSERT INTO video_tags (name) VALUES (?)");
            $stmt_link_tag = $pdo->prepare("INSERT INTO avatar_tags (avatar_id, tag_id) VALUES (?, ?)");

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
                
                // Finally, link the avatar and the tag in the `avatar_tags` pivot table
                $stmt_link_tag->execute([$avatar_id, $tag_id]);
            }
        }
        
        $pdo->commit();
        header('Location: ../avatars.php?status=success&message=Avatar saved successfully.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: ../avatars.php?status=error&message=Database error: ' . $e->getMessage());
    }
    exit();
}

function handle_avatar_delete($pdo) {
    $avatar_id = filter_var($_POST['avatar_id'], FILTER_VALIDATE_INT);
    if (!$avatar_id) {
        header('Location: ../avatars.php?status=error&message=Invalid ID.');
        exit();
    }
    // The ON DELETE CASCADE constraint will automatically clean up the avatar_tags table
    $stmt = $pdo->prepare("DELETE FROM avatars WHERE id = ?");
    $stmt->execute([$avatar_id]);
    header('Location: ../avatars.php?status=success&message=Avatar deleted successfully.');
    exit();
}