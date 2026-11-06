<?php

// Konfigurasi utama SIMADA

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'simada');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Autoload Classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load Helper Functions
require_once __DIR__ . '/helpers/functions.php';

?>