<?php
session_start();
// Redirect user if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
// Validate that a numeric clinic ID was provided in the URL
if (!isset($_GET['clinic_id']) || !is_numeric($_GET['clinic_id'])) {
    header('Location: clinics.php');
    exit();
}

require 'config/db.php';
$clinic_id = $_GET['clinic_id'];
$user_id = $_SESSION['user_id'];

// Security Check: Fetch clinic details and verify it belongs to the logged-in user
$stmt = $pdo->prepare("SELECT * FROM clinics WHERE id = ? AND user_id = ?");
$stmt->execute([$clinic_id, $user_id]);
$clinic = $stmt->fetch();

// If the clinic doesn't exist or belong to the user, redirect them with an error
if (!$clinic) {
    header('Location: clinics.php?status=error&message=' . urlencode('Invalid clinic specified.'));
    exit();
}

// Fetch all existing schedule slots for this specific clinic
$stmt_slots = $pdo->prepare("SELECT * FROM clinic_schedules WHERE clinic_id = ? ORDER BY FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), start_time");
$stmt_slots->execute([$clinic_id]);
$all_slots = $stmt_slots->fetchAll();

// Group the fetched slots by day for easy display
$schedule_by_day = [];
foreach ($all_slots as $slot) {
    $schedule_by_day[$slot['day_of_week']][] = $slot;
}
// Define the order of days for consistent display
$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

include 'includes/header.php';
?>

<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <!-- Navigation and Header -->
            <a href="clinics.php" class="auth-link mb-3 d-inline-block"><i class="fas fa-arrow-left me-2"></i>Back to Clinics List</a>
            <h1 style="color: #1e293b;">Manage Schedule</h1>
            <h5 class="text-muted mb-4">for clinic: <?= htmlspecialchars($clinic['clinic_name']) ?></h5>

            <!-- Status Messages (for success or error feedback) -->
            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? 'Action completed.') ?>
                </div>
            <?php endif; ?>

            <!-- Schedule Day Cards -->
            <div class="row">
                <?php foreach ($days as $day): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="stat-card h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><?= $day ?></h5>
                            <button class="btn btn-sm btn-custom add-slot-btn" data-bs-toggle="modal" data-bs-target="#addSlotModal" data-day="<?= $day ?>">
                                <i class="fas fa-plus"></i> Add Slot
                            </button>
                        </div>
                        
                        <ul class="list-group list-group-flush flex-grow-1">
                        <?php if (!empty($schedule_by_day[$day])): ?>
                            <?php foreach ($schedule_by_day[$day] as $slot): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>
                                        <i class="far fa-clock text-primary me-2"></i>
                                        <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                    </span>
                                    <form action="process/schedule_process.php" method="post" onsubmit="return confirm('Are you sure you want to delete this time slot?');">
                                        <input type="hidden" name="action" value="delete_slot">
                                        <input type="hidden" name="slot_id" value="<?= $slot['id'] ?>">
                                        <input type="hidden" name="clinic_id" value="<?= $clinic_id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted px-0">No slots scheduled.</li>
                        <?php endif; ?>
                        </ul>

                        <!-- "Apply to Other Days" button, shown only if slots exist for this day -->
                        <?php if (!empty($schedule_by_day[$day])): ?>
                            <div class="mt-auto pt-3 border-top">
                                <button class="btn btn-sm btn-outline-secondary w-100 apply-schedule-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#applyScheduleModal"
                                        data-day="<?= $day ?>">
                                    Apply This Schedule to Other Days...
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Time Slot Modal -->
<div class="modal fade" id="addSlotModal" tabindex="-1" aria-labelledby="addSlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card" style="background: rgba(255, 255, 255, 0.9);">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="addSlotModalLabel" style="color: #1e293b;">Add New Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="process/schedule_process.php" method="post">
                    <input type="hidden" name="action" value="add_slot">
                    <input type="hidden" name="clinic_id" value="<?= $clinic_id ?>">
                    <input type="hidden" name="day_of_week" id="modal-day-of-week">
                    
                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                     <div class="mb-3">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Save Slot</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Apply Schedule to Other Days Modal -->
<div class="modal fade" id="applyScheduleModal" tabindex="-1" aria-labelledby="applyScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card" style="background: rgba(255, 255, 255, 0.9);">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="applyScheduleModalLabel" style="color: #1e293b;">Apply Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="process/schedule_process.php" method="post">
                    <input type="hidden" name="action" value="apply_schedule">
                    <input type="hidden" name="clinic_id" value="<?= $clinic_id ?>">
                    <input type="hidden" name="source_day" id="modal-source-day">

                    <p class="mb-3">Select the days you want to apply this schedule to.
                    <br><strong class="text-danger small">Note: This will overwrite any existing schedule on the selected days.</strong></p>

                    <div class="day-checkbox-group">
                        <?php foreach ($days as $target_day): ?>
                            <div class="form-check form-check-inline mb-2">
                                <input class="form-check-input" type="checkbox" name="target_days[]" value="<?= $target_day ?>" id="day-<?= $target_day ?>">
                                <label class="form-check-label" for="day-<?= $target_day ?>"><?= $target_day ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-custom w-100 mt-3">Apply to Selected Days</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>