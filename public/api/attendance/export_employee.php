<?php
require_once __DIR__ . '/../../../app/bootstrap.php';

header('Content-Type: text/csv; charset=utf-8');
$filename = 'employee_attendance_' . date('Ymd_His') . '.csv';
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// UTF-8 BOM for Excel
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

try {
    $pdo = db();

    // ============================
    // 1) DETAILED ATTENDANCE ROWS
    // ============================

    fputcsv($out, [
        'Date',
        'Employee Name',
        'Designation',
        'Status',
        'In Time',
        'Out Time',
        'Hours Worked',
        'Remark',
        'Recorded At'
    ]);

    $sqlDetail = "
        SELECT 
            ea.att_date,
            e.name AS employee_name,
            e.role AS designation,
            ea.status,
            ea.in_time,
            ea.out_time,
            ea.work_hours,
            ea.remark,
            ea.created_at
        FROM employee_attendance ea
        INNER JOIN employees e ON e.id = ea.employee_id
        ORDER BY ea.att_date DESC, employee_name ASC
    ";

    $stmtDetail = $pdo->query($sqlDetail);

    while ($row = $stmtDetail->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $row['att_date'],
            $row['employee_name'],
            $row['designation'],
            ucfirst($row['status']),                // e.g. present -> Present
            $row['in_time'] ?? '',
            $row['out_time'] ?? '',
            $row['work_hours'] ?? '',
            $row['remark'] ?? '',
            $row['created_at']
        ]);
    }

    // ============================
    // 2) SUMMARY PER EMPLOYEE
    // ============================

    // Blank row as separator
    fputcsv($out, []);
    // Title row for summary section
    fputcsv($out, ['Summary by Employee']);
    // Header row
    fputcsv($out, [
        'Employee Name',
        'Designation',
        'Working Days',
        'Leave Days',
        'Other Days',
        'Total Attendance Records'
    ]);

    // Working Days: present + half-day + wfh + permission
    // Leave Days:   leave
    // Other Days:   holiday + anything not above
    $sqlSummary = "
        SELECT 
            e.id AS employee_id,
            e.name AS employee_name,
            e.role AS designation,

            SUM(
                CASE 
                    WHEN LOWER(ea.status) IN ('present','half-day','wfh','permission') 
                    THEN 1 ELSE 0 
                END
            ) AS working_days,

            SUM(
                CASE 
                    WHEN LOWER(ea.status) = 'leave' 
                    THEN 1 ELSE 0 
                END
            ) AS leave_days,

            SUM(
                CASE 
                    WHEN LOWER(ea.status) NOT IN ('present','half-day','wfh','permission','leave') 
                    THEN 1 ELSE 0 
                END
            ) AS other_days,

            COUNT(*) AS total_records

        FROM employee_attendance ea
        INNER JOIN employees e ON e.id = ea.employee_id
        GROUP BY e.id, e.name, e.role
        ORDER BY e.name ASC
    ";

    $stmtSummary = $pdo->query($sqlSummary);

    while ($row = $stmtSummary->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $row['employee_name'],
            $row['designation'],
            $row['working_days'],
            $row['leave_days'],
            $row['other_days'],
            $row['total_records']
        ]);
    }

} catch (Exception $e) {
    // If anything breaks, write an error row (so CSV isn't empty)
    fputcsv($out, ["ERROR", $e->getMessage()]);
}

fclose($out);
exit;
