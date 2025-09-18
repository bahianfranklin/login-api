<?php 
ob_start();                // ✅ Start output buffering
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$activeTab = $_GET['tab'] ?? 'user_management';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
        <title>User Maintance</title>
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
        <br>
        <h3 class="m-0">User Maintenance</h3>
        <br>
        <ul class="nav nav-tabs" id="maintenanceTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='user_management' ? 'active' : '' ?>" data-bs-toggle="tab" href="#user_management">User Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='schedules' ? 'active' : '' ?>" data-bs-toggle="tab" href="#schedules">Schedules</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='leave_credit' ? 'active' : '' ?>" data-bs-toggle="tab" href="#leave_credit">Leave Credit</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='approver_maintenance' ? 'active' : '' ?>" data-bs-toggle="tab" href="#approver_maintenance">Approvers Maintenance</a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <div class="tab-pane fade <?= $activeTab=='user_management' ? 'show active' : '' ?>" id="user_management">
                <?php include 'users.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='schedules' ? 'show active' : '' ?>" id="schedules">
                <?php include 'schedules.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='leave_credit' ? 'show active' : '' ?>" id="leave_credit">
                <?php include 'leave_credit.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='approver_maintenance' ? 'show active' : '' ?>" id="approver_maintenance">
                <?php include 'approver_maintenance1.php'; ?>
            </div>
        </div>
        
        <!-- Bootstrap JS (includes Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    </body

