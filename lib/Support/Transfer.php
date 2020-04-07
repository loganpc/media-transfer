<?php

namespace Loganpc\FileUpload\Support;

class Transfer
{
    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct()
    {
    }

    /**
     * request请求（GET || POST）
     * @param string $url 请求的url
     * @param array $data 请求传输的数据
     * @param string $method 请求的方法：GET || POST
     * @return boolean 返回运行结果
     */
    public static function request($url, $data = array(), $method  = 'GET', $timeout = 10)
    {
        $ch = curl_init();
        $curlOptions = array(
            CURLOPT_URL				=>	$url,
            CURLOPT_CONNECTTIMEOUT	=>	1,
            CURLOPT_TIMEOUT			=>	$timeout,
            CURLOPT_RETURNTRANSFER	=>	true,
            CURLOPT_HEADER			=>	false,
            CURLOPT_FOLLOWLOCATION	=>	true,
            //CURLOPT_SSL_VERIFYPEER  => true, //默认开启HTTPS
        );

        if(false === strpos($url, 'https')) {
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
        }
        else {
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
        }

        if('POST' === $method)
        {
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        curl_setopt_array($ch, $curlOptions);
        //重试
        for($i = 0; $i <= 3; $i++)
        {
            $response = curl_exec($ch);
            $errno = curl_errno($ch);
            if(0 == $errno)
            {
                curl_close($ch);
                return $response;
            }
        }
        return false;
    }

    /**
     * http请求获取数据
     * @param $url
     * @param $param
     * @param $method
     * @return bool
     */
    public function sendRequest($url, $params = [], $method = 'GET', $headers = [], $bodys = '')
    {
        $strApiUrl = $url;
        $client    = new \GuzzleHttp\Client();

        if (empty($headers)) {
            $header = [];
        } else {
            $header = $headers;
        }

        if (empty($params)) {
            $param = [];
        } else {
            $param = $params;
        }

        if (empty($bodys)) {
            $body = '';
        } else {
            $body = $bodys;
        }
        $response = $client->request($method, $strApiUrl, [
            'headers' => $header,
            'form_params' => $param,
            'body' => $body,
        ]);

        if (200 != $response->getStatusCode()) {
            return false;
        }

        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);

        return $data;
    }
}
