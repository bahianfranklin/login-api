<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");

    // $stmt = $conn->prepare("
    // SELECT id, name, address, contact, birthday, email, username, role, status, profile_pic 
    // FROM users 
    // WHERE username = ?
    // ");

    // $stmt->bind_param("s", $username);
    // $stmt->execute();

    // $result = $stmt->get_result();
    // $user = $result->fetch_assoc();

    $stmt = $conn->prepare("
    SELECT id, name, address, contact, birthday, email, username, role, status, profile_pic, password
    FROM users 
    WHERE username = ?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && is_array($user)) {
        // ✅ Check if account is inactive
        if ($user['status'] === 'inactive') {
            $error = "Your account is inactive. Please contact admin.";
        } elseif (password_verify($password, $user['password'])) {
            // ✅ Save user info to session
            $_SESSION['user'] = $user;
            $_SESSION['user_id'] = $user['id'];

            // ✅ Insert login history
            $ip = $_SERVER['REMOTE_ADDR'];
            $stmtLog = $conn->prepare("INSERT INTO user_logs (user_id, login_time, ip_address) VALUES (?, NOW(), ?)");
            $stmtLog->bind_param("is", $user['id'], $ip);
            $stmtLog->execute();

            $_SESSION['log_id'] = $conn->insert_id;

            // Redirect to dashboard
            header("Location: view.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } else {
        $error = "Invalid username or password!";
    }
}
?> 
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
        <!-- Custom CSS -->
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="card p-4 shadow-lg rounded" style="max-width: 400px; width:100%;">
                <h4 class="text-center mb-3">Welcome, Please Login your Account</h4>

                <!-- Success message -->
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success']); ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Error message -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="post">
                    <div class="mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <p class="mt-3 text-center">
                    Don’t have an account? <a href="register.php">Register</a>
                </p>
            </div>
        </div>

        <!-- JS for toggle password -->
        <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icon
            if (type === 'password') {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        });
        </script>
    </body>
</html>
