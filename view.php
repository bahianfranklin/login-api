<?php
    session_start();
    require 'db.php';

    // 🚫 Prevent cached pages
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    $user = $_SESSION['user'];

    // ✅ Assume logged-in user
    $user_id = $_SESSION['user_id'] ?? 1; // change if needed

    // ✅ Get Leave Balance
    $leave_sql = "SELECT mandatory, vacation_leave, sick_leave FROM leave_credits WHERE user_id = ?";
    $stmt = $conn->prepare($leave_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $leave = $stmt->get_result()->fetch_assoc();

    // ✅ Get Events
    $today = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
        <title>Welcome | Dashboard</title>
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

        <!-- WELCOME, DETAILS CODE -->
        <div class="container mt-5">
            <div class="card p-4">
                <!-- Row for Welcome + Date -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="mb-0">
                        Welcome, <?= htmlspecialchars($user['name']); ?> 👋
                        <!-- Toggle button -->
                        <button class="btn btn-sm btn-outline-primary ms-2" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#userDetails" 
                                aria-expanded="false" aria-controls="userDetails">
                            ▼
                        </button>
                    </h2>
                    <h5 class="text-muted mb-0"><?= date('l, F j, Y'); ?></h5>
                </div>

        <!-- Hidden details (dropdown) -->
        <div class="collapse mt-3" id="userDetails">

                   <!-- Profile Picture -->
                    <?php 
                        $profilePath = "uploads/" . $user['profile_pic']; 
                        if (!empty($user['profile_pic']) && file_exists($profilePath)): ?>
                            <div class="mb-3 text-center">
                                <img src="<?= $profilePath ?>?t=<?= time(); ?>" 
                                    alt="Profile Picture" 
                                    class="img-thumbnail rounded-circle" 
                                    style="width:150px; height:150px; object-fit:cover;">
                            </div>
                        <?php else: ?>
                            <div class="mb-3 text-center">
                                <img src="uploads/default.png" 
                                    class="rounded-circle border border-2" 
                                    style="width:120px; height:120px; object-fit:cover;">
                            </div>
                        <?php endif; 
                    ?>

                    <p><strong>Address:</strong> <?= htmlspecialchars($user['address']); ?></p>
                    <p><strong>Contact:</strong> <?= htmlspecialchars($user['contact']); ?></p>
                    <p><strong>Birthday:</strong> 
                        <?= !empty($user['birthday']) ? date("F d, Y", strtotime($user['birthday'])) : "—"; ?>
                    </p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
                    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></p>
                    <p><strong>Role:</strong> <?= htmlspecialchars($user['role']); ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($user['status']); ?></p>

                    <a href="edit_user-profile.php?id=<?= $user['id']; ?>" class="btn btn-primary">Edit</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>

            </div>
        </div>

        <!-- Bootstrap JS (needed for collapse to work) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <br>
        <br>
        <br>

        <div class="container">
            <h3 class="mb-4">Employee Dashboard</h3>

            <!-- Leave Balance -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">Leave Balance</div>
                    <div class="card-body">
                        <div id="leaveBalance">
                            Loading leave balance...
                        </div>
                    </div>
            </div>

            <!-- Events -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">Events</div>
                    <div class="card-body">
                        <div id="eventSection">
                            Loading events...
                        </div>
                    </div>
            </div>
            
            <!-- Work Schedule -->
            <div class="card mb-3">
                <div class="card-header bg-warning">Work Schedule</div>
                    <div id="scheduleSection">
                        Loading schedule...
                    </div>
            </div>

            <!-- Payroll Period -->
            <div class="card mb-3">
                <div class="card-header bg-info">Current Payroll Period</div>
                    <div class="card-body">
                        <div id="payrollSection">
                            Loading payroll...
                        </div>
                    </div>
            </div>
        </div>

    <script>
    function fetchDashboard() {
        fetch("dashboard_data.php")
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    document.getElementById("leaveBalance").innerHTML = "<p>Error: " + data.error + "</p>";
                    return;
                }

                // ✅ Update Leave Balance
                document.getElementById("leaveBalance").innerHTML = `
                    <p><b>Mandatory Leave:</b> ${data.leave?.mandatory ?? 0}</p>
                    <p><b>Vacation Leave:</b> ${data.leave?.vacation_leave ?? 0}</p>
                    <p><b>Sick Leave:</b> ${data.leave?.sick_leave ?? 0}</p>
                `;

                // ✅ Update Events
                let bdays = data.birthdays.length 
                    ? data.birthdays.map(b => `<p>${b.name} (${new Date(b.birthday).toLocaleDateString('en-US',{month:'short',day:'numeric'})})</p>`).join("")
                    : "<p>No birthdays today</p>";

                let holidays = data.holidays.length
                    ? data.holidays.map(h => `<p><b>${h.title}</b> - ${new Date(h.date).toLocaleDateString()}</p>`).join("")
                    : "<p>No upcoming holidays</p>";

                document.getElementById("eventSection").innerHTML = `
                    <h6>🎂 Birthdays Today</h6>${bdays}
                    <h6 class="mt-3">📅 Upcoming Holidays</h6>${holidays}
                `;

                // ✅ Update Work Schedule
                if (data.schedule) {
                    document.getElementById("scheduleSection").innerHTML = `
                        <table class="table table-bordered">
                            <tr><th>Monday</th><td>${data.schedule.monday}</td></tr>
                            <tr><th>Tuesday</th><td>${data.schedule.tuesday}</td></tr>
                            <tr><th>Wednesday</th><td>${data.schedule.wednesday}</td></tr>
                            <tr><th>Thursday</th><td>${data.schedule.thursday}</td></tr>
                            <tr><th>Friday</th><td>${data.schedule.friday}</td></tr>
                            <tr><th>Saturday</th><td>${data.schedule.saturday}</td></tr>
                            <tr><th>Sunday</th><td>${data.schedule.sunday}</td></tr>
                        </table>
                    `;
                } else {
                    document.getElementById("scheduleSection").innerHTML = "<p>No schedule set</p>";
                }

                // ✅ Update Payroll
                if (data.period) {
                    document.getElementById("payrollSection").innerHTML = `
                        <p><b>Period Code:</b> ${data.period.period_code}</p>
                        <p><b>Start:</b> ${data.period.start_date} | <b>End:</b> ${data.period.end_date}</p>
                        <p><b>Cutoff:</b> ${data.period.cutoff}</p>
                    `;
                } else {
                    document.getElementById("payrollSection").innerHTML = "<p>No payroll period found</p>";
                }
            });
    }

    // Run immediately and auto-refresh every 5s
    fetchDashboard();
    setInterval(fetchDashboard, 5000);
    </script>
    
    </body>
</html>
