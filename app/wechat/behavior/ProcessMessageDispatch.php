<?php
/**
 * 微信消息处理Behavior
 * @author 轻色年华 <616896861@qq.com>
 */
namespace app\wechat\behavior;

use app\wefee\Tree;
use app\model\ReplyRules;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Video;
use EasyWeChat\Message\Voice;

class ProcessMessageDispatch
{

    public function run(&$message)
    {
        $hook = Tree::hook();
        switch ($message->MsgType) {
            case 'event':
                /** 事件 */
                return $hook->listen('wefee-process-event', $message);
                break;
            case 'text':
                /** 交给 Wefee 自己处理。 */
                return $this->textMessageProcess($message);
                break;
            case 'image':
                /** 图片消息 */
                return $hook->listen('wefee-process-image', $message);
                break;
            case 'voice':
                /** 声音消息 */
                return $hook->listen('wefee-process-voice', $message);
                break;
            case 'video':
                /** 视频消息 */
                return $hook->listen('wefee-process-video', $message);
                break;
            case 'location':
                /** 位置消息 */
                return $hook->listen('wefee-process-location', $message);
                break;
            case 'link':
                /** 链接消息 */
                return $hook->listen('wefee-process-link', $message);
                break;
            default:
                /** 原消息 */
                return $hook->listen('wefee-process-original', $message);
                break;
        }
    }

    /**
     * 文本消息给 Wefee 自身处理
     */
    protected function textMessageProcess($message)
    {
        /** 1.获取消息内容 */
        $content = $message->Content;

        /** 2.构造WhereCondition */
        $where = "'{$content}' REGEXP rule_content AND rule_status = 1";

        /** 3.查询结果 */
        $rule = ReplyRules::where($where)->order('rule_sort', 'asc')->find();
        if ($rule) {
            $reply = $rule->replies()->where('status', '=', 1)->order('sort', 'asc')->find();
            if ($reply) {

                switch ($reply->type) {
                    case 'text':
                        return new Text(unserialize($reply->content));
                        break;
                    case 'image':
                        return new Image(unserialize($reply->content));
                        break;
                    case 'video':
                        return new Video(unserialize($reply->content));
                        break;
                    case 'voice':
                        return new Voice(unserialize($reply->content));
                        break;
                    case 'news':
                        /** 单图文 */
                        $news = unserialize($reply->content);
                        if (count($news) == 1) {
                            return new News($news);
                        }
                        /** 多条图文消息 */
                        $container = [];
                        foreach ($news as $new) {
                            $container[] = new News($new);
                        }
                        return $container;
                        break;
                }

            }
        }

        return '';
    }

}