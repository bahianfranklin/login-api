<?php
    session_start();
    require 'db.php';  

    // Make sure user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

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
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login History</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <h3>Login / Logout History (All Users)</h3>
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
                        <td><?= $counter ?></td>
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

            <a href="view.php" class="btn btn-primary">BACK</a>
        </div>
    </body>
</html>
