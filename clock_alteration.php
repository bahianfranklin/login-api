<?php
    session_start();
    require 'db.php';

    // 🚫 Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("Please login first.");
    }

    $user_id = $_SESSION['user_id'];

    /** ========== ADD ========= */
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $date_original     = $_POST['date_original'];
        $time_in_original  = $_POST['time_in_original'] ?: null;
        $time_out_original = $_POST['time_out_original'] ?: null;
        $date_new          = $_POST['date_new'];
        $time_in_new       = $_POST['time_in_new'] ?: null;
        $time_out_new      = $_POST['time_out_new'] ?: null;
        $reason            = $_POST['reason'];

        // Generate application number (CA-yyyyMMdd-##)
        $today = date("Ymd");
        $res = $conn->query("SELECT COUNT(*) as total FROM clock_alteration WHERE DATE(datetime_applied)=CURDATE()");
        $row = $res->fetch_assoc();
        $countToday = $row['total'] + 1;
        $appNo = "CA-" . $today . "-" . str_pad($countToday, 2, "0", STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO clock_alteration 
            (application_no, date_original, time_in_original, time_out_original, date_new, time_in_new, time_out_new, reason, applied_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssi", $appNo, $date_original, $time_in_original, $time_out_original, 
                        $date_new, $time_in_new, $time_out_new, $reason, $user_id);
        $stmt->execute();
    }

    /** ========== EDIT ========= */
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id                = $_POST['id'];
        $date_original     = $_POST['date_original'];
        $time_in_original  = $_POST['time_in_original'] ?: null;
        $time_out_original = $_POST['time_out_original'] ?: null;
        $date_new          = $_POST['date_new'];
        $time_in_new       = $_POST['time_in_new'] ?: null;
        $time_out_new      = $_POST['time_out_new'] ?: null;
        $reason            = $_POST['reason'];
        $status            = $_POST['status'];

        $stmt = $conn->prepare("UPDATE clock_alteration 
            SET date_original=?, time_in_original=?, time_out_original=?, date_new=?, time_in_new=?, time_out_new=?, reason=?, status=?, datetime_updated=NOW() 
            WHERE id=?");
        $stmt->bind_param("ssssssssi", $date_original, $time_in_original, $time_out_original,
                        $date_new, $time_in_new, $time_out_new, $reason, $status, $id);
        $stmt->execute();
    }

    /** ========== DELETE ========= */
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM clock_alteration WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    /** ========== FETCH ========= */
    $sql = "SELECT ca.*, u.username 
            FROM clock_alteration ca 
            JOIN users u ON ca.applied_by = u.id 
            ORDER BY ca.datetime_applied DESC";
    $result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Clock In/Out Alteration Requests</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="d-flex justify-content-between mb-3">
                <h3>Clock In/Out Alteration Requests</h3>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ Apply Alteration</button>
            </div>

            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Application No</th>
                    <th>User</th>
                    <th>Original Date</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>New Date</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php $i=1; while($row=$result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row['application_no'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['date_original'] ?></td>
                        <td><?= $row['time_in_original'] ?></td>
                        <td><?= $row['time_out_original'] ?></td>
                        <td><?= $row['date_new'] ?></td>
                        <td><?= $row['time_in_new'] ?></td>
                        <td><?= $row['time_out_new'] ?></td>
                        <td><?= $row['reason'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Edit Clock Alteration</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label>Original Date</label>
                                        <input type="date" name="date_original" value="<?= $row['date_original'] ?>" class="form-control mb-2" required>
                                        <label>Original Time In</label>
                                        <input type="time" name="time_in_original" value="<?= $row['time_in_original'] ?>" class="form-control mb-2">
                                        <label>Original Time Out</label>
                                        <input type="time" name="time_out_original" value="<?= $row['time_out_original'] ?>" class="form-control mb-2">
                                        <label>New Date</label>
                                        <input type="date" name="date_new" value="<?= $row['date_new'] ?>" class="form-control mb-2" required>
                                        <label>New Time In</label>
                                        <input type="time" name="time_in_new" value="<?= $row['time_in_new'] ?>" class="form-control mb-2">
                                        <label>New Time Out</label>
                                        <input type="time" name="time_out_new" value="<?= $row['time_out_new'] ?>" class="form-control mb-2">
                                        <label>Reason</label>
                                        <textarea name="reason" class="form-control mb-2" required><?= $row['reason'] ?></textarea>
                                        <label>Status</label>
                                        <select name="status" class="form-select">
                                            <option <?= $row['status']=="Pending"?"selected":"" ?>>Pending</option>
                                            <option <?= $row['status']=="Approved"?"selected":"" ?>>Approved</option>
                                            <option <?= $row['status']=="Rejected"?"selected":"" ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Delete Clock Alteration</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure to delete <strong><?= $row['application_no'] ?></strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Modal -->
        <div class="modal fade" id="addModal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">Apply Clock Alteration</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label>Original Date</label>
                            <input type="date" name="date_original" class="form-control mb-2" required>
                            <label>Original Time In</label>
                            <input type="time" name="time_in_original" class="form-control mb-2">
                            <label>Original Time Out</label>
                            <input type="time" name="time_out_original" class="form-control mb-2">
                            <label>New Date</label>
                            <input type="date" name="date_new" class="form-control mb-2" required>
                            <label>New Time In</label>
                            <input type="time" name="time_in_new" class="form-control mb-2">
                            <label>New Time Out</label>
                            <input type="time" name="time_out_new" class="form-control mb-2">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control mb-2" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Submit</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <a href="view.php" class="btn btn-primary mt-3">BACK</a> 
    <a href="official_business.php" class="btn btn-danger mt-3">OFFICAL BUSINESS</a>
    <a href="change_schedule.php" class="btn btn-danger mt-3">CHANGE SCHEDULE</a>
    <a href="failure_clock.php" class="btn btn-danger mt-3">FAILURE CLOCK</a>
    <a href="clock_alteration.php" class="btn btn-danger mt-3">CLOCK ALTERATION</a>
    <a href="work_restday.php" class="btn btn-danger mt-3">WORK RESTDAY</a>
    <a href="overtime.php" class="btn btn-danger mt-3">OVERTIME</a>
    </body>
</html>
