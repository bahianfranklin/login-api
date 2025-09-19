<?php

session_start();
require 'db.php';

$success = "";
$error = "";

// if ($_SERVER["REQUEST_METHOD"] === "POST") {
//     echo "<pre>";
//     print_r($_POST);
//     echo "</pre>";
//     exit;
// }

// ✅ ADD EVENT
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['update_event']) && !isset($_POST['delete_event']) && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    $event_type  = $_POST['event_type'];
    $title       = trim($_POST['title']);
    $date        = $_POST['date'];
    $location    = trim($_POST['location']);
    $description = trim($_POST['description']);
    $visibility  = $_POST['visibility'];

    $stmt = $conn->prepare("INSERT INTO holidays (event_type, title, date, location, description, visibility) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssss", $event_type, $title, $date, $location, $description, $visibility);
        if ($stmt->execute()) {
            $success = "✅ Event added successfully!";
            header("Location: calendar1.php?success=1");
            exit();
        } else {
            $error = "❌ Insert failed: " . $stmt->error;
            header("Location: calendar1.php?success=1");
            exit();
        }
        $stmt->close();
    } else {
        $error = "❌ SQL Prepare failed: " . $conn->error;
        header("Location: calendar1.php?success=1");
        exit();
    }
}

// ✅ UPDATE EVENT
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_event'])) {
    $id          = intval($_POST['id']);
    $event_type  = $_POST['event_type'];
    $title       = trim($_POST['title']);
    $date        = $_POST['date'];
    $location    = trim($_POST['location']);
    $description = trim($_POST['description']);
    $visibility  = $_POST['visibility'];

    $stmt = $conn->prepare("UPDATE holidays 
                            SET event_type=?, title=?, date=?, location=?, description=?, visibility=? 
                            WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("ssssssi", $event_type, $title, $date, $location, $description, $visibility, $id);
        if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $success = "✅ Event updated successfully!";
            header("Location: calendar1.php?success=1");
            exit();
        } else {
            $error = "⚠️ No changes made. Either the ID does not exist or the data is the same.";
            header("Location: calendar1.php?success=1");
            exit();
        }
        } else {
            $error = "❌ Update failed: " . $stmt->error;
            header("Location: calendar1.php?success=1");
            exit();
        }
    }
}

