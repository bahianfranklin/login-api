<?php
    require 'config.php';

    // Optional: support exporting filtered results (same as your view.php search)
    $search = $_GET['search'] ?? '';

    // Fetch all records from API
    $url = $baseUrl . "?action=view";
    $result = requestData($url);
    $result = preg_replace('/^[^{]+/', '', $result);
    $result = preg_replace('/[^}]+$/', '', $result);
    $dataArray = json_decode($result, true);
    $records = $dataArray['data'] ?? [];

    // Apply search filter if provided
    if ($search !== '') {
        $records = array_filter($records, function ($row) use ($search) {
            return stripos($row['fullname'], $search) !== false
                || stripos($row['address'], $search) !== false
                || stripos($row['contact_no'], $search) !== false;
        });
        // reindex array after filter
        $records = array_values($records);
    }

    // Which type to export
    $type = $_GET['type'] ?? 'csv';

    if ($type === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=contacts.csv');
        // BOM so Excel reads UTF-8 correctly
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Full Name', 'Address', 'Contact No']);
        foreach ($records as $r) {
            fputcsv($out, [$r['id'], $r['fullname'], $r['address'], $r['contact_no']]);
        }
        fclose($out);
        exit;
    }

    if ($type === 'excel') {
        // Send an Excel-compatible HTML table (no external libs)
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=contacts.xls');
        echo "\xEF\xBB\xBF"; // BOM
        ?>
        <table border="1">
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th style="width:220px;">Full Name</th>
                    <th style="width:300px;">Address</th>
                    <th style="width:160px;">Contact No</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($records as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['id']) ?></td>
                    <td><?= htmlspecialchars($r['fullname']) ?></td>
                    <td><?= htmlspecialchars($r['address']) ?></td>
                    <td><?= htmlspecialchars($r['contact_no']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        exit;
    }

    if ($type === 'pdf') {
        // Look for FPDF locally; if missing, show a helpful message
        $fpdfPath = __DIR__ . '/fpdf/fpdf.php';
        if (!file_exists($fpdfPath)) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo "PDF export requires the FPDF library.\n";
            echo "Expected file not found: {$fpdfPath}\n";
            echo "Fix: Download FPDF from https://www.fpdf.org/ and place the folder named 'fpdf' next to export.php\n";
            exit;
        }

        require $fpdfPath;
        $pdf = new FPDF('L', 'mm', 'A4'); // Landscape for wider table
        $pdf->AddPage();

        // Header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(25, 10, 'ID', 1);
        $pdf->Cell(90, 10, 'Full Name', 1);
        $pdf->Cell(130, 10, 'Address', 1);
        $pdf->Cell(40, 10, 'Contact No', 1);
        $pdf->Ln();

        // Rows
        $pdf->SetFont('Arial', '', 11);
        foreach ($records as $r) {
            $pdf->Cell(25, 8, $r['id'], 1);
            $pdf->Cell(90, 8, mb_convert_encoding($r['fullname'], 'ISO-8859-1', 'UTF-8'), 1);
            $pdf->Cell(130, 8, mb_convert_encoding($r['address'], 'ISO-8859-1', 'UTF-8'), 1);
            $pdf->Cell(40, 8, mb_convert_encoding($r['contact_no'], 'ISO-8859-1', 'UTF-8'), 1);
            $pdf->Ln();
        }

        $pdf->Output('D', 'contacts.pdf');
        exit;
    }

    http_response_code(400);
    echo 'Invalid export type.';
