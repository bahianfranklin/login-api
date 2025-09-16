<?php
    session_start();
    require 'db.php';

    if (!isset($_SESSION['user_id'])) {
        die("Please login first.");
    }

    $user_id = $_SESSION['user_id'];

    /** ========== ADD ========= */
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $ob_date = $_POST['ob_date'];
        $from_time = $_POST['from_time'];
        $to_time = $_POST['to_time'];
        $purpose = $_POST['purpose'];
        $location = $_POST['location'];

        $today = date("Ymd");
        $res = $conn->query("SELECT COUNT(*) as total FROM official_business WHERE DATE(datetime_applied)=CURDATE()");
        $row = $res->fetch_assoc();
        $countToday = $row['total'] + 1;
        $appNo = "OB-" . $today . "-" . str_pad($countToday, 2, "0", STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO official_business (application_no, ob_date, from_time, to_time, purpose, location, applied_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $appNo, $ob_date, $from_time, $to_time, $purpose, $location, $user_id);
        $stmt->execute();
    }

    /** ========== EDIT ========= */
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = $_POST['id'];
        $ob_date = $_POST['ob_date'];
        $from_time = $_POST['from_time'];
        $to_time = $_POST['to_time'];
        $purpose = $_POST['purpose'];
        $location = $_POST['location'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE official_business SET ob_date=?, from_time=?, to_time=?, purpose=?, location=?, status=?, datetime_updated=NOW() WHERE id=?");
        $stmt->bind_param("ssssssi", $ob_date, $from_time, $to_time, $purpose, $location, $status, $id);
        $stmt->execute();
    }

    /** ========== DELETE ========= */
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM official_business WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    /** ========== FETCH ========= */
    $sql = "SELECT ob.*, u.username FROM official_business ob JOIN users u ON ob.applied_by=u.id ORDER BY ob.datetime_applied DESC";
    $result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
        <title>Official Business Requests</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="d-flex justify-content-between mb-3">
                <h3>Official Business Requests</h3>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ Apply Official Business</button>
            </div>

            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Application No</th>
                        <th>User</th>
                        <th>OB Date</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Purpose</th>
                        <th>Location</th>
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
                        <td><?= $row['ob_date'] ?></td>
                        <td><?= $row['from_time'] ?></td>
                        <td><?= $row['to_time'] ?></td>
                        <td><?= $row['purpose'] ?></td>
                        <td><?= $row['location'] ?></td>
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
                                        <h5 class="modal-title">Edit Official Business</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label>OB Date</label>
                                        <input type="date" name="ob_date" value="<?= $row['ob_date'] ?>" class="form-control mb-2" required>
                                        <label>From Time</label>
                                        <input type="time" name="from_time" value="<?= $row['from_time'] ?>" class="form-control mb-2" required>
                                        <label>To Time</label>
                                        <input type="time" name="to_time" value="<?= $row['to_time'] ?>" class="form-control mb-2" required>
                                        <label>Purpose</label>
                                        <textarea name="purpose" class="form-control mb-2"><?= $row['purpose'] ?></textarea>
                                        <label>Location</label>
                                        <input type="text" name="location" value="<?= $row['location'] ?>" class="form-control mb-2" required>
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
                                        <h5 class="modal-title">Delete Official Business</h5>
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
                            <h5 class="modal-title">Apply Official Business</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label>OB Date</label>
                            <input type="date" name="ob_date" class="form-control mb-2" required>
                            <label>From Time</label>
                            <input type="time" name="from_time" class="form-control mb-2" required>
                            <label>To Time</label>
                            <input type="time" name="to_time" class="form-control mb-2" required>
                            <label>Purpose</label>
                            <textarea name="purpose" class="form-control mb-2" required></textarea>
                            <label>Location</label>
                            <input type="text" name="location" class="form-control mb-2" required>
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
