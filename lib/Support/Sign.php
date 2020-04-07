<?php

namespace Loganpc\FileUpload\Support;

class Sign
{
    /**
     * @var array
     */
    protected $user_config;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->user_config = $config;
    }

    /**
     * sign加密
     * @param array $data 请求传输的数据
     * @return string 返回加密结果
     */
    public function makeSign($data = array())
    {
        if (empty($data)) {
            return '';
        }

        ksort($data);
        $a = [];
        foreach ($data as $kreq => $vreq) {
            $a[] = $kreq.'='.$vreq;
        }
        $reqStr   = implode('&', $a);
        $arrToStr = $reqStr.'&key='. $this->user_config['secret_key'];
        $sign     = strtoupper(hash_hmac('sha256', $arrToStr, $this->user_config['secret_key']));

        return $sign;
    }
}
