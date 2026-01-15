<?php
include '../vendor/autoload.php';

//$response   = file_get_contents(__DIR__ . '/response/67.json');
//$data       = json_decode($response);
/*
$response = json_decode($response, true);
$output   = $response['output'][1]['content'][0]['text'] ?? '';
var_dump(json_decode($output,true));
exit;*/

$lines = file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') === false) continue;
    list($name, $value) = explode('=', $line, 2);
    putenv(trim($name) . '=' . trim($value));
}
$request = new \Moyuuuuuuuu\Nutrition\Request();
$image   = base64_encode(file_get_contents('./imgs/1.png'));
$image   = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQMPEeL6ye10YrhRKraqarB-5x4xXhbkyghUA&s';
$image   = __DIR__ . '/imgs/1.png';
$res = $request->do($image);
var_dump($res);


