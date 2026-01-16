<?php

namespace Moyuuuuuuuu\Nutrition;

class Util
{
    /**
     * @desc 从模型结果中解析出营养信息
     * @param string $content
     * @return array|null
     */
    static function parseNutrition($content)
    {

        if (empty($content)) {
            return [];
        }
        preg_match('/```(?:json)?\R([\s\S]*?)\R```/i', $content, $match);
        $jsonStr = $match[1] ?? $content;

        $result = json_decode(trim($jsonStr), true);

        return is_array($result) ? $result : [];
    }

    static function baseFile(string $path)
    {
        if (!(str_contains($path, 'http://') || !str_contains($path, 'https://')) && !file_exists($path)) {
            throw new \Exception("{$path} does not exist");
        }
        return base64_encode(file_get_contents($path));
    }
}
