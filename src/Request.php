<?php

namespace Moyuuuuuuuu\Nutrition;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

class Request
{
    private const DEFAULT_DOMAIN = 'https://qianfan.baidubce.com';
    private const DEFAULT_MODEL = 'ernie-4.5-turbo-vl-latest';
    private const API_PATH = '/v2/chat/completions';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $promptTemplate;

    /**
     * @var array
     */
    protected $contents = [];

    public function __construct(?string $apiKey = null, string $domain = self::DEFAULT_DOMAIN, string $model = self::DEFAULT_MODEL)
    {
        $this->apiKey = $apiKey ?: (string) getenv('API_KEY');
        if (empty($this->apiKey)) {
            throw new RuntimeException('API Key is required');
        }

        $this->model = $model;

        $this->client = new Client([
            'base_uri' => $domain,
            'verify'   => false
        ]);

        $templatePath = __DIR__ . '/template';
        if (file_exists($templatePath)) {
            $this->promptTemplate = file_get_contents($templatePath);
        }
    }

    /**
     * 添加文本内容
     *
     * @param string $text
     * @return $this
     */
    public function addText(string $text): self
    {
        $this->contents[] = [
            'type' => 'text',
            'text' => $text,
        ];
        return $this;
    }

    /**
     * 添加图片内容
     *
     * @param string $imagePath
     * @return $this
     */
    public function addImage(string $imagePath): self
    {
        if (!file_exists($imagePath)) {
            throw new RuntimeException("Image file not found: {$imagePath}");
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $this->contents[] = [
            'type'      => 'image_url',
            'image_url' => ['url' => $imageData],
        ];
        return $this;
    }

    /**
     * 清空已添加的内容
     *
     * @return $this
     */
    public function clearContents(): self
    {
        $this->contents = [];
        return $this;
    }

    /**
     * 设置请求参数
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * @param string|null $imageUrl
     * @return array|null
     * @throws GuzzleException
     */
    public function do(?string $imageUrl = null): ?array
    {
        // 兼容旧逻辑：如果传入了 imageUrl，则清空之前添加的内容并只处理这张图
        if ($imageUrl !== null) {
            $this->clearContents();
            if ($this->promptTemplate) {
                $this->addText($this->promptTemplate);
            }
            $this->addImage($imageUrl);
        }

        if (empty($this->contents)) {
            return null;
        }

        $messages = $this->buildMessages($this->contents);
        $payload  = $this->buildPayload($messages);

        try {
            $response = $this->client->post(self::API_PATH, [
                'json'    => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $this->format($data ?: []);
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    /**
     * 构建消息内容
     *
     * @param array $contents
     * @param string $role
     * @return array
     */
    protected function buildMessages(array $contents, string $role = 'user'): array
    {
        return [
            [
                'role'    => $role,
                'content' => $contents,
            ]
        ];
    }

    /**
     * 构建请求载荷
     *
     * @param array $messages
     * @return array
     */
    protected function buildPayload(array $messages): array
    {
        return array_merge([
            'model'    => $this->model,
            'messages' => $messages,
        ], $this->options);
    }

    /**
     * @param array $response
     * @return array|null
     */
    public function format(array $response): ?array
    {
        if (isset($response['error_code']) || isset($response['code'])) {
            $message = $response['error_msg'] ?? $response['message'] ?? 'Unknown error';
            throw new RuntimeException($message);
        }

        $content = $response['choices'][0]['message']['content'] ?? '';
        if (empty($content)) {
            return null;
        }
        return $content;
    }
}
