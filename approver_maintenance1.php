<?php
    require 'db.php';

    // ✅ Handle insert
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "insert") {
        $work_detail_id = $_POST['work_detail_id'] ?? null;
        $department_id  = $_POST['department_id'] ?? null;

        if ($work_detail_id && $department_id) {
            // 🔹 Check if department already has an approver
            $check = $conn->prepare("SELECT COUNT(*) FROM approver_assignments WHERE department_id = ?");
            $check->bind_param("i", $department_id);
            $check->execute();
            $check->bind_result($count);
            $check->fetch();
            $check->close();

            if ($count > 0) {
                // 🚫 Already has an approver
                header("Location: approver_maintenance1.php?error=department_exists");
                exit();
            }

            // 🔹 Get user_id from work_details
            $stmtUser = $conn->prepare("SELECT user_id FROM work_details WHERE work_detail_id = ?");
            $stmtUser->bind_param("i", $work_detail_id);
            $stmtUser->execute();
            $stmtUser->bind_result($user_id);
            $stmtUser->fetch();
            $stmtUser->close();

            if ($user_id) {
                $stmt = $conn->prepare("INSERT INTO approver_assignments (user_id, work_detail_id, department_id) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $user_id, $work_detail_id, $department_id);
                $stmt->execute();
            }
        }
        header("Location: approver_maintenance1.php?success=1");
        exit();
    }

    // ✅ Handle update
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "update") {
        $id             = $_POST['id'];
        $work_detail_id = $_POST['work_detail_id'];
        $department_id  = $_POST['department_id'];

        // 🔹 Get user_id from work_details
        $stmtUser = $conn->prepare("SELECT user_id FROM work_details WHERE work_detail_id = ?");
        $stmtUser->bind_param("i", $work_detail_id);
        $stmtUser->execute();
        $stmtUser->bind_result($user_id);
        $stmtUser->fetch();
        $stmtUser->close();

        if ($user_id) {
            $stmt = $conn->prepare("UPDATE approver_assignments SET user_id=?, work_detail_id=?, department_id=? WHERE id=?");
            $stmt->bind_param("iiii", $user_id, $work_detail_id, $department_id, $id);
            $stmt->execute();
        }

        header("Location: approver_maintenance1.php?updated=1");
        exit();
    }

    // ✅ Handle delete
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "delete") {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM approver_assignments WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: approver_maintenance1.php?deleted=1");
        exit();
    }

    // 🔹 Count departments WITH approvers
    $sqlWith = "SELECT COUNT(DISTINCT department_id) AS total_with FROM approver_assignments";
    $resWith = $conn->query($sqlWith);
    $rowWith = $resWith->fetch_assoc();
    $total_with = $rowWith['total_with'];

    // 🔹 Count ALL departments
    $sqlDept = "SELECT COUNT(*) AS total_depts FROM departments";
    $resDept = $conn->query($sqlDept);
    $rowDept = $resDept->fetch_assoc();
    $total_depts = $rowDept['total_depts'];

    // 🔹 Departments WITHOUT approvers
    $total_without = $total_depts - $total_with;

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Approver Maintenance</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-5">

        <div class="container mt-4">
            <h3>Approver Assignment</h3>
            <br>
            <form method="POST" action="">
                <input type="hidden" name="action" value="insert">

                <!--Display departments with approver and do not have -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card text-bg-success">
                            <div class="card-body">
                                <h6 class="card-title">Departments with Approvers</h6>
                                <p class="card-text fs-3"><?php echo $total_with; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-bg-danger">
                            <div class="card-body">
                                <h6 class="card-title">Departments without Approvers</h6>
                                <p class="card-text fs-3"><?php echo $total_without; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Display messages -->
                <?php if (isset($_GET['error']) && $_GET['error'] === "department_exists"): ?>
                    <div class="alert alert-danger">❌ This department already has an approver assigned.</div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">✅ Approver assigned successfully.</div>
                <?php endif; ?>
                <div class="card mb-3 p-3">
                <!-- Employee Dropdown -->
                <div class="col-md-4 mb-3">
                    <label for="employee">Select Employee</label>
                    <select class="form-control w-10" name="work_detail_id" required>
                        <option value="">-- Select Employee --</option>
                        <?php
                            $employees = $conn->query("
                                SELECT w.work_detail_id, w.employee_no, w.position, u.name
                                FROM work_details w
                                JOIN users u ON w.user_id = u.id order by u.name
                            ");
                            while($e = $employees->fetch_assoc()) {
                                echo "<option value='{$e['work_detail_id']}'>{$e['name']} ({$e['employee_no']} - {$e['position']})</option>";
                            }
                        ?>
                    </select>
                </div>

                <!-- Department Dropdown -->
                <div class="col-md-4 mb-3">
                    <label for="department">Select Department</label>
                    <select class="form-control w-100" name="department_id" required>
                        <option value="">-- Select Department --</option>
                        <?php
                            $depts = $conn->query("SELECT id, department FROM departments order by department");
                            while($d = $depts->fetch_assoc()) {
                                echo "<option value='{$d['id']}'>{$d['department']}</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
                </div>
            </form>
        </div>

        <!-- Assignment Table -->
        <div class="mt-5">
            <h4>Assigned Employees</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>User ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $sql = "SELECT a.id, w.employee_no, u.name AS employee_name, d.department, a.user_id, w.work_detail_id, d.id AS department_id
                            FROM approver_assignments a
                            JOIN work_details w ON a.work_detail_id = w.work_detail_id
                            JOIN users u ON w.user_id = u.id
                            JOIN departments d ON a.department_id = d.id order by d.department";
                    $result = $conn->query($sql);
                    while($row = $result->fetch_assoc()) {
                        echo "
                        <tr>
                            <td>{$row['id']}</td>
                            <td>{$row['employee_name']} ({$row['employee_no']})</td>
                            <td>{$row['department']}</td>
                            <td>{$row['user_id']}</td>
                            <td>
                                <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#editModal{$row['id']}'>Edit</button>
                                <button class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#deleteModal{$row['id']}'>Delete</button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class='modal fade' id='editModal{$row['id']}' tabindex='-1'>
                            <div class='modal-dialog'>
                                <form method='POST' action=''>
                                    <input type='hidden' name='action' value='update'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <div class='modal-content'>
                                        <div class='modal-header'><h5>Edit Assignment</h5></div>
                                        <div class='modal-body'>
                                            <div class='mb-3'>
                                                <label>Select Employee</label>
                                                <select class='form-control' name='work_detail_id' required>";
                                                    $empRes = $conn->query("
                                                        SELECT w.work_detail_id, w.employee_no, w.position, u.name
                                                        FROM work_details w
                                                        JOIN users u ON w.user_id = u.id
                                                    ");
                                                    while($e = $empRes->fetch_assoc()) {
                                                        $selected = ($row['work_detail_id'] == $e['work_detail_id']) ? "selected" : "";
                                                        echo "<option value='{$e['work_detail_id']}' $selected>{$e['name']} ({$e['employee_no']} - {$e['position']})</option>";
                                                    }
                                                echo "</select>
                                            </div>
                                            <div class='mb-3'>
                                                <label>Select Department</label>
                                                <select class='form-control' name='department_id' required>";
                                                    $deptRes = $conn->query("SELECT id, department FROM departments");
                                                    while($d = $deptRes->fetch_assoc()) {
                                                        $selected = ($row['department_id'] == $d['id']) ? "selected" : "";
                                                        echo "<option value='{$d['id']}' $selected>{$d['department']}</option>";
                                                    }
                                                echo "</select>
                                            </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='submit' class='btn btn-success'>Save</button>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class='modal fade' id='deleteModal{$row['id']}' tabindex='-1'>
                            <div class='modal-dialog'>
                                <form method='POST' action=''>
                                    <input type='hidden' name='action' value='delete'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <div class='modal-content'>
                                        <div class='modal-header'><h5>Confirm Delete</h5></div>
                                        <div class='modal-body'>
                                            <p>Are you sure you want to delete this assignment?</p>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='submit' class='btn btn-danger'>Delete</button>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        ";
                    }
                ?>
                </tbody>
            </table>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <a href="view.php" class="btn btn-primary">BACK</a>
    </body>
</html>
