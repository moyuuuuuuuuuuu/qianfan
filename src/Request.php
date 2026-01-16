<?php

namespace Moyuuuuuuuu\Nutrition;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Moyuuuuuuuu\Nutrition\Payload\PayloadInterface;

class Request
{
    protected Client $client;
    protected        $contentType;

    public function __construct(string $baseUri, string $apiKey, string $contentType = 'application/json')
    {
        $this->client      = new Client([
            'base_uri' => $baseUri,
            'verify'   => false,
            'headers'  => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => $contentType,
            ],
        ]);
        $this->contentType = $contentType;
    }

    /**
     * @param string $path
     * @param PayloadInterface $payload
     * @param AbstractApi $api
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(PayloadInterface $payload)
    {
        try {
            $response = $this->client->post($payload->getPath(), [
                $this->getKey() => $payload->build(),
            ]);
            $data     = json_decode($response->getBody()->getContents(), true);
            return $payload->formatResponse($data ?? []);
        } catch (ClientException $e) {
            throw new \RuntimeException($e->getResponse()->getBody()->getContents());
        } finally {
            throw new \RuntimeException($e->getMessage());
        }
    }

    private function getKey(): string
    {
        return match ($this->contentType) {
            'application/json' => 'json',
            'application/x-www-form-urlencoded' => 'form_params',
            'multipart/form-data' => 'multipart',
            default => 'body',
        };
    }
}
