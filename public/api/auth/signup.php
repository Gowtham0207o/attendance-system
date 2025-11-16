<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

try {
    $pdo = getPDO();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$email || !$password) {
        echo json_encode(['success'=>false,'error'=>'All fields are required']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=:username OR email=:email");
    $stmt->execute(['username'=>$username,'email'=>$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success'=>false,'error'=>'Username or email already exists']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $insert = $pdo->prepare("INSERT INTO users (username,email,password,role,created_by) VALUES (:username,:email,:password,'admin',:created_by)");
    $insert->execute([
        'username'=>$username,
        'email'=>$email,
        'password'=>$hashed,
        'created_by'=>$_SESSION['user_id']
    ]);

    echo json_encode(['success'=>true]);

} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
