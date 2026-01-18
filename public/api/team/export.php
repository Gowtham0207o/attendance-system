<?php
require_once __DIR__ . '/../../../app/bootstrap.php';

/**
 * BUSINESS RULE:
 * total_salary = Σ (working_count × labour.salary)
 * amount_paid  = Σ (team_payments.amount_paid)
 */

// --------------------
// Input parameters
// --------------------
$from   = $_GET['from'] ?? date('Y-m-d');
$to     = $_GET['to'] ?? date('Y-m-d');
$shift  = $_GET['shift'] ?? 'all';
$teamId = $_GET['team_id'] ?? null;

// --------------------
// CSV headers
// --------------------
$filename = "team_attendance_{$from}_to_{$to}_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// UTF-8 BOM (Excel compatibility)
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

try {
    $pdo = db();

    // =====================================================
    // 1️⃣ DETAILED ATTENDANCE
    // =====================================================
    fputcsv($out, [
        'Date',
        'Project',
        'Team',
        'Skill',
        'Working Count',
        'Salary Per Day',
        'Skill Total Salary',
        'Shift',
        'Recorded At'
    ]);

    $sqlDetail = "
        SELECT
            tsa.date,
            p.name AS project_name,
            t.name AS team_name,
            tsa.skill,
            tsa.working_count,
            l.salary AS salary_per_day,
            (tsa.working_count * l.salary) AS skill_total_salary,
            tsa.shift,
            tsa.created_at
        FROM team_skill_attendance tsa
        JOIN projects p ON p.id = tsa.project_id
        JOIN teams t ON t.id = tsa.team_id
        JOIN labours l
            ON l.team_id = tsa.team_id
           AND l.skill_type = tsa.skill
           AND l.status = 'active'
        WHERE tsa.date BETWEEN :from AND :to
    ";

    $params = [
        ':from' => $from,
        ':to'   => $to
    ];

    if ($shift !== 'all') {
        $sqlDetail .= " AND tsa.shift = :shift";
        $params[':shift'] = $shift;
    }

    if (!empty($teamId)) {
        $sqlDetail .= " AND tsa.team_id = :team_id";
        $params[':team_id'] = $teamId;
    }

    $sqlDetail .= " ORDER BY p.name, t.name, tsa.date, tsa.skill";

    $stmt = $pdo->prepare($sqlDetail);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $row['date'],
            $row['project_name'],
            $row['team_name'],
            $row['skill'],
            $row['working_count'],
            number_format($row['salary_per_day'], 2),
            number_format($row['skill_total_salary'], 2),
            ucfirst($row['shift']),
            $row['created_at']
        ]);
    }

    // =====================================================
    // 2️⃣ SUMMARY (WITH AMOUNT PAID)
    // =====================================================
    fputcsv($out, []);
    fputcsv($out, ['Project-wise Salary Summary']);
    fputcsv($out, [
        'Project',
        'Team',
        'Total Working Count',
        'Total Salary',
        'Amount Paid'
    ]);

    $sqlSummary = "
        SELECT
            p.id   AS project_id,
            p.name AS project_name,
            t.id   AS team_id,
            t.name AS team_name,

            SUM(tsa.working_count) AS total_workers,
            SUM(tsa.working_count * l.salary) AS total_salary,

            COALESCE((
                SELECT SUM(tp.amount_paid)
                FROM team_payments tp
                WHERE tp.project_id = p.id
                  AND tp.team_id = t.id
                  AND tp.payment_date BETWEEN :from AND :to
            ), 0) AS amount_paid

        FROM team_skill_attendance tsa
        JOIN projects p ON p.id = tsa.project_id
        JOIN teams t ON t.id = tsa.team_id
        JOIN labours l
            ON l.team_id = tsa.team_id
           AND l.skill_type = tsa.skill
           AND l.status = 'active'
        WHERE tsa.date BETWEEN :from AND :to
    ";

    $params2 = [
        ':from' => $from,
        ':to'   => $to
    ];

    if ($shift !== 'all') {
        $sqlSummary .= " AND tsa.shift = :shift";
        $params2[':shift'] = $shift;
    }

    if (!empty($teamId)) {
        $sqlSummary .= " AND tsa.team_id = :team_id";
        $params2[':team_id'] = $teamId;
    }

    $sqlSummary .= " GROUP BY p.id, t.id ORDER BY p.name, t.name";

    $stmt2 = $pdo->prepare($sqlSummary);
    $stmt2->execute($params2);

    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [
            $row['project_name'],
            $row['team_name'],
            $row['total_workers'],
            number_format($row['total_salary'], 2),
            number_format($row['amount_paid'], 2)
        ]);
    }

} catch (Exception $e) {
    fputcsv($out, ['ERROR', $e->getMessage()]);
}

fclose($out);
exit;
