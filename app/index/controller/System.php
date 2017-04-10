<?php
namespace app\index\controller;

use think\Db;
use app\common\controller\Base;

class System extends Base
{

    public function index()
    {
        /** 获取系统信息 */
        $system = [];

        /** 程序版本 */
        $version = ROOT_PATH . 'public/version.txt';
        $system['app_version'] = file_exists($version) ? file_get_contents($version) : '文件丢失';

        /** PHP版本 */
        $system['php_version'] = 'PHP ' . PHP_VERSION;

        /** Mysql版本 */
        $mysql = Db::query('select version() as v;');
        $system['mysql_version'] = 'MySql '. $mysql[0]['v'];

        /** 服务器软件 */
        $system['server_version'] = $_SERVER['SERVER_SOFTWARE'];

        /** GD库信息 */
        if (function_exists('gd_info')) {
            $gd = gd_info();
            $system['gd_version'] = $gd['GD Version'];
        } else {
            $system['gd_version'] = '不支持';
        }

        /** 最大上传限制 */
        $system['upload_max'] = ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Disabled';

        /** 最大执行时间 */
        $system['exec_max'] = ini_get('max_execution_time') . '秒';

        /** 服务器时间 */
        $system['date'] = date('Y-m-d H:i:s');

        $title =  '系统信息';

        return view('', compact('title', 'system'));
    }

}