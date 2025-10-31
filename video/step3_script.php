<?php
session_start();



// Redirect user if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
// Validate that a numeric Campaign ID was provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php'); // Go back to the campaign dashboard
    exit();
}

require '../config/db.php';
$user_id = $_SESSION['user_id'];
$campaign_id = $_GET['id'];
$cloudinary_cloud_name = "demo"; // Replace with your cloud name

// --- Security Check & Data Fetching ---
// 1. Fetch campaign details and verify ownership
$stmt_campaign = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? AND user_id = ?");
$stmt_campaign->execute([$campaign_id, $user_id]);
$campaign = $stmt_campaign->fetch();
if (!$campaign) {
    header('Location: index.php?status=error&message=Invalid_Campaign');
    exit();
}

// 2. Fetch all ACTIVE video templates for the library
$stmt_videos = $pdo->query("SELECT v.*, c.name as category_name, GROUP_CONCAT(t.name SEPARATOR ',') as tags FROM video_templates v LEFT JOIN video_categories c ON v.category_id = c.id LEFT JOIN template_tags tt ON v.id = tt.template_id LEFT JOIN video_tags t ON tt.tag_id = t.id WHERE v.status = 'Active' GROUP BY v.id ORDER BY v.created_at DESC");
$videos = $stmt_videos->fetchAll();

// 3. Fetch all unique categories and tags for the filters
$categories = $pdo->query("SELECT * FROM video_categories ORDER BY name ASC")->fetchAll();
$tags = $pdo->query("SELECT * FROM video_tags ORDER BY name ASC")->fetchAll();

// 4. Fetch previously saved inspiration videos to pre-select them
$selected_videos_array = !empty($campaign['inspiration_videos']) ? explode(',', $campaign['inspiration_videos']) : [];


if (!$campaign) { /* ... redirect if invalid ... */ }

// --- Fetch previously saved script data to pre-fill the form ---
$script_link = $campaign['script_link'];
$script_text = $campaign['script_text'];

include '../includes/header.php';
?>
<!-- Include TinyMCE Rich Text Editor library -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="page-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div id="content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <!-- Header -->
            <h1 class="my-4" style="color: #1e293b;">Campaign: <?= htmlspecialchars($campaign['campaign_name']) ?></h1>

            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Step 3: Provide the Video Script</h5>
                    <!-- Link to go back to the previous step -->
                    <a href="step2_assets.php?id=<?= $campaign_id ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
                </div>
                <hr>
                <form action="../process/campaign_process.php" method="post" id="step3-form">
                    <input type="hidden" name="action" value="save_step_3">
                    <input type="hidden" name="campaign_id" value="<?= $campaign_id ?>">
                    
                    <div class="mb-3">
                        <label for="script-link" class="form-label">Script Link (Optional)</label>
                        <input type="url" id="script-link" name="script_link" class="form-control" value="<?= htmlspecialchars($script_link ?? '') ?>" placeholder="e.g., https://docs.google.com/document/...">
                    </div>
                    
                    <div class="text-center my-3">
                        <strong class="text-muted">OR</strong>
                    </div>

                    <div class="mb-3">
                        <label for="script-editor" class="form-label">Write Script Directly</label>
                        <!-- This textarea will be converted into a rich text editor by TinyMCE -->
                        <textarea id="script-editor" name="script_text" class="form-control" rows="10"><?= htmlspecialchars($script_text ?? '') ?></textarea>
                    </div>
                    
                    <p class="text-muted small">You can provide either a link or write the script directly. If both are provided, the direct script will be prioritized.</p>
                    
                    <button type="submit" class="btn btn-custom w-100 mt-3">Save and Continue to Next Step</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>