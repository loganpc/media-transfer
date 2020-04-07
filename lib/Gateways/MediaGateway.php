<?php

namespace Loganpc\FileUpload\Gateways;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Vod\Vod;
use Loganpc\FileUpload\Exceptions\Exception;
use Loganpc\FileUpload\Support\Config;
use Loganpc\FileUpload\Support\Sign;
use Loganpc\FileUpload\Support\Transfer;
use Loganpc\FileUpload\Exceptions\InvalidArgumentException;
use vod\Request\V20170321\GetPlayInfoRequest;
use vod\Request\V20170321\GetVideoPlayAuthRequest;

class MediaGateway
{
    /**
     * @var array
     */
    protected $user_config;

    /**
     * api
     */
    protected $gateway_create_upload_video = '/openapi/media/create_upload_video';

    protected $gateway_refresh_upload_video = '/openapi/media/refresh_upload_video';

    protected $gateway_query_media_info_by_video_id = '/openapi/media/query_media_info_by_video_id';

    protected $gateway_get_play_info = '/openapi/media/get_play_info';

    protected $gateway_get_play_auth = '/openapi/media/get_play_auth';

    protected $gateway_register_media = '/openapi/media/register_media';

    protected $gateway_submit_audit_media = '/openapi/media/submit_audit_media';

    protected $gateway_query_audit_media = '/openapi/media/query_audit_media';

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
            $regionId = $this->user_config->get($this->user_config->get('env').'_regionid');
            $accessKeyId = $this->user_config->get($this->user_config->get('env').'_accesskeyid');
            $accessKeySecret = $this->user_config->get($this->user_config->get('env').'_accesskeysecret');
            $tmpId = $this->user_config->get($this->user_config->get('env').'_tmpid');

            $this->user_config->set('uri', $apiUri);
            $this->user_config->set('regionid', $regionId);
            $this->user_config->set('accesskeyid', $accessKeyId);
            $this->user_config->set('accesskeysecret', $accessKeySecret);
            $this->user_config->set('tmpid', $tmpId);
        }
    }

    /**
     * backend video upload
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function backendVideoUpload(array $param = [])
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vender' . DIRECTORY_SEPARATOR . 'voduploadsdk' . DIRECTORY_SEPARATOR . 'Autoloader.php';
        include_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vender' . DIRECTORY_SEPARATOR . 'voduploadsdk' . DIRECTORY_SEPARATOR . 'uploader' . DIRECTORY_SEPARATOR . 'AliyunVodUploader.php');
        include_once(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vender' . DIRECTORY_SEPARATOR . 'voduploadsdk' . DIRECTORY_SEPARATOR . 'uploader' . DIRECTORY_SEPARATOR . 'UploadVideoRequest.php');

        date_default_timezone_set('PRC');

        $uploader = new \AliyunVodUploader($this->user_config->get('accesskeyid'), $this->user_config->get('accesskeysecret'));
        $uploadVideoRequest = new \UploadVideoRequest($param['file_path'], $param['title']);
        $uploadVideoRequest->setTemplateGroupId($this->user_config->get('tmpid'));
        $videoId =  $uploader->uploadLocalVideo($uploadVideoRequest);

        //同步至视频服务
        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'video_id'  => $videoId,
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_register_media;

        $trans = new Transfer();
        $result = $trans->sendRequest($url, $sendRequest, 'POST');
        if ($result['status'] != 200) {
            return false;
        }
        return $videoId;
    }

    /**
     * backend get video play info
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function backendGetPlayInfo(array $param = []){
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        try{
            include(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vender' . DIRECTORY_SEPARATOR . 'voduploadsdk' . DIRECTORY_SEPARATOR . 'aliyun-php-sdk-core' . DIRECTORY_SEPARATOR . 'Config.php');

            $profile = \DefaultProfile::getProfile($this->user_config->get('regionid'), $this->user_config->get('accesskeyid'), $this->user_config->get('accesskeysecret'));
            $client = new \DefaultAcsClient($profile);

            $request = new GetPlayInfoRequest();
            $request->setVideoId($param['video_id']);
            $request->setAuthTimeout(3600*24);
            $request->setAcceptFormat('JSON');
            $request->setOutputType('cnd');
            $res = $client->getAcsResponse($request);

            $result = [
                'Status' => $res->VideoBase->status,
                'playList' => $res->PlayInfoList->PlayInfo,
            ];
        }catch (Exception $e){
            $result = [
                'Status' => 'UploadFail',
                'playList' => [],
            ];
        }
        return $result;
    }

    /**
     * backend get video play auth
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function backendGetPlayAuth(array $param = []){
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        try{
            include(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vender' . DIRECTORY_SEPARATOR . 'voduploadsdk' . DIRECTORY_SEPARATOR . 'aliyun-php-sdk-core' . DIRECTORY_SEPARATOR . 'Config.php');

            $profile = \DefaultProfile::getProfile($this->user_config->get('regionid'), $this->user_config->get('accesskeyid'), $this->user_config->get('accesskeysecret'));
            $client = new \DefaultAcsClient($profile);

            $request = new GetVideoPlayAuthRequest();
            $request->setVideoId($param['video_id']);
            $request->setAuthInfoTimeout(3600*24);
            $request->setAcceptFormat('JSON');
            $res = $client->getAcsResponse($request);

            $result = [
                'PlayAuth' => $res->PlayAuth,
            ];
        }catch (Exception $e){
            $result = [
                'PlayAuth' => '',
            ];
        }
        return $result;
    }

    //===============================================================================================================//


    /**
     * create upload video
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function createUploadVideo(array $param = [])
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'file_name'  => $param['file_name'],
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_create_upload_video;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }

    /**
     * refresh upload video
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function refreshUploadVideo(array $param = [])
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'video_id' => $param['video_id'],
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_refresh_upload_video;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }

    /**
     * query_media_info_by_video_id
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function queryMediaInfoByVideoId(array $param = [])
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'video_id' => $param['video_id'],
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_query_media_info_by_video_id;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }

    /**
     * get_play_info
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function getPlayInfo(array $param = [])
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'video_id' => $param['video_id'],
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_get_play_info;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }

    /**
     * get_play_auth
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function getPlayAuth(array $param = [])
    {
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'video_id' => $param['video_id'],
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_get_play_auth;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }

    /**
     * submit_audit_media
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function submitAuditMedia(array $param = []){
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'video_id' => $param['video_id'],
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_submit_audit_media;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }

    /**
     * query_audit_media
     *
     * @author Logan
     *
     * @param array $param
     *
     * @return boolean
     */
    public function queryAuditMedia(array $param = []){
        if (is_null($this->user_config->get('app_id'))) {
            throw new InvalidArgumentException('Missing Config -- [app_id]');
        }

        $sendRequest = [
            'app_id'     => $this->user_config->get('app_id'),
            'video_id' => $param['video_id'],
        ];
        $sign = new Sign($this->user_config->config);
        $sendRequest['sign'] = $sign->makeSign($sendRequest);

        //请求地址
        $url = $this->user_config->get('uri') . $this->gateway_query_audit_media;

        $trans = new Transfer();
        return $trans->sendRequest($url, $sendRequest, 'POST');
    }
}
