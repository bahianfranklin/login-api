<?php
    session_start();
    require 'db.php';

    // ✅ Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("Please login first.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_SESSION['user_id'];
        $leave_type = $_POST['leave_type'];
        $type = $_POST['type'];
        $credit_value = $_POST['credit_value'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];
        $remarks = $_POST['remarks'];

        // ✅ Generate Application No.
        // $today = date("Ymd");
        // $appNo = "L-" . $today . "-" . rand(100, 999);
        
        $today = date("Ymd");
        $sql = "SELECT COUNT(*) as total FROM leave_requests WHERE DATE(date_applied) = CURDATE()";
        $res = $conn->query($sql);
        $row = $res->fetch_assoc();
        $countToday = $row['total'] + 1;
        $appNo = "L-" . $today . "-" . str_pad($countToday, 2, "0", STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO leave_requests
            (application_no, user_id, leave_type, type, credit_value, date_from, date_to, remarks)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissdsss", $appNo, $user_id, $leave_type, $type, $credit_value, $date_from, $date_to, $remarks);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success text-center'>✅ Leave application submitted! Application No: $appNo</div>";
        } else {
            echo "<div class='alert alert-danger text-center'>❌ Error: " . $stmt->error . "</div>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Leave Application</title>
        <!-- ✅ Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- ✅ Font Awesome (optional) -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <!-- ✅ Flatpickr CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    </head>
    <body class="bg-light">

    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fa-solid fa-calendar-check"></i> Leave Application Form</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Leave Type</label>
                        <select class="form-select" name="leave_type" required>
                            <option value="">-- Select --</option>
                            <option value="Vacation Leave">Vacation Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Emergency Leave">Emergency Leave</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="">-- Select --</option>
                            <option value="With Pay">With Pay</option>
                            <option value="Without Pay">Without Pay</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Credit Value</label>
                        <input type="number" step="0.5" class="form-control" name="credit_value" placeholder="e.g. 1.0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Leave Date From</label>
                        <input type="text" id="date_from" class="form-control" name="date_from" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Leave Date To</label>
                        <input type="text" id="date_to" class="form-control" name="date_to" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" placeholder="Reason for leave..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa-solid fa-paper-plane"></i> Submit Application
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ✅ Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ✅ Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#date_from", {
            enableTime: true,
            dateFormat: "Y-m-d H:i"
        });

        flatpickr("#date_to", {
            enableTime: true,
            dateFormat: "Y-m-d H:i"
        });
    </script>

    </body>
</html>
