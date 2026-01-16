<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

use Moyuuuuuuuu\Nutrition\Contants\RequestMethod;
use Moyuuuuuuuu\Nutrition\Contants\Role;

class BasePayload
{
    // 存储所有请求参数
    private array $params = [];
    // 接口URI
    private string $uri = '';
    // 接口域名（支持多平台/多环境）
    private string $domain = '';
    // 自定义请求头（覆盖Content-Type等）
    private array $headers = [];
    // 请求方法（默认POST，支持动态修改）
    private string $method = 'POST';

    private array $multipart = [];

    /**
     * 通用参数添加（适配所有接口，支持数组路径）
     * @param string $key 支持"xxx.0.xxx"格式的嵌套键，如"audio.format"
     * @param mixed $value 参数值
     * @return $this
     */
    public function add(string $key, mixed $value): self
    {
        $keys    = explode('.', $key);
        $current = &$this->params;

        // 递归设置嵌套数组参数，避免索引越界/键名错误
        foreach ($keys as $k) {
            if (!isset($current[$k]) && !is_array($current)) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        $current = $value;
        return $this;
    }

    /**
     * 聊天场景便捷封装（仅适配有role/message的接口）
     * @param Role $role 角色（user/system/assistant）
     * @param string|array $content 消息内容
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addMessage(Role $role, string|array $content): self
    {
        // 复用add方法，统一参数处理逻辑
        $messageIndex = count($this->params['messages'] ?? []);
        $this->add("messages.{$messageIndex}.role", $role->value)
            ->add("messages.{$messageIndex}.content", $content);
        return $this;
    }

    /**
     * 设置请求方法（GET/POST/PUT等）
     * @param RequestMethod $method
     * @return $this
     */
    public function setMethod(RequestMethod $method): self
    {
        $this->method = $method->value;
        return $this;
    }

    /**
     * 设置自定义请求头（如Content-Type: multipart/form-data）
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function setHeader(string $key, mixed $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * 快速设置Content-Type
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType): self
    {
        $this->headers['Content-Type'] = $contentType;
        return $this;
    }

    /**
     * 设置接口URI
     * @param string $uri
     * @return $this
     */
    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * 设置接口域名
     * @param string $domain
     * @return $this
     */
    public function setDomain(string $domain): self
    {
        $this->domain = rtrim($domain, '/');
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHeaders(): array
    {
        // 默认Content-Type，可被自定义覆盖
        return array_merge(['Content-Type' => 'application/json'], $this->headers);
    }


    /**
     * 根据Content-Type构建请求体
     * @param array $params 请求参数
     * @param string $contentType 内容类型
     * @return string|resource|null
     * @throws \RuntimeException
     */
    public function buildBody(array $params)
    {
        // 重置 multipart 属性
        $this->multipart = [];
        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = 'application/json';
        }
        switch ($this->headers['Content-Type']) {
            // JSON格式（默认）
            case 'application/json':
                $json = json_encode($params, JSON_UNESCAPED_UNICODE);
                if ($json === false) {
                    throw new \RuntimeException(
                        'JSON encode failed: ' . json_last_error_msg()
                    );
                }
                return $json;

            // 表单格式（x-www-form-urlencoded）
            case 'application/x-www-form-urlencoded':
                return http_build_query($params);

            // 文件上传（multipart/form-data）
            case 'multipart/form-data':
                foreach ($params as $name => $value) {
                    // 如果是文件路径（支持 @file 或直接文件资源）
                    if (is_string($value) && str_starts_with($value, '@')) {
                        $filePath = ltrim($value, '@');
                        if (!file_exists($filePath) || !is_readable($filePath)) {
                            throw new \RuntimeException("文件不存在或不可读：{$filePath}");
                        }
                        $this->multipart[] = [
                            'name'     => $name,
                            'contents' => fopen($filePath, 'r'),
                            'filename' => basename($filePath)
                        ];
                    } // 如果是文件资源
                    elseif (is_resource($value)) {
                        $meta              = stream_get_meta_data($value);
                        $this->multipart[] = [
                            'name'     => $name,
                            'contents' => $value,
                            'filename' => basename($meta['uri'] ?? 'file')
                        ];
                    } // 如果是数组或对象（比如 messages 字段）
                    elseif (is_array($value) || is_object($value)) {
                        $this->multipart[] = [
                            'name'     => $name,
                            'contents' => json_encode($value, JSON_UNESCAPED_UNICODE)
                        ];
                    } // 普通文本
                    else {
                        $this->multipart[] = [
                            'name'     => $name,
                            'contents' => (string)$value
                        ];
                    }
                }

                // 用 MultipartStream 生成请求体
                $boundary = bin2hex(random_bytes(16));
                $stream   = new \GuzzleHttp\Psr7\MultipartStream($this->multipart, $boundary);
                // 返回数组，方便直接构造 Request
                $this->headers['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;
                return $stream;

            // GET/无请求体场景
            default:
                return null;
        }
    }

}
