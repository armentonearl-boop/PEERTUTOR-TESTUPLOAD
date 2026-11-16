<?php
// index.php - just sends the person to the right place
require "config.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
