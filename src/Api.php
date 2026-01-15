<?php

namespace Moyuuuuuuuu\Nutrition;

use GuzzleHttp\Client;

class Api
{

    static function query(string $id)
    {
        $response = (new Client([
            'verify'  => false,
            'timeout' => 30
        ]))->get('https://qianfan.baidubce.com/v2/chat/completions' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('API_KEY'),
            ],
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
        return $response;
    }

}
