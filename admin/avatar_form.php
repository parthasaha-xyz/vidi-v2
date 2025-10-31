<?php
session_start();
// ... (admin login check)
require_once __DIR__ . '/../config/db.php';

// --- Form State Logic ---
$is_editing = false;
$avatar = null;
$page_title = "Add New Avatar";
$form_action = "create";
$selected_tag_names = [];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_editing = true;
    $page_title = "Edit Avatar";
    $form_action = "update";
    
    // Fetch existing avatar data
    $stmt = $pdo->prepare("SELECT * FROM avatars WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $avatar = $stmt->fetch();
    
    // Fetch associated tag names for the chip input
    $stmt_tags = $pdo->prepare("SELECT t.name FROM video_tags t JOIN avatar_tags at ON t.id = at.tag_id WHERE at.avatar_id = ?");
    $stmt_tags->execute([$_GET['id']]);
    $selected_tag_names = $stmt_tags->fetchAll(PDO::FETCH_COLUMN, 0);
}

// Fetch all categories and tags for form dropdowns
$categories = $pdo->query("SELECT * FROM video_categories ORDER BY name ASC")->fetchAll();
$tags = $pdo->query("SELECT * FROM video_tags ORDER BY name ASC")->fetchAll();

include 'includes/header.php';
?>
<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <a href="avatars.php" class="auth-link mb-3 d-inline-block"><i class="fas fa-arrow-left me-2"></i>Back to Avatar List</a>
            <h1 class="my-4" style="color: #1e293b;"><?= $page_title ?></h1>

            <div class="stat-card">
                <form action="process/avatar_process.php" method="post">
                    <input type="hidden" name="action" value="<?= $form_action ?>">
                    <?php if ($is_editing): ?><input type="hidden" name="avatar_id" value="<?= $avatar['id'] ?>"><?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Avatar Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($avatar['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sample Video Link</label>
                            <input type="url" name="video_link" class="form-control" value="<?= htmlspecialchars($avatar['video_link'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">-- No Category --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= ($avatar['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-control" required>
                                <option value="Free" <?= ($avatar['type'] ?? 'Free') == 'Free' ? 'selected' : '' ?>>Free</option>
                                <option value="Premium" <?= ($avatar['type'] ?? '') == 'Premium' ? 'selected' : '' ?>>Premium</option>
                            </select>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="Male" <?= ($avatar['gender'] ?? 'Male') == 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= ($avatar['gender'] ?? '') == 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= ($avatar['gender'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Age Group</label>
                            <select name="age_group" class="form-control" required>
                                <option value="Young" <?= ($avatar['age_group'] ?? 'Young') == 'Young' ? 'selected' : '' ?>>Young</option>
                                <option value="Middle-aged" <?= ($avatar['age_group'] ?? '') == 'Middle-aged' ? 'selected' : '' ?>>Middle-aged</option>
                                <option value="Senior" <?= ($avatar['age_group'] ?? '') == 'Senior' ? 'selected' : '' ?>>Senior</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <div class="tag-input-container" data-existing-tags='<?= json_encode($selected_tag_names) ?>'>
                            <div id="tag-chips-wrapper"></div>
                            <input type="text" id="tag-text-input" class="tag-text-input" list="tag-suggestions" placeholder="Type or select a tag and press Enter...">
                        </div>
                        <datalist id="tag-suggestions">
                            <?php foreach ($tags as $tag): ?><option value="<?= htmlspecialchars($tag['name']) ?>"><?php endforeach; ?>
                        </datalist>
                    </div>
                     <input type="hidden" name="tags" id="tags-input-hidden">
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($avatar['description'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Save Avatar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>