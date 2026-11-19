<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config.php';

if (DEBUG) { ini_set('display_errors', '1'); error_reporting(E_ALL); }
else { ini_set('display_errors', '0'); }

if (session_status() === PHP_SESSION_NONE) {
    session_name('lavanya_sess');
    session_start();
}

require_once APP_ROOT . '/includes/db.php';
require_once APP_ROOT . '/includes/functions.php';

// Not installed yet? -> go to installer
if (!defined('INSTALLING') && !is_file(APP_ROOT . '/data/installed.lock')) {
    redirect(($GLOBALS['ROOT'] ?? '') . 'install.php');
}

// Upgrade the database automatically when new features are added
require_once APP_ROOT . '/includes/migrate.php';
if (!defined('INSTALLING')) run_migrations();
