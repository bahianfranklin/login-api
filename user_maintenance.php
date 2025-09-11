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

    </body

