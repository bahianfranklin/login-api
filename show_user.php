<?php
    session_start();
    require 'db.php';

    // ✅ Only allow logged in users
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    // ✅ Get user ID from URL
    $id = $_GET['id'] ?? null;
    if (!$id) {
        die("Invalid request. User ID is required.");
    }

    // ✅ Fetch user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        die("User not found.");
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Show User</title>
        <!-- ✅ Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- ✅ FontAwesome Icons -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="container mt-4">

        <div class="card shadow-lg">
            <!-- <div class="card-header bg-dark text-white"> -->
                <!-- <h3 class="mb-0"><i class="fa fa-user"></i> User Details</h3> -->
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">User Details</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="uploads/<?= $user['profile_pic'] ?: 'default.png' ?>" 
                        class="rounded-circle border shadow-sm" 
                        width="120" height="120">
                </div>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Name</th>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer text-end">
                <a href="users.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <!-- <a href="edit_user1.php?id=<?= $user['id'] ?>" class="btn btn-primary">
                    <i class="fa fa-pen"></i> Edit
                </a>
                <a href="delete_user.php?id=<?= $user['id'] ?>" 
                    onclick="return confirm('Are you sure you want to delete this user?\n\nName: <?= $user['name'] ?>\nEmail: <?= $user['email'] ?>\nUsername: <?= $user['username'] ?>');" 
                    class="btn btn-danger">
                    <i class="fa fa-trash"></i> Delete
                </a> -->
            </div>
        </div>

        <!-- ✅ Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
