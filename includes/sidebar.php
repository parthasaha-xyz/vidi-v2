<nav id="sidebar">
    <div class="sidebar-header text-center">
        <h3><i class="fa-solid fa-heart-pulse"></i> Clinic System</h3>
    </div>
<?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
    <ul class="list-unstyled components">
        <p class="text-center"><?= htmlspecialchars($_SESSION['business_name']); ?></p>
        <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        </li>

        <!-- Inside <ul class="list-unstyled components"> -->
<li class="<?php echo ($currentPage == 'clinic_staff.php') ? 'active' : ''; ?>">
    <a href="<?php echo BASE_URL; ?>/clinic_staff.php"><i class="fas fa-users-cog me-2"></i> Clinic Staff</a>
</li>
<!-- Inside <ul class="list-unstyled components"> -->
<li class="<?php echo ($currentPage == 'clinics.php') ? 'active' : ''; ?>">
    <a href="<?php echo BASE_URL; ?>/clinics.php"><i class="fas fa-clinic-medical me-2"></i> Clinics</a>
</li>
        
        <li>
            <a href="#"><i class="fas fa-calendar-alt me-2"></i> Appointments</a>
        </li>
        <li>
            <a href="#"><i class="fas fa-users me-2"></i> Patients</a>
        </li>
        <li>
            <a href="#"><i class="fas fa-file-invoice me-2"></i> Billing</a>
        </li>
        <li class="<?php echo ($currentPage == 'profile.php') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/profile.php"><i class="fas fa-cog me-2"></i> Profile</a>
        </li>

        <!-- Inside includes/sidebar.php, within the <ul> -->

<!-- ... (existing links like Dashboard, Clinics, etc.) ... -->
<hr> <!-- Add a separator for the new section -->
<li class="px-3"><small class="text-muted">AI Toolbox</small></li>
<li class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
    <a href="<?php echo BASE_URL; ?>/video/"><i class="fas fa-film me-2"></i> AI Video Editor</a>
</li>
<!-- You can add links to other AI tools here later -->
<hr>
<li>
    <a href="<?php echo BASE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
</li>
    </ul>
</nav>