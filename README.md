## 千帆api接口请求工具

### 获取模型列表
```php
//组装payload
$payload = (new \Moyuuuuuuuu\Nutrition\Payload\Universal())
    ->setDomain('https://qianfan.baidubce.com')
    ->setUri('v2/models')->setMethod(RequestMethod::GET);

```

### 视觉理解

```php

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

//发起请求
$request = new \Moyuuuuuuuu\Nutrition\Request(getenv('API_KEY'));
$res     = $request->send($payload);

var_dump($res);
```

### 文本生成
```php

#文本生成
$payload = (new Universal())
    ->setDomain('https://qianfan.baidubce.com')
    ->setUri('v2/chat/completions')
    ->setMethod(RequestMethod::POST)
    ->addMessage(Role::SYSTEM, file_get_contents($basePath . '/src/Template/text'))
    ->add('model', 'ERNIE-5.0-Thinking-Preview');

//发起请求
$request = new \Moyuuuuuuuu\Nutrition\Request(getenv('API_KEY'));
$res     = $request->send($payload);

var_dump($res);
```

### 短语音识别
```php
$payload = (new Universal())
    ->setDomain('http://vop.baidu.com')
    ->setUri('/server_api')
    ->setMethod(RequestMethod::POST)
    ->setHeader('Content-Type', 'application/json')
    ->add('speech', Util::baseFile($basePath . '/speech/1.m4a',null,false))
    ->add('format', 'm4a')
    ->add('channel', 1)
    ->add('cuid', 'default_user')
    ->add('dev_pid', 1537)
    ->add('len', filesize($basePath . '/speech/1.m4a'))
    ->add('rate', 16000);
$request = new \Moyuuuuuuuu\Nutrition\Request(getenv('API_KEY'));
$res     = $request->send($payload);
```
其他各个接口依此类推

