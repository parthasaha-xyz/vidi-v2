<?php
session_start();
// If admin is already logged in, redirect to the dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}
include 'includes/header.php';
?>
<div class="auth-container">
    <div class="glass-card">
        <div class="text-center mb-4">
            <i class="fas fa-user-shield fa-3x" style="color: #4f46e5;"></i>
            <h2 class="mt-3" style="color: #1e293b;">Admin Panel Login</h2>
        </div>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">Invalid credentials. Please try again.</div>
        <?php endif; ?>
        <form action="process/admin_login.php" method="post">
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Login</button>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>