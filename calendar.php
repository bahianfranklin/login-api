<?php
    // add_event.php
    session_start();
    require 'db.php';

    $success = "";
    $error = "";

    // ADD EVENT
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $event_type  = $_POST['event_type'];
        $title       = trim($_POST['title']);
        $date        = $_POST['date'];
        $location    = trim($_POST['location']);
        $description = trim($_POST['description']);
        $visibility  = $_POST['visibility'];

        $stmt = $conn->prepare("INSERT INTO holidays (event_type, title, date, location, description, visibility) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        // Check if prepare() failed
        if (!$stmt) {
            $error = "❌ SQL Prepare failed: " . $conn->error;
        } else {
            // Bind parameters
            $stmt->bind_param("ssssss", $event_type, $title, $date, $location, $description, $visibility);

            // Execute and handle result
            if ($stmt->execute()) {
                $success = "✅ Event added successfully!";
            } else {
                $error = "❌ Execute failed: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        }
    }

    // ✅ Update Event
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_event'])) {
        $id          = intval($_POST['id']); // use the hidden field from modal
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
                $success = "✅ Event updated successfully!";
            } else {
                $error = "❌ Update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "❌ SQL Prepare failed: " . $conn->error;
        }
    }

    // ✅ Delete Event
    if ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST['delete_event']) || (isset($_POST['action']) && $_POST['action'] === 'delete'))) {
        $id = intval($_POST['id']); // event id from hidden field

        $stmt = $conn->prepare("DELETE FROM holidays WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                // if ajax, return JSON
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

        <!-- ✅ Correct FullCalendar CSS -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet">

        <!-- ✅ Correct Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    </head>
    <body class="p-4">

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Company Calendar</h2>
            <!-- Add Event Button -->
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                ➕ Add Event
            </button>
        </div>

        <!-- ✅ Alerts placed here -->
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <!-- Add Event Modal -->
        <div class="modal fade" id="addEventModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
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

        <!-- Edit/View Modal -->
        <div class="modal fade" id="editEventModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
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
                            <input type="hidden" id="event_id" name="id">
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

        <div class="mt-3">
            <a href="view.php" class="back-btn">Back</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/rrule@2.6.4/dist/es5/rrule.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.11/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                events: 'fetch-birthdays.php',
                eventClick: function(info) {
                    const event = info.event;
                    // Fill modal fields
                    document.getElementById('edit-id').value = event.extendedProps.id;
                    document.getElementById('edit-title').value = event.title;
                    document.getElementById('edit-date').value = event.startStr;
                    document.getElementById('edit-location').value = event.extendedProps.location || '';
                    document.getElementById('edit-description').value = event.extendedProps.description || '';

                    // Populate select boxes
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

                    // Show modal
                    new bootstrap.Modal(document.getElementById('editEventModal')).show();
                }
            });
            calendar.render();

            // ✅ put delete function here
            document.getElementById('deleteEventBtn').addEventListener('click', function () {
                if (!confirm("Are you sure you want to delete this event?")) return;

                const eventId = document.getElementById('event_id').value;

                fetch('calendar.php', {
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
