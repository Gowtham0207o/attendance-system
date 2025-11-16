<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

$stmt = db()->query("SELECT * FROM payroll ORDER BY generated_at DESC");
echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
