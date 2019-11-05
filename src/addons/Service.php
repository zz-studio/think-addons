<?php

namespace think\addons;

use think\Route;
use think\Addons;

/**
 * 插件服务
 * Class Service
 * @package think\addons
 */
class Service extends \think\Service
{
    public function register()
    {
        // 初始化插件目录
        $addons_path = $this->app->getRootPath() . 'addons' . DIRECTORY_SEPARATOR;
        // 如果插件目录不存在则创建
        if (!is_dir($addons_path)) {
            @mkdir($addons_path, 0755, true);
        }
        $this->app->request->addons_path = $addons_path;
    }

    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            //$route->get("debugbar/:path", AssetController::class . "@index")->pattern(['path' => '[\w\.\/\-_]+']);
        });
    }
}
