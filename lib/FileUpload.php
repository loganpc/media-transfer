<?php

namespace Loganpc\FileUpload;

use Loganpc\FileUpload\Support\Config;
use Loganpc\FileUpload\Exceptions\InvalidArgumentException;

class FileUpload
{
    /**
     * @var \Loganpc\FileUpload\Support\Config
     */
    private $config;

    /**
     * @var object
     */
    private $gateways;

    /**
     * construct method.
     *
     * @author Logan
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = (new Config($config))->config;
    }

    /**
     * set passport's gateway.
     *
     * @author Logan
     *
     * @param string $gateway
     *
     * @return \Loganpc\FileUpload\Contracts\GatewayInterface
     */
    public function gateway($gateway = 'media')
    {
        $this->gateways = $this->createGateway($gateway);

        return $this->gateways;
    }

    /**
     * create passport's gateway.
     *
     * @author Logan
     *
     * @param string $gateway
     *
     * @return \Loganpc\FileUpload\Contracts\GatewayInterface
     */
    protected function createGateway($gateway)
    {
        if (!file_exists(__DIR__ . '/Gateways/' .ucfirst($gateway).'Gateway.php')) {
            throw new InvalidArgumentException("Gateway [$gateway] is not supported.");
        }

        $gateway = __NAMESPACE__.'\\Gateways\\'.ucfirst($gateway).'Gateway';

        return $this->build($gateway);
    }

    /**
     * build passport's gateway.
     *
     * @author Logan
     *
     * @param string $gateway
     *
     * @return \Loganpc\FileUpload\Contracts\GatewayInterface
     */
    protected function build($gateway)
    {
        return new $gateway($this->config);
    }
}

