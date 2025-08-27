<?php
require 'config.php';

    $id = $_GET['id'] ?? null;
    if (!$id) {
        header("Location: view.php");
        exit;
    }

    // ✅ Fetch record details (with id in URL)
    $url = $baseUrl . "?action=view&id=" . $id;
    $result = requestData($url);
    $result = preg_replace('/^[^\{]+/', '', $result);
    $result = preg_replace('/[^\}]+$/', '', $result);
    $dataArray = json_decode($result, true);

    $record = null;

    if (isset($dataArray['data'])) {
        foreach ($dataArray['data'] as $row) {
            if ($row['id'] == $id) {
                $record = $row;
                break;
            }
        }
    }

    if (!$record) {
        die("Record not found.");
    }

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname   = trim($_POST['fullname']);
        $address    = trim($_POST['address']);
        $contact_no = trim($_POST['contact_no']);

        // ✅ Validate Fullname (letters, spaces, dots, hyphens only)
        if (!preg_match("/^[a-zA-Z\s\.\-]+$/", $fullname)) {
            $errors[] = "Full Name can only contain letters, spaces, dots, and hyphens.";
        }

        // ✅ Validate Contact Number (numeric, max 12 digits)
        if (!preg_match("/^[0-9]{1,12}$/", $contact_no)) {
            $errors[] = "Contact No must be numeric and up to 12 digits only.";
        }

        // ✅ If no errors → update
        if (empty($errors)) {
            $url = $baseUrl . "?action=update";
            $data = [
                "record_id" => $id,
                "fullname"  => $fullname,
                "address"   => $address,
                "contact_no"=> $contact_no
            ];
            requestData($url, $data);
            header("Location: view.php");
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">

    <h3>Update Employee</h3>

    <!-- Show errors -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($record['fullname']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($record['address']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact No</label>
            <input type="text" name="contact_no" class="form-control" value="<?= htmlspecialchars($record['contact_no']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="view.php" class="btn btn-secondary">Back</a>
    </form>

</body>
</html>
