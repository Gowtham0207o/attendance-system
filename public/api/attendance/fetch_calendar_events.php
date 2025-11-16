<?php
// public/api/attendance/fetch_calendar_events.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

try {
    $pdo = db();

    // Determine date column
    $cols = $pdo->query("SHOW COLUMNS FROM attendance")->fetchAll(PDO::FETCH_COLUMN);
    $dateCol = in_array('attendance_date', $cols) ? 'attendance_date' : (in_array('date', $cols) ? 'date' : null);

    if (!$dateCol) {
        echo json_encode([]);
        exit;
    }

    $sql = "
      SELECT {$dateCol} AS dt,
             SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present_count,
             SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS absent_count
      FROM attendance
      GROUP BY {$dateCol}
      ORDER BY {$dateCol} ASC
    ";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = [];
    foreach ($rows as $r) {
        $color = (intval($r['absent_count']) > 0) ? '#ff6b6b' : '#51cf66'; // red if any absent else green
        $events[] = [
            'title' => "P: {$r['present_count']} / A: {$r['absent_count']}",
            'start' => $r['dt'],
            'color' => $color
        ];
    }

    echo json_encode($events);
} catch (Exception $e) {
    echo json_encode([]);
}
