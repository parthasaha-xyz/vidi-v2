<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
require_once __DIR__ . '/../config/db.php';

// Fetch all avatars with their category and tags for the main table
$stmt_avatars = $pdo->query("
    SELECT a.*, c.name as category_name, GROUP_CONCAT(t.name SEPARATOR ', ') as tags
    FROM avatars a
    LEFT JOIN video_categories c ON a.category_id = c.id
    LEFT JOIN avatar_tags at ON a.id = at.avatar_id
    LEFT JOIN video_tags t ON at.tag_id = t.id
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$avatars = $stmt_avatars->fetchAll();

include 'includes/header.php';
?>
<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 style="color: #1e293b;">Manage Avatars</h1>
                <a href="avatar_form.php" class="btn btn-custom"><i class="fas fa-plus me-2"></i>Add New Avatar</a>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? 'Action completed.') ?>
                </div>
            <?php endif; ?>

            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Name</th><th>Category</th><th>Tags</th><th>Type</th><th>Gender</th><th>Age Group</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($avatars as $avatar): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($avatar['name']) ?></strong><br>
                                    <small class="text-muted" style="word-break: break-all;"><a href="<?= htmlspecialchars($avatar['video_link'] ?? 'No Link') ?>" target="new">Link</a></small>
                                </td>
                                <td><?= htmlspecialchars($avatar['category_name'] ?? 'N/A') ?></td>
                                <td style="max-width: 200px;"><?= htmlspecialchars($avatar['tags'] ?? 'No Tags') ?></td>
                                <td><span class="badge <?= $avatar['type'] == 'Premium' ? 'bg-warning text-dark' : 'bg-success' ?>"><?= $avatar['type'] ?></span></td>
                                <td><?= htmlspecialchars($avatar['gender']) ?></td>
                                <td><?= htmlspecialchars($avatar['age_group']) ?></td>
                                <td class="text-end">
                                    <a href="avatar_form.php?id=<?= $avatar['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="process/avatar_process.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this avatar?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="avatar_id" value="<?= $avatar['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash-alt"></i></button>
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
<?php include 'includes/footer.php'; ?>