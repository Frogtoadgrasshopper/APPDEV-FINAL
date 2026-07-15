<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Manila');

define('APP_NAME', 'CampusThread Hoodies');
define('GROUP_NAME', 'Group 6 CampusThread');
define('GROUP_NUMBER', '6');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');

$configuredBasePath = getenv('BASE_PATH');
if ($configuredBasePath === false || $configuredBasePath === '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $baseDir = rtrim(dirname($scriptName), '/');

    if (substr($baseDir, -6) === '/admin') {
        $baseDir = substr($baseDir, 0, -6);
    }

    $configuredBasePath = $baseDir === '' || $baseDir === '.' ? '' : $baseDir;
}

define('BASE_PATH', '/APPDEV-FINAL/campus-thread-hoodies');
define('MAIL_FROM', getenv('MAIL_FROM') ?: 'no-reply@campusthread.test');

// Local demo uses SQLite. For hosting, set DB_DRIVER=mysql and import
// database/university_hoodies_mysql.sql into your hosting MySQL database.
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'sqlite');
define('DB_PATH', __DIR__ . '/../database/campus_thread_demo.sqlite');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'campus_thread_hoodies');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

$GROUP_MEMBERS = [
    'Member 1 - Cholo P. Torres',
    'Member 2 - Mac Glenvere Cayabyab',
    'Member 3 - John Kirby A. Cuevo',
    'Member 4 - Ramiel Francois O. Manlabao',
];
