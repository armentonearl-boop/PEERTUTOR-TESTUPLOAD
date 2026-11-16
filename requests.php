<?php
require "config.php";
require "logger.php";
require_login('tutor');

$me = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id     = $_POST['session_id'];
    $action = $_POST['action'];
    $notes  = trim($_POST['notes'] ?? '');

    // figure out the new status from the button that was clicked
    $new_status = "";
    if ($action == 'accept')   $new_status = 'accepted';
    if ($action == 'decline')  $new_status = 'declined';
    if ($action == 'complete') $new_status = 'completed';
    if ($action == 'no_show')  $new_status = 'no_show';

    if ($new_status != "") {
        // TRANSACTION: update the session and write the log together (all or nothing)
        try {
            $db->beginTransaction();

            // "AND tutor_id = ?" so a tutor can only change their own sessions
            $stmt = $db->prepare("UPDATE sessions SET status = ?, notes = ? WHERE id = ? AND tutor_id = ?");
            $stmt->execute([$new_status, $notes, $id, $me]);

            if ($stmt->rowCount() > 0) {
                log_activity($db, $me, 'session_' . $new_status, "Session #$id marked as $new_status");
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();
        }
    }
    header("Location: requests.php");
    exit;
}

$stmt = $db->prepare("SELECT s.*, sub.name AS subject, u.full_name AS tutee_name
                      FROM sessions s
                      JOIN subjects sub ON sub.id = s.subject_id
                      JOIN users u ON u.id = s.tutee_id
                      WHERE s.tutor_id = ?
                      ORDER BY s.session_date DESC, s.session_time DESC");
$stmt->execute([$me]);
$sessions = $stmt->fetchAll();

include "header.php";
?>

<h3>Tutoring Requests</h3>

<?php if (count($sessions) == 0): ?>
    <div class="card-box">No requests yet. Make sure you added your subjects and free time!</div>
<?php endif; ?>

<?php foreach ($sessions as $s): ?>
    <div class="card-box">
        <div class="d-flex justify-content-between">
            <div>
                <strong><?= e($s['subject']) ?></strong> with <?= e($s['tutee_name']) ?><br>
                <?= date("D, M j, Y", strtotime($s['session_date'])) ?>
                at <?= date("g:i A", strtotime($s['session_time'])) ?>
            </div>
            <div><span class="badge badge-<?= $s['status'] ?>"><?= e($s['status']) ?></span></div>
        </div>

        <?php if ($s['status'] == 'pending'): ?>
            <form method="post" class="mt-3">
                <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                <button name="action" value="accept" class="btn btn-success btn-sm">Accept</button>
                <button name="action" value="decline" class="btn btn-outline-danger btn-sm"
                        data-confirm="Decline this request?">Decline</button>
            </form>

        <?php elseif ($s['status'] == 'accepted'): ?>
            <!-- after the session: write notes, then mark it done (or no-show) -->
            <form method="post" class="mt-3">
                <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                <textarea name="notes" class="form-control mb-2" rows="2"
                          placeholder="Session notes (what did you cover?)"></textarea>
                <button name="action" value="complete" class="btn btn-primary btn-sm">Mark as completed</button>
                <button name="action" value="no_show" class="btn btn-outline-secondary btn-sm"
                        data-confirm="The tutee did not come?">Tutee did not show up</button>
            </form>

        <?php elseif ($s['notes'] != ""): ?>
            <p class="mt-3 mb-0"><small class="text-muted">Notes:</small> <?= e($s['notes']) ?></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php include "footer.php"; ?>
