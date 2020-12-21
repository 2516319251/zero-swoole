<?php

namespace zero;

/**
 * Class Config
 * @package zero
 */
class Config
{
    /**
     * @var array 配置信息
     */
    private $conf = [];

    /**
     * @var mixed 配置信息目录
     */
    public $confPath;

    /**
     * Config constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->confPath = $application->getConfigPath();
    }

    /**
     * 获取对应配置信息
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        // 如果已经加载过
        $arr = explode('.', $key);
        if (isset($this->conf[$arr[0]])) {
            return self::loopConfValue($this->conf[$arr[0]], $arr);
        }
        // 查找配置文件获取配置信息
        $file = str_replace('\\', '/', $this->confPath . $arr[0] . '.php');
        if (is_file($file)) {
            $conf = require_once $file;
            $this->conf[$arr[0]] = $conf;

            return $this->loopConfValue($conf, $arr);
        }
        return null;
    }

    /**
     * 循环获取配置信息
     * @param array $conf
     * @param array $array
     * @return mixed|null
     */
    private function loopConfValue(array $conf, array $array)
    {
        $tmp_conf = $conf;
        foreach ($array as $key => $value) {
            if (0 == $key) {continue;}
            if (null === ($tmp_conf = $tmp_conf[$value] ?? null)) {
                return null;
            }
        }
        return $tmp_conf;
    }

}
