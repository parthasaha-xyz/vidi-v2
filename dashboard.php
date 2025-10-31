<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'config/db.php';

// --- FETCH REAL DATA FOR THE DASHBOARD CARDS ---
$user_id = $_SESSION['user_id'];
$stmt_clinics = $pdo->prepare("SELECT COUNT(*) FROM clinics WHERE user_id = ?");
$stmt_clinics->execute([$user_id]);
$clinic_count = $stmt_clinics->fetchColumn();

$stmt_staff = $pdo->prepare("SELECT COUNT(*) FROM clinic_staff WHERE user_id = ?");
$stmt_staff->execute([$user_id]);
$staff_count = $stmt_staff->fetchColumn();

// Static data for now
$veni_status = "Pending";
$ad_credit = "INR 00.00";

include 'includes/header.php';
?>

<div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>
        
        <div class="container-fluid">
            <h1 class="my-4" style="color: #1e293b;">Dashboard</h1>

            <!-- =================================================================
             * NEW: AI TOOLBOX CARDS (NOW DIRECTLY ON THE DASHBOARD)
             * ================================================================= -->
            <h5 class="text-muted mb-4">Supercharge your clinic with our new AI-powered tools</h5>
            <div class="row ai-tools-grid"> <!-- Added .ai-tools-grid for animation targeting -->
                <!-- Tool 1: AI Video Editor -->
                <div class="col-xl-3 col-md-3 col-sm-6 col-xs-6">
                    <div class="tool-card tool-card-video">
                        <div class="tool-icon-wrapper">
                            <i class="fas fa-film tool-icon"></i>
                        </div>
                        <h5>AI<br>Video Editor</h5>
                        <p>Automatically create professional promotion videos for your clinic.</p>
                    </div>
                </div>
                
                <!-- Tool 2: AI Voice Call -->
                <div class="col-xl-3 col-md-3 col-sm-6 col-xs-6">
                    <div class="tool-card tool-card-voice">
                        <div class="tool-icon-wrapper">
                            <i class="fas fa-phone-alt tool-icon"></i>
                        </div>
                        <h5>AI<br>Voice Call</h5>
                        <p>Automate appointment reminders and confirmations with a smart AI voice.</p>
                    </div>
                </div>
                <!-- Tool 3: WA API Automation -->
                <div class="col-xl-3 col-md-3 col-sm-6 col-xs-6">
                    <div class="tool-card tool-card-wa">
                        <div class="tool-icon-wrapper">
                            <i class="fab fa-whatsapp tool-icon"></i>
                        </div>
                        <h5>WA API<br>Automation</h5>
                        <p>Engage patients with automated WhatsApp messages, offers, and follow-ups.</p>
                    </div>
                </div>
                <!-- Tool 4: Google Profile Manage -->
                <div class="col-xl-3 col-md-3 col-sm-6 col-xs-6">
                    <div class="tool-card tool-card-google">
                         <div class="tool-icon-wrapper">
                            <i class="fab fa-google tool-icon"></i>
                        </div>
                        <h5>Google Profile<br>Manage</h5>
                        <p>Let AI optimize your Google Business Profile to attract more local patients.</p>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Standard Stat Cards -->
            <div class="row">
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= $clinic_count ?></h3>
                                <p>Total Clinics</p>
                            </div>
                            <i class="fas fa-clinic-medical fa-3x" style="color: #c7d2fe;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3><?= $staff_count ?></h3>
                                <p>Total Staff</p>
                            </div>
                            <i class="fas fa-users-cog fa-3x" style="color: #c7d2fe;"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 mb-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="text-warning"><?= $veni_status ?></h3>
                            <p>Veni Status</p>
                        </div>
                    </div>
                </div>
                 <div class="col-md-6 col-xl-3 mb-4">
    <div class="stat-card wallet-card">
        <div class="wallet-header">
            <p>Ad Credit</p>
            <i class="fas fa-microchip"></i>
        </div>
        <div class="wallet-balance">
            <h3><?= $ad_credit ?></h3>
        </div>
        <div class="wallet-footer">
            <a href="#" class="btn btn-wallet">Add Funds</a>
        </div>
    </div>
</div>
            </div>
            
        </div>
    </div>
</div>

<!-- NOTE: The AI Toolbox Modal has been completely removed from this file. -->

<?php include 'includes/footer.php'; ?>