<?php
/**
 * +----------------------------------------------------------------------
 * | think-addons [基于 thinkphp6]
 * +----------------------------------------------------------------------
 *  .--,       .--,             | FILE: config.php
 * ( (  \.---./  ) )            | AUTHOR: byron sampson
 *  '.__/o   o\__.'             | EMAIL: xiaobo.sun@qq.com
 *     {=  ^  =}                | QQ: 150093589
 *      >  -  <                 | WECHAT: wx5ini99
 *     /       \                | DATETIME: 2019/10/29
 *    //       \\               |
 *   //|   .   |\\              |
 *   "'\       /'"_.-~^`'-.     |
 *      \  _  /--'         `    |
 *    ___)( )(___               |-----------------------------------------
 *   (((__) (__)))              | 高山仰止,景行行止.虽不能至,心向往之。
 * +----------------------------------------------------------------------
 * | Copyright (c) 2019 http://www.zzstudio.net All rights reserved.
 * +----------------------------------------------------------------------
 */

namespace think\addons\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Env;

class SendConfig extends Command
{

    public function configure()
    {
        $this->setName('addons:config')
             ->setDescription('send config to config folder');
    }

    public function execute(Input $input, Output $output)
    {
        //获取默认配置文件
        $content = file_get_contents(root_path() . 'vendor/zzstudio/think-addons/src/config.php');

        $configPath = config_path() . '/';
        $configFile = $configPath . 'addons.php';


        //判断目录是否存在
        if (!file_exists($configPath)) {
            mkdir($configPath, 0755, true);
        }

        //判断文件是否存在
        if (is_file($configFile)) {
            throw new \InvalidArgumentException(sprintf('The config file "%s" already exists', $configFile));
        }

        if (false === file_put_contents($configFile, $content)) {
            throw new \RuntimeException(sprintf('The config file "%s" could not be written to "%s"', $configFile,$configPath));
        }

        $output->writeln('create addons config ok');
    }

}