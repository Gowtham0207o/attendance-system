<?php
// public/api/attendance/mark.php
require_once __DIR__ . '/../../../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// Read JSON body if sent, else fall back to $_POST
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
$data = is_array($body) ? $body : $_POST;

// -------------
// Input fields
// -------------
$employeeId = isset($data['employee_id']) ? (int)$data['employee_id'] : 0;
$status     = trim($data['status'] ?? '');
$date       = trim($data['date'] ?? '');   // optional
$remark     = trim($data['remark'] ?? '');
$shift      = trim($data['shift'] ?? 'General'); // optional, not stored yet but available

// If no date provided, default to today (server timezone already in bootstrap)
if ($date === '') {
    $date = date('Y-m-d');
}

// -------------
// Basic validation
// -------------
if ($employeeId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid employee_id'
    ]);
    exit;
}

if ($status === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Status is required'
    ]);
    exit;
}

// Normalize status to match ENUM in employee_attendance
// Table ENUM: ('present','absent','leave','half-day','wfh','holiday')
$normalized = strtolower($status);

// Map some UI values to DB-safe values
switch ($normalized) {
    case 'present':
        $dbStatus = 'present';
        break;
    case 'absent':
        $dbStatus = 'absent';
        break;
    case 'leave':
    case 'permission':    // treat permission like leave
        $dbStatus = 'permission';
        break;
    case 'half-day':
    case 'halfday':
    case 'half_day':
        $dbStatus = 'half-day';
        break;
    case 'wfh':
    case 'work from home':
        $dbStatus = 'wfh';
        break;
    case 'holiday':
        $dbStatus = 'holiday';
        break;
    default:
        // Fallback: if unknown, treat as present or return error
        $dbStatus = 'present';
        break;
}

try {
    $pdo = db();

    // Check if a record already exists for this employee + date
    $checkSql = "SELECT id FROM employee_attendance WHERE employee_id = ? AND att_date = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$employeeId, $date]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // UPDATE existing attendance
        $updateSql = "
            UPDATE employee_attendance
            SET status = ?, remark = ?, updated_at = NOW()
            WHERE id = ?
        ";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$dbStatus, $remark, $existing['id']]);

        $mode = 'updated';
    } else {
        // INSERT new attendance
        $insertSql = "
            INSERT INTO employee_attendance (employee_id, att_date, status, remark, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([$employeeId, $date, $dbStatus, $remark]);

        $mode = 'inserted';
    }

    echo json_encode([
        'success' => true,
        'message' => "Attendance $mode successfully",
        'data' => [
            'employee_id' => $employeeId,
            'date'        => $date,
            'status'      => $dbStatus,
            'remark'      => $remark,
            'mode'        => $mode
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
