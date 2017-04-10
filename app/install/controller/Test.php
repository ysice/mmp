<?php namespace app\install\controller;

use think\Controller;

class Test extends Controller
{

    public function db()
    {
        $db_host = request()->param('db_host');
        $db_port = request()->param('db_port');
        $db_name = request()->param('db_name');
        $db_user = request()->param('db_user');
        $db_pass = request()->param('db_pass');
        $db_prefix = request()->param('db_prefix');

        try {
            new \PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name}", $db_user, $db_pass);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('连接成功');
    }

}