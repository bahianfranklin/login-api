<?php
    session_start();
    require 'db.php';

    $id = $_GET['id'] ?? null;
    if (!$id) {
        header("Location: view.php");
        exit;
    }

    // ✅ Fetch user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "User not found!";
        exit;
    }

    // ✅ Handle update form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = $_POST['name'];
        $address = $_POST['address'];
        $contact = $_POST['contact'];
        $birthday = $_POST['birthday'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $role = $_POST['role'];
        $status = $_POST['status'];

        // Keep old password if empty
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $user['password'];

        // Handle profile picture
        $profile_pic = $user['profile_pic'] ?: "default.png"; // fallback default
        if (!empty($_FILES['profile_pic']['name'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true); // create uploads/ if missing
            }

            $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
            $targetFilePath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
                $profile_pic = $fileName;
            }
        }

        $updateStmt = $conn->prepare("UPDATE users 
            SET name=?, address=?, contact=?, birthday=?, email=?, username=?, role=?, status=?, password=?, profile_pic=? 
            WHERE id=?");
        $updateStmt->bind_param("ssssssssssi", $name, $address, $contact, $birthday, $email, $username, $role, $status, $password, $profile_pic, $id);
        $updateStmt->execute();

        // ✅ Refresh session if editing logged-in user
        if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $id) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $_SESSION['user'] = $result->fetch_assoc();
        }

        header("Location: view.php?t=" . time());
        exit;

    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Edit User</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit User Information</h4>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                    
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" value="<?= htmlspecialchars($user['address']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" name="contact" value="<?= htmlspecialchars($user['contact']); ?>" class="form-control" required>
                        </div>

                        <!-- ✅ New Birthday Field -->
                        <div class="mb-3">
                            <label class="form-label">Birthday</label>
                            <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday']); ?>" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user['username']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password (leave blank to keep current)</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>>superadmin</option>
                                <option <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                                <option <?= $user['role'] === 'user' ? 'selected' : '' ?>>user</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option <?= $user['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option <?= $user['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="profile_pic" class="form-control" onchange="previewImage(event)">
                            <div class="mt-2">
                                <!-- ✅ Cache-busting + JS Preview -->
                                <img id="preview" 
                                    src="uploads/<?= htmlspecialchars($user['profile_pic'] ?: 'default.png'); ?>?t=<?= time(); ?>" 
                                    class="img-thumbnail" width="120">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Save Changes</button>
                        <a href="view.php" class="btn btn-secondary w-100 mt-2">Cancel</a>

                    </form>
                </div>
            </div>
        </div>

        <script>
        // ✅ Instant preview when selecting a new file
        function previewImage(event) {
            const output = document.getElementById('preview');
            output.src = URL.createObjectURL(event.target.files[0]);
        }
        </script>
    </body>
</html>
