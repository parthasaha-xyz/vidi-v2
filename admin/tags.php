<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->query("SELECT * FROM video_tags ORDER BY name ASC");
$tags = $stmt->fetchAll();
include 'includes/header.php';
?>
<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <h1 class="my-4" style="color: #1e293b;">Manage Video Tags</h1>
            <!-- Status Messages -->
            <div class="row">
                <!-- Add New Tag Form -->
                <div class="col-lg-4 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-plus-circle me-2"></i>Add New Tag</h5>
                        <hr>
                        <form action="process/tag_process.php" method="post">
                            <input type="hidden" name="action" value="create">
                            <div class="mb-3">
                                <label class="form-label">Tag Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-custom w-100">Create Tag</button>
                        </form>
                    </div>
                </div>
                <!-- Existing Tags List -->
                <div class="col-lg-8 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-tags me-2"></i>Existing Tags</h5>
                        <hr>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                            <div class="tag-chip">
                                <span><?= htmlspecialchars($tag['name']) ?></span>
                                <form action="process/tag_process.php" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $tag['id'] ?>">
                                    <button type="submit" class="tag-delete-btn">&times;</button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>