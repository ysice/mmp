<?php
namespace app\common\controller;

use think\Session;
use think\Request;
use think\Controller;
use Qsnh\think\Auth\Auth;
use app\traits\LoginCheck;

class Base extends Controller
{
    use LoginCheck;

    protected $loginOnly = [];

    protected $loginExcept = [];

    protected $repository = null;

    public function _initialize()
    {
        $this->loginCheck();

        /** 当前认证用户 */
        \think\View::share('user', Auth::user());
    }

    protected function checkToken(Request $request)
    {
        Session::get('__token__') != $request->post('__token__') && $this->error('请刷新页面重新提交');
    }

}