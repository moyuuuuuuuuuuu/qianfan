<?php

namespace Moyuuuuuuuu\QianFan;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\{RequestException,GuzzleException};
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Psr\Http\Message\ResponseInterface;

class Request
{
    private Client $client;
    private string $apiKey;
    private int    $timeout = 120;

    /**
     * 初始化请求客户端
     * @param string $apiKey API密钥
     * @param array $options 客户端配置（超时、重试等）
     */
    public function __construct(string $apiKey, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->client = new Client(array_merge($options, ['timeout' => $this->timeout, 'verify' => false]));
    }

    /**
     * 通用发送方法（适配所有请求类型）
     * @param Payload\Universal $payload 请求载荷
     * @return array
     * @throws \RuntimeException
     */
    public function send(Payload\BasePayload $payload): array
    {
        $uri         = $payload->getDomain() . '/' . ltrim($payload->getUri(), '/');
        $method      = $payload->getMethod();
        $headers     = $this->buildHeaders($payload->getHeaders());
        $body        = $payload->buildBody($payload->getParams());

        try {
            // 发送请求
            $guzzleRequest = new GuzzleRequest($method, $uri, $headers, $body);
            $response      = $this->client->send($guzzleRequest);

            // 记录请求日志（可选）
            $this->logRequest($method, $uri, $payload->getParams(), $response->getStatusCode());

            return $this->parseResponse($response);
        } catch (RequestException $e) {
            throw new \RuntimeException($e->getResponse()->getBody()->getContents());
        } catch (GuzzleException $e) {
            throw new \RuntimeException(
                "请求失败[{$method} {$uri}]：{$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * 构建请求头（添加API密钥等通用头）
     * @param array $payloadHeaders Payload自定义头
     * @return array
     */
    private function buildHeaders(array $payloadHeaders): array
    {
        $baseHeaders = [
            'Authorization' => "Bearer {$this->apiKey}",
        ];
        // 自定义头覆盖默认头
        return array_merge($baseHeaders, $payloadHeaders);
    }



    /**
     * 解析响应结果
     * @param ResponseInterface $response
     * @return array
     * @throws \RuntimeException
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $body        = (string)$response->getBody();
        $contentType = $response->getHeaderLine('Content-Type');

        // 优先解析JSON
        if (str_contains($contentType, 'application/json')) {
            $result = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("响应JSON解析失败：{$body}");
            }
            return $result;
        }
        // 非JSON返回原始数据
        return ['raw' => $body, 'status_code' => $response->getStatusCode()];
    }

    /**
     * 记录请求日志（可选，可根据需求调整存储方式）
     * @param string $method 请求方法
     * @param string $uri 请求地址
     * @param array $params 请求参数
     * @param int $statusCode 响应状态码
     */
    private function logRequest(string $method, string $uri, array $params, int $statusCode): void
    {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $log = sprintf(
            "[%s] %s %s | 状态码：%d | 参数：%s\n",
            date('Y-m-d H:i:s'),
            $method,
            $uri,
            $statusCode,
            json_encode($params, JSON_UNESCAPED_UNICODE)
        );
        file_put_contents($logDir . '/request.log', $log, FILE_APPEND);
    }
}
