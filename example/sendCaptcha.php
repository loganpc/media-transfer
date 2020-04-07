<?php
//获取验证示例
use Loganpc\FileUpload\FileUpload;

$config = array(
    'env' => 'sandbox',
    'app_id' => '12000',
    'secret_key'  => '123456',
    'live_regionid' => 'xxx'
);
$param = [
    'title' => '13810332846',
    'description'  => '1',
    'file_name' => '1',
    'cover_url' => 'www.baidu.com',
];

$file = new FileUpload($config);
$result = $file->gateway('media')->createUploadVideo($param);

var_dump($result);
