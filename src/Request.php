<?php

namespace Moyuuuuuuuu\Nutrition;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Moyuuuuuuuu\Nutrition\Contants\ContentType;
use Moyuuuuuuuu\Nutrition\Contants\Role;
use RuntimeException;

class Request
{
    private const DEFAULT_DOMAIN = 'https://qianfan.baidubce.com';
    private const DEFAULT_MODEL  = 'ernie-4.5-turbo-vl-latest';
    private const API_PATH       = '/v2/chat/completions';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    protected $contents = [];

    /**
     * @var string system user assistant
     */
    protected $role = Role::USER->value;

    /**
     * 使用的模型
     * @var string
     */
    protected $model;

    /**
     * 其他参数
     * @var array{
     *     stream_options:array{
     *         include_usage:bool,//流式响应是否输出usage
     *     },//流式响应的选项，说明：
     *     temperature:integer,//）较高的数值会使输出更加随机，而较低的数值会使其更加集中和确定 ,该参数支持模型及取值范围等，请参考千帆-模型默认参数说明
     *     top_p:integer,//影响输出文本的多样性，取值越大，生成文本的多样性越强
     *     penalty_score:float,//通过对已生成的token增加惩罚，减少重复生成的现象
     *     max_tokens:int,
     *     enable_thinking:bool,
     *     seed:integer,//取值范围: （0,2147483647‌），会由模型随机生成，默认值为空
     *     stop:string[],
     *     user:string,
     *     web_search:array{
     *         enable:bool,//是否开启实时搜索功能
     *         enable_citation:bool,//是否开启上角标返回
     *         enable_trace:bool,//是否返回搜索溯源信息
     *         enable_status:bool,//是否返回搜索信号
     *     },
     *     response_format:array{
     *         type:string,//指定响应内容的格式 json_object text json_schema
     *         json_schema:array,//json_schema格式，请参考 @link https://json-schema.org/understanding-json-schema/reference
     *     },
     *     metadata:array{
     *
     *     }
     * }
     */
    protected $options;

    public function __construct(?string $apiKey = null, string $domain = self::DEFAULT_DOMAIN, string $model = self::DEFAULT_MODEL)
    {
        $this->apiKey = $apiKey ?: (string)getenv('API_KEY');
        if (empty($this->apiKey)) {
            throw new RuntimeException('API Key is required');
        }

        $this->model = $model;

        $this->client = new Client([
            'base_uri' => $domain,
            'verify'   => false
        ]);
    }

    /**
     * 添加文本内容
     *
     * @param string $text
     * @return $this
     */
    public function addText(string $text): self
    {
        $typeText         = ContentType::TEXT->value;
        $this->contents[] = [
            'type'    => $typeText,
            $typeText => $text,
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

        $imageData        = base64_encode(file_get_contents($imagePath));
        $typeText         = ContentType::IMAGE_URL->value;
        $this->contents[] = [
            'type'    => $typeText,
            $typeText => ['url' => $imageData],
        ];
        return $this;
    }

    public function addVideo(string $videoPath): self
    {
        if (!file_exists($videoPath)) {
            throw new RuntimeException("Video file not found: {$videoPath}");
        }
        $videoData        = base64_encode(file_get_contents($videoPath));
        $typeText         = ContentType::VIDEO_URL->value;
        $this->contents[] = [
            'type'    => $typeText,
            $typeText => ['url' => $videoData],
        ];
        return $this;
    }

    /**
     *
     * @param string $content
     * @param ContentType $type
     * @return $this
     * @throws \Exception
     */
    public function addContent(string $content, ContentType $type): self
    {
        match ($type) {
            ContentType::TEXT => $this->addText($content),
            ContentType::IMAGE_URL => $this->addImage($content),
            ContentType::VIDEO_URL => $this->addVideo($content),
            default => throw new \Exception('不支持的消息类型')
        };
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

    public function setRole(Role $role = Role::USER): self
    {
        $this->role = $role->value;
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
    public function send(): ?array
    {
        if (empty($this->contents)) {
            throw new RuntimeException('No content available');
        }

        $payload = $this->buildPayload();

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
    protected function buildMessages(): array
    {
        return [
            [
                'role'    => $this->role,
                'content' => $this->contents,
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
            'messages' => $this->buildMessages($messages),
        ], $this->options);
    }

    /**
     * @param array $response
     * @return array|null
     */
    public function format(array $data): ?array
    {
        if (isset($data['error'])) {
            throw new \Exception('接口错误：' . $data['error']['message'] ?? '未知错误');
        }
        if (isset($data['error_code'])) {
            throw new \Exception('接口错误:' . $data['error_msg']);
        }

        if (!empty($data['choices']) && isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'] ?? [];
        }
        throw new \Exception('没有生成内容，choices 为空或格式异常。');
    }
}
