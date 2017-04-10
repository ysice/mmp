<?php
namespace app\wefee;

use app\model\Addons;
use app\model\AddonsHooks;
use app\model\Hooks AS HooksModel;

class Hooks
{
    protected $addons_path;

    protected $objContainer = [];

    public function __construct()
    {
        $this->addons_path = ROOT_PATH . DS . 'addons' . DS;
    }

    /** 插件钩子安装 */
    public function install(Addons $addons)
    {
        $addonsHooks = $this->getAddonsHooks($addons);

        if (empty($addonsHooks['model']) && empty($addonsHooks['view'])) {
            return true;
        }

        $hooks = $this->getSystemHooks();

        foreach ($hooks as $hook) {
            if (! in_array($hook->hook_sign, $addonsHooks[$hook->hook_type])) {
                /** 没有实现钩子 */
                continue;
            }

            if ($hook->addons()->where('addons_id', $addons->id)->find()) {
                /** 已经注册钩子 */
                continue;
            }
            /** 注册钩子 */
            $tmp = new AddonsHooks;
            $tmp->save([
                'addons_id' => $addons->id,
                'hook_id'   => $hook->id,
            ]);
        }

        return true;
    }

    /** 插件钩子卸载 */
    public function uninstall(Addons $addons)
    {
        $hooks = $this->getSystemHooks();

        foreach ($hooks as $hook) {
            if ($hook->addons()->where('addons_id', $addons->id)->find()) {
                $hook->addons()->where('addons_id', $addons->id)->delete();
            }
        }

        return true;
    }

    /** 插件钩子升级 */
    public function upgrade(Addons $addons)
    {
        $addonsHooks = $this->getAddonsHooks($addons);

        if (empty($addonsHooks['model']) && empty($addonsHooks['view'])) {
            return true;
        }

        $hooks = $this->getSystemHooks();
        foreach ($hooks as $hook) {
            if (! in_array($hook->hook_sign, $addonsHooks[$hook->hook_type])) {
                /** 当前没有注册钩子，检测以前是否注册钩子 */
                if ($addons->hooks()->where('hook_id', $hook->id)->find()) {
                    /** 以前注册了，现在没有注册，说明需要删除这个钩子 */
                    $addons->hooks()->where('hook_id', $hook->id)->delete();
                }
                continue;
            }
            if ($hook->addons()->where('addons_id', $addons->id)->find()) {
                /** 钩子已注册 */
                continue;
            }

            /** 注册钩子 */
            $tmp = new AddonsHooks;
            $tmp->save([
                'addons_id' => $addons->id,
                'hook_id'   => $hook->id,
            ]);
        }

        return true;
    }

    /** 获取系统预定义钩子 */
    protected function getSystemHooks()
    {
        $hooks = HooksModel::select();

        foreach ($hooks as $hook) {
            $hook->hook_sign = get_method_by_hook_name($hook->hook_sign);
        }

        return $hooks;
    }

    /** 获取插件全部钩子 */
    protected function getAddonsHooks(Addons $addons)
    {
        $modelHooks = $this->getAddonsModelHooks($addons);

        $viewHooks  = $this->getAddonsViewHooks($addons);

        $hooks = [
            'model' => $modelHooks,
            'view'  => $viewHooks,
        ];

        return $hooks;
    }

    /** 获取插件全部的业务钩子 */
    protected function getAddonsModelHooks(Addons $addons)
    {
        $obj = $this->getAddonsHookObj('model', $addons);

        if ($obj === false) {
            return [];
        }

        return get_class_methods($obj);
    }

    /** 获取插件全部的视图钩子 */
    protected function getAddonsViewHooks(Addons $addons)
    {
        $obj = $this->getAddonsHookObj('view', $addons);

        if ($obj === false) {
            return [];
        }

        return get_class_methods($obj);
    }

    /**
     * 执行挂载在钩子中的thinks
     * @param string $hooks_sign 钩子标识
     * @param array $params 传递给钩子的参数
     * @return mixed
     */
    public function listen($hooks_sign, &$params)
    {
        $hook = HooksModel::get([
            'hook_sign'   => ['eq', $hooks_sign],
            'hook_status' => ['eq', 1]
        ]);

        if (! $hook) {
            return ;
        }

        if (count($hook->addons) == 0) {
            return ;
        }

        foreach ($hook->addons as $addons) {
            $obj = $this->getAddonsHookObj($hook->hook_type, $addons->addons);

            if ($obj === false) {
                /** 修改状态，下次取消读取 */
                $hook->save(['hook_status' => 3]);
                break;
            }

            if (!$this->checkAddonsHasHookMethods($hook, $addons->addons)) {
                $hook->save(['hook_status' => 3]);
                break;
            }

            /** 执行方法 */
            $method = get_method_by_hook_name($hook->hook_sign);
            return $obj->$method($params);
        }

        return ;
    }

    /**
     * 检测插件是否存在钩子
     * @param \app\model\Hooks $hook 钩子对象
     * @param \app\model\Addons $addons 插件对象
     * @return boolean true|存在 false|不存在
     */
    private function checkAddonsHasHookMethods($hook, Addons $addons)
    {
        $obj = $this->getAddonsHookObj($hook->hook_type, $addons);

        if ($obj === false) {
            return false;
        }

        return method_exists($obj, get_method_by_hook_name($hook->hook_sign));
    }

    /**
     * 获取插件的钩子对象
     * @param string $hook_type 钩子类型：model,view
     * @param \app\model\Addons $addons 插件对象
     * @return Object
     */
    private function getAddonsHookObj($hook_type, Addons $addons)
    {
        $key = md5($addons->addons_sign . $hook_type);

        if (! isset($this->objContainer[$key])) {
            $path = $this->addons_path . $addons->addons_sign . DS . 'hook' . DS . strtolower($hook_type) . DS . 'Hook' . EXT;

            if (! file_exists($path)) {
                return false;
            }

            $objName = '\\addons\\' . $addons->addons_sign . '\\hook\\' . $hook_type . '\\Hook';
            $obj = new $objName();

            $this->objContainer[$key] = $obj;
        }

        return $this->objContainer[$key];
    }

}