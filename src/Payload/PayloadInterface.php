<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

interface PayloadInterface
{

    /**
     * 构建请求载荷
     * @return array
     */
    public function build(): array;

    public function formatResponse(array $data, callable $callable = null): array|\Exception;
}
