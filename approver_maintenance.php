<?php
    session_start();
    require 'db.php';

    if (!isset($_SESSION['user_id'])) {
        die("Access denied. Not logged in.");
    }

    // Get the user role
    $checkRole = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $checkRole->bind_param("i", $_SESSION['user_id']);
    $checkRole->execute();
    $result = $checkRole->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !isset($user['role'])) {
        die("Access denied. No role assigned.");
    }

    $userRole = strtolower(trim($user['role'])); // Normalize case and whitespace

    if ($userRole !== 'admin' && $userRole !== 'superadmin') {
        die("Unauthorized access. Your role is: " . htmlspecialchars($user['role']));
    }


    // Handle form submission
    if (isset($_POST['assign'])) {
        $employee_id = $_POST['employee_id'];
        $approver_id = $_POST['approver_id'];

        // Check if already assigned
        $check = $conn->prepare("SELECT id FROM approver_assignments WHERE employee_id = ?");
        $check->bind_param("i", $employee_id);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $update = $conn->prepare("UPDATE approver_assignments SET approver_id = ? WHERE employee_id = ?");
            $update->bind_param("ii", $approver_id, $employee_id);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO approver_assignments (employee_id, approver_id) VALUES (?, ?)");
            $insert->bind_param("ii", $employee_id, $approver_id);
            $insert->execute();
        }

        echo "<div class='alert alert-success'>Approver assigned successfully!</div>";
    }

    // Fetch employees and approvers
    $employees = $conn->query("SELECT u.id, u.name FROM users u WHERE u.role = 'Employee'");
    $approvers = $conn->query("SELECT u.id, u.name FROM users u WHERE u.role = 'Approver'");

    // Get current assignments
    $assignments = $conn->query("SELECT aa.id, e.name AS employee, a.name AS approver 
        FROM approver_assignments aa 
        JOIN users e ON aa.employee_id = e.id 
        JOIN users a ON aa.approver_id = a.id");
?>

<!DOCTYPE html>
    <html>
    <head>
        <title>Approver Maintenance</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-5">
        <h2>Assign Leave Approvers</h2>

        <form method="POST" class="row g-3 mt-3 mb-4">
            <div class="col-md-5">
                <label>Employee</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">Select Employee</option>
                    <?php while($emp = $employees->fetch_assoc()): ?>
                        <option value="<?= $emp['id'] ?>"><?= $emp['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label>Approver</label>
                <select name="approver_id" class="form-select" required>
                    <option value="">Select Approver</option>
                    <?php while($app = $approvers->fetch_assoc()): ?>
                        <option value="<?= $app['id'] ?>"><?= $app['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="assign" class="btn btn-primary w-100">Assign</button>
            </div>
        </form>

        <h4>Current Assignments</h4>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Approver</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while($row = $assignments->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $row['employee'] ?></td>
                    <td><?= $row['approver'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="view.php" class="btn btn-secondary mt-4">Back to Dashboard</a>
    </body>
</html>
