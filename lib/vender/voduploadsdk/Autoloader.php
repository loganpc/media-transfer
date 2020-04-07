<?php

function VodClassLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'uploader'. DIRECTORY_SEPARATOR . $path . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('VodClassLoader');

require_once  __DIR__ . DIRECTORY_SEPARATOR . 'aliyun-php-sdk-core' . DIRECTORY_SEPARATOR . 'Config.php';
require_once  __DIR__ . DIRECTORY_SEPARATOR . 'aliyun-php-sdk-oss' .DIRECTORY_SEPARATOR . 'autoload.php';

