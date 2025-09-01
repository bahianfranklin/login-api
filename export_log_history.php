<?php
    require 'db.php';
    //require 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    // Get export type
    $type = $_GET['type'] ?? 'csv';

    // Get filters
    $search = $_GET['search'] ?? '';
    $from   = $_GET['from'] ?? '';
    $to     = $_GET['to'] ?? '';

    // Build query (same as log_history)
    $sql = "SELECT u.name AS fullname, u.username, l.login_time, l.logout_time, l.ip_address
            FROM user_logs l
            JOIN users u ON l.user_id = u.id";
    $conditions = [];
    $params = [];
    $types = '';

    if ($search !== '') {
        $conditions[] = "(u.name LIKE ? OR u.username LIKE ? OR l.ip_address LIKE ?)";
        $like = "%$search%";
        $params = [$like, $like, $like];
        $types .= 'sss';
    }
    if ($from !== '') {
        $conditions[] = "l.login_time >= ?";
        $params[] = $from . " 00:00:00";
        $types .= 's';
    }
    if ($to !== '') {
        $conditions[] = "l.login_time <= ?";
        $params[] = $to . " 23:59:59";
        $types .= 's';
    }
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $sql .= " ORDER BY l.login_time DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Convert result to array
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Export handlers
    if ($type === 'csv') {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=log_history.csv");
        $out = fopen("php://output", "w");
        fputcsv($out, ["Fullname", "Username", "Login Time", "Logout Time", "IP Address"]);
        foreach ($data as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    if ($type === 'excel') {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(["Fullname", "Username", "Login Time", "Logout Time", "IP Address"], NULL, 'A1');
        $sheet->fromArray($data, NULL, 'A2');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="log_history.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
        exit;
    }

    if ($type === 'pdf') {
        require 'fpdf/fpdf.php';
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,'Login History',0,1,'C');
        $pdf->SetFont('Arial','',10);

        foreach ($data as $row) {
            $pdf->Cell(40,10,$row['fullname'],1);
            $pdf->Cell(35,10,$row['username'],1);
            $pdf->Cell(40,10,$row['login_time'],1);
            $pdf->Cell(40,10,$row['logout_time'] ?: '---',1);
            $pdf->Cell(35,10,$row['ip_address'],1);
            $pdf->Ln();
        }

        $pdf->Output("D","log_history.pdf");
        exit;
    }

    echo "Invalid export type";

?>