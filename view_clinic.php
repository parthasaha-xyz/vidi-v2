<?php
// This is a public page, so we DO NOT start a session or check for login.
require 'config/db.php';
require 'config/config.php'; // For BASE_URL

// 1. Validate the Clinic ID from the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // If no valid ID, redirect to the main site or a 404 page.
    header('Location: index.php');
    exit();
}
$clinic_id = $_GET['id'];

// 2. Fetch all data for this specific clinic. We don't check for user_id here.
// Fetch main details
$stmt_clinic = $pdo->prepare("SELECT c.*, cs.full_name as coordinator_name FROM clinics c LEFT JOIN clinic_staff cs ON c.staff_id = cs.id WHERE c.id = ?");
$stmt_clinic->execute([$clinic_id]);
$clinic = $stmt_clinic->fetch();

// If clinic doesn't exist, redirect.
if (!$clinic) {
    header('Location: index.php');
    exit();
}

// Fetch schedule
$stmt_schedule = $pdo->prepare("SELECT * FROM clinic_schedules WHERE clinic_id = ? ORDER BY FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), start_time");
$stmt_schedule->execute([$clinic_id]);
$schedule_slots = $stmt_schedule->fetchAll();
$schedule_by_day = [];
foreach ($schedule_slots as $slot) { $schedule_by_day[$slot['day_of_week']][] = $slot; }
$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Fetch services (only show 'Active' ones on a public profile)
$stmt_services = $pdo->prepare("SELECT * FROM clinic_services WHERE clinic_id = ? AND status = 'Active' ORDER BY treatment_name");
$stmt_services->execute([$clinic_id]);
$services = $stmt_services->fetchAll();

// --- Helper function for image paths ---
function get_public_image_path($image_column, $default_url) {
    global $clinic, $BASE_URL;
    return !empty($clinic[$image_column]) ? $BASE_URL . '/' . $clinic[$image_column] : $default_url;
}

// We include a simplified header for public pages
include 'includes/header_public.php'; // We will create this file next
?>
<body class="public-profile-body">
    <?php include 'includes/navbar_public.php'; // And this one too ?>

    <div class="container public-profile-container">
        <!-- Hero Section with Cover and Profile Picture -->
        <div class="clinic-hero-section">
            <img src="<?php echo BASE_URL; ?>/<?= get_public_image_path('cover_picture', 'https://via.placeholder.com/1200x400/e2e8f0/94a3b8?text=Clinic') ?>" class="cover-image" alt="Cover Photo">
            <div class="hero-overlay">
                <div class="hero-content">
                    <img src="<?php echo BASE_URL; ?>/<?= get_public_image_path('profile_picture', 'https://via.placeholder.com/150x150/e2e8f0/94a3b8?text=Logo') ?>" class="profile-image" alt="Profile Photo">
                    <h1 class="clinic-title"><?= htmlspecialchars($clinic['clinic_name']) ?></h1>
                    <span class="clinic-speciality"><?= htmlspecialchars($clinic['speciality']) ?> Clinic</span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row mt-4">
            <!-- Left Column: Details & Hours -->
            <div class="col-lg-5 mb-4">
                <div class="stat-card mb-4">
                    <h5><i class="fas fa-info-circle me-2 text-primary"></i>About This Clinic</h5>
                    <hr>
                    <p><strong><i class="fas fa-user-md fa-fw me-2"></i>Doctor in Charge:</strong> <?= htmlspecialchars($clinic['doctor_in_charge']) ?></p>
                    <p><strong><i class="fas fa-map-marker-alt fa-fw me-2"></i>Address:</strong> <?= htmlspecialchars($clinic['area']) ?>, <?= htmlspecialchars($clinic['district']) ?> - <?= htmlspecialchars($clinic['pincode']) ?></p>
                    <p><strong><i class="fas fa-landmark fa-fw me-2"></i>Landmark:</strong> <?= htmlspecialchars($clinic['landmark'] ?? 'N/A') ?></p>
                    <?php if(!empty($clinic['map_link'])): ?>
                        <a href="<?= htmlspecialchars($clinic['map_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2"><i class="fas fa-directions me-2"></i>Get Directions</a>
                    <?php endif; ?>
                </div>
                <div class="stat-card">
                    <h5><i class="fas fa-clock me-2 text-primary"></i>Opening Hours</h5>
                    <hr>
                    <ul class="list-group list-group-flush">
                        <?php foreach($days as $day): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong><?= $day ?></strong>
                                <span class="text-end">
                                    <?php if (!empty($schedule_by_day[$day])): ?>
                                        <?php foreach($schedule_by_day[$day] as $slot): ?>
                                            <div><?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?></div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Closed</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Right Column: Services & Gallery -->
            <div class="col-lg-7 mb-4">
                <div class="stat-card mb-4">
                    <h5><i class="fas fa-dollar-sign me-2 text-primary"></i>Our Services</h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <?php foreach($services as $service): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($service['treatment_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($service['mrp']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="stat-card">
                    <h5><i class="fas fa-images me-2 text-primary"></i>Gallery</h5>
                    <hr>
                    <div class="row g-3">
                        <div class="col-6 col-md-4"><div class="gallery-image-wrapper"><img src="<?php echo BASE_URL; ?>/<?= get_public_image_path('interior_picture', 'https://via.placeholder.com/300x300/e2e8f0/94a3b8?text=Interior') ?>"></div></div>
                        <div class="col-6 col-md-4"><div class="gallery-image-wrapper"><img src="<?php echo BASE_URL; ?>/<?= get_public_image_path('reception_picture', 'https://via.placeholder.com/300x300/e2e8f0/94a3b8?text=Reception') ?>"></div></div>
                        <div class="col-12 col-md-4"><div class="gallery-image-wrapper"><img src="<?php echo BASE_URL; ?>/<?= get_public_image_path('entrance_picture', 'https://via.placeholder.com/300x300/e2e8f0/94a3b8?text=Entrance') ?>"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>