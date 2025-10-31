<?php
session_start();
include 'includes/header.php';
?>

<div class="auth-container">
    <div class="glass-card">
        <div class="text-center mb-4">
            <i class="fa-solid fa-shield-halved fa-3x" style="color: #4f46e5;"></i>
            <h2 class="mt-3" style="color: #1e293b;">Email Verification</h2>
            <p style="color: #64748b;">An OTP has been sent to your email. Please enter it below.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                Incorrect OTP. Please try again.
            </div>
        <?php endif; ?>

        <form action="process/verify_otp_handle.php" method="post">
            <div class="mb-3">
                <input type="number" class="form-control text-center" name="otp" placeholder="Enter 6-Digit OTP" required>
            </div>
            <button type="submit" name="verify" class="btn btn-custom w-100">Verify Account</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>