<?php

namespace zero;

use zero\route\annotation\mapping\RequestMapping;
use zero\route\annotation\parser\RequestMappingParser;

/**
 * Class Route
 * @package zero
 */
class Route
{
    /**
     * @var string[] $method 请求方法
     */
    private $method = [
        'connect',
        'delete',
        'get',
        'head',
        'options',
        'patch',
        'post',
        'put',
        'trace'
    ];

    /**
     * @var array $route 注解路由信息
     */
    private $route = [];

    /**
     * @return array 获取当前注解路由信息
     */
    public function getRoute(): array
    {
        return $this->route;
    }

    /**
     * 路由调度
     * @param string $method 请求方法
     * @param string $uri 请求 uri
     * @return mixed|void
     */
    public function dispatch(string $method, string $uri)
    {
        $method = strtolower($method);
        foreach (($this->route[$method] ?? []) as $item) {
            if ($uri == $item['uri']) {
                list($controller, $action) = explode('@', $item['handle']);
                return (new $controller)->$action();
            }
        }
        return 'dispatch route error';
    }

    /**
     * 添加路由
     * @param string $method
     * @param array $routeInfo
     */
    public function addRoute(string $method, array $routeInfo)
    {
        if ($method == 'any') {
            foreach ($this->method as $item) {
                $this->route[$item][] = $routeInfo;
            }
        } else {
            $this->route[$method][] = $routeInfo;
        }
    }

    /**
     * 加载路由注解
     */
    public function loadRouterAnnotations()
    {
        $appPath = Application::getRootPathInCli() . 'application';
        $files = $this->getSpecifyDirectoryFilesTree($appPath, 'controller');
        foreach ($files as $file) {
            if (is_file($file)) {
                $fileInfo = pathinfo($file);
                if ('php' === $fileInfo['extension']) {
                    // 获取类名和命名空间
                    $filename = $fileInfo['filename'];
                    $namespace = substr_replace(strstr($fileInfo['dirname'], 'application'),
                        'app',
                        0,
                        strlen('application')
                    );
                    $class = str_replace('/', '\\', $namespace . '\\' . $filename);
                    // 实例化控制器
                    $ctrlObj = new $class();
                    // 类反射
                    $classReflect = new \ReflectionClass($ctrlObj);
                    // 类注解
                    $classDocComment = $classReflect->getDocComment();
                    foreach ($classReflect->getMethods() as $method) {
                        // 方法注解
                        $methodDocComment = $method->getDocComment();
                        // 收集路由信息
                        $annotation = new RequestMapping($classDocComment, $methodDocComment, $classReflect, $method);
                        // 执行注释逻辑
                        $parserCls = new RequestMappingParser();
                        $parserCls->parser($annotation);
                    }
                }
            }
        }
    }

    /**
     * 获取指定目录下符合条件的文件
     * @param string $directory 指定目录
     * @param string $filter 过滤条件
     * @return array
     */
    protected function getSpecifyDirectoryFilesTree(string $directory, string $filter): array
    {
        $dirs = glob($directory . '/*');
        $dirFiles = [];
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $result = $this->getSpecifyDirectoryFilesTree($dir, $filter);
                if (is_array($dirFiles)) {
                    foreach ($result as $value) {
                        $dirFiles[] = $value;
                    }
                }
            } else {
                if (stristr($dir, $filter)) {
                    $dirFiles[] = $dir;
                }
            }
        }
        return $dirFiles;
    }

}
