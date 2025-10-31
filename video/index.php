<?php
session_start();
// Redirect user to login page if they are not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Include necessary configuration files
require '../config/db.php';
require '../config/config.php'; // For BASE_URL
$user_id = $_SESSION['user_id'];

// --- DATA FETCHING ---

// 1. Fetch the logged-in user's video coin balance from the 'users' table.
$stmt_user = $pdo->prepare("SELECT video_coins FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
// Use fetchColumn() to get a single value from a single row.
$video_coins = $stmt_user->fetchColumn();
// Ensure it's treated as an integer, defaulting to 0 if something goes wrong.
$video_coins = (int) $video_coins;

// 2. Fetch all existing campaigns created by the logged-in user.
$stmt_campaigns = $pdo->prepare("SELECT * FROM campaigns WHERE user_id = ? ORDER BY created_at DESC");
$stmt_campaigns->execute([$user_id]);
$campaigns = $stmt_campaigns->fetchAll();

// Include the shared header file
include '../includes/header.php';
?>
<div class="page-wrapper">
    <!-- Include shared sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div id="content">
        <!-- Include shared navbar -->
        <?php include '../includes/navbar.php'; ?>
        
        <div class="container-fluid">
            <!-- Page Header -->
            <h1 class="my-4" style="color: #1e293b;"><i class="fas fa-rocket me-2"></i>AI Video Campaign Tool</h1>

            <!-- Display status messages (e.g., for errors) -->
            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? 'An error occurred.') ?>
                </div>
            <?php endif; ?>

            <!-- Main Dashboard Grid -->
            <div class="row">
                <!-- Left Column: Start Campaign & Credit Balance -->
                <div class="col-lg-4 mb-4">
                    <!-- "Start a New Campaign" Card -->
                    <div class="stat-card mb-4">
                        <h5><i class="fas fa-plus-circle me-2"></i>Start a New Campaign</h5>
                        <hr>
                        <form action="../process/campaign_process.php" method="post">
                            <input type="hidden" name="action" value="start_campaign">
                            <div class="mb-3">
                                <label class="form-label">Campaign Name</label>
                                <input type="text" name="campaign_name" class="form-control" placeholder="e.g., Summer Skincare Offer" required>
                            </div>
                            <button type="submit" class="btn btn-custom w-100">Start Building</button>
                        </form>
                    </div>
                    
                    <!-- "Video Credit" Wallet Card -->
                    <div class="stat-card wallet-card">
                        <div class="wallet-header">
                            <p>Video Credit</p>
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="wallet-balance">
                            <h3><?= $video_coins ?></h3>
                        </div>
                        <div class="wallet-footer">
                            <span class="small">Coins available for new campaigns</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: List of Existing Campaigns -->
                <div class="col-lg-8 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-list-ul me-2"></i>My Campaigns</h5>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Campaign Name</th>
                                        <th>Status</th>
                                        <th>Date Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <!-- Inside video/index.php -->
<tbody>
    <?php if (empty($campaigns)): ?>
        <tr>
            <td colspan="4" class="text-center py-4">You haven't created any campaigns yet. Start a new one to begin.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($campaigns as $campaign): ?>
            <tr>
                <td><strong><?= htmlspecialchars($campaign['campaign_name']) ?></strong></td>
                <td>
                    <?php
                        $status_class = 'bg-secondary';
                        if ($campaign['status'] == 'Live') $status_class = 'bg-success';
                        if ($campaign['status'] == 'Completed') $status_class = 'bg-primary';
                    ?>
                    <span class="badge <?= $status_class ?>"><?= htmlspecialchars($campaign['status']) ?></span>
                </td>
                <td><?= date('M j, Y', strtotime($campaign['created_at'])) ?></td>
                <td class="text-end">
                    <!-- "Continue" or "View" Button (existing) -->
                    <a href="step1_inspiration.php?id=<?= $campaign['id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> 
                        <?= ($campaign['status'] == 'Draft') ? 'Continue' : 'View' ?>
                    </a>

                    <!-- NEW: Conditional Delete Button -->
                    <?php if ($campaign['status'] == 'Draft'): ?>
                        <form action="../process/campaign_process.php" method="post" class="d-inline ms-1" onsubmit="return confirm('Are you sure you want to permanently delete this draft campaign?');">
                            <input type="hidden" name="action" value="delete_campaign">
                            <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Draft">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>