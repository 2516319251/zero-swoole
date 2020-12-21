<?php

namespace zero\route\annotation\mapping;

/**
 * Class RequestMapping
 * @package zero\route\annotation\mapping
 */
class RequestMapping
{
    /**
     * @var string
     */
    private $uriPath;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $handleClass;

    /**
     * RequestMapping constructor.
     * @param $classDocComment
     * @param $methodDocComment
     * @param $classReflect
     * @param $method
     */
    public function __construct($classDocComment, $methodDocComment, $classReflect, $method)
    {
        // 注解信息的收集
        $doc = $this->getAnnotationHandleByPreg([
            'comment' => $classDocComment,
            'filter' => 'controller'
        ], [
            'comment' => $methodDocComment,
            'filter' => 'requestMapping'
        ]);
        // 路由请求地址
        $this->uriPath = $doc['prefix'] . $doc['route'];
        // 请求方法
        $this->method = $doc['method'] ?? 'any';
        // 处理类
        $this->handleClass = $classReflect->getName() . '@' . $method->getName();
    }

    /**
     * @return string
     */
    public function getHandleClass(): string
    {
        return $this->handleClass;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUriPath(): string
    {
        return $this->uriPath;
    }

    /**
     * 通过正则获取注解路由信息
     * @param mixed ...$docComment
     * @return string[]
     */
    private function getAnnotationHandleByPreg(array ...$docComment): array
    {
        $doc = [];
        foreach ($docComment as $item) {
            $filter = $item['filter'];
            preg_match("/@{$filter}\((.*)\)/i", $item['comment'], $matches);
            $subMatches = explode(',', end($matches));

            foreach ($subMatches as $subMatch) {
                // 清除引号
                $vars = explode('=', trim($subMatch));
                // $needle = str_replace('"', '', end($vars));
                $doc[$vars[0]] = str_replace('"', '', end($vars));
            }
        }
        return $doc;
    }

}
