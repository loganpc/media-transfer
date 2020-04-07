<?php

namespace Loganpc\FileUpload\Exceptions;

class GatewayException extends Exception
{
    /**
     * error raw data.
     *
     * @var array
     */
    public $raw = [];

    /**
     * [__construct description].
     *
     * @author pengcheng123 <185560317@qq.com>
     *
     * @param string     $message
     * @param string|int $code
     */
    public function __construct($message, $code, $raw = [])
    {
        parent::__construct($message, intval($code));

        $this->raw = $raw;
    }
}
