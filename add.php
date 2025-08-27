<?php
require 'config.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname   = trim($_POST['fullname']);
        $address    = trim($_POST['address']);
        $contact_no = trim($_POST['contact_no']);

        $errors = [];

        // ✅ Validate Fullname (letters, spaces, dots, hyphens)
        if (!preg_match("/^[a-zA-Z\s\.\-]+$/", $fullname)) {
            $errors[] = "Full Name can only contain letters, spaces, dots, and hyphens.";
        }

        // ✅ Validate Contact Number (numeric, 1–12 digits)
        if (!preg_match("/^[0-9]{1,12}$/", $contact_no)) {
            $errors[] = "Contact No must be numeric and up to 12 digits only.";
        }

        // ✅ Check duplicates in database via API
        if (empty($errors)) {
            $url = $baseUrl . "?action=view";
            $result = requestData($url);

            // Try decoding directly
            $dataArray = json_decode($result, true);

            // If API wrapped with garbage → try cleanup
            if (!$dataArray) {
                $clean = preg_replace('/^[^\[\{]+/', '', $result);
                $clean = preg_replace('/[^\]\}]+$/', '', $clean);
                $dataArray = json_decode($clean, true);
            }

            // ✅ If valid array, drill down into "data" if it exists
            if ($dataArray && is_array($dataArray)) {
                    $records = $dataArray['data'] ?? $dataArray; // use data key if present, else root

                if (is_array($records)) {
                    foreach ($records as $row) {
                        if (isset($row['fullname']) && strcasecmp($row['fullname'], $fullname) === 0) {
                            $errors[] = "This Full Name already exists in the database.";
                        }
                        if (isset($row['contact_no']) && $row['contact_no'] === $contact_no) {
                            $errors[] = "This Contact Number already exists in the database.";
                        }
                    }
                }
            }

        }

        // ✅ If no errors → save
        if (empty($errors)) {
            $url = $baseUrl . "?action=add";
            $data = [
                "fullname"   => $fullname,
                "address"    => $address,
                "contact_no" => $contact_no
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
        <title>Add Employee</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-4">

        <h3>Add New Employee</h3>

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

        <!-- Form Input texts -->
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="fullname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact No</label>
                <input type="text" name="contact_no" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Save</button>
            <a href="view.php" class="btn btn-secondary">Back</a>
        </form>

    </body>
</html>