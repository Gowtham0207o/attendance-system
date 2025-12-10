<?php
// public/api/team/summary.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

$teamId = isset($_GET['team_id']) ? (int)$_GET['team_id'] : 0;
$date   = $_GET['date'] ?? date('Y-m-d');
$shift  = $_GET['shift'] ?? 'day';

if ($teamId <= 0) {
    echo json_encode(['success' => false, 'message' => 'team_id is required']);
    exit;
}

try {
    $pdo = db();

    // Treat labours as "skill templates" per team.
    // We use skill_type from labours as the logical skill key.
    $sql = "
        SELECT 
            COALESCE(l.skill_type, 'Unspecified') AS skill,
            COALESCE(tsa.working_count, 0)       AS working_count
        FROM labours l
        LEFT JOIN team_skill_attendance tsa
               ON tsa.team_id = l.team_id
              AND tsa.skill   = l.skill_type
              AND tsa.date    = :date
              AND tsa.shift   = :shift
        WHERE l.team_id = :team_id
        GROUP BY COALESCE(l.skill_type, 'Unspecified'), tsa.working_count
        ORDER BY skill ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':team_id' => $teamId,
        ':date'    => $date,
        ':shift'   => $shift
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $r) {
        $count = (int)$r['working_count'];
        $out[] = [
            'skill'         => $r['skill'],
            'total_labours' => $count,   // for UI we show same number as today's quantity
            'working_count' => $count
        ];
    }

    echo json_encode([
        'success' => true,
        'team_id' => $teamId,
        'date'    => $date,
        'shift'   => $shift,
        'data'    => $out
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
