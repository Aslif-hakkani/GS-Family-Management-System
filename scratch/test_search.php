<?php
$_GET['f_name'] = 'S';
require_once 'search.php';
echo "Matches: " . count($results);
