<?php
namespace app\index\controller;

use think\Request;
use think\Validate;
use think\helper\Hash;
use Qsnh\think\Auth\Auth;
use app\common\controller\Base;

class Index extends Base
{
    protected $loginOnly = [
        'admin', 'postchangepassword'
    ];

    public function index()
    {
        $title = 'Wefee 微信管理系统';

        return view('', compact('title'));
    }

    /**
     * 用户登录
     */
    public function postLogin(Request $request)
    {
        $this->checkToken($request);

        if (
            $request->post('username') == '' ||
            $request->post('password') == ''
        ) {
            $this->error('请输入完整信息');
        }

        if (
            !Auth::attempt([
                'username' => $request->post('username'),
                'password' => $request->post('password')
            ])
        ) {
            $this->error('登录失败');
        }

        $this->loginAfter($request);

        $this->success('登录成功', url('dashboard/index'));
    }

    /**
     * 登录之后
     * @param $request \think\Request 登录请求
     * @return void
     */
    protected function loginAfter(Request $request)
    {
        $user = Auth::user();
        $user->last_login_date = date('Y-m-d H:i:s');
        $user->last_login_ip   = $request->ip();
        $user->login_times     = $user->login_times + 1;
        $user->save();
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        Auth::logout();

        $this->success('成功退出', url('index/index'));
    }

    /**
     * 账户面板
     */
    public function admin()
    {
        $title = '账户中心';

        $user = Auth::user();

        return view('', compact('title', 'user'));
    }

    /**
     * 修改密码
     */
    public function postChangePassword(Request $request)
    {
        $validator = new Validate([
            'old_password|原密码' => 'require|token',
            'new_password|新密码' => 'require|length:6,15|confirm:new_password_confirmation',
        ], [
            'new_password.confirm' => '两次输入密码不一致',
        ]);

        $user = Auth::user();

        if (!$validator->check($request->post())) {
            $this->error($validator->getError());
        }

        if (!Hash::check($request->post('old_password'), $user->password)) {
            $this->error('原密码错误');
        }

        $user->password = Hash::make($request->post('new_password'));
        $user->save();

        $this->success('操作成功');
    }
}
