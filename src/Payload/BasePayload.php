<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

abstract class BasePayload implements PayloadInterface
{
    protected string $domain = '';
    protected string $uri    = '';

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;
        return $this;
    }

    public function domain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function formatResponse(array $data, callable $callable = null): array|\Exception
    {
        if ($callable === null) {
            return $data;
        }
        return $callable($data);
    }
}
