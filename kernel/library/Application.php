<?php

namespace zero;

/**
 * Class Application
 * @package zero
 */
class Application
{
    /**
     * @var string 控制器目录
     */
    protected $appPath;

    /**
     * @var string 配置文件目录
     */
    protected $configPath;

    /**
     * @var string 缓存目录
     */
    protected $runtimePath;

    /**
     * @var string 应用根目录
     */
    protected $rootPath;

    /**
     * 启动框架
     * @param $input
     * @throws \Exception
     */
    public function run($input)
    {
        // 初始化操作
        $this->init();
        // 启动服务
        Container::get('server')->handle($input);
    }

    /**
     * 初始化操作
     */
    public function init()
    {
        $this->rootPath = $this->getRootPathInCli();
        $this->appPath = $this->rootPath . 'application' . DIRECTORY_SEPARATOR;
        $this->configPath = $this->rootPath . 'config' . DIRECTORY_SEPARATOR;
        $this->runtimePath = $this->rootPath . 'runtime' . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public function getAppPath(): string
    {
        return $this->appPath;
    }

    /**
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * @return string
     */
    public function getRuntimePath(): string
    {
        return $this->runtimePath;
    }

    /**
     * 获取应用根目录
     * @return string
     */
    public static function getRootPathInCli(): string
    {
        if ('cli' == PHP_SAPI) {
            $script_name = realpath($_SERVER['argv'][0]);
        } else {
            $script_name = $_SERVER['SCRIPT_FILENAME'];
        }

        $path = realpath(dirname($script_name));
        if (!is_file($path . DIRECTORY_SEPARATOR . 'kernel')) {
            $path = dirname($path);
        }

        return $path . DIRECTORY_SEPARATOR;
    }

}
