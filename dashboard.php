<?php
require "config.php";
require_login();

$me   = $_SESSION['user_id'];
$role = $_SESSION['role'];

// ---- reminders: accepted sessions today or tomorrow ----
$reminders = [];
if ($role == 'tutee' || $role == 'tutor') {
    // the column we filter by depends on who is logged in
    $column = ($role == 'tutee') ? 'tutee_id' : 'tutor_id';
    // and the name we show is the OTHER person
    $other  = ($role == 'tutee') ? 'tutor_id' : 'tutee_id';

    $sql = "SELECT s.session_date, s.session_time, sub.name AS subject, u.full_name AS other_name
            FROM sessions s
            JOIN subjects sub ON sub.id = s.subject_id
            JOIN users u ON u.id = s.$other
            WHERE s.$column = ? AND s.status = 'accepted'
              AND s.session_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            ORDER BY s.session_date, s.session_time";
    $stmt = $db->prepare($sql);
    $stmt->execute([$me]);
    $reminders = $stmt->fetchAll();
}

// ---- small counters for the tutor and admin ----
$pending_requests = 0;
$pending_tutors = 0;
if ($role == 'tutor') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM sessions WHERE tutor_id = ? AND status = 'pending'");
    $stmt->execute([$me]);
    $pending_requests = $stmt->fetchColumn();
}
if ($role == 'admin') {
    $pending_tutors = $db->query("SELECT COUNT(*) FROM users WHERE role = 'tutor' AND approved = 0")->fetchColumn();
}

include "header.php";
?>

<h3>Welcome, <?= e($_SESSION['full_name']) ?>!</h3>

<?php if (count($reminders) > 0): ?>
    <div class="card-box">
        <h5>Reminders</h5>
        <?php foreach ($reminders as $r): ?>
            <div class="reminder">
                <?= e($r['subject']) ?> with <strong><?= e($r['other_name']) ?></strong>
                on <?= date("D, M j", strtotime($r['session_date'])) ?>
                at <?= date("g:i A", strtotime($r['session_time'])) ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card-box">
    <?php if ($role == 'tutee'): ?>
        <p>Need help in a subject? Find a classmate who can tutor you.</p>
        <a href="find_tutors.php" class="btn btn-primary">Find a Tutor</a>
        <a href="my_sessions.php" class="btn btn-outline-primary">My Sessions</a>

    <?php elseif ($role == 'tutor'): ?>
        <p>You have <strong><?= $pending_requests ?></strong> pending request(s).</p>
        <a href="requests.php" class="btn btn-primary">See Requests</a>
        <a href="tutor_setup.php" class="btn btn-outline-primary">Set my subjects and free time</a>

    <?php else: ?>
        <p>There are <strong><?= $pending_tutors ?></strong> tutor account(s) waiting for approval.</p>
        <a href="approve_tutors.php" class="btn btn-primary">Approve Tutors</a>
        <a href="reports.php" class="btn btn-outline-primary">View Reports</a>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
