<?php
// export_csv.php - downloads the sessions as a file that opens in Excel.
// A tutee gets only their own sessions, the teacher gets everything.
require "config.php";
require_login();

if ($_SESSION['role'] == 'tutor') {
    die("Not allowed.");
}

$sql = "SELECT s.session_date, s.session_time, sub.name AS subject,
               tutee.full_name AS tutee, tutor.full_name AS tutor,
               s.status, r.score, s.notes
        FROM sessions s
        JOIN subjects sub ON sub.id = s.subject_id
        JOIN users tutee ON tutee.id = s.tutee_id
        JOIN users tutor ON tutor.id = s.tutor_id
        LEFT JOIN ratings r ON r.session_id = s.id";

if ($_SESSION['role'] == 'tutee') {
    $stmt = $db->prepare($sql . " WHERE s.tutee_id = ? ORDER BY s.session_date");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    $stmt = $db->query($sql . " ORDER BY s.session_date");
}

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=peertutor_sessions.csv");

$out = fopen("php://output", "w");
fputcsv($out, ["Date", "Time", "Subject", "Tutee", "Tutor", "Status", "Rating", "Notes"]);
while ($row = $stmt->fetch()) {
    fputcsv($out, $row);
}
fclose($out);
