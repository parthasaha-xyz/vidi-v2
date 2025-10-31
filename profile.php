<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'config/db.php';
require 'config/config.php'; // For BASE_URL
$user_id = $_SESSION['user_id'];

// Fetch the current user's data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Determine the profile photo path
$profile_photo_path = !empty($user['profile_photo']) ? BASE_URL . '/' . $user['profile_photo'] : BASE_URL . '/assets/default_avatar.png'; // Path to a default image

include 'includes/header.php';
?>
<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <h1 class="my-4" style="color: #1e293b;">My Profile</h1>

            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? 'Action completed.') ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Left Side: Profile Details & Photo Upload -->
                <div class="col-lg-5 mb-4">
                    <div class="stat-card h-100 text-center">
                        <form action="process/profile_process.php" method="post" enctype="multipart/form-data">
                            <div class="profile-picture-container mb-3">
                                <img src="<?= $profile_photo_path ?>" alt="Profile Picture" id="profile-img-preview" class="profile-img">
                                <label for="profile-photo-input" class="upload-overlay">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" name="profile_photo" id="profile-photo-input" class="d-none" accept="image/png, image/jpeg, image/jpg">
                            </div>
                            <h4 class="mb-1"><?= htmlspecialchars($user['name']) ?></h4>
                            <p class="text-muted"><?= htmlspecialchars($user['business_name']) ?></p>
                            <hr>
                            <div class="text-start">
                                <p><i class="fas fa-envelope me-2 text-primary"></i> <?= htmlspecialchars($user['email']) ?></p>
                                <p><i class="fas fa-phone me-2 text-primary"></i> <?= htmlspecialchars($user['phone']) ?></p>
                            </div>
                            <button type="submit" class="btn btn-custom w-100 mt-3">Update Photo</button>
                        </form>
                    </div>
                </div>

                <!-- Right Side: Fix Appointment Form -->
                <div class="col-lg-7 mb-4">
                    <div class="stat-card h-100">
                        <h5><i class="fas fa-calendar-check me-2"></i>Fix an Appointment</h5>
                        <p class="text-muted small">Book a new appointment for one of your clinics.</p>
                        <hr>
                        <form action="#" method="post"> <!-- Update action to a new process file when ready -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Patient Name</label>
                                    <input type="text" name="patient_name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Patient Phone</label>
                                    <input type="tel" name="patient_phone" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Appointment Date</label>
                                    <input type="date" name="app_date" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Appointment Time</label>
                                    <input type="time" name="app_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason for Visit</label>
                                <textarea name="reason" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-custom w-100">Book Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>