// ✅ DELETE EVENT
if ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST['delete_event']) || (isset($_POST['action']) && $_POST['action'] === 'delete'))) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM holidays WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if (isset($_POST['action'])) {
                echo json_encode(["status" => "success"]);
                exit;
            }
            $success = "🗑️ Event deleted successfully!";
        } else {
            if (isset($_POST['action'])) {
                echo json_encode(["status" => "error", "message" => $stmt->error]);
                exit;
            }
            $error = "❌ Delete failed: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "❌ SQL Prepare failed: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">
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
  
                        <!-- ✅ Approving Dropdown -->
                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="approvingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-circle-check"></i> Approving
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="approvingDropdown">
                            <li><a class="dropdown-item" href="pending_leaves.php"><i class="fa fa-plane"></i> Leave </a></li>
                            <li><a class="dropdown-item" href="approver_overtime.php"><i class="fa fa-clock"></i> Overtime</a></li>
                            <li><a class="dropdown-item" href="approver_official_business.php"><i class="fa fa-briefcase"></i> Official Business</a></li>
                            <li><a class="dropdown-item" href="approver_change_schedule.php"><i class="fa fa-calendar-check"></i> Change Schedule</a></li>
                            <li><a class="dropdown-item" href="approver_failure_clock.php"><i class="fa fa-exclamation-triangle"></i> Failure to Clock</a></li>
                            <li><a class="dropdown-item" href="approver_clock_alteration.php"><i class="fa fa-edit"></i> Clock Alteration</a></li>
                            <li><a class="dropdown-item" href="approver_work_restday.php"><i class="fa fa-sun"></i> Work Rest Day</a></li>
                        </ul>
                        </li>
                        <!-- ✅ End Approving Dropdown -->

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
                <br>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Company Calendar</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">➕ Add Event</button>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- ✅ Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="calendar1.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" class="form-select" required>
                            <option value="Birthday">Birthday</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Custom">Custom</option>
                            <option value="Other">Other</option>
                        </select>
                        <label class="form-label mt-2">Title</label>
                        <input type="text" name="title" class="form-control" required>
                        <label class="form-label mt-2">Date</label>
                        <input type="date" name="date" class="form-control" required>
                        <label class="form-label mt-2">Location</label>
                        <input type="text" name="location" class="form-control">
                        <label class="form-label mt-2">Description</label>
                        <textarea name="description" class="form-control"></textarea>
                        <label class="form-label mt-2">Visibility</label>
                        <select name="visibility" class="form-select">
                            <option value="Public">Public</option>
                            <option value="Private">Private</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Event</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div style="display: flex;">
    <!-- Legend -->
    <div id="calendar-legend" style="width: 200px; padding: 10px; font-family: Arial; font-size: 14px;">
        <h3 style="margin-bottom: 10px; margin-top: 150px">Legend</h3>
        <div style="margin-bottom: 8px;">
        <span style="display:inline-block; width:16px; height:16px; background:#f39c12; margin-right:6px; border-radius:3px;"></span>
        Birthday 🎂
        </div>
        <div style="margin-bottom: 8px;">
        <span style="display:inline-block; width:16px; height:16px; background:#28a745; margin-right:6px; border-radius:3px;"></span>
        Holiday 🎉
        </div>
        <div style="margin-bottom: 8px;">
        <span style="display:inline-block; width:16px; height:16px; background:#007bff; margin-right:6px; border-radius:3px;"></span>
        Schedule Leaves 📌
        </div>
        <div style="margin-bottom: 8px;">
        <span style="display:inline-block; width:16px; height:16px; background:#6c757d; margin-right:6px; border-radius:3px;"></span>
        Other 📅
        </div>
    </div>

    <!-- Calendar -->
    <div id="calendar" style="flex-grow: 1;"></div>
    </div>

    <!-- ✅ Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="calendar1.php">
                    <input type="hidden" name="update_event" value="1">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Event Type</label>
                        <select name="event_type" id="edit-event_type" class="form-select" required></select>
                        <label class="form-label mt-2">Title</label>
                        <input type="text" name="title" id="edit-title" class="form-control" required>
                        <label class="form-label mt-2">Date</label>
                        <input type="date" name="date" id="edit-date" class="form-control" required>
                        <label class="form-label mt-2">Location</label>
                        <input type="text" name="location" id="edit-location" class="form-control">
                        <label class="form-label mt-2">Description</label>
                        <textarea name="description" id="edit-description" class="form-control"></textarea>
                        <label class="form-label mt-2">Visibility</label>
                        <select name="visibility" id="edit-visibility" class="form-select"></select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Event</button>
                        <button type="button" class="btn btn-danger" id="deleteEventBtn">Delete</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Calendar -->
    <div id="calendar"></div>
    <!-- <div class="mt-3">
        <a href="view.php" class="back-btn">Back</a>
    </div> -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/rrule@2.6.4/dist/es5/rrule.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.11/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        events: 'fetch-birthdays1.php',
        eventClick: function(info) {
            const event = info.event;
            document.getElementById('edit-id').value = event.id; // ✅ not extendedProps.id
            document.getElementById('edit-title').value = event.title;
            document.getElementById('edit-date').value = event.startStr;
            document.getElementById('edit-location').value = event.extendedProps.location || '';
            document.getElementById('edit-description').value = event.extendedProps.description || '';

            document.getElementById('edit-event_type').innerHTML = `
                <option ${event.extendedProps.event_type=='Birthday'?'selected':''}>Birthday</option>
                <option ${event.extendedProps.event_type=='Holiday'?'selected':''}>Holiday</option>
                <option ${event.extendedProps.event_type=='Custom'?'selected':''}>Custom</option>
                <option ${event.extendedProps.event_type=='Other'?'selected':''}>Other</option>
            `;
            document.getElementById('edit-visibility').innerHTML = `
                <option ${event.extendedProps.visibility=='Public'?'selected':''}>Public</option>
                <option ${event.extendedProps.visibility=='Private'?'selected':''}>Private</option>
            `;
            new bootstrap.Modal(document.getElementById('editEventModal')).show();
        }
    });
    calendar.render();

    // ✅ delete function
    document.getElementById('deleteEventBtn').addEventListener('click', function () {
        if (!confirm("Are you sure you want to delete this event?")) return;

        const eventId = document.getElementById('edit-id').value;

        fetch('calendar1.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'delete',
                id: eventId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                alert("Event deleted!");
                const modalEl = document.getElementById('editEventModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                calendar.refetchEvents();
            } else {
                alert("Delete failed: " + data.message);
            }
        })
        .catch(err => console.error(err));
    });
});
</script>

<style>
#calendar { 
    font-family: Arial, sans-serif;
    max-width: 900px; 
    margin: 40px auto; 
    background:#fff; 
    padding:20px; 
    border-radius:10px; 
    box-shadow:0 4px 10px rgba(0,0,0,.08); 
}
.fc-event, .fc-event-title {
    font-size: 11px; 
    line-height: 1.2; 
}
.back-btn {
    font-family: Arial, sans-serif; 
    display: inline-block;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
    background: #007bff;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s;
}
.back-btn:hover {
    background: #0056b3;
}
</style>
</body>
</html>
