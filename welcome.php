<?php
    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    $user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Welcome</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="card p-4">
                <h2 class="mb-3">Welcome, <?= htmlspecialchars($user['name']); ?> 👋</h2>
                <p><strong>Address:</strong> <?= htmlspecialchars($user['address']); ?></p>
                <p><strong>Contact:</strong> <?= htmlspecialchars($user['contact']); ?></p>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></p>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </body>
</html>


