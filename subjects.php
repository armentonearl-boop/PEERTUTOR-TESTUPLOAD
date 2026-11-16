<?php
require "config.php";
require "logger.php";
require_login('admin');

$message = "";
$me = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $name   = trim($_POST['name'] ?? '');

    try {
        // CREATE
        if ($action == 'add' && $name != "") {
            $stmt = $db->prepare("INSERT INTO subjects (name) VALUES (?)");
            $stmt->execute([$name]);
            log_activity($db, $me, 'add_subject', "Added subject: $name");
            $message = "Subject added.";
        }

        // UPDATE
        if ($action == 'update' && $name != "") {
            $stmt = $db->prepare("UPDATE subjects SET name = ? WHERE id = ?");
            $stmt->execute([$name, $_POST['id']]);
            log_activity($db, $me, 'edit_subject', "Renamed subject #" . $_POST['id'] . " to: $name");
            $message = "Subject updated.";
        }

        // DELETE
        if ($action == 'delete') {
            $stmt = $db->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            log_activity($db, $me, 'delete_subject', "Deleted subject #" . $_POST['id']);
            $message = "Subject deleted.";
        }
    } catch (PDOException $ex) {
        // the name column is UNIQUE, so a duplicate name ends up here
        $message = "That subject name already exists.";
    }
}

// READ
$subjects = $db->query("SELECT * FROM subjects ORDER BY name")->fetchAll();

include "header.php";
?>

<h3>Subjects</h3>

<?php if ($message != ""): ?>
    <div class="alert alert-info"><?= e($message) ?></div>
<?php endif; ?>

<div class="card-box">
    <form method="post" class="row g-2 mb-3">
        <input type="hidden" name="action" value="add">
        <div class="col-sm-9"><input type="text" name="name" class="form-control" placeholder="New subject name" maxlength="80" required></div>
        <div class="col-sm-3"><button class="btn btn-primary w-100">Add subject</button></div>
    </form>

    <!-- each row is its own little form: change the name and press Save, or press Delete -->
    <?php foreach ($subjects as $s): ?>
        <form method="post" class="row g-2 border-bottom py-2">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <div class="col-sm-7">
                <input type="text" name="name" value="<?= e($s['name']) ?>" class="form-control" maxlength="80" required>
            </div>
            <div class="col-sm-5">
                <button name="action" value="update" class="btn btn-sm btn-primary">Save</button>
                <button name="action" value="delete" class="btn btn-sm btn-outline-danger"
                        data-confirm="Delete this subject? Sessions and tutor entries for it will be deleted too.">Delete</button>
            </div>
        </form>
    <?php endforeach; ?>
</div>

<?php include "footer.php"; ?>
