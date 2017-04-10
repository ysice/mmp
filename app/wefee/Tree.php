<?php
namespace app\wefee;

use EasyWeChat\Foundation\Application;

class Tree
{

    const WECHAT = 'eashwechat';

    const HOOK = 'hook';

    protected static $tree = [
        self::WECHAT => null,
        self::HOOK => null,
    ];

    /**
     * 获取微信app
     * @return EasyWeChat\Foundation\Application
     */
    public static function wechat()
    {
        if (is_null(self::$tree[self::WECHAT])) {
            self::$tree[self::WECHAT] = new Application(get_wechat_config());
        }

        return self::$tree[self::WECHAT];
    }

    /**
     * 获取Hook对象
     * @return \app\wefee\Hooks
     */
    public static function hook()
    {
        if (is_null(self::$tree[self::HOOK])) {
            self::$tree[self::HOOK] = new Hooks();
        }

        return self::$tree[self::HOOK];
    }

}