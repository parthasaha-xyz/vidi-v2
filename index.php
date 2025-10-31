<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
include 'includes/header.php';
?>

<div class="auth-container full-page">
    <div class="container">
        <div class="row align-items-center" style="min-height: 100vh;">
            
            <!-- Right Column: Login Form (Order 2 on Desktop) -->
            <div class="col-lg-5 col-md-6 order-1 order-md-2">
                <div class="glass-card">
                    <div class="text-center mb-4">
                        <i class="fa-solid fa-heart-pulse fa-3x" style="color: #4f46e5;"></i>
                        <h2 class="mt-3" style="color: #1e293b;">Welcome Back</h2>
                        <p style="color: #64748b;">Please enter your details to login.</p>
                    </div>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php
                            if ($_GET['error'] == 'invalid_credentials') { echo 'Invalid email or password.'; } 
                            elseif ($_GET['error'] == 'not_verified') { echo 'Your account is not verified. Please check your email for the OTP.'; }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="process/process_handle.php" method="post">
                        <div class="mb-3">
                            <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                        <div class="text-end mb-3">
                            <a href="#" class="forgot-password-link">Forgot Password?</a>
                        </div>
                        <button type="submit" name="login" class="btn btn-custom w-100">Login</button>
                    </form>
                    <div class="text-center mt-4">
                        <a href="signup.php" class="auth-link">Don't have an account? Sign Up</a>
                    </div>
                </div>
            </div>

            <!-- Left Column: Information Panel (Order 1 on Desktop) -->
            <div class="col-lg-7 col-md-6 order-2 order-md-1 d-none d-md-block">
                <div class="info-panel">
                    <!-- Animated Shapes -->
                    <div class="shape shape1"></div>
                    <div class="shape shape2"></div>
                    <div class="shape shape3"></div>

                    <h1 class="info-title">Elevate Your Clinic Management with AI</h1>
                    <p class="info-subtitle">Streamline operations, engage patients, and grow your practice with our all-in-one platform.</p>
                    
                    <ul class="info-features">
                        <li>
                            <i class="fas fa-robot"></i>
                            <div>
                                <strong>AI Toolbox</strong>
                                <span>Generate videos, automate calls, and manage your online presence effortlessly.</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <strong>Smart Scheduling</strong>
                                <span>Manage complex multi-clinic schedules with ease.</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-users"></i>
                            <div>
                                <strong>Patient Management</strong>
                                <span>Keep all your patient and clinic data organized and accessible.</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>