<?php

namespace Loganpc\FileUpload\Support;

use ArrayAccess;
use Loganpc\FileUpload\Exceptions\InvalidArgumentException;

class Config implements ArrayAccess
{
    /**
     * @var array
     */
    public $config;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $default_config = include(dirname(dirname(__FILE__)).'/Config/fileUpload.php');
        $this->config = array_merge($default_config, $config);
    }

    /**
     * get a config.
     *
     * @author Logan
     *
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        $config = $this->config;

        if (is_null($key)) {
            return $config;
        }

        if (isset($config[$key])) {
            return $config[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * set a config.
     *
     * @author Logan
     *
     * @param string $key
     * @param array  $value
     *
     * @return array
     */
    public function set($key, $value)
    {
        if ($key == '') {
            throw new InvalidArgumentException('Invalid config key.');
        }

        // 只支持三维数组，多余无意义
        $keys = explode('.', $key);
        switch (count($keys)) {
            case '1':
                $this->config[$key] = $value;
                break;
            case '2':
                $this->config[$keys[0]][$keys[1]] = $value;
                break;
            case '3':
                $this->config[$keys[0]][$keys[1]][$keys[2]] = $value;
                break;

            default:
                throw new InvalidArgumentException('Invalid config key.');
        }

        return $this->config;
    }

    /**
     * [offsetExists description].
     *
     * @author Logan
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    /**
     * [offsetGet description].
     *
     * @author Logan
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * [offsetSet description].
     *
     * @author Logan
     *
     * @param string $offset
     * @param string $value
     *
     * @return array
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * [offsetUnset description].
     *
     * @author Logan
     *
     * @param string $offset
     *
     * @return array
     */
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }
}
