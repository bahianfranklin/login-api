<?php
    session_start();
    require 'db.php';

    $errors = [];
    $success = "";

    // ✅ Handle form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name     = trim($_POST['name']);
        $address  = trim($_POST['address']);
        $contact  = trim($_POST['contact']);
        $birthday = $_POST['birthday'];
        $email    = trim($_POST['email']);
        $username = trim($_POST['username']);
        $role     = $_POST['role'];
        $status   = $_POST['status'];
        $password = trim($_POST['password']);
        $profile_pic = null;

        // ✅ Validation
        if (empty($name))     $errors[] = "Name is required.";
        if (empty($address))  $errors[] = "Address is required.";
        if (empty($contact))  $errors[] = "Contact is required.";
        if (empty($birthday))  $errors[] = "Birthdate is required.";
        if (empty($username)) $errors[] = "Username is required.";
        if (empty($password)) $errors[] = "Password is required.";

        // ✅ Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Username already exists.";
        $stmt->close();

        // ✅ Check if email already exists (if provided)
        if (!empty($email)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) $errors[] = "Email already exists.";
            $stmt->close();
        }

        // ✅ Handle profile picture upload
        if (!empty($_FILES['profile_pic']['name'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $fileName = time() . "_" . basename($_FILES["profile_pic"]["name"]);
            $targetFilePath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
                $profile_pic = $fileName;
            } else {
                $errors[] = "Failed to upload profile picture.";
            }
        }

        // ✅ If no errors, insert into database
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO users (name, address, contact, birthday, email, username, role, status, password, profile_pic) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $name, $address, $contact, $birthday, $email, $username, $role, $status, $hashedPassword, $profile_pic);

            if ($stmt->execute()) {
                $success = "User added successfully!";
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Add User</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Add New User</h4>
                </div>
                <div class="card-body">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address *</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact *</label>
                            <input type="text" name="contact" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Birthday</label>
                            <input type="date" name="birthday" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">-- Select Role --</option>
                                <option value="superadmin">Superadmin</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="profile_pic" class="form-control" onchange="previewImage(event)">
                            <div class="mt-2">
                                <!-- ✅ Preview uploaded image -->
                                <img id="preview" src="uploads/default.png" class="img-thumbnail" width="120">
                            </div>
                        </div>

                        <!-- ✅ Buttons at bottom -->
                        <button type="submit" class="btn btn-success w-100">Add User</button>
                        <a href="users.php" class="btn btn-secondary w-100 mt-2">Back</a>

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
