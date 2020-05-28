<?php

namespace Loganpc\FileUpload\Gateways;

use Loganpc\FileUpload\Support\Config;
use Green\Request\V20180509 as Green;
require_once  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vender' . DIRECTORY_SEPARATOR .
    'voduploadsdk' . DIRECTORY_SEPARATOR . 'aliyun-php-sdk-core' . DIRECTORY_SEPARATOR . 'Config.php';

class TextGateway
{
    /**
     * @var array
     */
    protected $user_config;

    /**
     * [__construct description].
     *
     * @author Logan
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->user_config = new Config($config);
        if ($this->user_config->get('env')){
            $regionId = $this->user_config->get($this->user_config->get('env').'_regionid');
            $accessKeyId = $this->user_config->get($this->user_config->get('env').'_accesskeyid');
            $accessKeySecret = $this->user_config->get($this->user_config->get('env').'_accesskeysecret');

            $this->user_config->set('regionid', $regionId);
            $this->user_config->set('accesskeyid', $accessKeyId);
            $this->user_config->set('accesskeysecret', $accessKeySecret);
        }
    }

    /**
     * text identify
     *
     * @author Logan
     *
     * @param string $param 'text'
     *
     * @return array
     */
    public function textIdentify($text = '')
    {
        $iClientProfile = \DefaultProfile::getProfile(
            $this->user_config->get('regionid'),
            $this->user_config->get('accesskeyid'),
            $this->user_config->get('accesskeysecret')
        );
        \DefaultProfile::addEndpoint(
            $this->user_config->get('regionid'),
            $this->user_config->get('regionid'),
            "Green",
            "green.cn-shanghai.aliyuncs.com"
        );
        $client  = new \DefaultAcsClient($iClientProfile);
        $request = new Green\TextScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");
        $task1 = array('dataId' => uniqid(),
            'content'               => $text,
        );
        $request->setContent(json_encode(array("tasks" => array($task1),
            "scenes"                                       => array("antispam"))));
        try {
            $response = $client->getAcsResponse($request);
            if (200 == $response->code) {
                $taskResults = $response->data;
                foreach ($taskResults as $taskResult) {
                    if (200 == $taskResult->code) {
                        $sceneResults = $taskResult->results;
                        foreach ($sceneResults as $sceneResult) {
                            $label      = $sceneResult->label;
                            $suggestion = $sceneResult->suggestion;
                            if ($label != "normal" || $suggestion != "pass") {
                                $detail  = $sceneResult->details;
                                $context = $detail[0]->label;
                                return ["status" => false, "content" => $context, 'code' => $taskResult->code]; //文本不合法
                            }
                            return ["status" => true, "content" => $taskResult->content, 'code' => $taskResult->code]; //文本正常
                        }
                    } else {
                        return ["status" => false, "content" => '', 'code' => $taskResult->code];
                    }
                }
            } else {
                return ["status" => false, "content" => '', 'code' => $response->code];
            }
        } catch (\Exception $e) {
            return ["status" => false, "content" => '', 'code' => 500];
        }
    }
}
