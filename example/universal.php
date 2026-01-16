<?php

use Moyuuuuuuuu\Nutrition\Payload\Universal;
use Moyuuuuuuuu\Nutrition\Contants\{RequestMethod, Role, ContentType};

$basePath = dirname(__DIR__);
include $basePath . '/vendor/autoload.php';
$lines = file($basePath . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos($line, '=') === false) continue;
    list($name, $value) = explode('=', $line, 2);
    putenv(trim($name) . '=' . trim($value));
}
/*
#模型
$payload = new \Moyuuuuuuuu\Nutrition\Payload\Vision('ernie-4.5-turbo-vl-latest');
$payload->addText(file_get_contents($basePath . '/src/template'));
$payload->addImage(($basePath . '/images/1.jpeg'));
$payload->setUri('/v2/chat/completions');*/

#获取模型列表
$payload = (new \Moyuuuuuuuu\Nutrition\Payload\Universal())
    ->setDomain('https://qianfan.baidubce.com')
    ->setUri('v2/models')->setMethod(RequestMethod::GET);

#文本生成
$payload = (new Universal())
    ->setDomain('https://qianfan.baidubce.com')
    ->setUri('v2/chat/completions')
    ->setMethod(RequestMethod::POST)
    ->addMessage(Role::SYSTEM, file_get_contents($basePath . '/src/Template/text'))
    ->add('model', 'ERNIE-5.0-Thinking-Preview');

#视觉理解
$payload = (new Universal())
    ->setDomain('https://qianfan.baidubce.com')
    ->setUri('v2/chat/completions')
    ->setMethod(RequestMethod::POST)
//    ->addMessage(Role::SYSTEM, file_get_contents($basePath . '/src/Template/text'))
    ->add('messages', [
        [
            'role'    => Role::USER->value,
            'content' => [
                [
                    'type' => 'text',
                    'text' => file_get_contents($basePath . '/src/Template/image')
                ],
                [
                    'type'      => 'image_url',
                    'image_url' => ['url' => \Moyuuuuuuuu\Nutrition\Util::baseFile($basePath . '/images/1.jpeg')]
                ]
            ]
        ]
    ])
    ->add('model', 'ernie-4.5-turbo-vl-latest');
$request = new \Moyuuuuuuuu\Nutrition\Request(getenv('API_KEY'));
$res     = $request->send($payload);

var_dump($res);
