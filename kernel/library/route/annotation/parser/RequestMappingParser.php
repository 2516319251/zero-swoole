<?php

namespace zero\route\annotation\parser;

use zero\Container;

/**
 * Class RequestMappingParser
 * @package zero\route\annotation\parser
 */
class RequestMappingParser
{
    /**
     * @param $annotation
     * @throws \Exception
     */
    public function parser($annotation)
    {
        Container::get('route')->addRoute($annotation->getMethod(), [
            'uri' => $annotation->getUriPath(),
            'handle' => $annotation->getHandleClass()
        ]);
    }

}