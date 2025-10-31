<?php
session_start();
// Redirect user if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
// Validate that a numeric clinic ID was provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: clinics.php');
    exit();
}

require 'config/db.php';
require 'config/config.php'; // For BASE_URL
$clinic_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// --- DATA FETCHING ---

// 1. Fetch main clinic details and verify ownership
$stmt_clinic = $pdo->prepare("
    SELECT c.*, cs.full_name as coordinator_name 
    FROM clinics c
    LEFT JOIN clinic_staff cs ON c.staff_id = cs.id
    WHERE c.id = ? AND c.user_id = ?
");
$stmt_clinic->execute([$clinic_id, $user_id]);
$clinic = $stmt_clinic->fetch();

// If clinic not found or doesn't belong to the user, redirect
if (!$clinic) {
    header('Location: clinics.php?status=error&message=' . urlencode('Invalid clinic specified.'));
    exit();
}

// 2. Fetch clinic schedule, grouped by day
$stmt_schedule = $pdo->prepare("SELECT * FROM clinic_schedules WHERE clinic_id = ? ORDER BY FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), start_time");
$stmt_schedule->execute([$clinic_id]);
$schedule_slots = $stmt_schedule->fetchAll();
$schedule_by_day = [];
foreach ($schedule_slots as $slot) {
    $schedule_by_day[$slot['day_of_week']][] = $slot;
}
$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// 3. Fetch clinic pricing/services
$stmt_services = $pdo->prepare("SELECT * FROM clinic_services WHERE clinic_id = ? ORDER BY treatment_name");
$stmt_services->execute([$clinic_id]);
$services = $stmt_services->fetchAll();


// --- Helper function to generate image paths with placeholders ---
function get_image_path($image_column_name, $default_placeholder_url) {
    global $clinic, $BASE_URL;
    // Check if the column exists and has a value
    if (!empty($clinic[$image_column_name])) {
        return $BASE_URL . '/' . $clinic[$image_column_name];
    }
    return $default_placeholder_url;
}


include 'includes/header.php';
?>
<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <!-- Navigation and Header -->
            <a href="clinics.php" class="auth-link mb-3 d-inline-block"><i class="fas fa-arrow-left me-2"></i>Back to Clinics List</a>
           
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="my-0" style="color: #1e293b;"><?= htmlspecialchars($clinic['clinic_name']) ?></h1>
    <a href="<?= BASE_URL . '/view_clinic.php?id=' . $clinic_id ?>" target="_blank" class="btn btn-outline-success">
        <i class="fas fa-share-square me-2"></i>View & Share Public Profile
    </a>
</div>

            
            <!-- Status Messages -->
            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? 'Action completed.') ?>
                </div>
            <?php endif; ?>

            <!-- Clinic Image Gallery Section -->
            <div class="stat-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                     <h5><i class="fas fa-images me-2 text-primary"></i>Clinic Photo Gallery</h5>
                     <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#uploadImagesModal"><i class="fas fa-upload me-2"></i>Manage Images</button>
                </div>
                <div class="row g-3">
                    <div class="col-lg-5">
                        <div class="image-placeholder-wrapper cover">
                            <img src="<?php echo BASE_URL; ?>/<?= get_image_path('cover_picture', 'https://via.placeholder.com/800x450/e2e8f0/94a3b8?text=Cover+Photo') ?>" alt="Cover Picture">
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <div class="image-placeholder-wrapper small">
                                    <img src="<?php echo BASE_URL; ?>/<?= get_image_path('profile_picture', 'https://via.placeholder.com/300x300/e2e8f0/94a3b8?text=Profile') ?>" alt="Profile Picture">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="image-placeholder-wrapper small">
                                    <img src="<?php echo BASE_URL; ?>/<?= get_image_path('interior_picture', 'https://via.placeholder.com/300x300/e2e8f0/94a3b8?text=Interior') ?>" alt="Interior Picture">
                                </div>
                            </div>
                             <div class="col-sm-4">
                                <div class="image-placeholder-wrapper small">
                                     <img src="<?php echo BASE_URL; ?>/<?= get_image_path('reception_picture', 'https://via.placeholder.com/300x300/e2e8f0/94a3b8?text=Reception') ?>" alt="Reception Picture">
                                </div>
                            </div>
                        </div>
                         <div class="row g-3 mt-3">
                             <div class="col-12">
                                <div class="image-placeholder-wrapper entrance"> <!-- Using entrance class for different aspect ratio if needed -->
                                    <img src="<?php echo BASE_URL; ?>/<?= get_image_path('entrance_picture', 'https://via.placeholder.com/612x300/e2e8f0/94a3b8?text=Entrance') ?>" alt="Entrance Picture">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="row">
                <!-- Left Column: Core Details & Schedule -->
                <div class="col-lg-5 mb-4">
                    <div class="stat-card mb-4">
                        <h5><i class="fas fa-info-circle me-2 text-primary"></i>Clinic Details</h5>
                        <hr>
                        <p><strong>Doctor in Charge:</strong> <?= htmlspecialchars($clinic['doctor_in_charge']) ?></p>
                        <p><strong>Speciality:</strong> <?= htmlspecialchars($clinic['speciality']) ?></p>
                        <p><strong>Patient Coordinator:</strong> <?= htmlspecialchars($clinic['coordinator_name'] ?? 'N/A') ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($clinic['area']) ?>, <?= htmlspecialchars($clinic['district']) ?> - <?= htmlspecialchars($clinic['pincode']) ?></p>
                        <p><strong>Landmark:</strong> <?= htmlspecialchars($clinic['landmark'] ?? 'N/A') ?></p>
                        <?php if(!empty($clinic['map_link'])): ?>
                            <a href="<?= htmlspecialchars($clinic['map_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-map-marker-alt me-2"></i>View on Google Maps</a>
                        <?php endif; ?>
                    </div>

                    <div class="stat-card">
                        <h5><i class="fas fa-clock me-2 text-primary"></i>Weekly Schedule</h5>
                        <hr>
                        <ul class="list-group list-group-flush">
                        <?php foreach($days as $day): ?>
                            <li class="list-group-item">
                                <strong><?= $day ?>:</strong>
                                <?php if (!empty($schedule_by_day[$day])): ?>
                                    <div class="mt-1">
                                    <?php foreach($schedule_by_day[$day] as $slot): ?>
                                        <span class="badge bg-light text-dark me-1"><?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?></span>
                                    <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Closed</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                        <a href="schedule.php?clinic_id=<?= $clinic_id ?>" class="btn btn-custom w-100 mt-3">Edit Schedule</a>
                    </div>
                </div>

                <!-- Right Column: Pricing/Services -->
                <div class="col-lg-7 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-dollar-sign me-2 text-primary"></i>Clinic Services & Pricing</h5>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Treatment</th><th>Body Part</th><th>MRP</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php if (empty($services)): ?>
                                    <tr><td colspan="4" class="text-center py-4">No pricing has been uploaded for this clinic yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach($services as $service): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($service['treatment_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($service['description']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($service['body_part']) ?></td>
                                        <td><?= htmlspecialchars($service['mrp']) ?></td>
                                        <td>
                                            <span class="badge <?= $service['status'] == 'Pending' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                <?= htmlspecialchars($service['status']) ?>
                                            </span>
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

<!-- Image Upload Modal -->
<div class="modal fade" id="uploadImagesModal" tabindex="-1" aria-labelledby="uploadImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card" style="background: rgba(255, 255, 255, 0.9);">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="uploadImagesModalLabel" style="color: #1e293b;">Manage Clinic Images</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="process/clinic_image_process.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="clinic_id" value="<?= $clinic_id ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Profile Picture (1:1 Ratio)</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cover Picture (16:9 Ratio)</label>
                            <input type="file" name="cover_picture" class="form-control" accept="image/*">
                        </div>
                         <div class="col-md-6 mb-3">
                            <label class="form-label">Interior Picture</label>
                            <input type="file" name="interior_picture" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reception/Sitting Area</label>
                            <input type="file" name="reception_picture" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">From the Entrance</label>
                            <input type="file" name="entrance_picture" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom w-100 mt-3">Upload and Save Images</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>