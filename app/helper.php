<?php
if (!function_exists('full_table')) {
    /**
     * 补全数据库表的前缀
     * @param string $table_name 数据表名
     * @return string
     */
    function full_table($table_name)
    {
        return config('database.prefix') . $table_name;
    }
}

if (!function_exists('table_exists')) {
    /**
     * 检测数据表是否存在
     * @param string $table 表名
     * @return boolean
     */
    function table_exists($table)
    {
        $result = \think\Db::query('show tables;');
        if (is_null($table)) {
            return false;
        }
        $tables = [];
        foreach ($result as $val) {
            $tables[] = $val['Tables_in_'.config('database.database')];
        }
        return in_array($table, $tables);
    }
}
if (!function_exists('get_wechat_config')) {
    /**
     * 获取 Easywechat 需要的微信配置格式数组
     * @return array
     */
    function get_wechat_config()
    {
        return [
            'debug'  => true,

            'app_id'  => config('wefee.wechat_appid'),
            'secret'  => config('wefee.wechat_appsecret'),
            'token'   => config('wefee.wechat_token'),
            'aes_key' => config('wefee.wechat_aeskey'),

            'log' => [
                'level' => 'debug',
                'file'  => LOG_PATH . 'easywechat.log',
            ],

            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => url('wechat/wechat/webNotify'),
            ],

            'payment' => [
                'merchant_id'        => config('wefee.wepay_mchid'),
                'key'                => config('wefee.wepay_key'),
                'cert_path'          => ROOT_PATH . config('wefee.wepay_public_pem'),
                'key_path'           => ROOT_PATH . config('wefee.wepay_private_pem'),
            ],

            'guzzle' => [
                'timeout' => 30,
                'verify' => false,
            ],

        ];
    }
}

if (!function_exists('get_addon_logo')) {
    /**
     * 获取插件的封面
     * @param string $addons_sign 插件的标识符
     * @return string logo
     */
    function get_addon_logo_name($addons_sign)
    {
        $path = APP_PATH . '../addons/' . $addons_sign . '/';

        if ($result = glob($path . 'icon.*')) {
            return basename($result[0]);
        }

        return 'logo.png';
    }
}

if (!function_exists('get_method_by_hook_name')) {
    /**
     * 将hook名转换为首字母小写的驼峰法写法
     * @param string $hook_name
     * @return string
     */
    function get_method_by_hook_name($hook_name)
    {
        $preg = '#(-[0-9A-Za-z]{0,1})#';

        $name = preg_replace_callback($preg, function ($m) {
            return strtoupper(substr($m[0], 1));
        }, $hook_name);

        return $name;
    }
}

if (!function_exists('aurl')) {
    /**
     * 生成插件的控制器访问URL
     * @param string $path 格式：插件标识/控制器/方法
     * @param array $params 附加参数
     * @return string
     */
    function aurl($path, $params = [])
    {
        $path = explode('/', ltrim($path, '/'));

        if (! isset($path[0])) {
            throw new \Exception('Addons Url Error');
        }

        $data = [
            'addons'     => $path[0],
            'controller' => isset($path[1]) ? $path[1] : 'index',
            'action'     => isset($path[2]) ? $path[2] : 'index',
        ];

        $data = array_merge($params, $data);

        return url('addons/api/plus', $data);
    }
}

if (!function_exists('data_size')) {
    /**
     * 将整形的字节大小转换为易读的kb或MB形式
     * @param integer $size 字节大小
     * @return string
     */
    function data_size($size)
    {
        if ($size < 1024) {
            return $size . ' byte';
        }

        $kb = $size / 1024;

        if ($kb < 1024) {
            return round($kb, 2) . ' KB';
        }

        $mb = $kb / 1024;

        if ($mb < 1024) {
            return round($mb, 2) . ' MB';
        }

        $gb = $mb / 1024;

        return round($gb, 2) . ' GB';
    }
}

if (!function_exists('wechat_subscribe_event')) {
    /**
     * 微信关注，取消关注处理函数
     * @param integer $type 操作类型 1:关注 -1:取消关注
     * @return void
     */
    function wechat_subscribe_event($type = 1)
    {
        /** 1.检测今天记录是否创建 */
        $r = \think\Db::table(full_table('wechat_focus_records'))->whereTime('created_at', 'today')->find();

        if (! $r) {
            /** 获取昨天的记录 用于总关注数量传递 */
            $yesterday = \think\Db::table(full_table('wechat_focus_records'))->whereTime('created_at', 'yesterday')->find();

            /** 未创建记录，创建记录 */
            $id = \think\Db::table(full_table('wechat_focus_records'))->insertGetId([
                'focus_submit_num'  => 0,
                'focus_cancel_num'  => 0,
                'focus_confirm_num' => 0,
                'focus_all_num'     => $yesterday ? $yesterday['focus_all_num'] : 0,
                'created_at'        => date('Y-m-d', time()),
            ]);
        } else {
            $id = $r['id'];
        }

        /** 2.记录 */
        if ($type == 1) {
            \think\Db::table(full_table('wechat_focus_records'))
                ->where('id', $id)
                ->inc('focus_submit_num')
                ->inc('focus_confirm_num')
                ->inc('focus_all_num')
                ->update();
        } elseif ($type == -1) {
            \think\Db::table(full_table('wechat_focus_records'))
                ->where('id', $id)
                ->inc('focus_cancel_num')
                ->dec('focus_confirm_num')
                ->dec('focus_all_num')
                ->update();
        } else {
            //none
        }

        return ;
    }
}

