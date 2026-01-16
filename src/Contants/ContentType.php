<?php

namespace Moyuuuuuuuu\QianFan\Contants;

enum ContentType: string
{
    /**
     * 文本
     * @var string
     */
    case TEXT = 'text';

    /**
     * 图片
     */
    case IMAGE_URL = 'image_url';

    /**
     * 视频
     */
    case VIDEO_URL = 'video_url';
}
