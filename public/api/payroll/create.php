<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$start = $data['start'] ?? date('Y-m-01');
$end = $data['end'] ?? date('Y-m-t');
$created_by = $_SESSION['user_id'] ?? 1;

try {
    // Create payroll header
    $stmt = db()->prepare("INSERT INTO payroll (payroll_period_start, payroll_period_end, generated_by) VALUES (?, ?, ?)");
    $stmt->execute([$start, $end, $created_by]);
    $payroll_id = db()->lastInsertId();

    // Fetch summary
    $summaryStmt = db()->prepare("
        SELECT a.labour_id, a.project_id, 
               SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS days_present, 
               pl.pay_rate
        FROM attendance a
        JOIN project_labours pl ON pl.labour_id=a.labour_id AND pl.project_id=a.project_id
        WHERE a.attendance_date BETWEEN ? AND ?
        GROUP BY a.labour_id, a.project_id
    ");
    $summaryStmt->execute([$start, $end]);

    $total = 0;
    $detailsStmt = db()->prepare("
        INSERT INTO payroll_details (payroll_id, labour_id, project_id, total_days, pay_rate, total_amount)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    while ($row = $summaryStmt->fetch(PDO::FETCH_ASSOC)) {
        $amount = $row['days_present'] * $row['pay_rate'];
        $total += $amount;
        $detailsStmt->execute([$payroll_id, $row['labour_id'], $row['project_id'], $row['days_present'], $row['pay_rate'], $amount]);
    }

    db()->prepare("UPDATE payroll SET total_amount=? WHERE id=?")->execute([$total, $payroll_id]);

    echo json_encode(['success' => true, 'message' => 'Payroll created successfully', 'payroll_id' => $payroll_id]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
