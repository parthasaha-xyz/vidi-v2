<?php
session_start();
// Unset only the admin session variable
unset($_SESSION['admin_logged_in']);
// You can also use session_destroy() if the admin panel is completely separate
// session_destroy();
header('Location: index.php');
exit();