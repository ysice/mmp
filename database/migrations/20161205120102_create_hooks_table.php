<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateHooksTable extends AbstractMigration
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
        $table = $this->table('hooks');
        $table
            ->addColumn('hook_type', 'string', ['limit' => 20, 'default' => 'model', 'comment' => '钩子类型'])
            ->addColumn('hook_name', 'string', ['limit' => 20, 'comment' => 'Hook名'])
            ->addColumn('hook_sign', 'string', ['limit' => 64, 'comment' => 'Hook标识符'])
            ->addColumn('hook_description', 'string', ['limit' => 255, 'default' => '', 'comment' => 'Hook介绍'])
            ->addColumn('hook_status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'comment' => '1正常 3禁用', 'default' => 1])
            ->addColumn('updated_at', 'timestamp')
            ->save();
    }

    public function down()
    {
        if ($this->hasTable('hooks')) {
            $this->dropTable('hooks');
        }
    }

}
