<?php
require "config.php";
require "logger.php";
require_login('tutee');

$me = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // cancel a session (only if it's mine and not finished yet)
    if ($_POST['action'] == 'cancel') {
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("UPDATE sessions SET status = 'cancelled'
                                  WHERE id = ? AND tutee_id = ? AND status IN ('pending','accepted')");
            $stmt->execute([$_POST['session_id'], $me]);
            if ($stmt->rowCount() > 0) {
                log_activity($db, $me, 'cancel_session', "Cancelled session #" . $_POST['session_id']);
            }
            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();
        }
    }

    // rate a completed session
    if ($_POST['action'] == 'rate') {
        $score = (int) $_POST['score'];
        if ($score >= 1 && $score <= 5) {
            // make sure it's my session and it is completed
            $stmt = $db->prepare("SELECT id FROM sessions WHERE id = ? AND tutee_id = ? AND status = 'completed'");
            $stmt->execute([$_POST['session_id'], $me]);
            if ($stmt->fetch()) {
                // INSERT IGNORE: if there is already a rating for it, do nothing
                try {
                    $db->beginTransaction();
                    $stmt = $db->prepare("INSERT IGNORE INTO ratings (session_id, score, comment) VALUES (?, ?, ?)");
                    $stmt->execute([$_POST['session_id'], $score, trim($_POST['comment'])]);
                    if ($stmt->rowCount() > 0) {
                        log_activity($db, $me, 'rate_session', "Rated session #" . $_POST['session_id'] . " with $score/5");
                    }
                    $db->commit();
                } catch (Exception $ex) {
                    $db->rollBack();
                }
            }
        }
    }
    header("Location: my_sessions.php");
    exit;
}

$stmt = $db->prepare("SELECT s.*, sub.name AS subject, u.full_name AS tutor_name, r.score, r.comment
                      FROM sessions s
                      JOIN subjects sub ON sub.id = s.subject_id
                      JOIN users u ON u.id = s.tutor_id
                      LEFT JOIN ratings r ON r.session_id = s.id
                      WHERE s.tutee_id = ?
                      ORDER BY s.session_date DESC, s.session_time DESC");
$stmt->execute([$me]);
$sessions = $stmt->fetchAll();

// simple progress numbers
$total = count($sessions);
$done = 0;
foreach ($sessions as $s) {
    if ($s['status'] == 'completed') $done++;
}

include "header.php";
?>

<h3>My Sessions</h3>

<?php if (isset($_GET['booked'])): ?>
    <div class="alert alert-success">Request sent! Wait for the tutor to accept it.</div>
<?php endif; ?>

<div class="card-box">
    Total booked: <strong><?= $total ?></strong> &nbsp;|&nbsp;
    Completed: <strong><?= $done ?></strong>
    &nbsp;&nbsp;<a href="export_csv.php" class="btn btn-sm btn-outline-secondary">Download my progress (CSV)</a>
</div>

<?php if ($total == 0): ?>
    <div class="card-box">You have no sessions yet. <a href="find_tutors.php">Find a tutor</a></div>
<?php endif; ?>

<?php foreach ($sessions as $s): ?>
    <div class="card-box">
        <div class="d-flex justify-content-between">
            <div>
                <strong><?= e($s['subject']) ?></strong> with <?= e($s['tutor_name']) ?><br>
                <?= date("D, M j, Y", strtotime($s['session_date'])) ?>
                at <?= date("g:i A", strtotime($s['session_time'])) ?>
            </div>
            <div><span class="badge badge-<?= $s['status'] ?>"><?= e($s['status']) ?></span></div>
        </div>

        <?php if ($s['status'] == 'completed' && $s['notes'] != ""): ?>
            <p class="mt-3 mb-0"><small class="text-muted">Tutor's notes:</small> <?= e($s['notes']) ?></p>
        <?php endif; ?>

        <?php if ($s['status'] == 'pending' || $s['status'] == 'accepted'): ?>
            <form method="post" class="mt-3">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" data-confirm="Cancel this session?">Cancel session</button>
            </form>
        <?php endif; ?>

        <?php if ($s['status'] == 'completed'): ?>
            <?php if ($s['score']): ?>
                <p class="mt-3 mb-0">Your rating: &#9733; <?= $s['score'] ?>/5
                    <?php if ($s['comment']) echo "- " . e($s['comment']); ?></p>
            <?php else: ?>
                <form method="post" class="row g-2 mt-2">
                    <input type="hidden" name="action" value="rate">
                    <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                    <div class="col-sm-3">
                        <select name="score" class="form-select" required>
                            <option value="">Rate...</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Okay</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Bad</option>
                        </select>
                    </div>
                    <div class="col-sm-7">
                        <input type="text" name="comment" class="form-control" maxlength="255" placeholder="Comment (optional)">
                    </div>
                    <div class="col-sm-2"><button class="btn btn-primary w-100">Send</button></div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php include "footer.php"; ?>
