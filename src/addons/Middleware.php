<?php
/**
 * +----------------------------------------------------------------------
 * | zhizicms [稚子网络 CMS]
 * +----------------------------------------------------------------------
 *  .--,       .--,             | FILE: Auth.php
 * ( (  \.---./  ) )            | AUTHOR: byron
 *  '.__/o   o\__.'             | EMAIL: xiaobo.sun@qq.com
 *     {=  ^  =}                | QQ: 150093589
 *     /       \                | DATETIME: 2019-05-09 15:02
 *    //       \\               |
 *   //|   .   |\\              |
 *   "'\       /'"_.-~^`'-.     |
 *      \  _  /--'         `    |
 *    ___)( )(___               |-----------------------------------------
 *   (((__) (__)))              | 高山仰止,景行行止.虽不能至,心向往之。
 * +----------------------------------------------------------------------
 * | Copyright (c) 2017 http://www.zzstudio.net All rights reserved.
 * +----------------------------------------------------------------------
 */

namespace think\addons;

use think\facade\Config;
use think\facade\Env;

class Middleware
{
    /**
     * 插件控制器
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // 路由地址
        $pathinfo = explode('/', $request->path());
        $rules = explode('.', $pathinfo[1]);
        $request->setModule(array_shift($rules));
        $request->setController(join('/', $rules));
        $request->setAction($pathinfo[2]);

        // 生成view_path
        $view_path = Env::get('addons_path') . $request->module() . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
        Config::set('template.view_path', $view_path);

        return $next($request);
    }
}
