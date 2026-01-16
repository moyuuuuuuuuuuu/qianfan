## 千帆api接口请求工具

- 通用类(Universal)
```php
$payload = new \Moyuuuuuuuu\Nutrition\Payload\Universal();
$payload->add('model', 'ERNIE-5.0-Thinking-Preview');
$payload->add('messages.0.role', 'user');
$payload->add('messages.0.content', '你好');
$payload->add('messages.1.role', 'system');
$payload->add('messages.1.content', '你好');
$payload->setUri('v2/chat/completions');
$request = new \Moyuuuuuuuu\Nutrition\Request($payload->getDomain(), getenv('API_KEY'), 'application/json');
$res     = $request->send($payload);

```

