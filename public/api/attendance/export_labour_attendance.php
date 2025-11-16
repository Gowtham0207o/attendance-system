<?php
// public/api/attendance/export_labour_attendance.php

require_once __DIR__ . '/../../../app/bootstrap.php';

// Read date from query string
$date = $_GET['date'] ?? null;
if (!$date) {
    // Fallback to today if no date provided
    $date = date('Y-m-d');
}

// CSV headers
header('Content-Type: text/csv; charset=utf-8');
$filename = 'labour_attendance_' . $date . '_' . date('Ymd_His') . '.csv';
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// UTF-8 BOM (helps with Excel)
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

try {
    $pdo = db();

    // ============================
    // 1) DETAILED ROWS FOR THE DATE
    // ============================

    fputcsv($out, [
        'Date',
        'Labour Name',
        'Skill / Role',
        'Project',
        'Shift',
        'Status',
        'Remarks',
        'Marked At'
    ]);

    $sqlDetail = "
        SELECT 
            a.date,
            l.name AS labour_name,
            COALESCE(l.skill_type, '') AS labour_skill,
            p.name AS project_name,
            a.shift,
            a.status,
            a.remarks,
            a.created_at
        FROM attendance a
        LEFT JOIN labours  l ON l.id = a.labour_id
        LEFT JOIN projects p ON p.id = a.project_id
        WHERE a.date = :date
        ORDER BY labour_name ASC, project_name ASC
    ";

    $stmtDetail = $pdo->prepare($sqlDetail);
    $stmtDetail->execute([':date' => $date]);

    while ($row = $stmtDetail->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $row['date'],
            $row['labour_name'],
            $row['labour_skill'],
            $row['project_name'],
            ucfirst($row['shift'] ?? ''),
            ucfirst($row['status'] ?? ''),   // present -> Present, etc.
            $row['remarks'] ?? '',
            $row['created_at'] ?? ''
        ]);
    }

    // ============================
    // 2) SUMMARY FOR THAT DATE
    // ============================

    // Blank row separator
    fputcsv($out, []);
    // Title row
    fputcsv($out, ["Summary for {$date}"]);
    // Header row
    fputcsv($out, [
        'Status',
        'Count'
    ]);

    $sqlSummary = "
        SELECT 
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count,
            SUM(CASE WHEN status = 'absent'  THEN 1 ELSE 0 END) AS absent_count,
            SUM(CASE WHEN status = 'leave'   THEN 1 ELSE 0 END) AS leave_count,
            SUM(CASE WHEN status = 'half'    THEN 1 ELSE 0 END) AS half_count
        FROM attendance
        WHERE date = :date
    ";

    $stmtSummary = $pdo->prepare($sqlSummary);
    $stmtSummary->execute([':date' => $date]);
    $row = $stmtSummary->fetch(PDO::FETCH_ASSOC) ?: [];

    $present = (int)($row['present_count'] ?? 0);
    $absent  = (int)($row['absent_count']  ?? 0);
    $leave   = (int)($row['leave_count']   ?? 0);
    $half    = (int)($row['half_count']    ?? 0);

    fputcsv($out, ['Present',  $present]);
    fputcsv($out, ['Absent',   $absent]);
    fputcsv($out, ['Leave',    $leave]);
    fputcsv($out, ['Half-day', $half]);
    fputcsv($out, ['Total Records', $present + $absent + $leave + $half]);

} catch (Exception $e) {
    // If something fails, at least output an error row
    fputcsv($out, ['ERROR', $e->getMessage()]);
}

fclose($out);
exit;
