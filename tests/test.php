<?php
$basePath = dirname(__DIR__);
include $basePath . '/vendor/autoload.php';

$lines = file($basePath . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') === false) continue;
    list($name, $value) = explode('=', $line, 2);
    putenv(trim($name) . '=' . trim($value));
}


$request  = new \Moyuuuuuuuu\Nutrition\Request();
$image    = $basePath . '/images/3.jpeg';
$response = $request->addImage($image)->addText(file_get_contents($basePath . '/src/template'))->do();

$response = \Moyuuuuuuuu\Nutrition\Util::parseNutrition($response);
