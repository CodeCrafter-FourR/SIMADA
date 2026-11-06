<?php

if (PHP_SAPI !== 'cli') {
    exit("Jalankan pengujian melalui terminal.\n");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'simada_test1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

if (DB_NAME === 'simada') {
    exit("Pengujian dibatalkan: database utama tidak boleh digunakan.\n");
}

require_once dirname(__DIR__) . '/classes/Database.php';
require_once dirname(__DIR__) . '/classes/Auth.php';
require_once dirname(__DIR__) . '/classes/Dashboard.php';

require_once dirname(__DIR__) . '/helpers/functions.php';

function test_db(): PDO
{
    return Database::getInstance()->getConnection();
}