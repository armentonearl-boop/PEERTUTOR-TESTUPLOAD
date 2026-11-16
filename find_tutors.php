<?php
require "config.php";
require_login('tutee');

$subjects = $db->query("SELECT * FROM subjects ORDER BY name")->fetchAll();

$subject_id = $_GET['subject_id'] ?? "";
$tutors = [];

if ($subject_id != "") {
    // all approved tutors for this subject + their average rating
    $sql = "SELECT u.id, u.full_name, ts.skill_level,
                   AVG(r.score) AS avg_score, COUNT(r.id) AS total_ratings
            FROM tutor_subjects ts
            JOIN users u ON u.id = ts.tutor_id
            LEFT JOIN sessions s ON s.tutor_id = u.id
            LEFT JOIN ratings r ON r.session_id = s.id
            WHERE ts.subject_id = ? AND u.approved = 1
            GROUP BY u.id, u.full_name, ts.skill_level
            ORDER BY avg_score DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute([$subject_id]);
    $tutors = $stmt->fetchAll();

    // get the free time of each tutor (one small query per tutor is fine for a school project)
    $time_stmt = $db->prepare("SELECT * FROM availability WHERE tutor_id = ? ORDER BY day_of_week, start_time");
    foreach ($tutors as $i => $t) {
        $time_stmt->execute([$t['id']]);
        $tutors[$i]['times'] = $time_stmt->fetchAll();
    }
}

include "header.php";
?>

<h3>Find a Tutor</h3>

<div class="card-box">
    <form method="get" class="row g-2">
        <div class="col-sm-8">
            <select name="subject_id" class="form-select" required>
                <option value="">-- Choose a subject --</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($subject_id == $s['id']) ? 'selected' : '' ?>>
                        <?= e($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-4">
            <button class="btn btn-primary w-100">Search</button>
        </div>
    </form>
</div>

<?php if ($subject_id != ""): ?>

    <?php if (count($tutors) == 0): ?>
        <div class="card-box">No tutors for this subject yet. Please try again later.</div>
    <?php else: ?>
        <input type="text" id="tutor_search" class="form-control mb-3" placeholder="Filter by name or day...">
    <?php endif; ?>

    <?php foreach ($tutors as $t): ?>
        <div class="card-box tutor-card">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-1"><?= e($t['full_name']) ?></h5>
                    <span class="badge bg-secondary"><?= e($t['skill_level']) ?></span>
                    <?php if ($t['total_ratings'] > 0): ?>
                        <span class="ms-2">&#9733; <?= number_format($t['avg_score'], 1) ?>
                            <small class="text-muted">(<?= $t['total_ratings'] ?> ratings)</small></span>
                    <?php else: ?>
                        <span class="ms-2 text-muted">No ratings yet</span>
                    <?php endif; ?>
                </div>
                <div>
                    <a class="btn btn-primary" href="book.php?tutor_id=<?= $t['id'] ?>&subject_id=<?= e($subject_id) ?>">Book</a>
                </div>
            </div>

            <p class="mt-3 mb-1"><small class="text-muted">Free time:</small></p>
            <?php if (count($t['times']) == 0): ?>
                <p class="mb-0">No free time added yet.</p>
            <?php endif; ?>
            <?php foreach ($t['times'] as $time): ?>
                <div>
                    <?= $day_names[$time['day_of_week']] ?>,
                    <?= date("g:i A", strtotime($time['start_time'])) ?> -
                    <?= date("g:i A", strtotime($time['end_time'])) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<?php include "footer.php"; ?>
