<?php
require 'config.php';

// ✅ Get search query from form
$search = $_GET['search'] ?? null;

$dataArray = [];

if ($search) {
    // Example: search by ID
    $url = $baseUrl . "?action=view&id=" . urlencode($search);

    $result = requestData($url);
    $result = preg_replace('/^[^\{]+/', '', $result);
    $result = preg_replace('/[^\}]+$/', '', $result);
    $dataArray = json_decode($result, true);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Records</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <h2>🔎 Search Records</h2>

    <!-- Search Form -->
    <form method="GET" action="search.php">
        <input type="text" name="search" placeholder="Enter ID to search" 
               value="<?= htmlspecialchars($search ?? '') ?>" required>
        <button type="submit">Search</button>
        <a href="search.php">Reset</a>
    </form>
    <br>

    <!-- Show results if search was performed -->
    <?php if ($search): ?>
        <?php if (!empty($dataArray['data'])): ?>
            <table border="1" cellpadding="5">
                <tr>
                    <th>ID</th>
                    <th>Fullname</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($dataArray['data'] as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['fullname'] ?></td>
                        <td><?= $row['address'] ?></td>
                        <td><?= $row['contact_no'] ?></td>
                        <td>
                            <a href="edit.php?id=<?= $row['id'] ?>">Edit</a> | 
                            <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No records found for <b><?= htmlspecialchars($search) ?></b>.</p>
        <?php endif; ?>
    <?php endif; ?>