<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateWefeeAdminsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */

    public function up()
    {
        $table = $this->table('admins');
        $table->addColumn('username', 'string', ['limit' => 32, 'comment' => '用户名'])
            ->addColumn('password', 'string', ['limit' => 64, 'comment' => '密码'])
            ->addColumn('last_login_ip', 'string', ['limit' => 32, 'comment' => '最后登录IP'])
            ->addColumn('last_login_date', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP', 'comment' => '最后登录时间'])
            ->addColumn('login_times', 'integer', ['limit' => MysqlAdapter::INT_REGULAR, 'default' => 0, 'comment' => '登录次数'])
            ->save();
    }

    public function down()
    {
        if ($this->hasTable('admins')) {
            $this->dropTable('admins');
        }
    }
}
