<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateWechatFocusRecordsTable extends AbstractMigration
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
        $table = $this->table('wechat_focus_records');

        $table
            ->addColumn('focus_submit_num', 'integer')
            ->addColumn('focus_cancel_num', 'integer')
            ->addColumn('focus_confirm_num', 'integer')
            ->addColumn('focus_all_num', 'integer')
            ->addColumn('created_at', 'timestamp')
            ->save();
    }

    public function down()
    {
        if ($this->hasTable('wechat_focus_records')) {
            $this->dropTable('wechat_focus_records');
        }
    }

}
