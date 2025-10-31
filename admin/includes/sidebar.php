<nav id="sidebar">
    <div class="sidebar-header text-center">
        <h3><i class="fas fa-user-shield"></i> Admin Panel</h3>
    </div>
    <?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
    <ul class="list-unstyled components">
        <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        </li>
        <li class="<?php echo ($currentPage == 'videos.php') ? 'active' : ''; ?>">
        <a href="<?php echo BASE_URL; ?>/admin/videos.php"><i class="fas fa-video me-2"></i> Manage Videos</a>
    </li>

         <li class="<?php echo ($currentPage == 'categories.php') ? 'active' : ''; ?>">
        <a href="<?php echo BASE_URL; ?>/admin/categories.php"><i class="fas fa-tags me-2"></i> Video Categories</a>
    </li>

    <li class="<?php echo ($currentPage == 'tags.php') ? 'active' : ''; ?>">
    <a href="<?php echo BASE_URL; ?>/admin/tags.php"><i class="fas fa-hashtag me-2"></i> Video Tags</a>
</li>

<li>
        <a href="<?php echo BASE_URL; ?>/admin/avatars.php"><i class="fas fa-user-astronaut me-2"></i> Avatars</a>
    </li>
    </ul>
    <ul class="list-unstyled CTAs">
        <li>
            <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="btn btn-danger">Logout</a>
        </li>
    </ul>
</nav>