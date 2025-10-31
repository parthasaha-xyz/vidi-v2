<?php
session_start();
// ... (PHP code to fetch campaign details and verify ownership, same as in step1) ...
if (!$campaign) { /* ... redirect if invalid ... */ }

// --- Fetch previously saved links to pre-fill the form ---
$scene_link = $campaign['scene_image_link'];
$plot_link = $campaign['plot_image_link'];

include '../includes/header.php';
?>
<div class="page-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div id="content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <!-- Header -->
            <h1 class="my-4" style="color: #1e293b;">Campaign: <?= htmlspecialchars($campaign['campaign_name']) ?></h1>

            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Step 2: Provide Scene & Plot Images</h5>
                    <!-- Link to go back to the previous step -->
                    <a href="step1_inspiration.php?id=<?= $campaign_id ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
                </div>
                <hr>
                <form action="../process/campaign_process.php" method="post">
                    <input type="hidden" name="action" value="save_step_2">
                    <input type="hidden" name="campaign_id" value="<?= $campaign_id ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Scene Image Google Drive Link</label>
                            <input type="url" name="scene_image_link" class="form-control" value="<?= htmlspecialchars($scene_link ?? '') ?>" placeholder="https://drive.google.com/..." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Plot Image Google Drive Link</label>
                            <input type="url" name="plot_image_link" class="form-control" value="<?= htmlspecialchars($plot_link ?? '') ?>" placeholder="https://drive.google.com/..." required>
                        </div>
                    </div>
                    <p class="text-muted small">Please ensure your Google Drive links are set to "Anyone with the link can view".</p>
                    <button type="submit" class="btn btn-custom w-100 mt-3">Save and Continue to Next Step</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>