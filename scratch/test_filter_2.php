<?php
require_once 'includes/config.php';
$pageCategory = 'homeless';
$pageFile = 'homeless.php';
require_once 'includes/adv_filter.php';
echo "Matches: " . count($filteredResults);
