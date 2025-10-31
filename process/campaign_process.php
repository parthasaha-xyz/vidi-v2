<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
require '../config/db.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {

    // All actions require a campaign_id, so we can check it once at the top.
    if (!isset($_POST['campaign_id']) || !is_numeric($_POST['campaign_id'])) {
        // Redirect to the main dashboard if no campaign ID is provided.
        header('Location: ../video/index.php?status=error&message=Invalid Campaign ID.');
        exit();
    }
    $campaign_id = (int)$_POST['campaign_id'];

    // --- Security Check: Verify user owns this campaign before any action ---
    $stmt_check = $pdo->prepare("SELECT id FROM campaigns WHERE id = ? AND user_id = ?");
    $stmt_check->execute([$campaign_id, $user_id]);
    if ($stmt_check->rowCount() === 0) {
        header('Location: ../video/index.php?status=error&message=Authorization Error.');
        exit();
    }

    try {
        // --- ACTION: Save Step 1 (Inspiration Videos) ---
        if ($_POST['action'] == 'save_step_1') {
            $inspiration_videos = isset($_POST['inspiration_videos']) && is_array($_POST['inspiration_videos']) ? $_POST['inspiration_videos'] : [];
            $inspiration_videos_str = implode(',', $inspiration_videos);

            $stmt = $pdo->prepare("UPDATE campaigns SET inspiration_videos = ? WHERE id = ?");
            $stmt->execute([$inspiration_videos_str, $campaign_id]);
            
            // Redirect to the SECOND step
            header('Location: ../video/step2_assets.php?id=' . $campaign_id);
            exit();
        }

        // --- ACTION: Save Step 2 (Asset Links) ---
        if ($_POST['action'] == 'save_step_2') {
            $scene_link = filter_var($_POST['scene_image_link'], FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
            $plot_link = filter_var($_POST['plot_image_link'], FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);

            $stmt = $pdo->prepare("UPDATE campaigns SET scene_image_link = ?, plot_image_link = ? WHERE id = ?");
            $stmt->execute([$scene_link, $plot_link, $campaign_id]);
            
            // Redirect to the THIRD step
            header('Location: ../video/step3_script.php?id=' . $campaign_id);
            exit();
        }

        // --- ACTION: Save Step 3 (Script) ---
        if ($_POST['action'] == 'save_step_3') {
            $script_link = filter_var($_POST['script_link'], FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
            // Basic sanitization for rich text. For production, use a library like HTML Purifier.
            $script_text = !empty($_POST['script_text']) ? strip_tags($_POST['script_text'], '<p><strong><em><ul><ol><li><br><a>') : null;

            $stmt = $pdo->prepare("UPDATE campaigns SET script_link = ?, script_text = ? WHERE id = ?");
            $stmt->execute([$script_link, $script_text, $campaign_id]);
            
            // Redirect to the FOURTH step
            header('Location: ../video/step4_finalize.php?id=' . $campaign_id);
            exit();
        }

    } catch (PDOException $e) {
        // Generic error handler for all database exceptions
        // Redirects back to the last known page with an error. The Referer header tells us where the user came from.
        $redirect_url = $_SERVER['HTTP_REFERER'] ?? '../video/index.php';
        header('Location: ' . $redirect_url . '&status=error&message=' . urlencode('Database save failed: ' . $e->getMessage()));
        exit();
    }
}
// If we fall through, something is wrong, go back to the dashboard.
header('Location: ../video/index.php');
exit();