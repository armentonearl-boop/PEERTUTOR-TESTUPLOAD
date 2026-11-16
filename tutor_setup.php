<?php
require "config.php";
require_login('tutor');

$me = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    // add (or update) a subject I can teach
    if ($action == 'add_subject') {
        $stmt = $db->prepare("INSERT INTO tutor_subjects (tutor_id, subject_id, skill_level) VALUES (?, ?, ?)
                              ON DUPLICATE KEY UPDATE skill_level = VALUES(skill_level)");
        $stmt->execute([$me, $_POST['subject_id'], $_POST['skill_level']]);
        $message = "Subject saved.";
    }

    // remove a subject (the tutor_id check makes sure it's really mine)
    if ($action == 'delete_subject') {
        $stmt = $db->prepare("DELETE FROM tutor_subjects WHERE id = ? AND tutor_id = ?");
        $stmt->execute([$_POST['id'], $me]);
    }

    // add a free time slot
    if ($action == 'add_time') {
        if ($_POST['start_time'] >= $_POST['end_time']) {
            $message = "The end time must be later than the start time.";
        } else {
            $stmt = $db->prepare("INSERT INTO availability (tutor_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$me, $_POST['day_of_week'], $_POST['start_time'], $_POST['end_time']]);
            $message = "Free time added.";
        }
    }

    if ($action == 'delete_time') {
        $stmt = $db->prepare("DELETE FROM availability WHERE id = ? AND tutor_id = ?");
        $stmt->execute([$_POST['id'], $me]);
    }
}

$all_subjects = $db->query("SELECT * FROM subjects ORDER BY name")->fetchAll();

$stmt = $db->prepare("SELECT ts.id, ts.skill_level, s.name
                      FROM tutor_subjects ts JOIN subjects s ON s.id = ts.subject_id
                      WHERE ts.tutor_id = ? ORDER BY s.name");
$stmt->execute([$me]);
$my_subjects = $stmt->fetchAll();

$stmt = $db->prepare("SELECT * FROM availability WHERE tutor_id = ? ORDER BY day_of_week, start_time");
$stmt->execute([$me]);
$my_times = $stmt->fetchAll();

include "header.php";
?>

<h3>My Subjects and Free Time</h3>

<?php if ($message != ""): ?>
    <div class="alert alert-info"><?= e($message) ?></div>
<?php endif; ?>

<div class="row">
    <!-- LEFT: subjects -->
    <div class="col-md-6">
        <div class="card-box">
            <h5>Subjects I can teach</h5>

            <form method="post" class="row g-2 mb-3">
                <input type="hidden" name="action" value="add_subject">
                <div class="col-sm-5">
                    <select name="subject_id" class="form-select" required>
                        <?php foreach ($all_subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-4">
                    <select name="skill_level" class="form-select">
                        <option>Beginner</option>
                        <option selected>Intermediate</option>
                        <option>Advanced</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <button class="btn btn-primary w-100">Add</button>
                </div>
            </form>

            <?php if (count($my_subjects) == 0): ?>
                <p class="text-muted">You have not added any subject yet.</p>
            <?php endif; ?>

            <?php foreach ($my_subjects as $row): ?>
                <form method="post" class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <span><?= e($row['name']) ?> <small class="text-muted">(<?= e($row['skill_level']) ?>)</small></span>
                    <input type="hidden" name="action" value="delete_subject">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" data-confirm="Remove this subject?">Remove</button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT: free time -->
    <div class="col-md-6">
        <div class="card-box">
            <h5>My free time</h5>

            <form method="post" class="row g-2 mb-3">
                <input type="hidden" name="action" value="add_time">
                <div class="col-sm-4">
                    <select name="day_of_week" class="form-select">
                        <?php foreach ($day_names as $num => $name): ?>
                            <option value="<?= $num ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-3"><input type="time" name="start_time" class="form-control" required></div>
                <div class="col-sm-3"><input type="time" name="end_time" class="form-control" required></div>
                <div class="col-sm-2"><button class="btn btn-primary w-100">Add</button></div>
            </form>

            <?php if (count($my_times) == 0): ?>
                <p class="text-muted">You have not added any free time yet.</p>
            <?php endif; ?>

            <?php foreach ($my_times as $row): ?>
                <form method="post" class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <span>
                        <?= $day_names[$row['day_of_week']] ?>,
                        <?= date("g:i A", strtotime($row['start_time'])) ?> -
                        <?= date("g:i A", strtotime($row['end_time'])) ?>
                    </span>
                    <input type="hidden" name="action" value="delete_time">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" data-confirm="Remove this time?">Remove</button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
