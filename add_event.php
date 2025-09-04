<?php
    
    session_start();
    require 'db.php';

    $success = "";
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $event_type  = $_POST['event_type'];
        $title       = trim($_POST['title']);
        $date        = $_POST['date'];
        $location    = trim($_POST['location']);
        $description = trim($_POST['description']);
        $visibility  = $_POST['visibility'];

        $stmt = $conn->prepare("INSERT INTO events (event_type, title, date, location, description, visibility) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $event_type, $title, $date, $location, $description, $visibility);

        if ($stmt->execute()) {
            $success = "✅ Event added successfully!";
        } else {
            $error = "❌ Error: " . $stmt->error;
        }
        $stmt->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Add Event</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="p-4">

    <div class="container">
        <h2 class="mb-4">Event Manager</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Add Event Button -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addEventModal">
            ➕ Add Event
        </button>

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

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Calendar</title>

        <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"> -->

        <!-- (A) RRule lib (must be FIRST) -->
        <script src="https://cdn.jsdelivr.net/npm/rrule@2.6.4/dist/es5/rrule.min.js"></script>

        <!-- (B) FullCalendar -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

        <!-- (C) FullCalendar <-> RRule connector (AFTER rrule + fullcalendar) -->
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/rrule@6.1.11/index.global.min.js"></script>
        
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
            /* smaller event text */
            .fc-event, .fc-event-title {
                font-size: 11px; 
                line-height: 1.2; 
            }

            .back-btn {
                font-family: Arial, sans-serif; 
                display: inline-block;
                padding: 8px 16px;
                margin-bottom: 15px;
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
    </head>
    <body> 
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="m-0">Company Calendar</h3>
            <!-- Add button aligned RIGHT -->
            <a href="add_event.php" class="btn btn-success btn-sm">
                <i class="fa fa-plus"></i> Add New Event
            </a>
        </div>

        <div id="calendar"></div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                    initialView: 'dayGridMonth',
                    events: 'fetch-birthdays.php' // this will return events with "rrule"
                });
                calendar.render();
                });
            </script>
            <div style="max-width:900px; margin:20px auto; text-align:left;">
                <a href="view.php" class="back-btn"></i> Back</a>
            </div>
    </body>
</html>

