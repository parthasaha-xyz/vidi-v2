<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'config/db.php';

// Fetch unassigned staff members to populate the dropdown
$stmt = $pdo->prepare("
    SELECT cs.* 
    FROM clinic_staff cs
    LEFT JOIN clinics c ON cs.id = c.staff_id
    WHERE cs.user_id = ? AND c.id IS NULL AND cs.role = 'Patient Cordinator'
");
$stmt->execute([$_SESSION['user_id']]);
$available_coordinators = $stmt->fetchAll();

// Fetch existing clinics to display in the table
$stmt = $pdo->prepare("
    SELECT c.*, cs.full_name as coordinator_name 
    FROM clinics c
    JOIN clinic_staff cs ON c.staff_id = cs.id
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$clinics = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 style="color: #1e293b;">Manage Clinics</h1>
                <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#addClinicModal">
                    <i class="fas fa-plus me-2"></i>Add New Clinic
                </button>
            </div>
            <!-- Status messages -->
            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= $_GET['status'] == 'success' ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($_GET['message'] ?? ($_GET['status'] == 'success' ? 'Clinic added successfully!' : 'An error occurred.')) ?>
                </div>
            <?php endif; ?>

            <div class="stat-card">
                <div class="table-responsive">
                <!-- Clinic List Table -->
                <table class="table ">
                    <thead>
                        <tr><th>Clinic Name</th><th>Doctor</th><th>Speciality</th><th>Coordinator</th>
                        <th>Location</th>
                        <th>Pricing Status</th>
                        <th>Schedule</th>
                        <th>Profile</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($clinics as $clinic): ?>
                        <tr>
                            <td><?= htmlspecialchars($clinic['clinic_name']) ?></td>
                            <td><?= htmlspecialchars($clinic['doctor_in_charge']) ?></td>
                            <td><?= htmlspecialchars($clinic['speciality']) ?></td>
                            <td><?= htmlspecialchars($clinic['coordinator_name']) ?></td>
                            <td><?= htmlspecialchars($clinic['area']) ?>, <?= htmlspecialchars($clinic['district']) ?></td>
                            
                            <td> <?php if ($clinic['pricing_status'] == 1): ?>
                                <span class="badge bg-warning text-dark">Pricing Pending</span>
                            <?php else: ?>
                                
                                <!-- THIS IS THE MODIFIED LOGIC -->
                            <?php if ($clinic['pricing_status'] == 0): ?>
                                <!-- The button is now ONLY rendered if pricing_status is 0 -->
                                <button class="btn btn-sm btn-outline-primary upload-pricing-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#uploadPricingModal"
                                        data-clinic-id="<?= $clinic['id'] ?>"
                                        data-clinic-name="<?= htmlspecialchars($clinic['clinic_name']) ?>">
                                    <i class="fas fa-upload me-1"></i> Upload Pricing
                                </button>
                            <?php endif; ?>
                            <!-- If pricing_status is 1, this block is empty, so no button is shown -->


                            <?php endif; ?></td>


                              <td>
                            <?php if ($clinic['schedule_status'] == 1): ?>
                                <span class="badge bg-warning text-dark">Waiting for Approval</span>
                            <?php else: ?>
                                
                                <!-- THIS IS THE NEW BUTTON -->
    <a href="schedule.php?clinic_id=<?= $clinic['id'] ?>" class="btn btn-sm btn-outline-info mb-1">
        <i class="fas fa-clock me-1"></i> Manage Schedule
    </a>


                            <?php endif; ?>
                        </td>
                        <th> <a href="clinic_profile.php?id=<?= $clinic['id'] ?>" class="btn btn-sm btn-outline-success mb-1" title="View Profile">
        <i class="fas fa-eye"></i> Profile
    </a></th>

                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Clinic Modal -->
<div class="modal fade" id="addClinicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card" style="background: rgba(255, 255, 255, 0.9);">
            <div class="modal-header border-0">
                <h5 class="modal-title" style="color: #1e293b;">New Clinic Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process/clinic_process.php" method="post" id="add-clinic-form">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="clinic_name" placeholder="Clinic Name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="doctor_in_charge" placeholder="Doctor in Charge" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="url" class="form-control" name="map_link" placeholder="Google Map Link">
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="text" class="form-control" name="landmark" placeholder="Landmark">
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <select name="speciality" class="form-control" required>
                                <option value="" disabled selected>Select Speciality</option>
                                <option value="Hair">Hair</option>
                                <option value="Skin">Skin</option>
                                <option value="Vet">Vet</option>
                                <option value="Dental">Dental</option>
                            </select>
                        </div>
                         <div class="col-md-6 mb-3">
                            <select name="staff_id" id="coordinator-select" class="form-control" required>
                                <option value="" disabled selected>Select Patient Coordinator</option>
                                <?php foreach ($available_coordinators as $coord): ?>
                                    <option value="<?= $coord['id'] ?>" data-email="<?= htmlspecialchars($coord['email']) ?>" data-phone="<?= htmlspecialchars($coord['phone']) ?>">
                                        <?= htmlspecialchars($coord['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <input type="email" id="coordinator-email" class="form-control" placeholder="Coordinator Email" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <input type="tel" id="coordinator-phone" class="form-control" placeholder="Coordinator Phone" readonly>
                        </div>
                    </div>
                    <hr>
                     <div class="row">
                        <div class="col-md-4 mb-3">
                            <input type="number" id="pincode-input" class="form-control" name="pincode" placeholder="Pincode" required>
                        </div>
                        <div class="col-md-4 mb-3">
                             <input type="text" id="district-input" class="form-control" name="district" placeholder="District" readonly required>
                        </div>
                         <div class="col-md-4 mb-3">
                            <input type="text" id="area-input" class="form-control" name="area" placeholder="Area" readonly required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">Create Clinic</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Upload Clinic Pricing Modal -->
<div class="modal fade" id="uploadPricingModal" tabindex="-1" aria-labelledby="uploadPricingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card" style="background: rgba(255, 255, 255, 0.9);">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="uploadPricingModalLabel" style="color: #1e293b;">Upload Pricing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">
                    Upload a CSV file for: <strong id="modal-clinic-name-title"></strong><br>
                    Columns: <strong>Treatment name, Description, Body Part, MRP, Markup</strong>
                </p>
                <a href="assets/sample_pricing.csv" class="sample-link" download>
                    <i class="fas fa-download me-1"></i> Download Sample File
                </a>
                <hr>
                
                <!-- UPDATED IDs IN THIS FORM -->
                <form action="process/service_process.php" method="post" enctype="multipart/form-data" id="upload-csv-form-modal">
                    <input type="hidden" name="clinic_id" id="modal-clinic-id" value="">
                    <div class="input-group">
                        <input type="file" class="form-control" name="csv_file" id="csv-file-input-modal" accept=".csv" required>
                        <button class="btn btn-custom" type="submit" id="upload-csv-button-modal" disabled>Upload File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>  
<?php include 'includes/footer.php'; ?>