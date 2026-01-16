<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

use Moyuuuuuuuu\Nutrition\Util;

class Universal extends BasePayload
{
    protected string $domain  = 'https://qianfan.baidubce.com';
    protected        $payload = [];

    /**
     * @inheritDoc
     */
    public function build(): array
    {
        return $this->payload;
    }

    public function add($key, $value)
    {
        if (!str_contains($key, '.')) {
            $this->payload[$key] = $value;
            return $this;
        }

        Util::arrayByDot($this->payload, $key, $value);
        return $this;
    }

}
