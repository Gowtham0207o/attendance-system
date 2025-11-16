<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$stmt = db()->prepare("
    SELECT l.id AS labour_id, l.name AS labour_name, p.project_name, 
           SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS days_present,
           pl.pay_rate, 
           SUM(CASE WHEN a.status='present' THEN pl.pay_rate ELSE 0 END) AS total_pay
    FROM attendance a
    JOIN labours l ON l.id = a.labour_id
    JOIN projects p ON p.id = a.project_id
    JOIN project_labours pl ON pl.labour_id = l.id AND pl.project_id = p.id
    WHERE a.attendance_date BETWEEN ? AND ?
    GROUP BY l.id, p.id
");
$stmt->execute([$start, $end]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $rows]);
