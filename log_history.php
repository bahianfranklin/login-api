<?php
    session_start();
    require 'db.php';  

    // Make sure user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];  // ✅ define it here

    // Get search keyword and date range from GET
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $from = isset($_GET['from']) ? trim($_GET['from']) : '';
    $to = isset($_GET['to']) ? trim($_GET['to']) : '';

    // Base SQL
    $sql = "SELECT l.id, u.name AS fullname, u.username, l.login_time, l.logout_time, l.ip_address
            FROM user_logs l
            JOIN users u ON l.user_id = u.id";

    // Prepare conditions
    $conditions = [];
    $params = [];
    $types = '';

    // ✅ Add user restriction first
    $conditions[] = "l.user_id = ?";
    $params[] = $user_id;
    $types .= 'i';

    // Add search condition
    if ($search !== '') {
        $conditions[] = "(u.name LIKE ? OR u.username LIKE ? OR l.ip_address LIKE ?)";
        $likeSearch = "%$search%";
        $params[] = $likeSearch;
        $params[] = $likeSearch;
        $params[] = $likeSearch;
        $types .= 'sss';
    }

    // Add date range conditions
    if ($from !== '') {
        $conditions[] = "l.login_time >= ?";
        $params[] = $from . " 00:00:00";
        $types .= 's';
    }
    if ($to !== '') {
        $conditions[] = "l.login_time <= ?";
        $params[] = $to . " 23:59:59";
        $types .= 's';
    }

    // Combine conditions
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY l.login_time DESC";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // --- PAGINATION SETUP ---
    // Get limit per page (default 10)
    $perPage = isset($_GET['limit']) ? $_GET['limit'] : 5;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    // Clone SQL for counting total records
    $countSql = "SELECT COUNT(*) as total FROM user_logs l JOIN users u ON l.user_id = u.id";
    if (!empty($conditions)) {
        $countSql .= " WHERE " . implode(" AND ", $conditions);
    }

    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalRecords = $countResult->fetch_assoc()['total'];

    // Compute total pages
    if ($perPage === "all") {
        $totalPages = 1;
        $offset = 0;
    } else {
        $perPage = intval($perPage);
        $totalPages = ceil($totalRecords / $perPage);
        $offset = ($page - 1) * $perPage;
    }

    // Final SQL with LIMIT
    $sql .= ($perPage === "all") ? "" : " LIMIT $offset, $perPage";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login History</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>

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
            <h4>Login / Logout History</h4>
            <br>
            <!-- Search form with From/To dates -->
            <form method="get" class="mb-3 row g-2 align-items-end">
                <div class="col-md-3">
                    <label><strong><em>Keyword</em></strong></label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, username, or IP" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <label><strong><em>From:</em></strong></label>
                    <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
                </div>
                <div class="col-md-3">
                    <label><strong><em>To:</em></strong></label>
                    <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="?" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>FULLNAME</th>
                        <th>Username</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                        <?php 
                        $counter = 1; 
                        while ($row = $result->fetch_assoc()): 
                            $login_time = date("Y-m-d h:i:s A", strtotime($row['login_time']));
                            $logout_time = $row['logout_time'] ? date("Y-m-d h:i:s A", strtotime($row['logout_time'])) : '---';
                        ?>
                        <tr>
                            <td><?= $offset + $counter ?></td>
                            <td><?= htmlspecialchars($row['fullname']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= $login_time ?></td>
                            <td><?= $logout_time ?></td>
                            <td><?= htmlspecialchars($row['ip_address']) ?></td>
                        </tr>
                        <?php 
                        $counter++;
                        endwhile; 
                        ?>
                </tbody>
            </table>
            <br>
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">

                <!-- Pagination links -->
                <nav>
                    <ul class="pagination mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page-1 ?>&limit=<?= $perPage ?>&search=<?= urlencode($search) ?>&from=<?= $from ?>&to=<?= $to ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i=1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i==$page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&limit=<?= $perPage ?>&search=<?= urlencode($search) ?>&from=<?= $from ?>&to=<?= $to ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page+1 ?>&limit=<?= $perPage ?>&search=<?= urlencode($search) ?>&from=<?= $from ?>&to=<?= $to ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Page Dropdown -->
                <form method="get" class="d-inline ms-3">
                    <input type="hidden" name="limit" value="<?= $perPage ?>">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
                    <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">

                    <label for="pageSelect">Page</label>
                    <select name="page" id="pageSelect" onchange="this.form.submit()">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <option value="<?= $i ?>" <?= ($i == $page) ? 'selected' : '' ?>>
                                <?= "Page $i of $totalPages" ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </form>

                <!-- Dropdown for limit -->
                <form method="get" class="d-flex align-items-center ms-2">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
                    <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">
                    <input type="hidden" name="page" value="1">
                    <label class="me-2">Show</label>
                    <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                        <option value="5" <?= ($perPage==5)?'selected':'' ?>>5</option>
                        <option value="10" <?= ($perPage==10)?'selected':'' ?>>10</option>
                        <option value="25" <?= ($perPage==25)?'selected':'' ?>>25</option>
                        <option value="50" <?= ($perPage==50)?'selected':'' ?>>50</option>
                        <option value="100" <?= ($perPage==100)?'selected':'' ?>>100</option>
                        <option value="all" <?= ($perPage==='all')?'selected':'' ?>>Show All</option>
                    </select>
                    <label class="ms-2">entries</label>
                </form>
           

                <!-- Export Dropdown -->
                <form method="get" action="export_log_history.php" class="d-inline">
                    <label>Export:
                        <select id="exportSelect" class="form-select d-inline-block w-auto" onchange="if(this.value) window.location.href=this.value;">
                            <option value="">-- Select --</option>
                            <option value="export_log_history.php?type=csv">CSV</option>
                            <option value="export_log_history.php?type=excel">Excel</option>
                            <option value="export_log_history.php?type=pdf">PDF</option>
                        </select>
                    </label>
                </form>
            </div> 
            <br>
            <a href="view.php" class="btn btn-primary">BACK</a>
        </div>
        <!-- Bootstrap JS (includes Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
