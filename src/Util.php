<?php

namespace Moyuuuuuuuu\Nutrition;

use RuntimeException;

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

    static function baseFile(string $filePath, string $mimeType = null): string
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException("File not found or not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException("Failed to read file: {$filePath}");
        }

        if ($mimeType === null) {
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($filePath);

            if ($mimeType === false) {
                throw new RuntimeException("Unable to detect MIME type: {$filePath}");
            }
        }

        $base64 = base64_encode($content);

        return "data:{$mimeType};base64,{$base64}";
    }
}
