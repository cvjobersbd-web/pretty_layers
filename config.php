<?php
/**
 * ============================================================
 *  SITE CONFIGURATION
 * ============================================================
 *  1) Put your database details below.
 *  2) Open  http://your-site/install.php  once in the browser.
 *  3) Delete install.php after installation (recommended).
 * ============================================================
 */

// Database type: 'mysql' (XAMPP / cPanel hosting)  or  'sqlite' (no setup needed)
define('DB_DRIVER', getenv('SHOP_DB_DRIVER') ?: 'mysql');

// MySQL settings
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'pretty_layers');
define('DB_USER', 'root');
define('DB_PASS', '');

// SQLite file (only used when DB_DRIVER = 'sqlite')
define('DB_SQLITE_PATH', __DIR__ . '/data/shop.sqlite');

// Time zone
date_default_timezone_set('Asia/Dhaka');

// Show PHP errors (keep false on the live site)
define('DEBUG', false);
