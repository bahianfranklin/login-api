<?php
    require 'config.php';

    // Check if ID is passed
    if (!isset($_GET['id'])) {
        die("No ID provided.");
    }

    $id = $_GET['id'];

    // Fetch all records from API
    $url = $baseUrl . "?action=view";
    $result = requestData($url);

    // Clean and decode JSON
    $result = preg_replace('/^[^\{]+/', '', $result);
    $result = preg_replace('/[^\}]+$/', '', $result);
    $dataArray = json_decode($result, true);

    if (!isset($dataArray['data']) || !is_array($dataArray['data'])) {
        die("No records found in API.");
    }

    // Find record by ID
    $row = null;
    foreach ($dataArray['data'] as $record) {
        if ($record['id'] == $id) {
            $row = $record;
            break;
        }
    }

    if (!$row) {
        die("Record not found.");
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>View Contact</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body class="container mt-4">

        <h3>Contact Details</h3>
        <table class="table table-bordered">
            <tr>
                <th>Name</th>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?= htmlspecialchars($row['address']) ?></td>
            </tr>
            <tr>
                <th>Contact No</th>
                <td><?= htmlspecialchars($row['contact_no']) ?></td>
            </tr>
        </table>
        <a href="view.php" class="btn btn-secondary">Back</a>
    </body>
</html>
