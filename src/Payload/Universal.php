<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

class Universal extends BasePayload
{
    protected $payload = [];

    /**
     * @inheritDoc
     */
    public function build(): array
    {
        return $this->payload;
    }

    public function add($key, $value)
    {
        $this->payload[$key] = $value;
    }

}
