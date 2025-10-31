<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->query("SELECT * FROM video_categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

include 'includes/header.php';
?>
<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <h1 class="my-4" style="color: #1e293b;">Manage Video Categories</h1>
            
            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? 'Action completed.') ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Add New Category Form -->
                <div class="col-lg-4 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-plus-circle me-2"></i>Add New Category</h5>
                        <hr>
                        <form action="process/category_process.php" method="post">
                            <input type="hidden" name="action" value="create">
                            <div class="mb-3">
                                <label class="form-label">Category Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-custom w-100">Create Category</button>
                        </form>
                    </div>
                </div>
                
                <!-- Existing Categories List -->
                <div class="col-lg-8 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-list-ul me-2"></i>Existing Categories</h5>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Category Name</th><th class="text-end">Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($category['name']) ?></td>
                                        <td class="text-end">
                                            <form action="process/category_process.php" method="post" onsubmit="return confirm('Are you sure you want to delete this category? Videos in this category will become uncategorized.');" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </form>
                                        </td>
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