<?php
    ob_start();                // ✅ Start output buffering
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require 'db.php';

    if (headers_sent($file, $line)) {
    die("Headers already sent in $file on line $line");
    }

    // Search functionality
    $search = "";
    if (isset($_GET['leave_search'])) {
        $search = $_GET['leave_search'];
        // $query = "SELECT u.id, u.name, u.username, lc.mandatory, lc.vacation_leave, lc.sick_leave 
        //         FROM users u 
        //         LEFT JOIN leave_credits lc ON u.id = lc.user_id
        //         WHERE u.name LIKE ? OR u.username LIKE ? order by u.name";

        $query = "SELECT u.id, u.name, u.username, lc.mandatory, lc.vacation_leave, lc.sick_leave 
                FROM users u
                INNER JOIN work_details wd ON u.id = wd.user_id
                LEFT JOIN leave_credits lc ON u.id = lc.user_id
                WHERE (u.name LIKE ? OR u.username LIKE ?)
                AND wd.status_desc = 'Regular'
                ORDER BY u.name";

        $stmt = $conn->prepare($query);
        $like = "%$search%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // $result = $conn->query("SELECT u.id, u.name, u.username, lc.mandatory, lc.vacation_leave, lc.sick_leave 
        //                         FROM users u 
        //                         LEFT JOIN leave_credits lc ON u.id = lc.user_id order by u.name");
        $result = $conn->query("SELECT u.id, u.name, u.username, lc.mandatory, lc.vacation_leave, lc.sick_leave 
                        FROM users u
                        INNER JOIN work_details wd ON u.id = wd.user_id
                        LEFT JOIN leave_credits lc ON u.id = lc.user_id
                        WHERE wd.status_desc = 'Regular'
                        ORDER BY u.name");
    }

    // ✅ Handle Update
    if (isset($_POST['update'])) {
        $id = $_POST['user_id'];
        $mandatory = $_POST['mandatory'];
        $vacation = $_POST['vacation_leave'];
        $sick = $_POST['sick_leave'];

        // Check if row exists
        $check = $conn->prepare("SELECT id FROM leave_credits WHERE user_id=?");
        $check->bind_param("i", $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // ✅ Update existing row
            $stmt = $conn->prepare("UPDATE leave_credits 
                                    SET mandatory=?, vacation_leave=?, sick_leave=? 
                                    WHERE user_id=?");
            $stmt->bind_param("iiii", $mandatory, $vacation, $sick, $id);
            $stmt->execute();
        } else {
            // ✅ Insert if not exists
            $stmt = $conn->prepare("INSERT INTO leave_credits 
                                    (user_id, mandatory, vacation_leave, sick_leave) 
                                    VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $id, $mandatory, $vacation, $sick);
            $stmt->execute();
        }
            header("Location: user_maintenance.php");
            exit;
    }

    // ✅ Handle Delete
    if (isset($_POST['delete'])) {
        $id = $_POST['delete_user_id'];

        $stmt = $conn->prepare("DELETE FROM leave_credits WHERE user_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: leave_credit.php");
        exit;

    }
    ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Leave Credits</title>
        <!-- ✅ Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- ✅ FontAwesome Icons -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="container mt-4">

        <div class="container mt-2">
            <h5 class="mb-4">Leave Credits Management</h5>

            <!-- Search Form -->
            <form method="get" class="row g-2 mb-3">
                <div class="col-md-8">
                    <input type="text" name="leave_search" class="form-control" placeholder="Search name or username" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"> Search</button>
                </div>
                <div class="col-md-2">
                    <a href="user_maintenance.php" class="btn btn-secondary w-100"> Reset</a>
                </div>
            </form>

            <!-- Leave Credits Table -->
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Mandatory</th>
                                <th>Vacation</th>
                                <th>Sick</th>
                                <th style="width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= $row['mandatory'] ?? 0 ?></td>
                                <td><?= $row['vacation_leave'] ?? 0 ?></td>
                                <td><?= $row['sick_leave'] ?? 0 ?></td>
                                <td>
                                    <!-- ✅ Edit Button (opens modal instead of redirect) -->
                                    <button type="button" class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal"
                                        data-id="<?= $row['id'] ?>"
                                        data-mandatory="<?= $row['mandatory'] ?? 0 ?>"
                                        data-vacation="<?= $row['vacation_leave'] ?? 0 ?>"
                                        data-sick="<?= $row['sick_leave'] ?? 0 ?>">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>

                                    <!-- ✅ Delete Button (opens modal instead of redirect) -->
                                    <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-id="<?= $row['id'] ?>"
                                        data-name="<?= htmlspecialchars($row['name']) ?>">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-edit"></i> Edit Leave Credits</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                <input type="hidden" name="user_id" id="modalUserId">

                <div class="mb-3">
                    <label class="form-label">Mandatory Leave</label>
                    <input type="number" class="form-control" name="mandatory" id="modalMandatory" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Vacation Leave</label>
                    <input type="number" class="form-control" name="vacation_leave" id="modalVacation" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sick Leave</label>
                    <input type="number" class="form-control" name="sick_leave" id="modalSick" required>
                </div>
                </div>
                <div class="modal-footer">
                <button type="submit" name="update" class="btn btn-success">
                    <i class="fa fa-save"></i> Save Changes
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
            </div>
        </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <form method="post">
                <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa fa-trash"></i> Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                <p>Are you sure you want to delete leave credits for <strong id="deleteUserName"></strong>?</p>
                <input type="hidden" name="delete_user_id" id="deleteUserId">
                </div>
                <div class="modal-footer">
                <button type="submit" name="delete" class="btn btn-danger">
                    <i class="fa fa-trash"></i> Yes, Delete
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
            </div>
        </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        // Fill Edit Modal
        var editModal = document.getElementById('editModal')
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            document.getElementById('modalUserId').value = button.getAttribute('data-id')
            document.getElementById('modalMandatory').value = button.getAttribute('data-mandatory')
            document.getElementById('modalVacation').value = button.getAttribute('data-vacation')
            document.getElementById('modalSick').value = button.getAttribute('data-sick')
        })

        // Fill Delete Modal
        var deleteModal = document.getElementById('deleteModal')
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            document.getElementById('deleteUserId').value = button.getAttribute('data-id')
            document.getElementById('deleteUserName').textContent = button.getAttribute('data-name')
        })
        </script>

    </body>
</html>
