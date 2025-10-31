<?php
session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit();
    }
    
    // Sanitize and retrieve POST data
    $user_id = $_SESSION['user_id'];
    $clinic_name = filter_var($_POST['clinic_name'], FILTER_SANITIZE_STRING);
    $map_link = filter_var($_POST['map_link'], FILTER_SANITIZE_URL);
    $landmark = filter_var($_POST['landmark'], FILTER_SANITIZE_STRING);
    $doctor_in_charge = filter_var($_POST['doctor_in_charge'], FILTER_SANITIZE_STRING);
    $speciality = $_POST['speciality'];
    $staff_id = filter_var($_POST['staff_id'], FILTER_VALIDATE_INT);
    $pincode = filter_var($_POST['pincode'], FILTER_SANITIZE_STRING);
    $district = filter_var($_POST['district'], FILTER_SANITIZE_STRING);
    $area = filter_var($_POST['area'], FILTER_SANITIZE_STRING);

    // --- Validation ---
    $allowed_specialities = ['Hair', 'Skin', 'Vet', 'Dental'];
    if (!in_array($speciality, $allowed_specialities)) {
        header('Location: ../clinics.php?status=error&message=' . urlencode('Invalid speciality selected.'));
        exit();
    }
    if (empty($district) || empty($area)) {
        header('Location: ../clinics.php?status=error&message=' . urlencode('Pincode could not be verified. District and Area are required.'));
        exit();
    }

    // Verify the selected staff member belongs to the user and is not already assigned
    $stmt = $pdo->prepare("
        SELECT cs.id 
        FROM clinic_staff cs
        LEFT JOIN clinics c ON cs.id = c.staff_id
        WHERE cs.user_id = ? AND cs.id = ? AND c.id IS NULL
    ");
    $stmt->execute([$user_id, $staff_id]);
    if ($stmt->rowCount() == 0) {
        header('Location: ../clinics.php?status=error&message=' . urlencode('Selected coordinator is invalid or already assigned.'));
        exit();
    }

    // --- Insertion ---
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO clinics (user_id, clinic_name, map_link, landmark, doctor_in_charge, speciality, staff_id, pincode, district, area) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        if ($stmt->execute([$user_id, $clinic_name, $map_link, $landmark, $doctor_in_charge, $speciality, $staff_id, $pincode, $district, $area])) {
            header('Location: ../clinics.php?status=success');
        } else {
            header('Location: ../clinics.php?status=error&message=' . urlencode('An unknown database error occurred.'));
        }
    } catch (PDOException $e) {
        // This will catch the UNIQUE constraint violation from the database
        if ($e->errorInfo[1] == 1062) {
            header('Location: ../clinics.php?status=error&message=' . urlencode('This Patient Coordinator is already assigned to another clinic.'));
        } else {
            header('Location: ../clinics.php?status=error&message=' . urlencode('Database error: ' . $e->getMessage()));
        }
    }
    exit();
} else {
    header('Location: ../dashboard.php');
    exit();
}