<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

abstract class BasePayload implements PayloadInterface
{
    protected string $domain;
    protected string $path;

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
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
