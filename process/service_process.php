<?php
// --- ENABLE DETAILED ERROR REPORTING (FOR DEBUGGING) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit();
    }
    
    // Validate that clinic_id and the file were actually submitted
    if (!isset($_POST['clinic_id']) || !is_numeric($_POST['clinic_id'])) {
        header('Location: ../clinics.php?status=error&message=' . urlencode('Error: Clinic ID was not provided.'));
        exit();
    }
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
        header('Location: ../clinics.php?status=error&message=' . urlencode('An error occurred during file upload.'));
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $clinic_id = (int)$_POST['clinic_id'];
    $file_tmp_name = $_FILES['csv_file']['tmp_name'];

    // Security Check: Verify the clinic belongs to the user
    $stmt = $pdo->prepare("SELECT id FROM clinics WHERE id = ? AND user_id = ?");
    $stmt->execute([$clinic_id, $user_id]);
    if ($stmt->rowCount() == 0) {
        header('Location: ../clinics.php?status=error&message=' . urlencode('Authorization error: You do not own this clinic.'));
        exit();
    }
    
    // File type validation
    $file_ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
    if ($file_ext != 'csv') {
        header('Location: ../clinics.php?status=error&message=' . urlencode('Invalid file type. Please upload a .csv file.'));
        exit();
    }

    if (($handle = fopen($file_tmp_name, 'r')) !== FALSE) {
    
    $inserted_rows = 0;

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO clinic_services (user_id, clinic_id, treatment_name, description, body_part, mrp, markup, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')"
        );

        fgetcsv($handle); // Skip header row
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) == 5 && !empty(trim($data[0]))) {
                $stmt->execute([
                    $user_id, $clinic_id, $data[0], $data[1], $data[2], (float)$data[3], (float)$data[4]
                ]);
                $inserted_rows++;
            }
        }
        
        if ($inserted_rows > 0) {
            // --- THIS IS THE NEW LOGIC ---
            // 1. Commit the service insertions first
            $pdo->commit();

            // 2. NOW, update the clinic's pricing status to 1 (Pending)
            $status_update_stmt = $pdo->prepare("UPDATE clinics SET pricing_status = 1 WHERE id = ? AND user_id = ?");
            $status_update_stmt->execute([$clinic_id, $user_id]);
            // --- END OF NEW LOGIC ---

            header('Location: ../clinics.php?status=success&message=' . urlencode($inserted_rows . ' services were imported and are pending review.'));
        
        } else {
            $pdo->rollBack();
            header('Location: ../clinics.php?status=error&message=' . urlencode('Import failed: No valid rows found in the CSV file.'));
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Database Error: " . $e->getMessage());
    }
    fclose($handle);

} else {
     header('Location: ../clinics.php?status=error&message=' . urlencode('Critical Error: Could not open the uploaded temporary file.'));
}
exit();
}