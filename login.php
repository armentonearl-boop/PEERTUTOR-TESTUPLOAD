<?php
require "config.php";
require "logger.php";

$error = "";

if (isset($_GET['msg']) && $_GET['msg'] == 'timeout') {
    $error = "You were logged out because you were inactive for 15 minutes.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']);
    $password   = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $error = "Wrong student ID or password.";
    } elseif ($user['approved'] == 0) {
        $error = "Your tutor account is still waiting for the teacher's approval.";
    } else {
        // login ok - remember who this is
        session_regenerate_id(true);
        $_SESSION['user_id']     = $user['id'];
        $_SESSION['full_name']   = $user['full_name'];
        $_SESSION['role']        = $user['role'];
        $_SESSION['last_active'] = time();
        log_activity($db, $user['id'], 'login', 'Logged in');
        header("Location: dashboard.php");
        exit;
    }
}

include "header.php";
?>

<div class="card-box login-box">
    <img src="images/logo.png" alt="PeerTutor logo" class="auth-logo">
    <h3 class="mb-3 text-center">Log in</h3>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Student ID</label>
            <input type="text" name="student_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Log in</button>
    </form>

    <p class="mt-3 mb-0">No account yet? <a href="register.php">Register here</a></p>
</div>

<?php include "footer.php"; ?>
