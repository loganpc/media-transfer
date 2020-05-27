<?php

namespace Green\Request\V20170823;

/**
 * @deprecated Please use https://github.com/aliyun/openapi-sdk-php
 *
 * Request of DescribeImageLib
 *
 * @method string getServiceModule()
 * @method string getSourceIp()
 */
class DescribeImageLibRequest extends \RpcAcsRequest
{

    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'Green',
            '2017-08-23',
            'DescribeImageLib',
            'green'
        );
    }

    /**
     * @param string $serviceModule
     *
     * @return $this
     */
    public function setServiceModule($serviceModule)
    {
        $this->requestParameters['ServiceModule'] = $serviceModule;
        $this->queryParameters['ServiceModule'] = $serviceModule;

        return $this;
    }

    /**
     * @param string $sourceIp
     *
     * @return $this
     */
    public function setSourceIp($sourceIp)
    {
        $this->requestParameters['SourceIp'] = $sourceIp;
        $this->queryParameters['SourceIp'] = $sourceIp;

        return $this;
    }
}
