<?php
namespace app\addons\controller;

use think\Db;
use think\Request;
use Qsnh\think\Auth\Auth;
use app\common\controller\Base;
use app\model\Hooks AS HooksModel;

class Hooks extends Base
{

    public function getList()
    {
        $hooks = HooksModel::select();

        $title = '钩子管理';

        $user = Auth::user();

        return view('', compact('user', 'title', 'hooks'));
    }

    public function ban(Request $request)
    {
        $hook = HooksModel::get($request->param('id/d'));

        !$hook && $this->error('钩子不存在');

        $status = $hook['hook_status'] == 1 ? 3 : 1;

        $hook->save(['hook_status' => $status]);

        $this->success('操作成功');
    }

    /**
     * 对挂在钩子中的插件顺序手动调整
     */
    public function postSort(Request $request)
    {
        $hook_sign = $request->post('hook_sign');
        $sortHooks = $request->post('sortArray/a');

        if ($hook_sign == '' || empty($sortHooks)) {
            $this->error('数据错误');
        }

        $hook = HooksModel::get(['hook_sign' => $hook_sign]);

        !$hook && $this->error('钩子不存在');

        $hook->save([
            'hook_thinks' => serialize($sortHooks),
        ]);

        $this->success('操作成功');
    }

}