<?php
    session_start();
    require 'db.php';

    if (!isset($_SESSION['user_id'])) {
        die("Please login first.");
    }

    // Handle Add Leave Request
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $user_id = $_SESSION['user_id'];
        $leave_type = $_POST['leave_type'];
        $type = $_POST['type'];
        $credit_value = $_POST['credit_value'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        $remarks = $_POST['remarks'];

        $today = date("Ymd");
        $sql = "SELECT COUNT(*) as total FROM leave_requests WHERE DATE(date_applied) = CURDATE()";
        $res = $conn->query($sql);
        $row = $res->fetch_assoc();
        $countToday = $row['total'] + 1;
        $appNo = "L-" . $today . "-" . str_pad($countToday, 2, "0", STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO leave_requests 
            (application_no, user_id, leave_type, type, credit_value, date_from, date_to, remarks) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissdsss", $appNo, $user_id, $leave_type, $type, $credit_value, $date_from, $date_to, $remarks);
        $stmt->execute();
    }

    // Handle Update
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = $_POST['id'];
        $leave_type = $_POST['leave_type'];
        $type = $_POST['type'];
        $credit_value = $_POST['credit_value'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        $remarks = $_POST['remarks'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE leave_requests SET leave_type=?, type=?, credit_value=?, date_from=?, date_to=?, remarks=?, status=?, date_updated=NOW() WHERE id=?");
        $stmt->bind_param("ssdssssi", $leave_type, $type, $credit_value, $date_from, $date_to, $remarks, $status, $id);
        $stmt->execute();
    }

    // Handle Delete
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM leave_requests WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    // Fetch all leave requests
    $result = $conn->query("SELECT lr.*, u.username FROM leave_requests lr JOIN users u ON lr.user_id = u.id ORDER BY lr.date_applied DESC");

    // Filtering logic
    
    $where = [];
    if(!empty($_GET['date_range'])){
        $dates = explode(" to ", $_GET['date_range']);
    if(count($dates) == 2 && !empty($dates[0]) && !empty($dates[1])){
        $from = $conn->real_escape_string($dates[0]);
        $to = $conn->real_escape_string($dates[1]);
        $where[] = "lr.date_from >= '$from' AND lr.date_to <= '$to'";
    }
    }


    if(!empty($_GET['leave_type'])){
        $leave_type = $conn->real_escape_string($_GET['leave_type']);
        $where[] = "lr.leave_type = '$leave_type'";
    }

    if(!empty($_GET['status'])){
        $status = $conn->real_escape_string($_GET['status']);
        $where[] = "lr.status = '$status'";
    }

    $whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT lr.*, u.username 
            FROM leave_requests lr 
            JOIN users u ON lr.user_id = u.id
            $whereSQL
            ORDER BY lr.date_applied DESC";

    $result = $conn->query($sql);

    if (!$result) {
        die("Query Failed: " . $conn->error . " -- SQL: " . $sql);
    }

?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Leave Requests Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    </head>
    <body class="container mt-4">
    
        		<!-- ✅ NAVIGATION BAR -->
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container-fluid">

                    <!-- Toggle button for mobile -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Navbar Links -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                        <a class="nav-link" href="view.php"><i class="fa fa-home"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="log_history.php"><i class="fa fa-clock"></i> Log History</a>
                        </li>

                        <!-- ✅ Application Dropdown -->
                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="applicationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-file"></i> Application
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="applicationDropdown">
                            <li><a class="dropdown-item" href="leave_application.php"><i class="fa fa-plane"></i> Leave Application</a></li>
                            <li><a class="dropdown-item" href="overtime.php"><i class="fa fa-clock"></i> Overtime</a></li>
                            <li><a class="dropdown-item" href="official_business.php"><i class="fa fa-briefcase"></i> Official Business</a></li>
                            <li><a class="dropdown-item" href="change_schedule.php"><i class="fa fa-calendar-check"></i> Change Schedule</a></li>
                            <li><a class="dropdown-item" href="failure_clock.php"><i class="fa fa-exclamation-triangle"></i> Failure to Clock</a></li>
                            <li><a class="dropdown-item" href="clock_alteration.php"><i class="fa fa-edit"></i> Clock Alteration</a></li>
                            <li><a class="dropdown-item" href="work_restday.php"><i class="fa fa-sun"></i> Work Rest Day</a></li>
                        </ul>
                        </li>
                        <!-- ✅ End Application Dropdown -->

                        <li class="nav-item">
                        <a class="nav-link" href="pending_leaves.php"><i class="fa fa-circle-check"></i> For Approving</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="user_maintenance.php"><i class="fa fa-users"></i> Users Info</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="directory.php"><i class="fa fa-building"></i> Directory</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="contact_details.php"><i class="fas fa-address-book"></i> Contact Details</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="calendar1.php"><i class="fa fa-calendar"></i> Calendar</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="maintenance.php"><i class="fa fa-cogs"></i> Maintenance</a>
                        </li>
                    </ul>
                    </div>
                </div>
                </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-3">
            <h3>Leave Requests</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fa-solid fa-plus"></i> Apply Leave</button>
        </div>

        <div class="card mb-3 p-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Date Coverage</label>
                    <input type="text" class="form-control dateRangePicker" name="date_range" required>
                    <input type="hidden" name="date_from">
                    <input type="hidden" name="date_to">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Leave Type</label>
                    <select class="form-select" name="leave_type">
                        <option value="">Select</option>
                        <option value="Vacation Leave" <?= isset($_GET['leave_type']) && $_GET['leave_type']=="Vacation Leave"?"selected":"" ?>>Vacation Leave</option>
                        <option value="Sick Leave" <?= isset($_GET['leave_type']) && $_GET['leave_type']=="Sick Leave"?"selected":"" ?>>Sick Leave</option>
                        <option value="Emergency Leave" <?= isset($_GET['leave_type']) && $_GET['leave_type']=="Emergency Leave"?"selected":"" ?>>Emergency Leave</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Select</option>
                        <option value="Pending" <?= isset($_GET['status']) && $_GET['status']=="Pending"?"selected":"" ?>>Pending</option>
                        <option value="Approved" <?= isset($_GET['status']) && $_GET['status']=="Approved"?"selected":"" ?>>Approved</option>
                        <option value="Rejected" <?= isset($_GET['status']) && $_GET['status']=="Rejected"?"selected":"" ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Filter</button>
                </div>
            </form>
        </div>

        <script>

        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            onClose: function(selectedDates, dateStr, instance) {
                // Make sure the date range is formatted as "YYYY-MM-DD to YYYY-MM-DD"
                if(selectedDates.length === 2){
                    const from = selectedDates[0].toISOString().slice(0,10);
                    const to = selectedDates[1].toISOString().slice(0,10);
                    instance.input.value = `${from} to ${to}`;
                }
            }
        });

        </script>

        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Application No</th>
                    <th>User</th>
                    <th>Leave Type</th>
                    <th>Type</th>
                    <th>Credit</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php $i=1; while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= $row['application_no'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><?= $row['leave_type'] ?></td>
                    <td><?= $row['type'] ?></td>
                    <td><?= $row['credit_value'] ?></td>
                    <td><?= $row['date_from'] ?></td>
                    <td><?= $row['date_to'] ?></td>
                    <td><?= $row['remarks'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>"><i class="fa-solid fa-trash"></i></button>
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
                            <h5 class="modal-title">Edit Leave Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label>Leave Type</label>
                                <select class="form-select" name="leave_type" required>
                                    <option value="Vacation Leave" <?= $row['leave_type']=="Vacation Leave"?"selected":"" ?>>Vacation Leave</option>
                                    <option value="Sick Leave" <?= $row['leave_type']=="Sick Leave"?"selected":"" ?>>Sick Leave</option>
                                    <option value="Emergency Leave" <?= $row['leave_type']=="Emergency Leave"?"selected":"" ?>>Emergency Leave</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Type</label>
                                <select class="form-select" name="type" required>
                                    <option value="With Pay" <?= $row['type']=="With Pay"?"selected":"" ?>>With Pay</option>
                                    <option value="Without Pay" <?= $row['type']=="Without Pay"?"selected":"" ?>>Without Pay</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Credit Value</label>
                                <input type="number" step="0.5" class="form-control" name="credit_value" value="<?= $row['credit_value'] ?>" required>
                            </div>
                            <div class="mb-2">
                                <label>Date From</label>
                                <input type="text" class="form-control datepicker" name="date_from" value="<?= $row['date_from'] ?>" required>
                            </div>
                            <div class="mb-2">
                                <label>Date To</label>
                                <input type="text" class="form-control datepicker" name="date_to" value="<?= $row['date_to'] ?>" required>
                            </div>
                            <div class="mb-2">
                                <label>Remarks</label>
                                <textarea class="form-control" name="remarks" required><?= $row['remarks'] ?></textarea>
                            </div>
                            <div class="mb-2">
                                <label>Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="Pending" <?= $row['status']=="Pending"?"selected":"" ?>>Pending</option>
                                    <option value="Approved" <?= $row['status']=="Approved"?"selected":"" ?>>Approved</option>
                                    <option value="Rejected" <?= $row['status']=="Rejected"?"selected":"" ?>>Rejected</option>
                                </select>
                            </div>
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
                            <h5 class="modal-title">Cancel Leave Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to cancel <strong><?= $row['application_no'] ?></strong>?
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Proceed</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
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
                    <h5 class="modal-title">Apply Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Leave Type</label>
                        <select class="form-select" name="leave_type" required>
                            <option value="">-- Select --</option>
                            <option value="Vacation Leave">Vacation Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Emergency Leave">Emergency Leave</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Type</label>
                        <select class="form-select" name="type" required>
                            <option value="">-- Select --</option>
                            <option value="With Pay">With Pay</option>
                            <option value="Without Pay">Without Pay</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Credit Value</label>
                        <input type="number" step="0.5" class="form-control" name="credit_value" required>
                    </div>
                    <div class="mb-2">
                        <label>Date From</label>
                        <input type="text" class="form-control datepicker" name="date_from" required>
                    </div>
                    <div class="mb-2">
                        <label>Date To</label>
                        <input type="text" class="form-control datepicker" name="date_to" required>
                    </div>
                    <div class="mb-2">
                        <label>Remarks</label>
                        <textarea class="form-control" name="remarks" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>   
                </div>
                </div>
            </form>          
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
    flatpickr(".dateRangePicker", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                document.querySelector("input[name='date_from']").value = selectedDates[0].toISOString().slice(0, 10);
                document.querySelector("input[name='date_to']").value = selectedDates[1].toISOString().slice(0, 10);
            }
        }
    });
    </script>
    </body>
</html>
