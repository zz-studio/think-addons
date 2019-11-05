<?php

namespace think\addons;

use think\Route;
use think\Addons;

class Service extends \think\Service
{
    public function register()
    {
        $this->app->bind('addons', Addons::class);
    }

    public function boot()
    {
        $this->registerRoutes(function (Route $route) {
            $route->get("debugbar/:path", AssetController::class . "@index")->pattern(['path' => '[\w\.\/\-_]+']);
        });
    }
}
