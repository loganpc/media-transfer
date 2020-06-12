<?php

namespace Loganpc\FileUpload\Gateways;

use Loganpc\FileUpload\Support\Config;
use Loganpc\FileUpload\Support\Sign;
use Loganpc\FileUpload\Support\Transfer;
use Loganpc\FileUpload\Exceptions\InvalidArgumentException;
use OSS\OssClient;

class FileGateway
{
    /**
     * @var array
     */
    protected $user_config;

    /**
     * api
     */
    protected $gateway_create_upload_video = '/openapi/media/create_upload_file';

    protected $gateway_create_sts_token = '/openapi/media/create_sts_token';

    protected $gateway_scan_image = '/openapi/media/scan_image';

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
            $apiUri = $this->user_config->get($this->user_config->get('env').'_uri');
            $this->user_config->set('uri', $apiUri);
        }
    }

    /**
     * create upload file
     *
     * @author Logan
     *
     * @return boolean
     */
    public function createUploadFile()
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_create_upload_video;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }

    public function getSTSToken(){
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //获取STSToken
        $url = $this->user_config->get('uri') . $this->gateway_create_sts_token;

        $trans = new Transfer();
        $stsResult = $trans->sendRequest($url, $sendRequest, 'POST');

        return $stsResult['data']['sts_info'];
    }

    /**
     * backend file upload
     *
     * @author Logan
     *
     * @param array $param['file', 'path']
     *
     * @return boolean/string
     */
    public function backendFileUpload(array $param = [])
    {
        $stsInfo = $this->getSTSToken();

        $accessKeyId     = $stsInfo['access_key_id'];
        $accessKeySecret = $stsInfo['access_key_secret'];
        $endpoint        = $stsInfo['endpoint'];
        $securityToken   = $stsInfo['security_token'];

        //解析文件
        $fileContent = $param['file'];
        $fileNames = explode('.', $fileContent['name']);
        $end       = array_pop($fileNames);

        $bucket   = $stsInfo['bucket'];
        $object   = $this->user_config->get('app_id').$param['path'].md5(time().'_'.$fileContent['name']).'.'.$end;
        $filePath = $fileContent['tmp_name'];

        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, $securityToken);
        $ossResult = $ossClient->uploadFile($bucket, $object, $filePath);

        if (!isset($ossResult['info']['url'])) {
            return false;
        }

        return $ossResult['info']['url'];
    }

    /**
     * scan_image
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function scanImage(array $param = []){
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'url' => $param['url'],
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_scan_image;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }
}
