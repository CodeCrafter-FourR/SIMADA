<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Auth.php';

if (Auth::check()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
