<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../../config/config.php';

try {
    $pdo = getPDO();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        echo json_encode(['success'=>false,'error'=>'Username and password required']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=:username AND status='active'");
    $stmt->execute(['username'=>$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success'=>false,'error'=>'Invalid credentials']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['username'] = $user['username'];

    $update = $pdo->prepare("UPDATE users SET last_login=NOW() WHERE id=:id");
    $update->execute(['id'=>$user['id']]);

    echo json_encode(['success'=>true]);

} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
