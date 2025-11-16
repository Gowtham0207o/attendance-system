<?php
/**
 * Attendance System Configuration
 * -------------------------------
 * Stores database credentials, global constants, and other configuration
 */

// ------------------------
// Database Connection
// ------------------------
$db_host = 'mysql.selfmade.ninja';      
$db_name = 'Gowtham032_attendance_system';     
$db_user = 'Gowtham032';           
$db_pass = 'Gowtham@@@2003';               

// ------------------------
// Application Settings
// ------------------------
define('APP_NAME', 'Attendance System');
define('APP_URL', 'https://auth.selfmade.technology/attendance-system/public');  // Base URL of your app

// Timezone
date_default_timezone_set('Asia/Kolkata');

// ------------------------
// Paths
// ------------------------
define('ROOT_PATH', '/home/Gowtham032/htdocs/attendance-system/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('STORAGE_PATH', ROOT_PATH . 'storage/');

// ------------------------
// Error Reporting
// ------------------------
// Enable errors for development (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ------------------------
// PDO Connection Helper
// ------------------------
function getPDO() {
    global $db_host, $db_name, $db_user, $db_pass;

    try {
        $pdo = new PDO(
            "mysql:host={$db_host};dbname={$db_name};charset=utf8",
            $db_user,
            $db_pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        // Stop execution and return error
        die(json_encode([
            'success' => false,
            'error' => 'Database connection failed: ' . $e->getMessage()
        ]));
    }
}
