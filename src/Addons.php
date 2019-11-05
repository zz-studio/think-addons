<?php
// +----------------------------------------------------------------------
// | thinkphp5 Addons [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.zzstudio.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Byron Sampson <xiaobo.sun@qq.com>
// +----------------------------------------------------------------------
namespace think;

use think\facade\Env;
use think\addons\Controller;

/**
 * 插件基类
 * Class Addns
 * @author Byron Sampson <xiaobo.sun@qq.com>
 * @package think\addons
 */
abstract class Addons extends Controller
{
    // 当前错误信息
    protected $error;

    /**
     * $info = [
     *  'name'          => 'Test',
     *  'title'         => '测试插件',
     *  'description'   => '用于thinkphp5的插件扩展演示',
     *  'status'        => 1,
     *  'author'        => 'byron sampson',
     *  'version'       => '0.1'
     * ]
     */
    public $info = [];
    public $addons_path = '';
    public $config_file = '';

    // 初始化
    protected function initialize()
    {
        // 获取当前插件目录
        $this->addons_path = Env::get('addons_path') . $this->getName() . DIRECTORY_SEPARATOR;

        // 重新定义模板的根目录
        if ($this->view) {
            $this->view->config('view_path', $this->addons_path . 'view' . DIRECTORY_SEPARATOR);
        }
    }

    /**
     * 获取插件信息
     * @return array
     */
    final public function getInfo()
    {
        $info_path = $this->addons_path . 'info.ini';
        if (is_file($info_path)) {
            $info = parse_ini_file($info_path);
            if (is_array($info)) {
                $this->info = array_merge($this->info, $info);
            }
        }
        return $this->info;
    }

    /**
     * 获取插件的配置数组
     * @param string $name 可选模块名
     * @return array|mixed|null
     */
    final public function getConfig($parse = false)
    {
        $name = $this->getName();
        return get_addons_config($name, $parse);
    }

    /**
     * 获取当前模块名
     * @return string
     */
    final public function getName()
    {
        $data = array_reverse(explode('\\', get_class($this)));
        return $data[1];
    }

    /**
     * 检查配置信息是否完整
     * @return bool
     */
    final public function checkInfo()
    {
        $info_check_keys = ['name', 'title', 'description', 'status', 'author', 'version'];
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, $this->getInfo())) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取当前错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();
}
    
