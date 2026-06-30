<?php
require_once 'includes/config.php';
$_GET['f_name'] = 'S';
$pageCategory = 'homeless';
$pageFile = 'homeless.php';
require_once 'includes/adv_filter.php';
echo "Matches: " . count($filteredResults);
