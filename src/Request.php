<?php

namespace Moyuuuuuuuu\Nutrition;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Opis\JsonSchema\Validator;
use RuntimeException;

class Request
{
    /**
     * @var Client
     */
    protected $client;


    public function __construct()
    {
        $this->client = new Client([
            'verify' => false
        ]);
    }


    public function do(string $imageUrl)
    {
        if (empty($imageUrl)) {
            return null;
        }
        $content = [
            [
                'text' => file_get_contents(__DIR__ . '/template'),
                'type' => 'text',
            ],
            [
                'type'      => 'image_url',
                'image_url' => [
                    'url' => base64_encode(file_get_contents($imageUrl)),
                ],
            ]
        ];
        $payload = [
            'model'         => 'ernie-4.5-turbo-vl-latest',
            'messages'      => [
                [
                    'content' => $content,
                    'role'    => 'user',
                ]
            ],
            'fps'           => 2,
            'temperature'   => 0.2,
            'top_p'         => 0.8,
            'penalty_score' => 1,
            'stop'          => [],
            'use_audio'     => true,
            'compression'   => true
        ];
        try {
            $response = $this->client->post('https://qianfan.baidubce.com/v2/chat/completions', [
                'json'    => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . getenv('API_KEY'),
                    'Content-Type'  => 'application/json',
                ]
            ]);
        } catch (GuzzleException $e) {
            $error = $e->getMessage();
            var_dump($error);
            exit;
        }
        return $this->format(json_decode($response->getBody()->getContents(), true));
    }

    public function format(array $resp)
    {
        return $resp;
// 3. 提取模型输出的 JSON 字符串
        $modelJson = null;
        foreach ($resp['output'] as $item) {
            if ($item['type'] === 'message') {
                $modelJson = $item['content'][0]['text'] ?? null;
                break;
            }
        }

        if (!$modelJson) {
            throw new RuntimeException('No model output found');
        }

// 4. 二次 decode（模型真正输出）
        $data = json_decode($modelJson);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Model output is not valid JSON');
        }

// 5. 加载 JSON Schema（关键）
        $schemaJson = file_get_contents(__DIR__ . '/storage/schema/nutrition.schema.json');
        $schema     = json_decode($schemaJson);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Schema JSON invalid');
        }

// 6. 校验
        $validator = new Validator();
        $result    = $validator->validate($data, $schema);

        if (!$result->isValid()) {
            foreach ($result->error() as $error) {
                echo sprintf(
                    "Path: %s | Keyword: %s\n",
                    $error->dataPointer(),
                    $error->keyword()
                );
            }
            throw new RuntimeException('Schema validation failed');
        }
        return $data;
    }
}
