<?php
require "config.php";
require "logger.php";

// write the log first, then destroy the session
if (isset($_SESSION['user_id'])) {
    log_activity($db, $_SESSION['user_id'], 'logout', 'Logged out');
}

session_destroy();
header("Location: login.php");
exit;
