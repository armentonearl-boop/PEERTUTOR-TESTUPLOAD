<?php
// config.php - included at the top of every page.
// It connects to the database, starts the session and has a few helper functions.

session_start();

$host   = "localhost";
$dbname = "peertutor";
$dbuser = "root";   // default XAMPP user
$dbpass = "";       // default XAMPP has no password

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
    die("Cannot connect to the database. Is MySQL started in XAMPP? Did you import database.sql?");
}

// auto-logout after 15 minutes of doing nothing
$timeout = 15 * 60;
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_active']) && time() - $_SESSION['last_active'] > $timeout) {
        session_destroy();
        header("Location: login.php?msg=timeout");
        exit;
    }
    $_SESSION['last_active'] = time();
}

// short name for htmlspecialchars so we don't write it so many times
// (it stops people from typing html/script into our forms)
function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

// call this at the top of a page that needs a logged in user.
// $role = 'admin', 'tutor' or 'tutee'. Leave it out if any role is okay.
function require_login($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    if ($role !== null && $_SESSION['role'] !== $role) {
        die("Sorry, you are not allowed to open this page. <a href='dashboard.php'>Go back</a>");
    }
}

$day_names = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
              5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
