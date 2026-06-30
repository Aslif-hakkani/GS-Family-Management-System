<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$_SESSION['user_id'] = 1;
$_GET['f_name'] = 'S';
require_once 'includes/config.php';
$_SESSION['user_id'] = 1; // config.php might have session_start
ob_start();
require 'homeless.php';
$out = ob_get_clean();
echo "Rows: " . substr_count($out, '<tr') . "\n";