if (!function_exists('env')) {
    /**
     * \think\Env的封装
     * @param string $key 键
     * @param mixed $default 默认值，没有获取到值的情况下返回
     * @return mixed
     */
    function env($key, $default = null)
    {
        return \think\Env::get($key, $default);
    }
}

if (!function_exists('get_addon_config')) {
    /**
     * 获取插件的配置信息
     * @param string $addon_sign 插件标识
     * @return array
     */
    function get_addon_config($addon_sign, $key = '')
    {
        $cacheKey = $addon_sign.'-config';
        cache($cacheKey, null);
        $config = cache($cacheKey);
        if ($config === false) {
            $addon = \think\Db::table(full_table('addons'))->field(['addons_config'])->where('addons_sign', $addon_sign)->find();
            if (! $addon) {
                throw new \DataNotFoundException();
            }
            $config = $addon['addons_config'] != '' ? unserialize($addon['addons_config']) : [];
            cache($cacheKey, $config);
        }

        if (! empty($config)) {
            if ('' == $key) {
                return $config;
            }

            return isset($config[$key]) ? $config[$key] : null;
        }

        return [];
    }
}

if (! function_exists('copy_all')) {
    /**
     * 将一个目录下的所有文件复制到另一个目录下
     * @param string $original 等待复制的目录
     * @param string $dest 需要复制到的目录
     */
    function copy_all($original, $dest)
    {
        if (! $lists = glob($original . '/*')) {
            return ;
        }

        foreach ($lists as $item) {
            $val = str_replace($original, '', $item);
            if (preg_match('#(.*)\.(.*)#ius', $val)) {
                /** 文件Copy */
                @mkdir($dest, 0777, true);
                copy($item, $dest . $val);
            } else {
                /** 递归 */
                copy_all($item, $dest . $val);
            }
        }
    }
}

if (! function_exists('delete_dir')) {
    /**
     * 删除目录
     * @param string $dest 待删除的目录
     * @return void
     */
    function delete_dir($dest)
    {
        if (! is_dir($dest)) {
            return ;
        }
        $dest = realpath($dest);

        if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
            exec('rmdir /s/q '. $dest);
        } else {
            exec('rm -rf '. $dest);
        }
    }
}

if (! function_exists('get_all_files')) {
    /**
     * 获取目录下所有的文件，包括子目录
     * @param string $path 目标路径
     * @return array
     */
    function get_all_files($path)
    {
        $box = [];

        $path = rtrim($path, DS) . DS;

        $files = \Anekdotes\File\File::files($path);

        $box = array_merge($box, $files);

        $dirs = \Anekdotes\File\File::directories($path);

        foreach ($dirs as $item) {
            $box = array_merge($box, get_all_files($item));
        }

        return $box;
    }
}

if (! function_exists('wefee_get')) {
    /**
     * GET请求方法
     * @param string $url 请求地址
     * @param array $data 发送参数
     * @param bool $safe SSL验证
     * @return \GuzzleHttp\Psr7\Response
     */
    function wefee_get($url, $data = [],  $safe = false)
    {
        return wefee_request('GET', $url, $data, $safe);
    }
}

if (! function_exists('wefee_post')) {
    /**
     * POST请求方法
     * @param string $url 请求地址
     * @param array $data 发送参数可选
     * @param bool $safe SSL验证
     * @return \GuzzleHttp\Psr7\Response
     */
    function wefee_post($url, $data = [], $safe = false)
    {
        return wefee_request('POST', $url, $data, $safe);
    }
}

if (! function_exists('wefee_request')) {
    /**
     * HTTP请求方法
     * @param string $method 请求方法
     * @param string $url 请求地址
     * @param array $data 发送参数可选
     * @param bool $safe SSL验证
     * @return \GuzzleHttp\Psr7\Response
     */
    function wefee_request($method, $url, $data = [], $safe = false)
    {
        $client = new \GuzzleHttp\Client([
            'verify' => $safe,
        ]);

        $res = $client->request(strtoupper($method), $url, $data);

        return $res;
    }
}