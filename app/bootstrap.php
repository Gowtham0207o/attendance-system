<?php

date_default_timezone_set('Asia/Kolkata');

// Load config
require_once __DIR__ . '/../config/config.php';  // config.php defines $db_host, $db_name, $db_user, $db_pass and getPDO()

// Database connection using PDO
try {
    // Use getPDO() helper from config.php
    $db = getPDO();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Global function for DB access
function db()
{
    global $db;
    return $db;
}

// Auto-load classes from app/ folders
spl_autoload_register(function ($class) {
    $paths = ['Controllers', 'Models', 'Services', 'Lib'];
    foreach ($paths as $dir) {
        $file = __DIR__ . "/$dir/$class.php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
