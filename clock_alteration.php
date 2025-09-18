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
    // Parse filters
    $date_range = $_GET['date_range'] ?? null;
    $status     = $_GET['status'] ?? null;

    $from_date = null;
    $to_date   = null;

    if (!empty($date_range) && strpos($date_range, " to ") !== false) {
        [$from_date, $to_date] = explode(" to ", $date_range);
    }

    $query  = "SELECT ca.*, u.username 
            FROM clock_alteration ca
            JOIN users u ON ca.applied_by = u.id
            WHERE ca.applied_by = ?";
    $params = [$user_id];
    $types  = "i";

    if (!empty($from_date) && !empty($to_date)) {
        $query .= " AND ca.date_original BETWEEN ? AND ?";
        $params[] = $from_date;
        $params[] = $to_date;
        $types   .= "ss";
    }

    if (!empty($status)) {
        $query .= " AND ca.status = ?";
        $params[] = $status;
        $types   .= "s";
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Clock In/Out Alteration Requests</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                <h3>Clock In/Out Alteration Requests</h3>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ Apply Alteration</button>
            </div>

            <div class="card mb-3 p-3">
                <form method="GET" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="date_range" class="form-control dateRangePicker" 
                            placeholder="Select Date Range" value="<?= $_GET['date_range'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending" <?= ($_GET['status'] ?? '')=="Pending"?"selected":"" ?>>Pending</option>
                            <option value="Approved" <?= ($_GET['status'] ?? '')=="Approved"?"selected":"" ?>>Approved</option>
                            <option value="Rejected" <?= ($_GET['status'] ?? '')=="Rejected"?"selected":"" ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
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
                    
                    <?php
                   // Status badge
                    $statusClass = "secondary";
                    if ($row['status'] == "Pending")  $statusClass = "warning";
                    if ($row['status'] == "Approved") $statusClass = "success";
                    if ($row['status'] == "Rejected") $statusClass = "danger";
                    ?>

                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row['application_no'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['date_original'] ?></td>
                        <td><?= $row['time_in_original'] ? date("h:i A", strtotime($row['time_in_original'])) : '' ?></td>
                        <td><?= $row['time_out_original'] ? date("h:i A", strtotime($row['time_out_original'])) : '' ?></td>
                        <td><?= $row['date_new'] ?></td>
                        <td><?= $row['time_in_new'] ? date("h:i A", strtotime($row['time_in_new'])) : '' ?></td>
                        <td><?= $row['time_out_new'] ? date("h:i A", strtotime($row['time_out_new'])) : '' ?></td>
                        <td><?= $row['reason'] ?></td>
                        <td><span class="badge bg-<?= $statusClass ?>"><?= $row['status'] ?></span></td>
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

        <script>
            // For Date Range Picker
            flatpickr(".dateRangePicker", {
                mode: "range",
                dateFormat: "Y-m-d",
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        const from = instance.formatDate(selectedDates[0], "Y-m-d");
                        const to   = instance.formatDate(selectedDates[1], "Y-m-d");
                        instance.input.value = `${from} to ${to}`;
                    }
                }
            });

            // For Time Picker (12-hour format with AM/PM)
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K",  // 12-hour format with AM/PM
                time_24hr: false
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
