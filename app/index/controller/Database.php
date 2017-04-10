<?php
namespace app\index\controller;

use think\Db;
use think\Request;
use app\common\controller\Base;

class Database extends Base
{

    protected $path = '';

    public function _initialize()
    {
        parent::_initialize();

        $this->path = ROOT_PATH . 'data' . DS . 'backup';
    }

    /** 备份数据库界面 */
    public function backup()
    {
        $tables = $this->getAllTables();

        $title = '数据库备份';

        return view('', compact('title', 'tables'));
    }

    /**
     * 获取全部的表
     * @return array
     */
    protected function getAllTables()
    {
        $mysql = Db::query('show tables;');
        $tables = [];
        foreach ($mysql as $val) {
            $tmp = [];
            $key = array_keys($val);
            $tmp['name'] = $val[$key[0]];
            $tmp['count'] = Db::table($tmp['name'])->count();
            $tables[] = $tmp;
        }

        return $tables;
    }

    /**
     * 提交备份
     */
    public function postBackup(Request $request)
    {
        $this->checkToken($request);

        $tables = $request->post('table/a');

        !$tables && $this->error('请选择需要备份的表');

        $sql = "-- Wefee Backup System\r\n";
        $sql .= "-- date:".date('Y-m-d H:i:s', time())."\r\n";
        $sql .= "\r\n\r\n\r\n";
        foreach ($tables as $table) {
            $sql .= $this->getBackSqlInTable($table);
        }

        /** 创建备份文件 */
        !is_dir($this->path) && $this->error('备份文件夹不存在');

        /** 存储文件名 */
        $file = $this->path . DS . date('YmdHis') . '.sql';
        if (!file_put_contents($file, $sql)) {
            $this->error('备份失败，原因：创建备份文件失败，请检查权限！');
        }

        $this->success('备份成功');
    }

    /**
     * 获取表的备份SQL语句
     * @param string $table 表名
     * @return string SQL语句
     */
    protected function getBackSqlInTable($table)
    {
        $sql = "";
        /** 表的删除语句 */
        $sql .= "DROP TABLE IF EXISTS {$table};\r\n";

        /** 表的创建语句 */
        $tmp = Db::query("show create table {$table};");
        $createSql = $tmp[0]['Create Table'];
        $sql .= $createSql . ";\r\n";

        /** 表的记录语句 -> 插入 */
        $records = Db::table($table)->select();
        $insertSql = '';
        foreach ($records as $record) {
            /** 获取表中的字段 */
            $fields = implode(',', array_keys($record));
            /** 获取值 */
            $tmp = array_map(function ($val) {
                return '"'.addslashes($val).'"';
            }, $record);
            $values = implode(',', $tmp);
            /** 拼接插入语句 */
            $insertSql .= "INSERT INTO {$table} ({$fields}) VALUES ({$values});\r\n";
        }
        $sql .= $insertSql;

        return $sql;
    }

    /** 还原 */
    public function restore()
    {
        /** 获取已经备份的文件 */
        $files = scandir($this->path);

        $backupFiles = [];
        foreach ($files as $file) {
            if (substr($file, -3 , 3) == 'sql') {
                $tmp = [];
                $filepath = $this->path . DS . $file;
                /** 文件名 */
                $tmp['name'] = $file;
                /** 文件大小 */
                $size = floatval(filesize($filepath) / 1024);
                $size = $size >= 1024 ? round(floatval($size / 1024), 2) . 'MB' : round($size, 2) . 'KB';
                $tmp['size'] = $size;
                /** 文件创建时间 */
                $tmp['created_at'] = date('Y-m-d H:i:s', filemtime($filepath));

                $backupFiles[] = $tmp;
            }
        }

        $title = '数据库恢复';

        return view('', compact('title', 'backupFiles'));
    }

    /**
     * POST提交还原，验证token,防止CSRF攻击
     */
    public function postRestore(Request $request)
    {
        $this->checkToken($request);

        /** 文件存在检测 */
        $pathname = $this->path . DS . $request->post('file');
        !file_exists($pathname) && $this->error('备份文件不存在');

        /** 读取备份文件 */
        $text = @file_get_contents($pathname);
        !$text && $this->error('读取文件错误，请检查权限.');

        /** 切割SQL成单句 */
        $sqls = explode("\r\n", $text);

        /** 执行SQL */
        $success = 0;
        $error = 0;
        $errorSqls = [];
        foreach ($sqls as $sql) {
            if (substr($sql, 0, 1) == '#' || substr($sql, 0, 2) == '--' || $sql == '') {
                continue;
            }

            try {
                Db::query($sql);
            } catch (\Exception $e) {
                $error++;
                $errorSqls[] = $sql;
                continue;
            }
            $success++;
        }

        if ($error == 0) {
            $this->success('数据库还原成功');
        }

        header('Content-type:text/html,charset=utf8;');
        echo '成功条数：'.$success.'<br />';
        echo '失败条数：'.$error.'<br />';
        echo '失败Sql语句：<br />';
        foreach ($errorSqls as $key => $sql) {
            echo '<b>'.$key.'：</b>'.$sql.'<br />';
        }
    }


    /**
     * 删除备份文件
     */
    public function deleteBackupFile(Request $request)
    {
        $file = $this->path . DS . $request->param('file');

        $obj = new \phootwork\file\File($file);

        try {
            $obj->delete();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('操作成功');
    }

    public function optimize()
    {
        /** 获取需要优化的表 */
        $mysql = Db::query('SHOW TABLE STATUS;');
        $tables = [];
        foreach ($mysql as $table) {
            if ($table['Engine'] == 'InnoDB') {
                continue;
            }
            if (! empty($table['Data_free'])) {
                $tables[] = $table;
            }
        }

        $title = '数据库优化';

        return view('', compact('title', 'tables'));
    }

    /**
     * 优化数据库
     */
    public function postOptimize(Request $request)
    {
        $this->checkToken($request);

        $tables = $request->post('table/a');

        !$tables && $this->error('请选择需要优化的数据表');

        $tableString = implode(',', $tables);

        Db::query('OPTIMIZE TABLE '.$tableString.';');

        $this->success('优化成功');
    }

}