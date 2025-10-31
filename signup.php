<?php
session_start();
// If user is already logged in, redirect them away from the signup page
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
include 'includes/header.php';
?>

<div class="auth-container full-page">
    <div class="container">
        <div class="row align-items-center" style="min-height: 100vh;">
            
            <!-- Right Column: Signup Form (Order 2 on Desktop) -->
            <div class="col-lg-5 col-md-6 order-1 order-md-2">
                <div class="glass-card">
                     <div class="text-center mb-4">
                        <i class="fa-solid fa-user-plus fa-3x" style="color: #4f46e5;"></i>
                        <h2 class="mt-3" style="color: #1e293b;">Create Your Account</h2>
                        <p style="color: #64748b;">Join us to manage your clinic efficiently.</p>
                    </div>

                    <form action="process/process_handle.php" method="post" id="signup-form">
                        <div class="mb-3"><input type="text" class="form-control" name="name" placeholder="Full Name" required></div>
                        <div class="mb-3"><input type="text" class="form-control" name="business_name" placeholder="Business Name" required></div>
                        <div class="mb-3"><input type="tel" class="form-control" name="phone" placeholder="Phone Number" required></div>
                        <div class="mb-3"><input type="email" class="form-control" name="email" placeholder="Email Address" required></div>
                        <div class="mb-3"><input type="password" class="form-control" id="password" name="password" placeholder="Password" required></div>
                        <div class="mb-3"><input type="password" class="form-control" id="confirm-password" name="confirm_password" placeholder="Confirm Password" required></div>
                        
                        <div id="password-criteria" class="mb-3">
                            <p id="length" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>Min 8 characters</p>
                            <p id="uppercase" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>One uppercase letter</p>
                            <p id="lowercase" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>One lowercase letter</p>
                            <p id="number" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>One number</p>
                            <p id="symbol" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>One symbol</p>
                            <p id="match" class="criteria invalid"><i class="fas fa-times-circle me-2"></i>Passwords match</p>
                        </div>

                        <button type="submit" name="signup" id="signup-button" class="btn btn-custom w-100" disabled>Create Account</button>
                    </form>

                     <div class="text-center mt-4">
                        <a href="index.php" class="auth-link">Already have an account? Login</a>
                    </div>
                </div>
            </div>

            <!-- Left Column: Information Panel (Order 1 on Desktop) -->
            <div class="col-lg-7 col-md-6 order-2 order-md-1 d-none d-md-block">
                <div class="info-panel">
                    <!-- Animated Shapes (reusing the same ones) -->
                    <div class="shape shape1"></div>
                    <div class="shape shape2"></div>
                    <div class="shape shape3"></div>

                    <h1 class="info-title">Join the Future of Clinic Management</h1>
                    <p class="info-subtitle">Sign up in seconds to unlock a suite of tools designed to help your practice grow.</p>
                    
                    <ul class="info-features">
                        <li>
                            <i class="fas fa-robot"></i>
                            <div>
                                <strong>AI-Powered Marketing</strong>
                                <span>Generate videos and automate patient communication with our intelligent tools.</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-tasks"></i>
                            <div>
                                <strong>Effortless Organization</strong>
                                <span>Manage multiple clinics, staff, schedules, and pricing from a single dashboard.</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-chart-line"></i>
                            <div>
                                <strong>Drive Growth</strong>
                                <span>Create beautiful public profiles for your clinics to attract and retain more patients.</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>