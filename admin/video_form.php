<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
require_once __DIR__ . '/../config/db.php';

// --- CORRECTED LOGIC ORDER ---

// 1. Initialize all variables first.
$is_editing = false;
$video = null;
$page_title = "Add New Video Template";
$form_action = "create";
$selected_tag_names = []; // Initialize as an empty array

// 2. Check if we are in "Edit Mode".
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_editing = true;
    $page_title = "Edit Video Template";
    $form_action = "update";
    
    // 3. If we are editing, fetch ALL data related to the existing video.
    
    // Fetch the main video data
    $stmt = $pdo->prepare("SELECT * FROM video_templates WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $video = $stmt->fetch();
    
    // Fetch the names of the tags associated with this video
    $stmt_tag_names = $pdo->prepare("
        SELECT t.name FROM video_tags t
        JOIN template_tags tt ON t.id = tt.tag_id
        WHERE tt.template_id = ?
    ");
    $stmt_tag_names->execute([$_GET['id']]);
    $selected_tag_names = $stmt_tag_names->fetchAll(PDO::FETCH_COLUMN, 0);
}

// 4. Fetch general data needed for the form in both "Add" and "Edit" modes.
$categories = $pdo->query("SELECT * FROM video_categories ORDER BY name ASC")->fetchAll();
$tags = $pdo->query("SELECT * FROM video_tags ORDER BY name ASC")->fetchAll();

include 'includes/header.php';
?>
<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <a href="videos.php" class="auth-link mb-3 d-inline-block"><i class="fas fa-arrow-left me-2"></i>Back to Video List</a>
            <h1 class="my-4" style="color: #1e293b;"><?= $page_title ?></h1>

            <div class="stat-card">
                <form action="process/video_process.php" method="post">
                    <input type="hidden" name="action" value="<?= $form_action ?>">
                    <?php if ($is_editing): ?>
                        <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Video Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($video['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Video Link</label>
                            <input type="url" name="video_link" class="form-control" value="<?= htmlspecialchars($video['video_link'] ?? '') ?>" placeholder="e.g., https://res.cloudinary.com/.../video.mp4">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= ($video['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Active" <?= ($video['status'] ?? 'Active') == 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= ($video['status'] ?? '') == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <!-- The data-existing-tags attribute will now be correctly populated -->
                        <div class="tag-input-container" data-existing-tags='<?= json_encode($selected_tag_names) ?>'>
                            <div id="tag-chips-wrapper"></div>
                            <input type="text" id="tag-text-input" class="tag-text-input" list="tag-suggestions" placeholder="Type or select a tag and press Enter...">
                        </div>
                        <datalist id="tag-suggestions">
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?= htmlspecialchars($tag['name']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($video['description'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Save Video</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>