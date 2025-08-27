    <!-- ✅ Get data in Database -->
<?php
    session_start();

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
?>

<?php
    require 'config.php';

    // ✅ Get search keyword
    $search = $_GET['search'] ?? '';

    // ✅ Get per-page limit from dropdown (default 5)
    $perPage = $_GET['limit'] ?? 5;

    // ✅ Current page
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    // ✅ Fetch all records from API
    $url = $baseUrl . "?action=view";
    $result = requestData($url);
    $result = preg_replace('/^[^\{]+/', '', $result);
    $result = preg_replace('/[^\}]+$/', '', $result);
    $dataArray = json_decode($result, true);

    $records = $dataArray['data'] ?? [];

    // ✅ Filter if search entered
    if (!empty($search)) {
        $records = array_filter($records, function($row) use ($search) {
            return stripos($row['fullname'], $search) !== false ||
                stripos($row['address'], $search) !== false ||
                stripos($row['contact_no'], $search) !== false;
        });
    }

    // ✅ Pagination setup
    $totalRecords = count($records);

    if ($perPage === "all") {
        // Show ALL records
        $currentRecords = $records;
        $totalPages = 1;
        $page = 1;
        $offset = 0;
    } else {
        $perPage = intval($perPage);
        $totalPages = ceil($totalRecords / $perPage);
        $offset = ($page - 1) * $perPage;
        $currentRecords = array_slice($records, $offset, $perPage);
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Welcome | Contacts Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>
    <body class="container mt-4">

        <!-- WELCOME, DETAILS CODE -->
        <div class="container mt-5">
            <div class="card p-4">

                 <!-- Top-right day and date -->
                <div class="position-absolute top-3 end-0 p-2 text-muted">
                    <h4><?= date('l, F j, Y'); ?></h4>
                </div>

                <!-- Always visible -->
                <h2 class="mb-3">
                    Welcome, <?= htmlspecialchars($user['name']); ?> 👋
                    <!-- Toggle button -->
                    <button class="btn btn-sm btn-outline-primary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#userDetails" aria-expanded="false" aria-controls="userDetails">
                        ▼
                    </button>
                </h2>

                <!-- Hidden details (dropdown) -->
                <div class="collapse mt-3" id="userDetails">
                    <p><strong>Address:</strong> <?= htmlspecialchars($user['address']); ?></p>
                    <p><strong>Contact:</strong> <?= htmlspecialchars($user['contact']); ?></p>
                    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']); ?></p>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS (needed for collapse to work) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <br>
        <br>
        <br>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="m-0">Contacts Details</h3>

            <!-- Add button aligned RIGHT -->
            <a href="add.php" class="btn btn-success btn-sm">
                <i class="fa fa-plus"></i> Add New Employee
            </a>
        </div>

        <!-- Search Form -->
        <form method="get" class="mb-3 d-flex">
            <input type="text" name="search" class="form-control me-2" 
                placeholder="Search by name, address and contact..."
                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="view.php" class="btn btn-secondary ms-2">Reset</a>
        </form>

        <?php if (isset($dataArray['data']) && is_array($dataArray['data'])): ?>
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Contact No</th>
                    <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentRecords as $i => $row): ?>
                    <tr>
                    <td><?= $offset + $i + 1 ?></td> <!-- Execute Tables numbering per pages (ex.1-19) -->
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['contact_no']) ?></td>
                    <td>
                        <!-- View button -->
                        <a href="show.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">
                        <i class="fa fa-eye"></i>
                        </a>
                        <!-- Edit button -->
                        <a href="update.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-pen"></i>
                        </a>

                        <!-- Delete button 
                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this contact?');">
                        <i class="fa fa-trash"></i>
                        </a>-->

                         <!-- Delete Button with details in popup -->
                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                        onclick="return confirm('Delete this contact?\n\nName: <?= addslashes($row['fullname']) ?>\nAddress: <?= addslashes($row['address']) ?>\nContact No: <?= addslashes($row['contact_no']) ?>');">
                        <i class="fa fa-trash"></i>
                        </a>
                        
                    </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <br>
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                    <!-- ✅ Pagination controls -->
                    <nav>
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                    href="?page=<?= $page-1 ?>&limit=<?= $perPage ?>&search=<?= urlencode($search) ?>">
                                    Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" 
                                    href="?page=<?= $i ?>&limit=<?= $perPage ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" 
                                    href="?page=<?= $page+1 ?>&limit=<?= $perPage ?>&search=<?= urlencode($search) ?>">
                                    Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>

                    <!-- ✅ Page Dropdown Pagination -->
                    <form method="get" class="d-inline">
                        <input type="hidden" name="limit" value="<?= $perPage ?>">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <label for="pageSelect">Page</label>
                        <select name="page" id="pageSelect" onchange="this.form.submit()">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <option value="<?= $i ?>" <?= ($i == $page) ? 'selected' : '' ?>>
                                    Page <?= $i ?> of <?= $totalPages ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </form>

                    <!-- ✅ Dropdown entries (10, 25, 50, 100, or all) -->
                    <form method="get" action="view.php" style="margin-bottom:10px;">
                        <input type="hidden" name="page" value="<?= $page ?>">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <label>Show 
                            <select name="limit" onchange="this.form.submit()">
                                <option value="5" <?= ($perPage == 5) ? 'selected' : '' ?>>5</option>
                                <option value="10" <?= ($perPage == 10) ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= ($perPage == 25) ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= ($perPage == 50) ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= ($perPage == 100) ? 'selected' : '' ?>>100</option>
                                <option value="all" <?= ($perPage === 'all') ? 'selected' : '' ?>>Show All</option>
                            </select>
                            entries
                        </label>
                    </form>

                    <!-- ✅ Export file to CSV, Excel & PDF -->
                    <form method="get" action="export.php" class="d-inline">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <label>Export:
                            <select name="type" onchange="this.form.submit()" class="form-select d-inline w-auto">
                            <option value="">-- Select --</option>
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                            </select>
                        </label>
                    </form>

                    <?php else: ?>
                        <p>No records found.</p>
                    <?php endif; ?>
        </div>
        <br>
        <a href="log_history.php">LOG HISTORY</a>
    </body>
</html>