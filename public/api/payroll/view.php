<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing payroll ID']);
    exit;
}

$stmt = db()->prepare("
    SELECT pd.*, l.name AS labour_name, p.project_name 
    FROM payroll_details pd
    JOIN labours l ON l.id = pd.labour_id
    JOIN projects p ON p.id = pd.project_id
    WHERE pd.payroll_id = ?
");
$stmt->execute([$id]);
echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
