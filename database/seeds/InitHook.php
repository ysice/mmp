<?php

use Phinx\Seed\AbstractSeed;

class InitHook extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [
            [
                'hook_name' => '应用初始化',
                'hook_sign' => 'app-init',
            ],
            [
                'hook_name' => '应用开始钩子',
                'hook_sign' => 'app-begin',
            ],
            [
                'hook_name' => '模块初始化钩子',
                'hook_sign' => 'module-init',
            ],
            [
                'hook_name' => '控制器初始化钩子',
                'hook_sign' => 'action-begin',
            ],
            [
                'hook_name' => '视图过滤钩子',
                'hook_sign' => 'view-filter',
            ],
            [
                'hook_name' => '应用结束钩子',
                'hook_sign' => 'app-end',
            ],
            [
                'hook_name' => '日志写入钩子',
                'hook_sign' => 'log-write',
            ],
            [
                'hook_name' => '输出结束钩子',
                'hook_sign' => 'response-end',
            ],
            [
                'hook_name' => '登录之前钩子',
                'hook_sign' => 'before-login',
            ],
            [
                'hook_name' => '登录后钩子',
                'hook_sign' => 'after-login',
            ],

            /** 消息订阅钩子 */
            [
                'hook_name' => '微信事件订阅',
                'hook_sign' => 'wefee-subscribe-event',
            ],
            [
                'hook_name' => '文本消息订阅',
                'hook_sign' => 'wefee-subscribe-text',
            ],
            [
                'hook_name' => '图片消息订阅',
                'hook_sign' => 'wefee-subscribe-image',
            ],
            [
                'hook_name' => '语音消息订阅',
                'hook_sign' => 'wefee-subscribe-voice',
            ],
            [
                'hook_name' => '视频消息订阅',
                'hook_sign' => 'wefee-subscribe-video',
            ],
            [
                'hook_name' => '位置消息订阅',
                'hook_sign' => 'wefee-subscribe-location',
            ],
            [
                'hook_name' => '链接消息订阅',
                'hook_sign' => 'wefee-subscribe-link',
            ],
            [
                'hook_name' => '原始消息订阅',
                'hook_sign' => 'wefee-subscribe-original',
            ],

            /** 消息处理钩子 */
            [
                'hook_name' => '微信事件处理',
                'hook_sign' => 'wefee-process-event',
            ],
            [
                'hook_name' => '图片消息处理',
                'hook_sign' => 'wefee-process-image',
            ],
            [
                'hook_name' => '语音消息处理',
                'hook_sign' => 'wefee-process-voice',
            ],
            [
                'hook_name' => '视频消息处理',
                'hook_sign' => 'wefee-process-video',
            ],
            [
                'hook_name' => '位置消息处理',
                'hook_sign' => 'wefee-process-location',
            ],
            [
                'hook_name' => '链接消息处理',
                'hook_sign' => 'wefee-process-link',
            ],
            [
                'hook_name' => '原始消息处理',
                'hook_sign' => 'wefee-process-original',
            ],
        ];

        $table = $this->table('hooks');
        $table->insert($data)->save();
    }
}
