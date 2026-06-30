<?php
require_once 'includes/config.php';
$_GET['f_name'] = 'Sunil';
$_GET['f_sex'] = '';
$pageCategory = 'homeless';
$pageFile     = 'homeless.php';
require_once 'includes/adv_filter.php';

echo "Query: $filterSQL\n";
echo "Params: "; print_r($params);
echo "Filtered count: " . count($filteredResults);
