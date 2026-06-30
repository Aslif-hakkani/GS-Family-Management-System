<?php
function testUpload($payload) {
    $url = 'http://localhost/family%20details/api/bulk-upload-process.php';
    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($payload),
            'ignore_errors' => true
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return json_decode($result, true);
}

echo "Testing Valid Family No...\n";
$res1 = testUpload([
    'Family_No' => '105',
    'Name' => 'Test User 1',
    'Address' => 'Test Address',
    'GlobalCategory' => ''
]);
print_r($res1);

echo "\nTesting Empty Family No...\n";
$res2 = testUpload([
    'Family_No' => '',
    'Name' => 'Test User 2',
    'Address' => 'Test Address',
    'GlobalCategory' => ''
]);
print_r($res2);

echo "\nTesting Zero Family No...\n";
$res3 = testUpload([
    'Family_No' => '0',
    'Name' => 'Test User 3',
    'Address' => 'Test Address',
    'GlobalCategory' => ''
]);
print_r($res3);

echo "\nTesting Numeric 0 Family No...\n";
$res4 = testUpload([
    'Family_No' => 0,
    'Name' => 'Test User 4',
    'Address' => 'Test Address',
    'GlobalCategory' => ''
]);
print_r($res4);

echo "\nTesting 'no' column fallback...\n";
$res5 = testUpload([
    'no' => '202',
    'Name' => 'Test User 5',
    'Address' => 'Test Address',
    'GlobalCategory' => ''
]);
print_r($res5);
?>
