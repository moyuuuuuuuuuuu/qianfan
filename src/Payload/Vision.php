<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

use Moyuuuuuuuu\Nutrition\Contants\ContentType;
use Moyuuuuuuuu\Nutrition\Contants\Role;
use Moyuuuuuuuu\Nutrition\Util;
use RuntimeException;

class Vision extends BasePayload implements PayloadInterface
{

    protected string $model;
    protected array  $options;
    protected array  $content = [];
    protected Role   $role;


    /**
     * @param string $model ,
     * @param $options array{
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
     * }
     */
    public function __construct(string $model, array $options = [])
    {
        $this->model   = $model;
        $this->options = $options;
    }

    public function domain(): string
    {
        return 'https://qianfan.baidubce.com';
    }

    public function build(): array
    {
        return array_merge([
            'model'    => $this->model,
            'messages' => [
                [
                    'role'    => $this->role->value ?? Role::USER->value,
                    'content' => $this->content,
                ]
            ],
        ], $this->options);
    }


    /**
     * 添加文本内容
     *
     * @param string $text
     * @return $this
     */
    public function addText(string $text): self
    {
        $typeText        = ContentType::TEXT->value;
        $this->content[] = [
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

        $imageData       = base64_encode(file_get_contents($imagePath));
        $typeText        = ContentType::IMAGE_URL->value;
        $this->content[] = [
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
        $videoData       = base64_encode(file_get_contents($videoPath));
        $typeText        = ContentType::VIDEO_URL->value;
        $this->content[] = [
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

    public function setRole(Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function formatResponse(array $data): array|\Exception
    {
        if (isset($data['error'])) {
            throw new \RuntimeException('接口错误：' . $data['error']['message'] ?? '未知错误');
        }
        if (isset($data['error_code'])) {
            throw new \RuntimeException('接口错误:' . $data['error_msg']);
        }

        if (!empty($data['choices'])) {
            return Util::parseNutrition($data['choices'][0]['message']['content']) ?? [];
        }
        throw new \RuntimeException('没有生成内容，choices 为空或格式异常。');
    }
}
