<?php

namespace Moyuuuuuuuu\Nutrition;

class Util
{
    /**
     * @desc 从模型结果中解析出营养信息
     * @param string $content
     * @return array|null
     */
    static function parseNutrition(array $content)
    {

        if (empty($content)) {
            return [];
        }
        preg_match('/```(?:json)?\R([\s\S]*?)\R```/i', $content, $match);
        $jsonStr = $match[1] ?? $content;

        $result = json_decode(trim($jsonStr), true);

        return is_array($result) ? $result : [];
    }
}
