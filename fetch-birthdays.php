<?php
    require 'db.php';
    header('Content-Type: application/json');

    $sql = $conn->query("SELECT name, birthday FROM users WHERE birthday IS NOT NULL");
    $events = [];

    while ($row = $sql->fetch_assoc()) {
        $month = (int)date('m', strtotime($row['birthday']));
        $day   = (int)date('d', strtotime($row['birthday']));
        $birthdate = date('Y-m-d', strtotime($row['birthday'])); // full original date

        $events[] = [
            'title'  => $row['name'] . "'s Birthday 🎂",
            'rrule'  => [
                'freq'       => 'yearly',
                'bymonth'    => $month,
                'bymonthday' => $day,
                'dtstart'    => $birthdate  // make recurrence start at original birthday
            ],
            'allDay' => true
            // 'color'  => '#f39c12' // 🟠 orange for birthdays
        ];
    }

    $sql = $conn->query("SELECT title, date FROM holidays WHERE date IS NOT NULL");
    while ($row = $sql->fetch_assoc()) {
        $month = (int)date('m', strtotime($row['date']));
        $day   = (int)date('d', strtotime($row['date']));
        $holiday = date('Y-m-d', strtotime($row['date'])); // full original date

        $events[] = [
            'title'  => $row['title'] . "'s Holiday 🎉",
            'rrule'  => [
                'freq'       => 'yearly',
                'bymonth'    => $month,
                'bymonthday' => $day,
                'dtstart'    => $holiday  // make recurrence start at original birthday
            ],
            'allDay' => true
            // 'color'  => '#28a745' // 🟢 green for holidays
        ];
    }
    
    echo json_encode($events, JSON_UNESCAPED_UNICODE);
?>