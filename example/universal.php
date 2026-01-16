<?php
$basePath = dirname(__DIR__);
include $basePath . '/vendor/autoload.php';
$lines = file($basePath . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') === false) continue;
    list($name, $value) = explode('=', $line, 2);
    putenv(trim($name) . '=' . trim($value));
}

#模型
$payload = new \Moyuuuuuuuu\Nutrition\Payload\Vision('ernie-4.5-turbo-vl-latest');
$payload->addText(file_get_contents($basePath . '/src/template'));
$payload->addImage(($basePath . '/images/1.jpeg'));
$payload->setUri('/v2/chat/completions');
$payload = new \Moyuuuuuuuu\Nutrition\Payload\Universal();
$payload->add('model', 'ERNIE-5.0-Thinking-Preview');
$payload->add('messages.0.role', 'user');
$payload->add('messages.0.content', '你好');
$payload->add('messages.1.role', 'system');
$payload->add('messages.1.content', '你好');
$payload->setUri('v2/chat/completions');
$request = new \Moyuuuuuuuu\Nutrition\Request($payload->getDomain(), getenv('API_KEY'), 'application/json');
$res     = $request->send($payload);
var_dump($res);
