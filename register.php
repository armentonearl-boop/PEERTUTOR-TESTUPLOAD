<?php
require "config.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']);
    $full_name  = trim($_POST['full_name']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm'];
    $role       = $_POST['role'];

    // only these two roles can be chosen here, admin is made by hand in the database
    if ($role != 'tutor' && $role != 'tutee') {
        $role = 'tutee';
    }

    if ($student_id == "" || $full_name == "" || $password == "") {
        $error = "Please fill in all the boxes.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password != $confirm) {
        $error = "The two passwords are not the same.";
    } else {
        // is the student ID already used?
        $check = $db->prepare("SELECT id FROM users WHERE student_id = ?");
        $check->execute([$student_id]);

        if ($check->fetch()) {
            $error = "That student ID is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);   // never save the real password
            $approved = ($role == 'tutor') ? 0 : 1;                  // tutors wait for the teacher

            $insert = $db->prepare("INSERT INTO users (student_id, full_name, password, role, approved) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$student_id, $full_name, $hashed, $role, $approved]);

            if ($role == 'tutor') {
                $success = "Account created! Please wait for the teacher to approve it before you log in.";
            } else {
                $success = "Account created! You can log in now.";
            }
        }
    }
}

include "header.php";
?>

<div class="card-box login-box">
    <img src="images/logo.png" alt="PeerTutor logo" class="auth-logo">
    <h3 class="mb-3 text-center">Create an account</h3>
    <p class="text-muted text-center">Incomplete tutor details are automatically declined.</p>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success != ""): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Student ID</label>
            <input type="text" name="student_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Full name</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">I want to be a...</label>
            <select name="role" class="form-select">
                <option value="tutee">Tutee (I need help)</option>
                <option value="tutor">Tutor (I want to help)</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm password</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>

    <p class="mt-3 mb-0">Already have an account? <a href="login.php">Log in</a></p>
</div>

<?php include "footer.php"; ?>
