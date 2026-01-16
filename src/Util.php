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

    static function deepMerge(array $a, array $b): array
    {
        foreach ($b as $key => $value) {
            if (isset($a[$key]) && is_array($a[$key]) && is_array($value)) {
                if (array_is_list($a[$key]) && array_is_list($value)) {
                    $a[$key] = array_merge($a[$key], $value);
                } else {
                    $a[$key] = self::deepMerge($a[$key], $value);
                }
            } else {
                $a[$key] = $value;
            }
        }
        return $a;
    }

    static function arrayByDot(array &$data, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $ref  = &$data;

        foreach ($keys as $i => $key) {

            // 最后一个 key
            if ($i === count($keys) - 1) {
                $ref[$key] = $value;
                return;
            }

            // 中间节点：必须是数组
            if (!isset($ref[$key]) || !is_array($ref[$key])) {
                $ref[$key] = [];
            }

            $ref = &$ref[$key];
        }
    }
}
