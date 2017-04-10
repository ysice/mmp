<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateAddonsTable extends AbstractMigration
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
        $table = $this->table('addons');
        $table->addColumn('addons_sign', 'string', ['limit' => 24, 'comment' => '插件标识符'])
            ->addColumn('addons_name', 'string', ['limit' => 64, 'comment' => '插件名'])
            ->addColumn('addons_description', 'string', ['limit' => 255, 'comment' => '插件描述'])
            ->addColumn('addons_author', 'string', ['limit' => 24, 'comment' => '插件作者'])
            ->addColumn('addons_version', 'string', ['limit' => 24, 'comment' => '插件版本'])
            ->addColumn('addons_config', 'text', ['comment' => '插件配置，json格式'])
            ->addColumn('addons_status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'comment' => '1正常 3禁用', 'default' => 1])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp')
            ->save();
    }

    public function down()
    {
        if ($this->hasTable('addons')) {
            $this->dropTable('addons');
        }
    }

}
