<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php'; // For BASE_URL

// Fetch all data needed for the page
$stmt_videos = $pdo->query("SELECT v.*, c.name as category_name, GROUP_CONCAT(t.name SEPARATOR ',') as tags FROM video_templates v LEFT JOIN video_categories c ON v.category_id = c.id LEFT JOIN template_tags tt ON v.id = tt.template_id LEFT JOIN video_tags t ON tt.tag_id = t.id GROUP BY v.id ORDER BY v.created_at DESC");
$videos = $stmt_videos->fetchAll();
$categories = $pdo->query("SELECT * FROM video_categories ORDER BY name ASC")->fetchAll();
$tags = $pdo->query("SELECT * FROM video_tags ORDER BY name ASC")->fetchAll();

include 'includes/header.php';
?>
<!-- Add Video.js library for the player -->
<link href="https://vjs.zencdn.net/7.17.0/video-js.css" rel="stylesheet" />

<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 style="color: #1e293b;">Manage Video Templates</h1>
                <a href="video_form.php" class="btn btn-custom"><i class="fas fa-plus me-2"></i>Add New Video</a>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? 'Action completed.') ?>
                </div>
            <?php endif; ?>

            <!-- =================================================================
             * FILTER CONTROLS (RESTORED)
             * ================================================================= -->
            <div class="stat-card mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <select id="category-filter" class="form-control">
                            <option value="">Filter by Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="tag-filter" class="form-control">
                            <option value="">Filter by Tag</option>
                             <?php foreach ($tags as $tag): ?>
                                <option value="<?= htmlspecialchars($tag['name']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                         <select id="status-filter" class="form-control">
                            <option value="">Filter by Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button id="reset-filters" class="btn btn-outline-secondary w-100">Reset All Filters</button>
                    </div>
                </div>
            </div>

            <!-- Video Card Grid -->
            <div class="row" id="admin-video-grid">
                <?php if (empty($videos)): ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">No video templates found.</h4>
                        <p><a href="video_form.php">Click here to add the first one.</a></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($videos as $video): ?>
                        <div class="col-xl-4 col-md-6 mb-4 admin-video-card-wrapper" 
                             data-category="<?= htmlspecialchars($video['category_name'] ?? '') ?>" 
                             data-tags="<?= htmlspecialchars($video['tags'] ?? '') ?>"
                             data-status="<?= htmlspecialchars($video['status']) ?>">
                            <div class="admin-video-card">
                                <div class="admin-video-embed-wrapper">
                                    <?php if (!empty($video['video_link'])): ?>
                                        <video
                                            class="video-js vjs-default-skin"
                                            controls
                                            preload="metadata"
                                            poster="<?= (!empty($video['video_link']) && strpos($video['video_link'], 'cloudinary') !== false) ? str_replace('/upload/', '/upload/w_400,h_225,c_fill,q_auto,so_2/', $video['video_link']) . '.jpg' : '' ?>"
                                            data-setup="{}">
                                            <source src="<?= htmlspecialchars($video['video_link']) ?>" type="video/mp4" />
                                        </video>
                                    <?php else: ?>
                                        <div class="generic-video-thumb h-100">
                                            <i class="fas fa-video-slash"></i>
                                            <span class="small d-block mt-2">No Video Link</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="admin-video-card-body">
                                     <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="admin-video-card-title"><?= htmlspecialchars($video['title']) ?></h5>
                                        <span class="badge flex-shrink-0 <?= $video['status'] == 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $video['status'] ?>
                                        </span>
                                    </div>
                                    <div class="admin-video-card-tags mt-2">
                                        <?php if (!empty($video['tags'])): ?>
                                            <?php foreach (explode(',', $video['tags']) as $tag): ?>
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars(trim($tag)) ?></span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="admin-video-card-footer">
                                    <a href="assign_videos.php?video_id=<?= $video['id'] ?>" class="btn btn-sm btn-outline-info" title="Assign"><i class="fas fa-user-plus"></i></a>
                                    <a href="video_form.php?id=<?= $video['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="process/video_process.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div id="no-results-message" class="col-12 text-center py-5" style="display: none;">
                    <h4 class="text-muted">No videos match your filters.</h4>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add Video.js script file -->
<script src="https://vjs.zencdn.net/7.17.0/video.min.js"></script>
<?php include 'includes/footer.php'; ?>