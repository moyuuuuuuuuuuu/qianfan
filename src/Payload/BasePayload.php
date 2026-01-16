<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

abstract class BasePayload implements PayloadInterface
{
    protected $path;

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }
}
