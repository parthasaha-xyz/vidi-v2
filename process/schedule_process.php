<?php
session_start();
require '../config/db.php';

// Ensure the request is a POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ../dashboard.php');
    exit();
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Ensure the core parameters are provided
if (!isset($_POST['clinic_id'], $_POST['action'])) {
    header('Location: ../clinics.php?status=error&message=' . urlencode('Invalid request parameters.'));
    exit();
}

$user_id = $_SESSION['user_id'];
$clinic_id = (int)$_POST['clinic_id'];
$action = $_POST['action'];

try {
    // =================================================================
    // SECURITY CHECK: Verify Clinic Ownership
    // This single check runs for ALL actions, ensuring the user owns the clinic
    // they are trying to modify. This is the most critical security step.
    // =================================================================
    $stmt = $pdo->prepare("SELECT id FROM clinics WHERE id = ? AND user_id = ?");
    $stmt->execute([$clinic_id, $user_id]);
    if ($stmt->rowCount() === 0) {
        // If the query returns 0 rows, the user does not own this clinic.
        header('Location: ../clinics.php?status=error&message=' . urlencode('Authorization Error.'));
        exit();
    }

    // =================================================================
    // ACTION: Add a new time slot
    // =================================================================
    if ($action === 'add_slot') {
        $day_of_week = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // Validate that the end time is after the start time
        if (strtotime($end_time) <= strtotime($start_time)) {
             header('Location: ../schedule.php?clinic_id=' . $clinic_id . '&status=error&message=' . urlencode('End time must be after start time.'));
             exit();
        }

        $stmt = $pdo->prepare("INSERT INTO clinic_schedules (clinic_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$clinic_id, $day_of_week, $start_time, $end_time]);

    } 
    // =================================================================
    // ACTION: Delete an existing time slot
    // =================================================================
    elseif ($action === 'delete_slot') {
        $slot_id = (int)$_POST['slot_id'];
        
        // We include clinic_id in the DELETE statement as an extra security layer
        $stmt = $pdo->prepare("DELETE FROM clinic_schedules WHERE id = ? AND clinic_id = ?");
        $stmt->execute([$slot_id, $clinic_id]);

    }
    // =================================================================
    // ACTION: Apply a schedule from a source day to multiple target days
    // =================================================================
    elseif ($action === 'apply_schedule') {
        $source_day = $_POST['source_day'];
        $target_days = isset($_POST['target_days']) && is_array($_POST['target_days']) ? $_POST['target_days'] : [];

        if (empty($target_days)) {
            header('Location: ../schedule.php?clinic_id=' . $clinic_id . '&status=error&message=' . urlencode('No target days were selected.'));
            exit();
        }

        $pdo->beginTransaction();

        // 1. Fetch all slots from the source day
        $stmt_select = $pdo->prepare("SELECT start_time, end_time FROM clinic_schedules WHERE clinic_id = ? AND day_of_week = ?");
        $stmt_select->execute([$clinic_id, $source_day]);
        $source_slots = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

        // 2. Prepare statements for deleting and inserting
        $stmt_delete = $pdo->prepare("DELETE FROM clinic_schedules WHERE clinic_id = ? AND day_of_week = ?");
        $stmt_insert = $pdo->prepare("INSERT INTO clinic_schedules (clinic_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        
        // 3. Loop through each target day to apply the changes
        foreach ($target_days as $target_day) {
            if ($target_day === $source_day) continue; // Skip if source and target are the same

            // First, delete all existing slots for the target day
            $stmt_delete->execute([$clinic_id, $target_day]);

            // Then, insert all the source slots for the target day
            if (!empty($source_slots)) {
                foreach ($source_slots as $slot) {
                    $stmt_insert->execute([$clinic_id, $target_day, $slot['start_time'], $slot['end_time']]);
                }
            }
        }
        
        $pdo->commit();
    }
    
    // =================================================================
    // FINAL STEP: Update the clinic's status to "1" (Waiting for Approval)
    // This runs after any of the successful actions above.
    // =================================================================
    $status_update_stmt = $pdo->prepare("UPDATE clinics SET schedule_status = 1 WHERE id = ?");
    $status_update_stmt->execute([$clinic_id]);

    // Redirect with a generic success message
    header('Location: ../schedule.php?clinic_id=' . $clinic_id . '&status=success&message=' . urlencode('Schedule updated successfully. It is now waiting for approval.'));
    exit();

} catch (Exception $e) {
    // If any exception occurs (including a transaction failure), roll back and show an error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // For debugging, it's useful to see the actual error. For production, use a generic message.
    $error_message = "An error occurred. Please try again. Details: " . $e->getMessage();
    header('Location: ../schedule.php?clinic_id=' . $clinic_id . '&status=error&message=' . urlencode($error_message));
    exit();
}