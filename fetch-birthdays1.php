<?php
require 'db.php';
header('Content-Type: application/json');

$events = [];

/**
 * 🎂 Fetch Birthdays
 */
$sql = $conn->query("SELECT id, name, birthday FROM users WHERE birthday IS NOT NULL");
while ($row = $sql->fetch_assoc()) {
    $month = (int)date('m', strtotime($row['birthday']));
    $day   = (int)date('d', strtotime($row['birthday']));
    $birthdate = date('Y-m-d', strtotime($row['birthday']));

    $events[] = [
        'id'     => "bday-" . $row['id'], // prefix so it won’t conflict with holidays
        'title'  => $row['name'] . "'s Birthday 🎂",
        'rrule'  => [
            'freq'       => 'yearly',
            'bymonth'    => $month,
            'bymonthday' => $day,
            'dtstart'    => $birthdate
        ],
        'allDay' => true,
        'color'  => '#f39c12'
    ];
}

/**
 * 🎉 Fetch Holidays
 */
$sql = $conn->query("SELECT id, title, date, event_type, location, description, visibility 
                     FROM holidays WHERE date IS NOT NULL");
while ($row = $sql->fetch_assoc()) {
    $holiday = date('Y-m-d', strtotime($row['date']));

    $events[] = [
        'id'          => $row['id'],          // ✅ now the ID is included
        'title'       => $row['title'],
        'start'       => $holiday,
        'event_type'  => $row['event_type'],
        'location'    => $row['location'],
        'description' => $row['description'],
        'visibility'  => $row['visibility'],
        'allDay'      => true,
        'color'       => '#28a745'
    ];
}

echo json_encode($events, JSON_UNESCAPED_UNICODE);
