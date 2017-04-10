<?php
/**
 * 订阅消息分发器
 * @author 轻色年华 <616896861@qq.com>
 */
namespace app\wechat\behavior;

use app\wefee\Tree;

class SubscribeMessageDispatch
{

    public function run(&$message)
    {
        $hook = Tree::hook();
        switch ($message->MsgType) {
            case 'event':
                /** 系统自身关注统计 */
                if ($message->Event == 'subscribe' || $message->Event == 'unsubscribe') {
                    wechat_subscribe_event($message->Event == 'subscribe' ? 1 : -1);
                }

                /** 事件 */
                $hook->listen('wefee-subscribe-event', $message);
                break;
            case 'text':
                /** 文本消息 */
                $hook->listen('wefee-subscribe-text', $message);
                break;
            case 'image':
                /** 图片消息 */
                $hook->listen('wefee-subscribe-image', $message);
                break;
            case 'voice':
                /** 声音消息 */
                $hook->listen('wefee-subscribe-voice', $message);
                break;
            case 'video':
                /** 视频消息 */
                $hook->listen('wefee-subscribe-video', $message);
                break;
            case 'location':
                /** 位置消息 */
                $hook->listen('wefee-subscribe-location', $message);
                break;
            case 'link':
                /** 链接消息 */
                $hook->listen('wefee-subscribe-link', $message);
                break;
            default:
                /** 原消息 */
                $hook->listen('wefee-subscribe-original', $message);
                break;
        }
    }

}