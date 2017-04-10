<?php namespace app\install\controller;

use think\Db;
use app\model\Hooks;
use think\Exception;
use app\model\Admins;
use think\Controller;
use think\helper\Hash;

class Index extends Controller
{

    public function step1()
    {
        return view('install/step1');
    }

    public function step2()
    {
        /**  */
        $php_version = PHP_VERSION;
        $is_on_php = version_compare($php_version, '5.6.0', '>=');

        $extensions = get_loaded_extensions();

        /** OpenSSL */
        $is_on_openssl = in_array('openssl', $extensions);

        /** fileinfo */
        $is_on_fileinfo = in_array('fileinfo', $extensions);

        /** pdo_mysql */
        $is_on_pdo_mysql = in_array('pdo_mysql', $extensions);

        /** curl */
        $is_on_curl = in_array('curl', $extensions);

        /** gd */
        $is_on_gd = in_array('gd', $extensions);

        /** tokenizer */
        $is_on_tokenizer = in_array('tokenizer', $extensions);

        /** mcrypt */
        $is_on_mcrypt = in_array('mcrypt', $extensions);

        /** iconv */
        $is_on_iconv = in_array('iconv', $extensions);

        return view('install/step2', compact(
            'php_version', 'is_on_php', 'is_on_openssl', 'is_on_fileinfo', 'is_on_pdo_mysql',
            'is_on_curl', 'is_on_gd', 'is_on_tokenizer', 'is_on_mcrypt',
            'is_on_iconv'
        ));
    }

    public function step3()
    {
        return view('install/step3');
    }

    public function postStep3()
    {
        $data = request()->post();

        session('config', $data);

        $this->success('success');
    }

    public function step4()
    {
        if (! session('config')) {
            $this->error('请先配置信息', url('index/step3'));
        }

        /** 生成配置文件 */
        $text = '';
        $text .= 'database_hostname='.session('config.db_host')."\r\n";
        $text .= 'database_database='.session('config.db_name')."\r\n";
        $text .= 'database_username='.session('config.db_user')."\r\n";
        $text .= 'database_password='.session('config.db_pass')."\r\n";
        $text .= 'database_prefix='.session('config.db_prefix')."\r\n";

        if (! file_put_contents(ROOT_PATH . DS . '.env', $text)) {
            $this->error('配置文件生成失败，请检测目录权限');
        }

        return view('install/step4');
    }

    public function postStep4()
    {
        if (request()->post('action') == 'db') {
            $action = 'installTable_' . request()->post('val');
            try {
                $this->$action(session('config.db_prefix'));
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

            $this->success('安装成功');
        }

        if (request()->post('action') == 'data') {
            try {
                $this->installDbData();
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }

            $this->success('安装成功');
        }

        if (request()->post('action') == 'hook') {
            $result = $this->installHook();
            $result ? $this->success('安装成功') : $this->error('生成钩子文件失败');
        }

        if (request()->post('action') == 'lock') {
            $result = $this->genLockFile();
            $result ? $this->success('安装成功') : $this->error('生成锁文件失败');
        }
    }

    protected function installTable_addons($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}addons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addons_sign` varchar(24) NOT NULL COMMENT '插件标识符',
  `addons_name` varchar(64) NOT NULL COMMENT '插件名',
  `addons_description` varchar(255) NOT NULL COMMENT '插件描述',
  `addons_author` varchar(24) NOT NULL COMMENT '插件作者',
  `addons_version` varchar(24) NOT NULL COMMENT '插件版本',
  `addons_config` text NOT NULL COMMENT '插件配置，json格式',
  `addons_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1正常 3禁用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installTable_addons_hooks($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}addons_hooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addons_id` int(11) NOT NULL,
  `hook_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installTable_admins($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) NOT NULL COMMENT '用户名',
  `password` varchar(64) NOT NULL COMMENT '密码',
  `last_login_ip` varchar(32) NOT NULL COMMENT '最后登录IP',
  `last_login_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后登录时间',
  `login_times` int(11) NOT NULL DEFAULT '0' COMMENT '登录次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installTable_hooks($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}hooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hook_type` varchar(20) NOT NULL DEFAULT 'model' COMMENT '钩子类型',
  `hook_name` varchar(20) NOT NULL COMMENT 'Hook名',
  `hook_sign` varchar(64) NOT NULL COMMENT 'Hook标识符',
  `hook_description` varchar(255) NOT NULL DEFAULT '' COMMENT 'Hook介绍',
  `hook_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1正常 3禁用',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installTable_migrations($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}migrations` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installTable_reply_contents($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}reply_contents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) NOT NULL,
  `sort` smallint(6) NOT NULL COMMENT '小靠前',
  `type` varchar(10) NOT NULL DEFAULT 'text' COMMENT '回复类型',
  `content` text NOT NULL COMMENT '序列化内容',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1正常-1停止',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installTable_reply_rules($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}reply_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(32) NOT NULL COMMENT '规则名',
  `rule_sort` smallint(6) NOT NULL COMMENT '小靠前',
  `rule_type` varchar(12) NOT NULL COMMENT '匹配规则',
  `rule_content` varchar(32) NOT NULL COMMENT '规则名',
  `rule_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1正常-1停止',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installTable_settings($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wefee_key` varchar(255) NOT NULL,
  `wefee_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installTable_wechat_focus_records($prefix)
    {
        Db::execute("
CREATE TABLE IF NOT EXISTS `{$prefix}wechat_focus_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `focus_submit_num` int(11) NOT NULL,
  `focus_cancel_num` int(11) NOT NULL,
  `focus_confirm_num` int(11) NOT NULL,
  `focus_all_num` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function installDbData()
    {
        /** 管理员 */
        $this->installDbAdministrator();

        /** 钩子安装 */
        $this->installDbHook();

        /** 系统配置 */
        $this->installDbSettins();
    }

    protected function installDbAdministrator()
    {
        $admin = new Admins;
        $admin->username = session('config.username');
        $admin->password = Hash::make(session('config.password'));
        $admin->last_login_ip = '127.0.0.1';
        $admin->save();

        return true;
    }

    protected function installDbHook()
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

        $hook = new Hooks;
        $hook->saveAll($data);

        return true;
    }

    protected function installDbSettins()
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

        Db::table(full_table('settings'))->insertAll($init);
    }

    protected function installHook()
    {
        $content = file_get_contents(ROOT_PATH . DS . 'data' . DS . 'install' . DS . 'tags.php');
        return file_put_contents(ROOT_PATH . DS . 'app' . DS . 'tags.php', $content);
    }

    protected function genLockFile()
    {
        return file_put_contents(ROOT_PATH . DS . 'data' . DS . 'install' . DS . 'install.lock', date('Y-m-d H:i:s'));
    }

}