<?php
namespace app\traits;

use think\Request;
use Qsnh\think\Auth\Auth;

trait LoginCheck
{

    protected function loginCheck()
    {
        $request = Request::instance();

        if (!empty($this->loginOnly)) {
            if (in_array($request->action(), $this->loginOnly)) {
                return $this->_loginCheck();
            }
            return true;
        }

        if (!in_array($request->action(), $this->loginExcept)) {
            return $this->_loginCheck();
        }
    }

    private function _loginCheck()
    {
        !Auth::check() && $this->error('请重新登录', url('index/index/index'));

        return ;
    }

}