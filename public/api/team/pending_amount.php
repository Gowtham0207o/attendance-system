<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json');

// $projectId = (int)($_GET['project_id'] ?? 0);
// $teamId    = (int)($_GET['team_id'] ?? 0);

$projectId =8;
$teamId =10;

if (!$projectId || !$teamId) {
    echo json_encode([
        'success' => false,
        'message' => 'project_id and team_id required'
    ]);
    exit;
}

try {
    $pdo = db();

    // -------------------------
    // 1️⃣ TOTAL EARNED
    // -------------------------
    $sqlEarned = "
     SELECT
    SUM(t.total_count * COALESCE(l.salary, 0)) AS total_earned
FROM (
    SELECT
        team_id,
        project_id,
        TRIM(LOWER(skill)) AS skill,
        SUM(working_count) AS total_count
    FROM team_skill_attendance
    WHERE project_id = :project_id
      AND team_id = :team_id
    GROUP BY team_id, project_id, TRIM(LOWER(skill))
) t
LEFT JOIN labours l
  ON l.team_id = t.team_id
 AND TRIM(LOWER(l.skill_type)) = t.skill;
    ";

    $stmt = $pdo->prepare($sqlEarned);
    $stmt->execute([
        ':project_id' => $projectId,
        ':team_id'    => $teamId
    ]);

    $totalEarned = (float)$stmt->fetchColumn();
  

    // -------------------------
    // 2️⃣ TOTAL PAID
    // -------------------------
    $sqlPaid = "
        SELECT COALESCE(SUM(amount_paid),0)
        FROM team_payments
        WHERE project_id = :project_id
          AND team_id = :team_id
    ";

    $stmt2 = $pdo->prepare($sqlPaid);
    $stmt2->execute([
        ':project_id' => $projectId,
        ':team_id'    => $teamId
    ]);

    $totalPaid = (float)$stmt2->fetchColumn();

    // -------------------------
    // 3️⃣ RESPONSE
    // -------------------------
    echo json_encode([
        'success'       => true,
        'total_earned' => $totalEarned,
        'total_paid'   => $totalPaid,
        'pending'      => max($totalEarned - $totalPaid, 0)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
