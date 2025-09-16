<?php 
$activeTab = $_GET['tab'] ?? 'branch'; // default to branch
$activeTab = $_GET['tab'] ?? 'department';
$activeTab = $_GET['tab'] ?? 'position';
$activeTab = $_GET['tab'] ?? 'level';
$activeTab = $_GET['tab'] ?? 'tax';
$activeTab = $_GET['tab'] ?? 'Status';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
        <title>Maintance</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    </head>
    <body class="container mt-4">
        <br>
        <h3 class="m-0">Maintenance Tabs</h3>
        <br>
        <ul class="nav nav-tabs" id="maintenanceTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='branch' ? 'active' : '' ?>" data-bs-toggle="tab" href="#branch">Branch</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='department' ? 'active' : '' ?>" data-bs-toggle="tab" href="#department">Departments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='position' ? 'active' : '' ?>" data-bs-toggle="tab" href="#position">Position</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='level' ? 'active' : '' ?>" data-bs-toggle="tab" href="#level">Level</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='tax' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tax">Tax Category</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='status' ? 'active' : '' ?>" data-bs-toggle="tab" href="#status">Status</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeTab=='payroll' ? 'active' : '' ?>" data-bs-toggle="tab" href="#payroll">Payroll Period</a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <div class="tab-pane fade <?= $activeTab=='branch' ? 'show active' : '' ?>" id="branch">
                <?php include 'branch.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='department' ? 'show active' : '' ?>" id="department">
                <?php include 'department.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='position' ? 'show active' : '' ?>" id="position">
                <?php include 'position.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='level' ? 'show active' : '' ?>" id="level">
                <?php include 'level.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='tax' ? 'show active' : '' ?>" id="tax">
                <?php include 'tax.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='status' ? 'show active' : '' ?>" id="status">
                <?php include 'status.php'; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab=='payroll' ? 'show active' : '' ?>" id="payroll">
                <?php include 'payroll_periods.php'; ?>
            </div>
        </div>

    </body

