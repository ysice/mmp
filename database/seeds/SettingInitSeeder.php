<?php

use Phinx\Seed\AbstractSeed;

class SettingInitSeeder extends AbstractSeed
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
        $init = [
            /** 微信公众号基本信息 */
            [
                'wefee_key'   => 'wechat_appid',
                'wefee_value' => '',
            ],
            [
                'wefee_key'   => 'wechat_appsecret',
                'wefee_value' => '',
            ],
            [
                'wefee_key'   => 'wechat_token',
                'wefee_value' => '',
            ],
            [
                'wefee_key'   => 'wechat_aeskey',
                'wefee_value' => '',
            ],

            /** 微信支付基本信息 */
            [
                'wefee_key'   => 'wepay_mchid',
                'wefee_value' => '',
            ],
            [
                'wefee_key'   => 'wepay_key',
                'wefee_value' => '',
            ],
            [
                'wefee_key'   => 'wepay_public_pem',
                'wefee_value' => '',
            ],
            [
                'wefee_key'   => 'wepay_private_pem',
                'wefee_value' => '',
            ],

            /** 关注回复消息 */
            [
                'wefee_key'   => 'wechat_focus_reply_message',
                'wefee_value' => ''
            ],

            /** 上传配置 */
            [
                'wefee_key'   => 'upload_driver',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_size',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_ext',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_type',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_path',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_default_remote_url',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_qiniu_access_key',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_qiniu_secret_key',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_qiniu_bucket',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_qiniu_remote_url',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_aliyun_oss_server',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_aliyun_access_key_id',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_aliyun_access_key_secret',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_aliyun_bucket',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'upload_aliyun_remote_url',
                'wefee_value' => ''
            ],

            /** Memcache */
            [
                'wefee_key'   => 'memcache_host',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'memcache_port',
                'wefee_value' => ''
            ],

            /** Redis */
            [
                'wefee_key'   => 'redis_host',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'redis_port',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'redis_password',
                'wefee_value' => ''
            ],
            [
                'wefee_key'   => 'redis_database',
                'wefee_value' => ''
            ],

            /** 微信关注统计 */
            [
                'wefee_key'   => 'today_subscribe',
                'wefee_value' => 0
            ],
            [
                'wefee_key'   => 'today_unsubscribe',
                'wefee_value' => 0
            ],
            [
                'wefee_key'   => 'yesterday_subscribe',
                'wefee_value' => 0
            ],
            [
                'wefee_key'   => 'yesterday_unsubscribe',
                'wefee_value' => 0
            ],

            /** 系统相关配置 */
            [
                'wefee_key'   => 'updated_at',
                'wefee_value' => date('Y-m-d H:i:s'),
            ],
        ];


        $table = $this->table('settings');

        $table->insert($init)->save();
    }
}
