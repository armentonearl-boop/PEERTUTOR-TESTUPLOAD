<?php
// logger.php - saves what the users do into the activity_logs table.
// Pages that want to write a log just do:  require "logger.php";
// and then:  log_activity($db, $user_id, 'action name', 'some details');

function log_activity($db, $user_id, $action, $details = "") {
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $details]);
}
