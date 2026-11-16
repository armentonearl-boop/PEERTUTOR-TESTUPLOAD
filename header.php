<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PeerTutor</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <!-- Bootstrap is only used for the grid and the buttons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-md navbar-dark topbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <span class="logo-badge"><img src="images/logo.png" alt="PeerTutor logo"></span>
            PeerTutor
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Home</a></li>

                    <?php if ($_SESSION['role'] == 'tutee'): ?>
                        <li class="nav-item"><a class="nav-link" href="find_tutors.php">Find a Tutor</a></li>
                        <li class="nav-item"><a class="nav-link" href="my_sessions.php">My Sessions</a></li>
                    <?php elseif ($_SESSION['role'] == 'tutor'): ?>
                        <li class="nav-item"><a class="nav-link" href="tutor_setup.php">My Subjects &amp; Time</a></li>
                        <li class="nav-item"><a class="nav-link" href="requests.php">Requests</a></li>
                    <?php elseif ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="approve_tutors.php">Approve Tutors</a></li>
                        <li class="nav-item"><a class="nav-link" href="subjects.php">Subjects</a></li>
                        <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text me-3"><?= e($_SESSION['full_name']) ?> (<?= e($_SESSION['role']) ?>)</span>
                <a class="btn btn-sm btn-light" href="logout.php">Log out</a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<div class="container main-box">
