<?php
declare(strict_types=1);

use think\facade\Event;
use think\facade\Route;
use think\helper\{
    Str, Arr
};

\think\Console::starting(function (\think\Console $console) {
    $console->addCommands([
        'addons:config' => '\\think\\addons\\command\\SendConfig'
    ]);
});

// 插件类库自动载入
spl_autoload_register(function ($class) {

    $class = ltrim($class, '\\');

    $dir = app()->getRootPath();
    $namespace = 'addons';

    if (strpos($class, $namespace) === 0) {
        $class = substr($class, strlen($namespace));
        $path = '';
        if (($pos = strripos($class, '\\')) !== false) {
            $path = str_replace('\\', '/', substr($class, 0, $pos)) . '/';
            $class = substr($class, $pos + 1);
        }
        $path .= str_replace('_', '/', $class) . '.php';
        $dir .= $namespace . $path;

        if (file_exists($dir)) {
            include $dir;
            return true;
        }

        return false;
    }

    return false;

});

if (!function_exists('hook')) {
    /**
     * 处理插件钩子
     * @param string $event 钩子名称
     * @param array|null $params 传入参数
     * @param bool $once 是否只返回一个结果
     * @return mixed
     */
    function hook($event, $params = null, bool $once = false)
    {
        $result = Event::trigger($event, $params, $once);

        return join('', $result);
    }
}

if (!function_exists('get_addons_info')) {
    /**
     * 读取插件的基础信息
     * @param string $name 插件名
     * @return array
     */
    function get_addons_info($name)
    {
        $addon = get_addons_instance($name);
        if (!$addon) {
            return [];
        }

        return $addon->getInfo();
    }
}

if (!function_exists('get_addons_instance')) {
    /**
     * 获取插件的单例
     * @param string $name 插件名
     * @return mixed|null
     */
    function get_addons_instance($name)
    {
        static $_addons = [];
        if (isset($_addons[$name])) {
            return $_addons[$name];
        }
        $class = get_addons_class($name);
        if (class_exists($class)) {
            $_addons[$name] = new $class(app());

            return $_addons[$name];
        } else {
            return null;
        }
    }
}

if (!function_exists('get_addons_class')) {
    /**
     * 获取插件类的类名
     * @param string $name 插件名
     * @param string $type 返回命名空间类型
     * @param string $class 当前类名
     * @return string
     */
    function get_addons_class($name, $type = 'hook', $class = null)
    {
        $name = trim($name);
        // 处理多级控制器情况
        if (!is_null($class) && strpos($class, '.')) {
            $class = explode('.', $class);

            $class[count($class) - 1] = Str::studly(end($class));
            $class = implode('\\', $class);
        } else {
            $class = Str::studly(is_null($class) ? $name : $class);
        }
        switch ($type) {
            case 'controller':
                $namespace = '\\addons\\' . $name . '\\controller\\' . ucfirst($class);
                break;
            default:
                $namespace = '\\addons\\' . $name . '\\' . ucfirst($name);
        }

        return class_exists($namespace) ? $namespace : '';
    }
}

if (!function_exists('addons_url')) {
    /**
     * 插件显示内容里生成访问插件的url
     * @param $url
     * @param array $param
     * @param bool|string $suffix 生成的URL后缀
     * @param bool|string $domain 域名
     * @return bool|string
     */
    function addons_url($url = '', $param = [], $suffix = true, $domain = false)
    {
        $request = app('request');
        if (empty($url)) {
            // 生成 url 模板变量
            $addons = $request->addon;
            $controller = $request->controller();
            $controller = str_replace('/', '.', $controller);
            $action = $request->action();
        } else {
            $url = Str::studly($url);
            $url = parse_url($url);
            if (isset($url['scheme'])) {
                $addons = strtolower($url['scheme']);
                $controller = $url['host'];
                $action = trim($url['path'], '/');
            } else {
                $route = explode('/', $url['path']);
                $addons = $request->addon;
                $action = array_pop($route);
                $controller = array_pop($route) ?: $request->controller();
            }
            $controller = Str::snake((string)$controller);

            /* 解析URL带的参数 */
            if (isset($url['query'])) {
                parse_str($url['query'], $query);
                $param = array_merge($query, $param);
            }
        }

        return Route::buildUrl("@addons/{$addons}/{$controller}/{$action}", $param)->suffix($suffix)->domain($domain);
    }
}

if (!function_exists('get_addon_list')) {
    /**
     * 获取所有插件列表
     * @return array
     */
    function get_addon_list() {
        $addonPath = app()->getRootPath(). 'addons' . DIRECTORY_SEPARATOR;
        //获取插件列表数据
        $results = scandir($addonPath);
        $list = [];
        foreach ($results as $name) {

            if ($name === '.' or $name === '..')
                continue;
            if (is_file($addonPath . $name))
                continue;
            $addonDir = $addonPath . $name . DIRECTORY_SEPARATOR;
            if (!is_dir($addonDir))
                continue;

            if (!is_file($addonDir . ucfirst($name) . '.php'))
                continue;
            $class = get_addons_class($name);
            if (!class_exists($class)) {
                continue;
            }
            $addon = get_addons_instance($name);
            if (!$addon) {
                continue;
            }
            $arr = [];
            $arr['info'] = $addon->getInfo();
            $arr['config'] = $addon->getConfig(true);
            $info_file = $addonDir . 'config.php';
            if (!is_file($info_file)){
                $arr['info']['status'] = 0;
            }
            $list[] = $arr;
        }
        return $list;
    }
}

if (!function_exists('create_config')) {
    /**
     * 安装时-生成插件配置文件
     * @return bool
     */
    function create_config($config){
        $name="config.php";
        $config_file = app()->getRootPath() . 'addons' . DIRECTORY_SEPARATOR . $config['name'] . DIRECTORY_SEPARATOR . $name;
        
        // 如果文件存在则已经安装成功
        if(is_file($config_file) && file_exists($config_file)){
            return false;
        }
        $config=var_export($config, true);
        $content =<<<EOT
<?php
return {$config};
EOT;
        $result=file_put_contents($config_file,$content);
        if($result===false){
            return false;
        }
        return true;
    }
}