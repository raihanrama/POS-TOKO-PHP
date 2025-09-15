<?php
// Session Management
session_start();

// Fungsi untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Fungsi untuk cek role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Fungsi untuk redirect jika bukan role yang diizinkan
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header("Location: unauthorized.php");
        exit();
    }
}

// Fungsi untuk logout
function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
