# file-extend
文件管理模块分文档文件和视频文件，根据不同网关进行不同文件的处理操作


**一、安装引用**

1、composer.json文件中添加

    {
        "require": {
            "loganpc/file-extend": "^1.0.0"
        }
    }
2、执行 cmoposer update




**二、功能说明**

文档文件

`1、 createUploadFile //获取文件上传凭证`

    use Loganpc\FileUpload\FileUpload;
    //配置信息
    $config = array(
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key'  => '123456',
    );
    //接口参数
    $param = [];
    $file = new FileUpload($config);
    $result = $file->gateway('file')->createUploadFile($param);
    
    var_dump($result);

`2、 backendFileUpload  //文档文件后端直接上传`
    
    use Loganpc\FileUpload\FileUpload;
    //配置信息
    $config = array(
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key'  => '123456',
    );
    //接口参数
    $param = [
        'path' => '/your bucket file path/',
        'file' => [
            'name' => 'your file name',
            'tmp_name' => 'your_file_path',
        ],
    ];
    $file = new FileUpload($config);
    $result = $file->gateway('file')->backendFileUpload($param);
    
    var_dump($result);

视频文件
    
`1、 createUploadVideo  //获取视频上传凭证`
    
    use Loganpc\FileUpload\FileUpload;
    //配置信息
    $config = array(
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key'  => '123456',
    );
    //接口参数
    $param = [
        'file_name' => '1.mp4',
    ];
    $file = new FileUpload($config);
    $result = $file->gateway('media')->createUploadVideo($param);
    
    var_dump($result);
    
`2、 refreshUploadVideo  //更新视频上传凭证`
    
    use Loganpc\FileUpload\FileUpload;
    //配置信息
    $config = array(
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key'  => '123456',
    );
    //接口参数
    $param = [
        'vedio_id' => '1183',
    ];
    $file = new FileUpload($config);
    $result = $file->gateway('media')->refreshUploadVideo($param);
    
    var_dump($result);
    
`3、 queryMediaInfoByVideoId  //视频转码状态查询`

    use Loganpc\FileUpload\FileUpload;
    //配置信息
    $config = array(
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key'  => '123456',
    );
    //接口参数
    $param = [
        'vedio_id' => '1183',
    ];
    $file = new FileUpload($config);
    $result = $file->gateway('media')->queryMediaInfoByVideoId($param);
    
    var_dump($result);

`4、 getPlayInfo  //获取播放地址`

    use Loganpc\FileUpload\FileUpload;
    //配置信息
    $config = array(
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key'  => '123456',
    );
    //接口参数
    $param = [
        'vedio_id' => '1183',
    ];
    $file = new FileUpload($config);
    $result = $file->gateway('media')->getPlayInfo($param);
    
    var_dump($result);

`5、 getPlayAuth  //获取播放凭证`

    use Loganpc\FileUpload\FileUpload;
    //配置信息
    $config = array(
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key'  => '123456',
    );
    //接口参数
    $param = [
        'vedio_id' => '1183',
    ];
    $file = new FileUpload($config);
    $result = $file->gateway('media')->getPlayAuth($param);
    
    var_dump($result);
    
`6、 backendVideoUpload  //后端上传阿里视频点播系统`

    use Loganpc\FileUpload\FileUpload;

    $file = $_FILES['file'];
    
    $filePath = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $file['name'];

    move_uploaded_file($file["tmp_name"], $filePath);

    $config = [
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key' => '123456',
    ];
    $param  = [
        'title' => '13810332846',
        'file_path' => $filePath,
    ];
    $file   = new FileUpload($config);
    $result = $file->gateway('media')->backendVideoUpload($param);
    dd($result);

`7、 backendGetPlayInfo  //后端获取阿里视频点播内容`

    use Loganpc\FileUpload\FileUpload;

    $file = $_FILES['file'];
    
    $filePath = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $file['name'];

    move_uploaded_file($file["tmp_name"], $filePath);

    $config = [
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key' => '123456',
    ];
    $param  = [
        'video_id' => 'xxxxx',
    ];
    $file   = new FileUpload($config);
    $result = $file->gateway('media')->backendGetPlayInfo($param);
    dd($result);
    
`8、 backendGetPlayAuth  //后端获取阿里视频点播播放权限`

    use Loganpc\FileUpload\FileUpload;

    $file = $_FILES['file'];
    
    $filePath = base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $file['name'];

    move_uploaded_file($file["tmp_name"], $filePath);

    $config = [
        'env' => 'sandbox',
        'app_id' => '12000',
        'secret_key' => '123456',
    ];
    $param  = [
        'video_id' => 'xxxxx',
    ];
    $file   = new FileUpload($config);
    $result = $file->gateway('media')->backendGetPlayAuth($param);
    dd($result);