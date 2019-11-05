<?php

namespace think\addons;

use think\Route;
use think\facade\Config;
use think\addons\middleware\Addons;

/**
 * 插件服务
 * Class Service
 * @package think\addons
 */
class Service extends \think\Service
{
    protected $addons_path;

    public function register()
    {
        // 初始化插件目录
        $this->addons_path = $this->app->getRootPath() . 'addons' . DIRECTORY_SEPARATOR;
        // 如果插件目录不存在则创建
        if (!is_dir($this->addons_path)) {
            @mkdir($this->addons_path, 0755, true);
        }
        $this->app->request->addons_path = $this->addons_path;
        // 加载语言包
        $lang = Config::get('lang');
        if (!isset($lang['extend_list']['zh-cn'])) {
            $lang['extend_list']['zh-cn'] = [];
        }
        $lang['extend_list']['zh-cn'] += [
            $this->app->getRootPath() . '/vendor/zzstudio/think-addons/src/lang/zh-cn.php'
        ];
        Config::set($lang, 'lang');
        // 加载插件系统服务
        $this->loadService();
    }

    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            $route->rule("addons/:addon/[:controller]/[:action]", '\\think\\addons\\Route::execute')
                ->middleware(Addons::class);
        });
    }

    /**
     * 挂载插件服务
     */
    private function loadService()
    {
        $results = scandir($this->addons_path);
        $bind = [];
        foreach ($results as $name) {
            if ($name === '.' or $name === '..') {
                continue;
            }
            if (is_file($this->addons_path . $name)) {
                continue;
            }
            $addonDir = $this->addons_path . $name . DIRECTORY_SEPARATOR;
            if (!is_dir($addonDir)) {
                continue;
            }

            if (!is_file($addonDir . ucfirst($name) . '.php')) {
                continue;
            }

            $service_file = $addonDir . 'service.ini';
            if (!is_file($service_file)) {
                continue;
            }
            $info = parse_ini_file($service_file, true, INI_SCANNER_TYPED) ?: [];
            $bind = array_merge($bind, $info);
        }
        $this->app->bind($bind);
    }
}
