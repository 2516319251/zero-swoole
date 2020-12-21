<?php

namespace zero;

/**
 * Class Container
 * @package zero
 */
class Container
{
    /**
     * @var Container 容器实例
     */
    protected static $instance;

    /**
     * @var array 容器种的对象实例
     */
    protected $container = [];

    /**
     * @var array 容器绑定标识
     */
    protected $bind = [
        'app'               => Application::class,
        'config'            => Config::class,
        'reload'            => Reload::class,
        'route'             => Route::class,
        'server'            => Server::class,
    ];

    /**
     * @var array 容器标识别名
     */
    protected $name = [];

    /**
     * 获取当前容器实例
     * @return Container
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 设置当前容器实例
     * @param Container $instance
     */
    public static function setInstance(Container $instance)
    {
        self::$instance = $instance;
    }

    /**
     * 获取容器中的对象实例
     * @param string $abstract 类名或标识
     * @param array|true $vars 变量
     * @param bool $newInstance 是否实例化
     * @return false|mixed|object
     * @throws \Exception
     */
    public static function get(string $abstract, $vars = [], $newInstance = false)
    {
        return static::getInstance()->make($abstract, $vars, $newInstance);
    }

    /**
     * 创建类的实例
     * @param string $abstract 类名或标识
     * @param array|true $vars 变量
     * @param false $newInstance 是否实例化
     * @return false|mixed|object
     * @throws \Exception
     */
    public function make(string $abstract, $vars = [], $newInstance = false)
    {
        if (true === $vars) {
            $newInstance = true;
            $vars        = [];
        }

        $abstract = $this->name[$abstract] ?? $abstract;

        if (isset($this->container[$abstract]) && !$newInstance) {
            return $this->container[$abstract];
        }

        if (isset($this->bind[$abstract])) {
            $concrete = $this->bind[$abstract];

            if ($concrete instanceof \Closure) {
                $object = $this->invokeFunction($concrete, $vars);
            } else {
                $this->name[$abstract] = $concrete;
                return $this->make($concrete, $vars, $newInstance);
            }
        } else {
            $object = $this->invokeClass($abstract, $vars);
        }

        if (!$newInstance) {
            $this->container[$abstract] = $object;
        }

        return $object;
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * @param mixed $function 函数或闭包
     * @param array $vars 参数
     * @return false|mixed
     * @throws \Exception
     */
    public function invokeFunction($function, $vars = [])
    {
        try {
            $reflect = new \ReflectionFunction($function);

            $args = $this->bindParams($reflect, $vars);

            return call_user_func_array($function, $args);
        } catch (\ReflectionException $e) {
            throw new \Exception('function not exists: ' . $function . '()');
        }
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     * @param string $class 类名
     * @param array $vars 参数
     * @return mixed|object
     * @throws \Exception
     */
    public function invokeClass(string $class, $vars = [])
    {
        try {
            $reflect = new \ReflectionClass($class);

            if ($reflect->hasMethod('__make')) {
                $method = new \ReflectionMethod($class, '__make');

                if ($method->isPublic() && $method->isStatic()) {
                    $args = $this->bindParams($method, $vars);
                    return $method->invokeArgs(null, $args);
                }
            }

            $constructor = $reflect->getConstructor();

            $args = $constructor ? $this->bindParams($constructor, $vars) : [];

            return $reflect->newInstanceArgs($args);
        } catch (\ReflectionException $e) {
            throw new \Exception('class not exists: ' . $class);
        }
    }

    /**
     * 绑定参数
     * @param \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param array $vars 参数
     * @return array
     * @throws \ReflectionException
     */
    protected function bindParams($reflect, $vars = [])
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        reset($vars);
        $type = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();

        foreach ($params as $param) {
            $name = $param->getName();
            $lowerName = $this->parseName($name);
            $class = $param->getClass();

            if ($class) {
                $args[] = $this->getObjectParam($class->getName(), $vars);
            } elseif (1 == $type && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif (0 == $type && isset($vars[$name])) {
                $args[] = $vars[$name];
            } elseif (0 == $type && isset($vars[$lowerName])) {
                $args[] = $vars[$lowerName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \Exception('method param miss:' . $name);
            }
        }

        return $args;
    }

    /**
     * 获取对象类型的参数值
     * @param string $className 类名
     * @param array $vars 参数
     * @return mixed
     */
    protected function getObjectParam($className, &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @access public
     * @param  string  $name 字符串
     * @param  integer $type 转换类型
     * @param  bool    $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    public function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);
            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * @param string $abstract
     * @param mixed $concrete
     * @return static
     */
    public static function set(string $abstract, $concrete = null)
    {
        return static::getInstance()->bindTo($abstract, $concrete);
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * @param string|array $abstract
     * @param mixed $concrete
     * @return $this
     */
    public function bindTo($abstract, $concrete)
    {
        if (is_array($abstract)) {
            $this->bind = array_merge($this->bind, $abstract);
        } elseif ($concrete instanceof \Closure) {
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            if (isset($this->bind[$abstract])) {
                $abstract = $this->bind[$abstract];
            }
            $this->container[$abstract] = $concrete;
        } else {
            $this->bind[$abstract] = $concrete;
        }
        return $this;
    }

}
