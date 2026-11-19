<?php
define('ADMIN_PUBLIC', true);
require __DIR__ . '/includes/init.php';
unset($_SESSION['admin']);
session_regenerate_id(true);
redirect('login.php');
