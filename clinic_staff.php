<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'config/db.php';

// Fetch existing staff for the logged-in user from the new table
$stmt = $pdo->prepare("SELECT * FROM clinic_staff WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$staff_members = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div id="content">
        <?php include 'includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 style="color: #1e293b;">Manage Clinic Staff</h1>
                <button class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="fas fa-plus me-2"></i>Add New Staff
                </button>
            </div>

            <!-- Display success or error messages -->
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert alert-success">Staff member added successfully!</div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['message']) ?></div>
            <?php endif; ?>

            <div class="stat-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone No</th>
                                <th>Role</th> <!-- New Column -->
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($staff_members)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No staff members found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($staff_members as $staff): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($staff['full_name']) ?></td>
                                        <td><?= htmlspecialchars($staff['email']) ?></td>
                                        <td><?= htmlspecialchars($staff['phone']) ?></td>
                                        <td><?= htmlspecialchars($staff['role']) ?></td> <!-- Display Role -->
                                        <td><?= date('M j, Y', strtotime($staff['created_at'])) ?></td>
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

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card" style="background: rgba(255, 255, 255, 0.9);">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="addStaffModalLabel" style="color: #1e293b;">New Staff Member Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="process/staff_process.php" method="post" id="add-staff-form">
                    <input type="hidden" name="action" value="add_staff"> <!-- Changed action value -->
                    <div class="mb-3">
                        <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                    </div>
                    <div class="mb-3">
                        <input type="tel" class="form-control" name="phone" placeholder="Phone Number" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                    </div>
                    <!-- New Role Dropdown -->
                    <div class="mb-3">
                        <select name="role" class="form-control" required>
                            <option value="" disabled selected>Select a Role</option>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Patient Cordinator">Patient Coordinator</option>
                            <option value="Helping Hand">Helping Hand</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" id="staff-password-modal" name="password" placeholder="Password" required>
                    </div>
                    <div id="password-criteria-modal" class="mb-3">
                        <p id="length-modal" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>Min 8 characters</p>
                        <p id="uppercase-modal" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>One uppercase</p>
                        <p id="lowercase-modal" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>One lowercase</p>
                        <p id="number-modal" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>One number</p>
                        <p id="symbol-modal" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>One symbol</p>
                    </div>
                    <button type="submit" id="add-staff-button" class="btn btn-custom w-100" disabled>Add Staff Member</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>