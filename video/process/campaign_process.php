<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require '../config/db.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // --- ACTION: Start a New Campaign (from index.php) ---
    if ($_POST['action'] == 'start_campaign') {
        $campaign_name = trim(filter_var($_POST['campaign_name'], FILTER_SANITIZE_STRING));
        if (empty($campaign_name)) {
            header('Location: ../video/index.php?status=error&message=' . urlencode('Campaign Name cannot be empty.'));
            exit();
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO campaigns (user_id, campaign_name, status) VALUES (?, ?, 'Draft')");
            $stmt->execute([$user_id, $campaign_name]);
            $campaign_id = $pdo->lastInsertId();
            
            // --- THIS IS THE CORRECTED REDIRECT ---
            // It now points to the first step of the new multi-page process.
            header('Location: ../video/step1_inspiration.php?id=' . $campaign_id);
            
        } catch (PDOException $e) {
            header('Location: ../video/index.php?status=error&message=' . urlencode('Database error. Could not start campaign.'));
        }
        exit();
    }

    // --- ACTION: Save Step 1 (Inspiration Videos) ---
    if ($_POST['action'] == 'save_step_1' && isset($_POST['campaign_id'])) {
        $campaign_id = filter_var($_POST['campaign_id'], FILTER_VALIDATE_INT);
        // Security Check...
        $stmt_check = $pdo->prepare("SELECT id FROM campaigns WHERE id = ? AND user_id = ?");
        $stmt_check->execute([$campaign_id, $user_id]);
        if ($stmt_check->rowCount() == 0) { exit('Authorization Error.'); }
        
        $inspiration_videos = isset($_POST['inspiration_videos']) ? $_POST['inspiration_videos'] : [];
        $inspiration_videos_str = implode(',', $inspiration_videos);

        try {
            $stmt = $pdo->prepare("UPDATE campaigns SET inspiration_videos = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$inspiration_videos_str, $campaign_id, $user_id]);
            // Redirect to the SECOND step
            header('Location: ../video/step2_assets.php?id=' . $campaign_id);
        } catch (PDOException $e) {
            header('Location: ../video/step1_inspiration.php?id=' . $campaign_id . '&status=error&message=Save failed.');
        }
        exit();
    }

    // --- ACTION: Save Step 2 (Asset Links) ---
    if ($_POST['action'] == 'save_step_2' && isset($_POST['campaign_id'])) {
        $campaign_id = filter_var($_POST['campaign_id'], FILTER_VALIDATE_INT);
        // Security Check...
        $stmt_check = $pdo->prepare("SELECT id FROM campaigns WHERE id = ? AND user_id = ?");
        $stmt_check->execute([$campaign_id, $user_id]);
        if ($stmt_check->rowCount() == 0) { exit('Authorization Error.'); }

        $scene_link = filter_var($_POST['scene_image_link'], FILTER_VALIDATE_URL) ? $_POST['scene_image_link'] : null;
        $plot_link = filter_var($_POST['plot_image_link'], FILTER_VALIDATE_URL) ? $_POST['plot_image_link'] : null;

        try {
            $stmt = $pdo->prepare("UPDATE campaigns SET scene_image_link = ?, plot_image_link = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$scene_link, $plot_link, $campaign_id, $user_id]);
            // Redirect to the THIRD step (e.g., step3_script.php), create this file next
            header('Location: ../video/step3_script.php?id=' . $campaign_id);
        } catch (PDOException $e) {
            header('Location: ../video/step2_assets.php?id=' . $campaign_id . '&status=error&message=Save failed.');
        }
        exit();
    }
}