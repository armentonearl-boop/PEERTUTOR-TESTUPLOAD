<?php
require "config.php";
require "logger.php";
require_login('admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['user_id'];

    if ($_POST['action'] == 'approve') {
        $stmt = $db->prepare("UPDATE users SET approved = 1 WHERE id = ? AND role = 'tutor'");
        $stmt->execute([$id]);
        log_activity($db, $_SESSION['user_id'], 'approve_tutor', "Approved tutor account #$id");
    }
    if ($_POST['action'] == 'reject') {
        // rejecting = deleting the account
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'tutor' AND approved = 0");
        $stmt->execute([$id]);
        log_activity($db, $_SESSION['user_id'], 'reject_tutor', "Rejected tutor account #$id");
    }
    header("Location: approve_tutors.php");
    exit;
}

$waiting = $db->query("SELECT id, student_id, full_name, created_at FROM users
                       WHERE role = 'tutor' AND approved = 0 ORDER BY created_at")->fetchAll();

$approved = $db->query("SELECT student_id, full_name FROM users
                        WHERE role = 'tutor' AND approved = 1 ORDER BY full_name")->fetchAll();

include "header.php";
?>

<h3>Approve Tutors</h3>

<div class="card-box">
    <h5>Waiting for approval</h5>
    <?php if (count($waiting) == 0): ?>
        <p class="text-muted mb-0">Nobody is waiting.</p>
    <?php endif; ?>

    <?php foreach ($waiting as $w): ?>
        <form method="post" class="d-flex justify-content-between align-items-center border-bottom py-2">
            <span><?= e($w['full_name']) ?> <small class="text-muted">(ID: <?= e($w['student_id']) ?>)</small></span>
            <input type="hidden" name="user_id" value="<?= $w['id'] ?>">
            <span>
                <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                <button name="action" value="reject" class="btn btn-sm btn-outline-danger"
                        data-confirm="Reject and delete this account?">Reject</button>
            </span>
        </form>
    <?php endforeach; ?>
</div>

<div class="card-box">
    <h5>Approved tutors</h5>
    <?php foreach ($approved as $a): ?>
        <div class="border-bottom py-1"><?= e($a['full_name']) ?> <small class="text-muted">(ID: <?= e($a['student_id']) ?>)</small></div>
    <?php endforeach; ?>
    <?php if (count($approved) == 0): ?>
        <p class="text-muted mb-0">No approved tutors yet.</p>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
