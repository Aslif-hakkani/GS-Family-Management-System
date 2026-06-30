<?php
// Quick debug: show the actual HTML structure around the filter panel
session_start();
$_SESSION['user_id'] = 1; // fake auth for debug

// Simulate widows.php setup
$pageCategory = 'widow';
$pageFile = 'widows.php';
require '../includes/config.php';
require '../includes/adv_filter.php';

echo "<!-- activeCount: $activeCount -->\n";
echo "<!-- totalRecords: $totalRecords -->\n";

// Now render just the filter panel
ob_start();
require '../includes/adv_filter_panel.php';
$html = ob_get_clean();
echo $html;
?>
