<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');
$project_id = $_GET['project_id'] ?? null;

$query = "SELECT a.id, l.name AS labour_name, a.status, a.shift, p.project_name 
          FROM attendance a
          JOIN labours l ON l.id = a.labour_id
          JOIN projects p ON p.id = a.project_id
          WHERE a.attendance_date = ?";
$params = [$date];

if ($project_id) {
    $query .= " AND a.project_id = ?";
    $params[] = $project_id;
}

$stmt = db()->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $rows]);
