<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require '../config/db.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['clinic_id'])) {
    $clinic_id = filter_var($_POST['clinic_id'], FILTER_VALIDATE_INT);
    if (!$clinic_id) {
        header('Location: ../clinics.php?status=error&message=Invalid_Clinic_ID');
        exit();
    }
    
    // --- Security Check: Verify user owns the clinic ---
    $stmt_check = $pdo->prepare("SELECT * FROM clinics WHERE id = ? AND user_id = ?");
    $stmt_check->execute([$clinic_id, $user_id]);
    $clinic = $stmt_check->fetch();
    if (!$clinic) {
        header('Location: ../clinics.php?status=error&message=Authorization_Error');
        exit();
    }

    // --- Reusable Upload Function ---
    function process_upload($file_key, $clinic_id, $current_path) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$file_key];
            
            // Validation (size, type)
            if ($file['size'] > 5242880) return ['error' => 'File size exceeds 5MB limit.']; // 5MB limit
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_exts)) return ['error' => 'Invalid file type.'];

            // Create unique filename and path (using the corrected '../' path)
            $new_file_name = "clinic_{$clinic_id}_{$file_key}_" . time() . '.' . $file_ext;
            $target_dir = '../uploads/clinics/';
            $target_file = $target_dir . $new_file_name;
            $db_path = 'uploads/clinics/' . $new_file_name;

            // Move the file
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                // Delete old file if it exists
                if ($current_path && file_exists('../' . $current_path)) {
                    unlink('../' . $current_path);
                }
                return ['success' => $db_path];
            } else {
                return ['error' => 'Failed to move uploaded file. Check server permissions.'];
            }
        }
        return ['no_upload' => true];
    }
    
    // --- Process each potential upload ---
    $image_types = ['profile_picture', 'cover_picture', 'interior_picture', 'reception_picture', 'entrance_picture'];
    $update_data = [];
    
    foreach ($image_types as $type) {
        $result = process_upload($type, $clinic_id, $clinic[$type]);
        if (isset($result['success'])) {
            $update_data[$type] = $result['success'];
        } elseif (isset($result['error'])) {
            header('Location: ../clinic_profile.php?id=' . $clinic_id . '&status=error&message=' . urlencode($result['error']));
            exit();
        }
    }

    // --- Update database if there's anything to update ---
    if (!empty($update_data)) {
        $sql_parts = [];
        $params = [];
        foreach ($update_data as $column => $value) {
            $sql_parts[] = "$column = ?";
            $params[] = $value;
        }
        $params[] = $clinic_id;

        $sql = "UPDATE clinics SET " . implode(', ', $sql_parts) . " WHERE id = ?";
        
        try {
            $stmt_update = $pdo->prepare($sql);
            $stmt_update->execute($params);
        } catch (PDOException $e) {
            header('Location: ../clinic_profile.php?id=' . $clinic_id . '&status=error&message=' . urlencode('Database update failed.'));
            exit();
        }
    }

    header('Location: ../clinic_profile.php?id=' . $clinic_id . '&status=success&message=' . urlencode('Images updated successfully.'));
    exit();
}