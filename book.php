<?php
require "config.php";
require "logger.php";
require_login('tutee');

$me         = $_SESSION['user_id'];
$tutor_id   = $_GET['tutor_id'] ?? 0;
$subject_id = $_GET['subject_id'] ?? 0;

// get the tutor and the subject (also checks that this tutor really teaches it)
$stmt = $db->prepare("SELECT u.full_name, s.name AS subject
                      FROM tutor_subjects ts
                      JOIN users u ON u.id = ts.tutor_id
                      JOIN subjects s ON s.id = ts.subject_id
                      WHERE ts.tutor_id = ? AND ts.subject_id = ? AND u.approved = 1");
$stmt->execute([$tutor_id, $subject_id]);
$info = $stmt->fetch();

if (!$info) {
    die("Tutor not found. <a href='find_tutors.php'>Go back</a>");
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['session_date'];
    $time = $_POST['session_time'];

    if ($date < date("Y-m-d")) {
        $error = "You cannot book a date that already passed.";
    } else {
        // what day is it? date("N") gives 1 (Monday) to 7 (Sunday), same as our database
        $day = date("N", strtotime($date));

        // is the tutor free on that day and time?
        $stmt = $db->prepare("SELECT id FROM availability
                              WHERE tutor_id = ? AND day_of_week = ? AND ? >= start_time AND ? < end_time");
        $stmt->execute([$tutor_id, $day, $time, $time]);
        $is_free = $stmt->fetch();

        // does the tutor already have a session at that same time?
        $stmt = $db->prepare("SELECT id FROM sessions
                              WHERE tutor_id = ? AND session_date = ? AND session_time = ?
                                AND status IN ('pending','accepted')");
        $stmt->execute([$tutor_id, $date, $time]);
        $is_taken = $stmt->fetch();

        if (!$is_free) {
            $error = "The tutor is not free at that day and time. Please check the free time list.";
        } elseif ($is_taken) {
            $error = "Sorry, that time is already booked. Please pick another one.";
        } else {
            // TRANSACTION: we save the session AND the log together.
            // If one of them fails, both are undone so the data stays consistent.
            try {
                $db->beginTransaction();

                $stmt = $db->prepare("INSERT INTO sessions (tutee_id, tutor_id, subject_id, session_date, session_time)
                                      VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$me, $tutor_id, $subject_id, $date, $time]);
                $new_id = $db->lastInsertId();

                log_activity($db, $me, 'book_session',
                             "Booked session #$new_id with " . $info['full_name'] . " on $date $time");

                $db->commit();
                header("Location: my_sessions.php?booked=1");
                exit;
            } catch (Exception $ex) {
                $db->rollBack();
                $error = "Something went wrong and nothing was saved. Please try again.";
            }
        }
    }
}

// show the free times on the page so the student knows what to pick
$stmt = $db->prepare("SELECT * FROM availability WHERE tutor_id = ? ORDER BY day_of_week, start_time");
$stmt->execute([$tutor_id]);
$times = $stmt->fetchAll();

include "header.php";
?>

<h3>Book a Session</h3>

<div class="card-box">
    <p><strong><?= e($info['subject']) ?></strong> with <strong><?= e($info['full_name']) ?></strong></p>

    <p class="mb-1"><small class="text-muted">Free time of this tutor:</small></p>
    <?php foreach ($times as $t): ?>
        <div>
            <?= $day_names[$t['day_of_week']] ?>,
            <?= date("g:i A", strtotime($t['start_time'])) ?> -
            <?= date("g:i A", strtotime($t['end_time'])) ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="card-box">
    <?php if ($error != ""): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="session_date" id="session_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Start time</label>
            <input type="time" name="session_time" class="form-control" required>
        </div>
        <button class="btn btn-primary">Send request</button>
        <a href="find_tutors.php?subject_id=<?= e($subject_id) ?>" class="btn btn-link">Back</a>
    </form>
</div>

<?php include "footer.php"; ?>
