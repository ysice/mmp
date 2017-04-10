<?php
namespace app\addons\controller;

use think\Request;
use app\wefee\Tree;
use Qsnh\think\Auth\Auth;
use app\common\controller\Base;
use app\model\Addons AS AddonsModel;

class Addons extends Base
{

    protected $index_template = './common/addons';

    /**
     * 已安装插件列表
     */
    public function getList()
    {
        $addons = AddonsModel::order('created_at', 'desc')->select();

        $title = '已安装插件';

        $user = Auth::user();

        return view('', compact('user', 'title', 'addons'));
    }

    /**
     * 未安装插件列表
     */
    public function getNoInstallList()
    {
        $files = scandir(ADDONS_PATH);

        /** 未安装插件容器 */
        $noInstallContainer = [];

        foreach ($files as $value) {
            if (in_array($value, ['.', '..'])) {
                continue;
            }

            $path = ADDONS_PATH . $value;

            if (!is_dir($path)) {
                continue;
            }

            if (AddonsModel::get(['addons_sign' => $value])) {
                continue;
            }

            $infoPath = $path . '/wefee.json';

            if (!file_exists($infoPath)) {
                continue;
            }

            $noInstallContainer[$value] = json_decode(@file_get_contents("{$path}/wefee.json"), true);
        }

        $title = '未安装插件';

        $user = Auth::user();

        return view('', compact('user', 'title', 'noInstallContainer'));
    }

    /**
     * 插件安装
     */
    public function install(Request $request)
    {
        $this->queryValidator($request);

        $addons_sign = strtolower($request->param('addons_sign'));

        /** 1.重复性检测 */
        if (AddonsModel::get(['addons_sign' => $addons_sign])) {
            $this->error('该插件已经安装了');
        }

        /** 2.读取插件信息 */
        $path = ADDONS_PATH . $addons_sign . '/';
        if (!is_dir($path)) {
            $this->error('插件不存在');
        }
        if (!file_exists($path . ucfirst($addons_sign) . EXT)) {
            $this->error('插件主文件缺失.');
        }
        if (!file_exists($path . 'wefee.json')) {
            $this->error('插件缺少wefee.json文件');
        }

        /** 获取插件的信息 */
        $addons = json_decode(@file_get_contents($path . 'wefee.json'), true);
        $addons['sign'] = $addons_sign;

        /** 分发Logo */
        $this->distAddonLogo($path, $addons_sign);

        /** 分发Assets */
        $this->distAssets($path, $addons_sign);

        /** 3.数据库安装 */
        $data = [
            'addons_sign'    => $addons['sign'],
            'addons_name'    => $addons['name'],
            'addons_description' => $addons['description'],
            'addons_author'  => $addons['author'],
            'addons_version' => $addons['version'],
            'addons_config'  => '',
            'updated_at'     => date('Y-m-d H:i:s'),
        ];
        $addons = new AddonsModel;
        $addons->save($data);

        if (! $addons) {
            $this->error('安装失败，错误代码：100.');
        }

        /** 4.安装钩子 */
        if (! Tree::hook()->install($addons)) {
            /** 数据库回滚 */
            $addons->delete();
            /** 通知错误信息 */
            $this->error('插件钩子安装失败，请联系技术人员。');
        }

        /** 5.插件安装 */
        $obj = $this->getAddonsObj($addons);
        if (method_exists($obj, 'up')) {
            try {
                /** 执行插件安装方法 */
                $obj->up();
            } catch (\Exception $e) {
                /** 数据库回滚 */
                $addons->delete();
                /** 注册钩子回滚 */
                Tree::hook()->uninstall($data['addons_sign']);
                /** 通知错误信息 */
                $this->error($e->getMessage());
            }
        }

        $this->success('安装成功', url('addons/addons/index', ['addons_sign' => $data['addons_sign']]));
    }

    /**
     * 创建插件Logo的分发目录
     * @param string $addons_sign 插件标识
     * @return void
     */
    protected function distAddonLogo($addonsPath, $addons_sign)
    {
        /** Logo分发目录 */
        $path = $this->getAddonLogoDistDir($addons_sign);

        /** 查询是否存在Logo文件 */
        if ($result = glob($addonsPath . 'icon.*')) {
            /** 是否需要创建目录 */
            if (! $this->mkdir($path)) {
                $this->error("创建目录：{$path} 失败，请检查权限.");
            }
            /** 分发 */
            if (! copy($result[0], $path . '/' . basename($result[0]))) {
                $this->error('插件Logo分发失败，请检查目录权限');
            }
        }
    }

    /** 分发AssetsResource */
    protected function distAssets($addonPath, $addonSign)
    {
        /** 待复制目录检测 */
        $copyPath = $addonPath . 'public/assets';
        if (! is_dir($copyPath)) {
            return ;
        }

        /** 目标目录检测 */
        $distPath = $this->getAddonAssetsDistDir($addonSign);
        if (! $this->mkdir($distPath)) {
            $this->error('创建Assets目录失败');
        }

        /** 复制 */
        copy_all($copyPath, $distPath);
    }

