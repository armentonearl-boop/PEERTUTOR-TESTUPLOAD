<?php
require "config.php";
require_login('admin');

// ---------- numbers for the summary boxes ----------
$total_users    = $db->query("SELECT COUNT(*) FROM users WHERE role <> 'admin'")->fetchColumn();
$total_sessions = $db->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
$total_done     = $db->query("SELECT COUNT(*) FROM sessions WHERE status = 'completed'")->fetchColumn();
$avg_rating     = $db->query("SELECT AVG(score) FROM ratings")->fetchColumn();

// ---------- 1. sessions per subject ----------
// LEFT JOIN: so a subject with zero sessions still shows up in the list
$per_subject = $db->query("SELECT sub.name,
                                  COUNT(s.id) AS requested,
                                  SUM(s.status = 'completed') AS held
                           FROM subjects sub
                           LEFT JOIN sessions s ON s.subject_id = sub.id
                           GROUP BY sub.id, sub.name
                           ORDER BY requested DESC")->fetchAll();

// ---------- 2. sessions by status (for the pie chart) ----------
$per_status = $db->query("SELECT status, COUNT(*) AS total FROM sessions GROUP BY status")->fetchAll();

// ---------- 3. tutors ----------
$tutors = $db->query("SELECT u.full_name,
                             SUM(s.status = 'completed') AS sessions_done,
                             SUM(s.status = 'no_show') AS no_shows,
                             AVG(r.score) AS avg_score
                      FROM users u
                      LEFT JOIN sessions s ON s.tutor_id = u.id
                      LEFT JOIN ratings r ON r.session_id = s.id
                      WHERE u.role = 'tutor' AND u.approved = 1
                      GROUP BY u.id, u.full_name
                      ORDER BY avg_score DESC")->fetchAll();

// ---------- 4. transaction history (latest 15 sessions) ----------
// INNER JOIN: a session always has a tutee, a tutor and a subject
$history = $db->query("SELECT s.id, s.session_date, s.session_time, s.status,
                              sub.name AS subject, tutee.full_name AS tutee, tutor.full_name AS tutor
                       FROM sessions s
                       JOIN subjects sub ON sub.id = s.subject_id
                       JOIN users tutee ON tutee.id = s.tutee_id
                       JOIN users tutor ON tutor.id = s.tutor_id
                       ORDER BY s.created_at DESC, s.id DESC
                       LIMIT 15")->fetchAll();

// ---------- 5. user activity logs (latest 20) ----------
$logs = $db->query("SELECT l.created_at, l.action, l.details, u.full_name, u.role
                    FROM activity_logs l
                    LEFT JOIN users u ON u.id = l.user_id
                    ORDER BY l.id DESC
                    LIMIT 20")->fetchAll();

// the first row is the most requested because we sorted it above
$top_subject = (count($per_subject) > 0 && $per_subject[0]['requested'] > 0) ? $per_subject[0]['name'] : "none yet";

// data for the charts (turned into JavaScript arrays)
$chart_names     = [];
$chart_requested = [];
$chart_held      = [];
foreach ($per_subject as $row) {
    $chart_names[]     = $row['name'];
    $chart_requested[] = (int) $row['requested'];
    $chart_held[]      = (int) ($row['held'] ?? 0);
}
$status_names  = [];
$status_totals = [];
foreach ($per_status as $row) {
    $status_names[]  = $row['status'];
    $status_totals[] = (int) $row['total'];
}

include "header.php";
?>

<h3>Program Reports</h3>

<!-- summary boxes -->
<div class="row">
    <div class="col-6 col-md-3"><div class="card-box text-center"><h3><?= $total_users ?></h3>Students</div></div>
    <div class="col-6 col-md-3"><div class="card-box text-center"><h3><?= $total_sessions ?></h3>Sessions booked</div></div>
    <div class="col-6 col-md-3"><div class="card-box text-center"><h3><?= $total_done ?></h3>Sessions completed</div></div>
    <div class="col-6 col-md-3"><div class="card-box text-center"><h3><?= $avg_rating ? number_format($avg_rating, 1) : "-" ?></h3>Average rating</div></div>
</div>

<div class="card-box">
    Most requested subject: <strong><?= e($top_subject) ?></strong>
    &nbsp;&nbsp;<a href="export_csv.php" class="btn btn-sm btn-outline-secondary">Download all sessions (CSV)</a>
    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">Print / Save as PDF</button>
</div>

<!-- charts -->
<div class="row">
    <div class="col-md-7">
        <div class="card-box">
            <h5>Sessions per subject</h5>
            <canvas id="subjectChart" height="150"></canvas>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card-box">
            <h5>Sessions by status</h5>
            <canvas id="statusChart" height="210"></canvas>
        </div>
    </div>
</div>

<div class="card-box">
    <h5>Subjects</h5>
    <table class="table">
        <thead><tr><th>Subject</th><th>Requested</th><th>Held (completed)</th></tr></thead>
        <tbody>
        <?php foreach ($per_subject as $row): ?>
            <tr>
                <td><?= e($row['name']) ?></td>
                <td><?= $row['requested'] ?></td>
                <td><?= $row['held'] ?? 0 ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card-box">
    <h5>Tutors</h5>
    <table class="table">
        <thead><tr><th>Tutor</th><th>Sessions done</th><th>No-shows</th><th>Average rating</th></tr></thead>
        <tbody>
        <?php foreach ($tutors as $t): ?>
            <tr>
                <td><?= e($t['full_name']) ?></td>
                <td><?= $t['sessions_done'] ?? 0 ?></td>
                <td><?= $t['no_shows'] ?? 0 ?></td>
                <td><?= $t['avg_score'] ? number_format($t['avg_score'], 1) . " / 5" : "-" ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card-box">
    <h5>Transaction history (latest 15 sessions)</h5>
    <div class="table-responsive">
    <table class="table">
        <thead><tr><th>#</th><th>Date</th><th>Time</th><th>Subject</th><th>Tutee</th><th>Tutor</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
            <tr>
                <td><?= $h['id'] ?></td>
                <td><?= date("M j, Y", strtotime($h['session_date'])) ?></td>
                <td><?= date("g:i A", strtotime($h['session_time'])) ?></td>
                <td><?= e($h['subject']) ?></td>
                <td><?= e($h['tutee']) ?></td>
                <td><?= e($h['tutor']) ?></td>
                <td><span class="badge badge-<?= $h['status'] ?>"><?= e($h['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php if (count($history) == 0) echo "<p class='text-muted mb-0'>No sessions yet.</p>"; ?>
</div>

<div class="card-box">
    <h5>User activity log (latest 20)</h5>
    <div class="table-responsive">
    <table class="table">
        <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
            <tr>
                <td><?= date("M j, g:i A", strtotime($l['created_at'])) ?></td>
                <td><?= e($l['full_name'] ?? 'deleted user') ?> <small class="text-muted">(<?= e($l['role']) ?>)</small></td>
                <td><?= e($l['action']) ?></td>
                <td><?= e($l['details']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php if (count($logs) == 0) echo "<p class='text-muted mb-0'>No activity yet.</p>"; ?>
</div>

<!-- Chart.js draws the two charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // bar chart: requested vs completed for every subject
    new Chart(document.getElementById("subjectChart"), {
        type: "bar",
        data: {
            labels: <?= json_encode($chart_names) ?>,
            datasets: [
                { label: "Requested", data: <?= json_encode($chart_requested) ?>, backgroundColor: "#0284c7" },
                { label: "Completed", data: <?= json_encode($chart_held) ?>, backgroundColor: "#16a34a" }
            ]
        },
        options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    // doughnut chart: how many sessions are pending, accepted, completed...
    new Chart(document.getElementById("statusChart"), {
        type: "doughnut",
        data: {
            labels: <?= json_encode($status_names) ?>,
            datasets: [{
                data: <?= json_encode($status_totals) ?>,
                backgroundColor: ["#f59e0b", "#0284c7", "#16a34a", "#dc2626", "#64748b", "#a855f7"]
            }]
        }
    });
</script>

<?php include "footer.php"; ?>
