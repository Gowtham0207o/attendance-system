<?php
// public/api/team/save_payment.php

require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// --------------------------------------------------
// Accept JSON or normal POST
// --------------------------------------------------
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

// --------------------------------------------------
// Read & sanitize inputs
// --------------------------------------------------
$projectId   = (int)($data['project_id'] ?? 0);
$teamId      = (int)($data['team_id'] ?? 0);
$paymentDate = trim($data['payment_date'] ?? '');
$amountPaid  = (float)($data['amount_paid'] ?? 0);

$paymentMode = $data['payment_mode'] ?? 'cash';
$referenceNo = trim($data['reference_no'] ?? '');
$remarks     = trim($data['remarks'] ?? '');
$createdBy   = isset($data['created_by']) ? (int)$data['created_by'] : null;

// --------------------------------------------------
// Basic validation
// --------------------------------------------------
if ($projectId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Project is required']);
    exit;
}

if ($teamId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Team is required']);
    exit;
}

if ($paymentDate === '') {
    echo json_encode(['success' => false, 'message' => 'Payment date is required']);
    exit;
}

if ($amountPaid <= 0) {
    echo json_encode(['success' => false, 'message' => 'Amount must be greater than zero']);
    exit;
}

// --------------------------------------------------
// Save payment
// --------------------------------------------------
try {
    $pdo = db();

    $sql = "
        INSERT INTO team_payments (
            project_id,
            team_id,
            payment_date,
            amount_paid,
            payment_mode,
            reference_no,
            remarks,
            created_by,
            created_at
        ) VALUES (
            :project_id,
            :team_id,
            :payment_date,
            :amount_paid,
            :payment_mode,
            :reference_no,
            :remarks,
            :created_by,
            NOW()
        )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':project_id'  => $projectId,
        ':team_id'     => $teamId,
        ':payment_date'=> $paymentDate,
        ':amount_paid' => $amountPaid,
        ':payment_mode'=> $paymentMode,
        ':reference_no'=> $referenceNo ?: null,
        ':remarks'     => $remarks ?: null,
        ':created_by'  => $createdBy
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Payment recorded successfully',
        'payment_id' => $pdo->lastInsertId()
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