    /**
     * 插件卸载
     */
    public function uninstall(Request $request)
    {
        $addons = $this->existsValidator($request);

        $this->removeAddonLogo($addons->addons_sign);

        $this->removeAddonAssets($addons->addons_sign);

        /** 1.卸载钩子 */
        if (! Tree::hook()->uninstall($addons)) {
            $this->error('插件钩子卸载失败，请联系技术人员。');
        }

        /** 2.执行插件的卸载方案 */
        $obj = $this->getAddonsObj($addons);

        if (method_exists($obj, 'down')) {
            try {
                $obj->down();
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        $addons->delete();

        $this->success('操作成功');
    }

    /** 删除插件分发的Logo */
    protected function removeAddonLogo($addonSign)
    {
        $path = $this->getAddonLogoDistDir($addonSign);

        if ($result = glob($path . '/' . 'icon.*')) {
            @unlink($result[0]);
        }
    }

    /** 删除插件分发的Assets */
    protected function removeAddonAssets($addonSign)
    {
        $path = $this->getAddonAssetsDistDir($addonSign);

        delete_dir($path);
    }

    /**
     * 插件升级
     */
    public function upgrade(Request $request)
    {
        $addons     = $this->existsValidator($request);

        $addonsInfo = $this->getAddonsInfo($addons);

        if (version_compare($addonsInfo['version'], $addons['addons_version'], '<=')) {
            $this->error('不能升级为低版本。');
        }

        /** 更新资源文件 */
        $path = ADDONS_PATH . $addons->addons_sign . '/';
        $this->distAssets($path, $addons->addons_sign);
        $this->distAddonLogo($path, $addons->addons_sign);

        $obj = $this->getAddonsObj($addons);

        /** 1。更新钩子 */
        if (! Tree::hook()->upgrade($addons)) {
            $this->error('插件钩子更新失败，请联系技术人员。');
        }

        /** 2.执行插件升级方案 */
        if (method_exists($obj, 'down')) {
            try {
                $obj->upgrade();
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }

        /** 3.修改数据库信息 */
        $addons->save([
            'addons_version' => $addonsInfo['version'],
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->success('操作成功');
    }

    /** 创建目录 */
    protected function mkdir($path)
    {
        if (! is_dir($path)) {
            return @mkdir($path, 0777, true);
        }

        return true;
    }

    /**
     * 获取插件的Logo分发目录
     * @param string $addons_sign 插件标识
     * @return string
     */
    protected function getAddonLogoDistDir($addons_sign)
    {
        return APP_PATH . '../public/addons/logo/' . $addons_sign;
    }

    protected function getAddonAssetsDistDir($addonSign)
    {
        return APP_PATH . '../public/addons/assets/' . $addonSign;
    }

    /**
     * 获取插件信息
     * @param \app\model\Addons $addons 插件
     * @return array
     */
    protected function getAddonsInfo(AddonsModel $addons)
    {
        $file = ADDONS_PATH . strtolower($addons->addons_sign) . DS . 'wefee.json';

        !file_exists($file) && $this->error('插件wefee.json文件不存在');

        $json = @file_get_contents($file);

        return json_decode($json, true);
    }

    /**
     * 获取插件对象
     * @param \app\model\Addons $addons 插件信息
     * @return
     */
    protected function getAddonsObj(AddonsModel $addons)
    {
        $objName = 'addons\\' . strtolower($addons->addons_sign) . '\\' . ucfirst($addons->addons_sign);
        $obj = new $objName();
        return $obj;
    }

    /**
     * 插件状态操作
     */
    public function ban(Request $request)
    {
        $addons = $this->existsValidator($request);

        $status = $addons['addons_status'] == 1 ? 3 : 1;

        $addons->save(['addons_status' => $status]);

        $this->success('操作成功');
    }

    /**
     * 插件直接删除
     */
    public function delete(Request $request)
    {
        $this->queryValidator($request);

        $addons_sign = $request->param('addons_sign');

        /** 1.检测是否安装 */
        if (AddonsModel::get(['addons_sign' => $addons_sign])) {
            $this->error('请先卸载该插件');
        }

        /** 2.检测目录是否存在 */
        $path = ADDONS_PATH . $addons_sign;
        if (! is_dir($path)) {
            $this->error('插件不存在');
        }

        /** 3.删除插件 */
        delete_dir($path);

        $this->success('操作成功.');
    }

    /**
     * 访问插件页面
     */
    public function index(Request $request)
    {
        $addons = $this->existsValidator($request);

        $path = ADDONS_PATH . strtolower($addons->addons_sign) . DS . 'wefee.html';

        $path = file_exists($path) ? $path : $this->index_template;

        $title = "{$addons->addons_name}的主页 - PowerBy Wefee.CC";

        $user = Auth::user();

        return view($path, compact('user', 'title', 'addons'));
    }

    /** 插件配置 */
    public function config(Request $request)
    {
        $addons = $this->existsValidator($request);

        $path = ADDONS_PATH . strtolower($addons->addons_sign) . DS . 'config.html';

        if (! file_exists($path)) {
            $this->error('该插件无需配置');
        }

        $title = '插件配置';

        $user = Auth::user();

        return view($path, compact('user', 'title', 'addons'));
    }

    /**
     * 保存插件配置
     */
    public function postConfig(Request $request)
    {
        $addons = $this->existsValidator($request);

        $post = $request->except(['__token__', 'addons_sign']);

        $addons->save(['addons_config' => $post]);

        $this->success('操作成功');
    }

    protected function existsValidator(Request $request)
    {
        $this->queryValidator($request);

        $addons = AddonsModel::get(['addons_sign' => $request->param('addons_sign')]);

        !$addons && $this->error('插件未安装');

        return $addons;
    }

    protected function queryValidator(Request $request)
    {
        if ($request->param('addons_sign') == '') {
            $this->error('参数错误');
        }
    }

}