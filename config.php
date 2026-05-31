<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'controle_despesas');
define('DB_USER', 'root');
define('DB_PASS', '');

date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/src/Database.php';
