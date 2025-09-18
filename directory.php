<?php
    session_start();
    require 'db.php';

    // ✅ Fetch all active users (optional: only show active)
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
    $role = isset($_GET['role']) && $_GET['role'] !== '' ? $_GET['role'] : null;

    $sql = "SELECT id, name, address, contact, email, role, profile_pic 
            FROM users 
            WHERE status = 'active' AND name LIKE ?";

    $params = [$search];
    $types = "s";

    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
        $types .= "s";
    }

    $sql .= " ORDER BY name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $userCount = $result->num_rows;

    $countQuery = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    $totalEmployees = $countQuery->fetch_assoc()['total'];

?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
            <title>Company Directory</title>
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
        <style>
            body {
                background: #f8f9fa;
            }

            .directory-card {
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: 0.2s;
            padding: 20px;
            height: 100%;
            }

            .profile-pic {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
            }

            .button-container {
            position: fixed;
            bottom: 10px;
            left: 120px;
            }

            .back-button {
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
        </style>
        
    <div class="container py-5">
        <h2 class="mb-4 text-left">Company Directory</h2>
        <br>
        <form method="GET" class="row mb-4">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search by name" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            <div class="col-md-4">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin" <?= (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="superadmin" <?= (isset($_GET['role']) && $_GET['role'] === 'superadmin') ? 'selected' : '' ?>>Superadmin</option>
                    <option value="user" <?= (isset($_GET['role']) && $_GET['role'] === 'user') ? 'selected' : '' ?>>User</option>
                    <!-- Add other roles as needed -->
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>
            <p class="text-muted">No. of Records Found: <strong><?= $userCount ?></strong></p>

        <div class="row g-4">
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card directory-card text-start p-4">
                        <img src="<?= !empty($row['profile_pic']) ? 'uploads/' . $row['profile_pic'] : 'uploads/default.png' ?>" 
                             class="profile-pic mx-auto" alt="Profile">
                        <h5><?= htmlspecialchars($row['name']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($row['role']) ?></p>
                        <p>
                            📧 
                            <span id="email-<?= $row['id'] ?>"><?= htmlspecialchars($row['email']) ?></span>
                            <button class="btn btn-sm btn-outline-secondary copy-btn" 
                                    data-email="<?= htmlspecialchars($row['email']) ?>">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </p>
                        <script>
                            document.querySelectorAll('.copy-btn').forEach(button => {
                                button.addEventListener('click', () => {
                                    const email = button.getAttribute('data-email');
                                    navigator.clipboard.writeText(email).then(() => {
                                        // Optional: show feedback
                                        button.innerHTML = '✔'; // check mark
                                        setTimeout(() => {
                                            button.innerHTML = '<i class="fa-regular fa-copy"></i>';
                                        }, 1000);
                                    }).catch(err => {
                                        alert('Failed to copy email');
                                    });
                                });
                            });
                        </script>
                        <p>📞 <span id="contact-<?= $row['id'] ?>"><?= htmlspecialchars($row['contact']) ?></span>
                            <button class="btn btn-sm btn-outline-secondary copy-btn" 
                                    data-contact="<?= htmlspecialchars($row['contact']) ?>">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </p>
                        <script>
                            document.querySelectorAll('.copy-btn').forEach(button => {
                                button.addEventListener('click', () => {
                                    const contact = button.getAttribute('data-contact');
                                    navigator.clipboard.writeText(contact).then(() => {
                                        // Optional: show feedback
                                        button.innerHTML = '✔'; // check mark
                                        setTimeout(() => {
                                            button.innerHTML = '<i class="fa-regular fa-copy"></i>';
                                        }, 1000);
                                    }).catch(err => {
                                        alert('Failed to copy contact');
                                    });
                                });
                            });
                        </script>
                        <p>🏠 <small class="text-secondary"><?= htmlspecialchars($row['address']) ?></small></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <div class="button-container">
        <a href="view.php"><button class="back-button">BACK</button></a>
    </div>

    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    </body>
</html>
