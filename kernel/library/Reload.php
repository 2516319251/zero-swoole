<?php

namespace zero;

/**
 * Class Reload
 * @package zero
 */
class Reload
{
    /**
     * @var array $watches 监控目录 todo:（配置信息目录暂时无法热加载）
     */
    private $watches;

    /**
     * @var string $lastValue 上次计算的 md5 的值
     */
    private $lastValue;

    /**
     * Reload constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->watches = [
            $app->getAppPath()
        ];
        $this->lastValue = $this->getDirectoriesMD5();
    }

    /**
     * 监控目录文件是否已存在修改
     * @return bool
     */
    public function hotReload(): bool
    {
        $md5 = $this->getDirectoriesMD5();
        if ($md5 != $this->lastValue) {
            $this->lastValue = $md5;
            return true;
        }
        return false;
    }

    /**
     * 获取监控目录的所有文件的 md5 值
     * @return string
     */
    public function getDirectoriesMD5(): string
    {
        $md5 = '';
        foreach ($this->watches as $watch) {
            $md5 .= $this->getDirectoryFilesMD5($watch);
        }
        return md5($md5);
    }

    /**
     * 计算对应目录所有文件的 md5 值
     * @param string $directory
     * @return string
     */
    public function getDirectoryFilesMD5(string $directory): string
    {
        // 如果不是目录
        if (!is_dir($directory)) {return '';}

        $filesMD5 = [];
        $openDir = dir($directory);
        while (false !== ($entry = $openDir->read())) {
            // 如果是文件或者文件夹
            if ($entry !== '.' && $entry !== '..') {
                $subDir = $directory . '/' . $entry;
                // 如果是文件夹则递归调用
                if (is_dir($subDir)) {
                    $filesMD5[] = $this->getDirectoryFilesMD5($subDir);
                } elseif (substr($entry, -4) === '.php') {
                    // 如果是文件则计算 md5
                    $filesMD5[] = md5_file($directory . '/' . $entry);
                }
            }
        }
        $openDir->close();
        // 返回 md5 值
        return md5(md5(implode('', $filesMD5)));
    }

}
