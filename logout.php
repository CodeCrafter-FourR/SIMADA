<?php
/**
 * logout.php — Proses Logout SIMADA
 *
 * Menghapus sesi dan redirect ke halaman login.
 * Menggunakan class Auth untuk proses logout.
 */
require_once __DIR__ . '/config.php';

Auth::logout();
header('Location: login.php');
exit;
