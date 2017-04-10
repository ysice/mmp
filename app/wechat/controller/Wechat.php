<?php
namespace app\wechat\controller;

use think\Hook;
use think\Request;
use think\Session;
use app\wefee\Tree;
use think\Controller;

class Wechat extends Controller
{

    public function _initialize()
    {
        parent::_initialize();

        /** 消息订阅钩子注册 */
        Hook::add('subscribe_message', [
            'app\\wechat\\behavior\\SubscribeMessageDispatch',
        ]);

        /** 消息处理钩子注册 */
        Hook::add('process_message', [
            'app\\wechat\\behavior\\ProcessMessageDispatch',
        ]);
    }

    /**
     * 微信开发者接口
     * @return string  e.g: echostr
     */
    public function api()
    {
        /** 获取SDK服务端实例 */
        $server = Tree::wechat()->server;

        /** 消息,事件处理 */
        $server->setMessageHandler(function ($message) {

            /** 消息订阅钩子 */
            Hook::listen('subscribe_message', $message);

            /** 消息处理钩子 */
            $result = Hook::listen('process_message', $message);

            /** 如果存在回复消息，则回复此条消息，默认回复第一条消息 */
            if (! is_null($result)) {
                return $result[0];
            }

            return null;
        });

        /** 消息,事件响应 */
        $response = $server->serve();

        /** 返回数据 */
        $response->send();
    }

    /**
     * 网页授权回调
     * @param \think\Request $request
     */
    public function requestAuth(Request $request)
    {
        /** 前一页地址 */
        $targetUrl = $_SERVER['HTTP_REFERER'];
        Session::set('target_url', $targetUrl);

        $oauth = Tree::wechat()->oauth;

        $oauth->redirect()->send();
    }

    /**
     * 微信网页授权回调
     */
    public function webNotify(Request $request)
    {
        $oauth = Tree::wechat()->oauth;

        $user = $oauth->user();

        Session::set('wechat_user', $user);

        $url = Session::has('target_url') ? Session::get('target_url') : '/';

        header('Location:'.$url);
    }

}