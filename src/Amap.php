<?php
/**
 * Created by PhpStorm.
 * User: li
 * Date: 2017/12/7
 * Time: 14:24
 */

namespace Reprover\Amap;


use Reprover\Amap\Contracts\GatewayInterface;
use Reprover\Amap\Exceptions\InvalidArgumentException;
use Reprover\Amap\Support\ClassmapConvert;

class Amap
{

    protected $driver;
    protected $gateways;
    protected $base_config;

    public function __construct($base_config=[])
    {
        $this->base_config = $base_config;
    }

    /**
     * @param $driver
     * @return $this
     */
    public function uses($driver)
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @param string $gateway
     * @return Amap
     * @throws InvalidArgumentException
     */
    public function gateway($gateway)
    {
        if (!$this->driver) {
            throw new InvalidArgumentException("You Must Call Method {use} First.");
        }
        if (!file_exists(__DIR__ . '/Gateways/' . ClassmapConvert::toCamelCase($this->driver) . '/' . ClassmapConvert::toCamelCase($gateway) . 'Gateway.php')) {
            throw new InvalidArgumentException("Gateway [$gateway] is not supported.");
        }
        $this->gateways = ClassmapConvert::toCamelCase($gateway) . "Gateway";
        return $this;
    }

    /**
     * @param $config
     * @return GatewayInterface
     * @throws InvalidArgumentException
     */
    public function build($config)
    {
        if (!$this->driver)
            throw new InvalidArgumentException("You Must Call Method {use} First.");
        $ns = __NAMESPACE__ . "\\Gateways\\" . ClassmapConvert::toCamelCase($this->driver) . "\\";
        $classname = $ns . ($this->gateways ?: (ClassmapConvert::toCamelCase($this->driver) . "Gateway"));
        return new $classname($config, $this->base_config);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static)->{($name . "s")}(...$arguments);
    }

}