<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

interface PayloadInterface
{
    public function domain(): string;

    /**
     * 构建请求载荷
     * @return array
     */
    public function build(): array;

    public function formatResponse(array $data): array|\Exception;
}
