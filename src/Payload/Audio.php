<?php

namespace Moyuuuuuuuu\Nutrition\Payload;

use RuntimeException;

class Audio extends BasePayload implements PayloadInterface
{

    protected string $speech;
    protected string $domain = 'http://vop.baidu.com';
    protected array  $audioOptions;

    /**
     * @param string $speech
     * @param array{
     *     speech:string,
     *     len:int,
     *     cuid:string,
     *     channel:integer,
     *     dev_pid:integer,
     *     rate:int
     * } $audioOptions
     */
    public function __construct(string $speech, array $audioOptions = [])
    {
        $this->speech       = $speech;
        $this->audioOptions = $audioOptions;
    }

    public function build(): array
    {
        return array_merge([
            'speech'  => $this->speech,
            'len'     => strlen($this->speech),
            'cuid'    => 'default',
            'channel' => 1,
            'dev_pid' => 1537,
            'rate'    => 16000
        ], $this->audioOptions);
    }

    public function formatResponse(array $data, callable $callable = null): array|\Exception
    {
        if (isset($data['error_no']) && $data['error_no'] !== 0) {
            throw new RuntimeException($data['err_msg'], $data['error_no']);
        }
        return $data;
    }
}
