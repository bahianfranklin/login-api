<?php
session_start();
require 'db.php';

$approver_id = $_SESSION['user_id'] ?? null;
if (!$approver_id) {
    die("Not logged in");
}

/**
 * 🔹 Pending Official Business Requests
 */
$sqlPending = "
    SELECT 
        ob.application_no,
        u.name AS employee,
        d.department,
        ob.ob_date,
        ob.from_time,
        ob.to_time,
        ob.purpose,
        ob.location,
        ob.status,
        ob.datetime_applied
    FROM official_business AS ob
    INNER JOIN users AS u ON ob.applied_by = u.id
    INNER JOIN work_details AS wd ON u.id = wd.user_id
    INNER JOIN departments AS d ON d.department = wd.department
    INNER JOIN approver_assignments AS aa ON aa.department_id = d.id
    WHERE aa.user_id = ?
      AND ob.status = 'Pending'
    ORDER BY ob.datetime_applied DESC
";

$stmt = $conn->prepare($sqlPending);
$stmt->bind_param("i", $approver_id);
$stmt->execute();
$pending = $stmt->get_result();

/**
 * 🔹 Approved Official Business Requests
 */
$sqlApproved = "
    SELECT 
        ob.application_no,
        u.name AS employee,
        d.department,
        ob.ob_date,
        ob.from_time,
        ob.to_time,
        ob.purpose,
        ob.location,
        ob.status,
        ob.datetime_action
    FROM official_business ob
    JOIN users u ON ob.applied_by = u.id
    JOIN work_details wd ON u.id = wd.user_id
    JOIN departments d ON wd.department = d.department
    JOIN approver_assignments aa ON aa.department_id = d.id
    WHERE aa.user_id = ?
      AND ob.status = 'Approved'
    ORDER BY ob.datetime_action DESC
";

$stmt2 = $conn->prepare($sqlApproved);
$stmt2->bind_param("i", $approver_id);
$stmt2->execute();
$approved = $stmt2->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Approver - Official Business</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                        
                        <!-- ✅ Approving Dropdown -->
                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="approvingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-circle-check"></i> Approving
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="approvingDropdown">
                            <li><a class="dropdown-item" href="pending_leaves.php"><i class="fa fa-plane"></i> Leave </a></li>
                            <li><a class="dropdown-item" href="approver_overtime.php"><i class="fa fa-clock"></i> Overtime</a></li>
                            <li><a class="dropdown-item" href="approver_official_business.php"><i class="fa fa-briefcase"></i> Official Business</a></li>
                            <li><a class="dropdown-item" href="approver_change_schedule.php"><i class="fa fa-calendar-check"></i> Change Schedule</a></li>
                            <li><a class="dropdown-item" href="approver_failure_clock.php"><i class="fa fa-exclamation-triangle"></i> Failure to Clock</a></li>
                            <li><a class="dropdown-item" href="approver_clock_alteration.php"><i class="fa fa-edit"></i> Clock Alteration</a></li>
                            <li><a class="dropdown-item" href="approver_work_restday.php"><i class="fa fa-sun"></i> Work Rest Day</a></li>
                        </ul>
                        </li>
                        <!-- ✅ End Approving Dropdown -->

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

<div class="container py-4">
    <br>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Official Business (PENDING | APPROVED)</h3>
    </div>

    <!-- Pending Requests -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark fw-bold">
            Pending Official Business Requests
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Application No</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Purpose</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Date Applied</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $pending->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['application_no'] ?></td>
                            <td><?= $row['employee'] ?></td>
                            <td><?= $row['department'] ?></td>
                            <td><?= $row['ob_date'] ?></td>
                            <td><?= $row['from_time'] ?></td>
                            <td><?= $row['to_time'] ?></td>
                            <td><?= $row['purpose'] ?></td>
                            <td><?= $row['location'] ?></td>
                            <td><span class="badge bg-warning text-dark"><?= $row['status'] ?></span></td>
                            <td><?= $row['datetime_applied'] ?></td>
                            <td class="d-flex gap-1">
                                <form method="POST" action="update_official_business_status.php">
                                    <input type="hidden" name="application_no" value="<?= $row['application_no'] ?>">
                                    <input type="hidden" name="action" value="Approved">
                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form method="POST" action="update_official_business_status.php">
                                    <input type="hidden" name="application_no" value="<?= $row['application_no'] ?>">
                                    <input type="hidden" name="action" value="Rejected">
                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Approved Requests -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white fw-bold">
            Approved Official Business Requests
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Application No</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Purpose</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Date Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $approved->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['application_no'] ?></td>
                            <td><?= $row['employee'] ?></td>
                            <td><?= $row['department'] ?></td>
                            <td><?= $row['ob_date'] ?></td>
                            <td><?= $row['from_time'] ?></td>
                            <td><?= $row['to_time'] ?></td>
                            <td><?= $row['purpose'] ?></td>
                            <td><?= $row['location'] ?></td>
                            <td><span class="badge bg-success"><?= $row['status'] ?></span></td>
                            <td><?= $row['datetime_action'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <a href="view.php" class="btn btn-primary mt-3">BACK</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
