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


include '../includes/header.php';
?>
<!-- Add Video.js library for the player -->
<link href="https://vjs.zencdn.net/7.17.0/video-js.css" rel="stylesheet" />

<div class="page-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div id="content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <!-- Header and Navigation -->
            <a href="index.php" class="auth-link mb-3 d-inline-block"><i class="fas fa-arrow-left me-2"></i>Back to Campaigns</a>
            <h1 class="my-4" style="color: #1e293b;">Campaign: <?= htmlspecialchars($campaign['campaign_name']) ?></h1>

            <div class="row">
                <!-- Left Column: Filters -->
                <div class="col-lg-3 mb-4">
                    <div class="stat-card">
                        <h5><i class="fas fa-filter me-2"></i>Filters</h5>
                        <hr>
                        <!-- Search Box -->
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <input type="text" id="video-search-input" class="form-control" placeholder="By title or description...">
                        </div>
                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label">Categories</label>
                            <div id="category-filter-list">
                                <?php foreach ($categories as $category): ?>
                                <div class="form-check">
                                    <input class="form-check-input category-filter-check" type="checkbox" value="<?= htmlspecialchars($category['name']) ?>" id="cat-<?= $category['id'] ?>">
                                    <label class="form-check-label" for="cat-<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Tag Filter -->
                        <div>
                            <label class="form-label">Tags</label>
                            <div id="tag-filter-list" class="tag-cloud">
                                <?php foreach ($tags as $tag): ?>
                                    <span class="tag-filter-pill" data-tag="<?= htmlspecialchars($tag['name']) ?>"><?= htmlspecialchars($tag['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Video Library & Form -->
                <div class="col-lg-9 mb-4">
                    <div class="stat-card">
                        <form action="../process/campaign_process.php" method="post" id="step1-form">
                            <input type="hidden" name="action" value="save_step_1">
                            <input type="hidden" name="campaign_id" value="<?= $campaign_id ?>">
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Step 1: Select up to 3 Inspiration Videos</h5>
                                <button type="submit" id="next-step-button" class="btn btn-custom" disabled>Save & Continue <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                            
                            <div class="row" id="inspiration-video-grid">
                                <?php foreach ($videos as $video): ?>
                                    <?php $is_selected = in_array($video['id'], $selected_videos_array); ?>
                                    <div class="col-xl-3 col-md-4 mb-4 inspiration-video-col" 
                                         data-category="<?= htmlspecialchars($video['category_name'] ?? '') ?>" 
                                         data-tags="<?= htmlspecialchars($video['tags'] ?? '') ?>"
                                         data-title="<?= strtolower(htmlspecialchars($video['title'])) ?>"
                                         data-description="<?= strtolower(htmlspecialchars($video['description'] ?? '')) ?>">
                                        
                                        <div class="inspiration-card-vertical <?= $is_selected ? 'selected' : '' ?>" data-video-id="<?= $video['id'] ?>">
                                            <!-- Hidden checkbox to submit selected IDs -->
                                            <input type="checkbox" name="inspiration_videos[]" value="<?= $video['id'] ?>" class="d-none video-checkbox" <?= $is_selected ? 'checked' : '' ?>>
                                            
                                            <div class="video-container-vertical">
                                                <video class="video-js vjs-default-skin" loop muted preload="metadata" poster="<?= (!empty($video['video_link']) && strpos($video['video_link'], 'cloudinary') !== false) ? str_replace('/upload/', '/upload/w_300,h_533,c_fill,q_auto,so_2/', $video['video_link']) . '.jpg' : '' ?>">
                                                    <source src="<?= htmlspecialchars($video['video_link'] ?? '') ?>" type="video/mp4" />
                                                </video>
                                                <div class="play-button-overlay"><i class="fas fa-play"></i></div>
                                                <div class="selection-checkmark-overlay"><i class="fas fa-check-circle"></i></div>
                                            </div>
                                            <div class="video-card-body-vertical">
                                                <h6 class="video-title-vertical"><?= htmlspecialchars($video['title']) ?></h6>
                                                <button type="button" class="btn btn-sm <?= $is_selected ? 'btn-primary' : 'btn-outline-primary' ?> select-video-btn w-100">
                                                    <?= $is_selected ? 'Selected' : 'Select' ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div id="no-results-message" class="col-12 text-center text-muted" style="display: none;">
                                    <h4>No videos match your filters.</h4>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://vjs.zencdn.net/7.17.0/video.min.js"></script>
<?php include '../includes/footer.php'; ?>