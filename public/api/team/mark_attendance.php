<?php
// public/api/team/mark_attendance.php
require_once __DIR__ . '/../../../app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

// Support JSON and form POST
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$teamId = isset($data['team_id']) ? (int)$data['team_id'] : 0;
$date   = $data['date'] ?? date('Y-m-d');
$shift  = $data['shift'] ?? 'day';
$items  = $data['items'] ?? '[]';
$projectId = isset($data['project_id']) ? (int)$data['project_id'] : 0;
if ($teamId <= 0) {
    echo json_encode(['success' => false, 'message' => 'team_id is required']);
    exit;
}
if ($projectId <= 0) {
    echo json_encode(['success' => false, 'message' => 'project_id is required Not received from js']);
    exit;
}
if (is_string($items)) {
    $items = json_decode($items, true);
}
if (!is_array($items)) {
    echo json_encode(['success' => false, 'message' => 'items must be array/JSON']);
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $totalUpdated = 0;
    $perSkill = [];

    $sqlUpsert = "
        INSERT INTO team_skill_attendance (team_id, project_id, date, shift, skill, working_count, created_at, updated_at)
        VALUES (:team_id,:project_id , :date, :shift, :skill, :working_count, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            working_count = VALUES(working_count),
            updated_at    = NOW()
    ";
    $stmt = $pdo->prepare($sqlUpsert);

    foreach ($items as $item) {
        $skill = isset($item['skill']) ? trim($item['skill']) : '';
        $count = isset($item['working_count']) ? (int)$item['working_count'] : 0;

        if ($skill === '') continue;

        $stmt->execute([
            ':team_id'        => $teamId,
            ':project_id' => $projectId,
            ':date'           => $date,
            ':shift'          => $shift,
            ':skill'          => $skill,
            ':working_count'  => $count
        ]);

        $perSkill[$skill] = [
            'working_count' => $count
        ];
        $totalUpdated++;
    }

    $pdo->commit();

    echo json_encode([
        'success'        => true,
        'team_id'        => $teamId,
        'date'           => $date,
        'shift'          => $shift,
        'total_updated'  => $totalUpdated,
        'per_skill'      => $perSkill
    ]);
} catch (Exception $e) {
    if (!empty($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
