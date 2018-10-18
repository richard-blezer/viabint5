<?php
/**
 * No PHPunit support
 */

require_once __DIR__ . '/../vendor/autoload.php';

$check = [
    'max_execution_time' => [
        'old' => ini_get('max_execution_time'),
    ],
    'memory_limit' => [
        'old' => ini_get('memory_limit'),
    ],
];

$less = new \cakebake\lesscss\LessConverter();
$check['max_execution_time']['setting'] = $less->maxExecutionTime = 327;
$check['memory_limit']['setting'] = $less->memoryLimit = '222M';
$less->forceUpdate = true;
$outputFile = __DIR__ . '/../tmp/iniSettings.css';

$less->init([
    [
        'input' => __DIR__ . '/example-1.less',
        'webFolder' => '../tests',
    ],
    [
        'input' => __DIR__ . '/example-2.less',
        'webFolder' => '../tests',
    ],
], $outputFile);

$check['max_execution_time']['new'] = ini_get('max_execution_time');
$check['memory_limit']['new'] = ini_get('memory_limit');

?>

<pre><?php print_r($check); ?></pre>