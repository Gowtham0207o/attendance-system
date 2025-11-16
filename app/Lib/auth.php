<?php
// app/Lib/auth.php

/**
 * Check if user is logged in
 */
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        // Not logged in, redirect to login page
        header('Location: /attendance-system/public/login.php');
        exit;
    }
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
