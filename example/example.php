<?php

use Mechanik\Mechanik;

// Include Composer
require_once getcwd() . '/vendor/autoload.php';

$mechanik = new Mechanik;
$mechanik->register(
    new \Mechanik\Monitor\DiskUsage(70, 90),
    new \Mechanik\Monitor\Service('nginx', 'Nginx'),
    new \Mechanik\Monitor\Service('php8.1-fpm', 'PHP FPM'),
);

$report = $mechanik->report();

// Render this as a JSON response
header('Content-Type: application/json');
header('Content-Length: ' . strlen($report));
header('Cache-Control: no-cache, must-revalidate');

echo $mechanik->report();
