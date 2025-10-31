<?php
session_start();
// This is the security check for all admin pages
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Connect to the database to fetch existing videos
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->query("SELECT * FROM video_templates ORDER BY created_at DESC");
$videos = $stmt->fetchAll();

include 'includes/header.php';
?>
<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <h1 class="my-4" style="color: #1e293b;">Admin Dashboard</h1>
            
            <!-- Status messages -->
            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? 'Action completed.') ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Video Upload Form -->
                <div class="col-lg-5 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-upload me-2"></i>Upload New Video Template</h5>
                        <hr>
                        <form action="process/video_upload.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">Video Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cloudinary Public ID</label>
                                <input type="text" name="cloudinary_public_id" class="form-control" placeholder="e.g., samples/dog" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-control" required>
                                    <option value="General">General</option>
                                    <option value="Dental">Dental</option>
                                    <option value="Skin Care">Skin Care</option>
                                    <option value="Vet">Vet</option>
                                    <option value="Hair">Hair</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tags (comma-separated)</label>
                                <input type="text" name="tags" class="form-control" placeholder="e.g., happy,promo,clean">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-custom w-100">Add to Library</button>
                        </form>
                    </div>
                </div>
                
                <!-- Existing Videos List -->
                <div class="col-lg-7 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-video me-2"></i>Existing Video Library</h5>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead><tr><th>Title</th><th>Category</th><th>Cloudinary ID</th></tr></thead>
                                <tbody>
                                    <?php foreach ($videos as $video): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($video['title']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($video['category']) ?></span></td>
                                        <td><?= htmlspecialchars($video['cloudinary_public_id']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>