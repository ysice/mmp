<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

class CreateReplyRulesTable extends AbstractMigration
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
        $table = $this->table('reply_rules');

        $table
            ->addColumn('rule_name', 'string', ['limit' => 32, 'comment' => '规则名'])
            ->addColumn('rule_sort', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'comment' => '小靠前'])
            ->addColumn('rule_type', 'string', ['limit' => 12, 'comment' => '匹配规则'])
            ->addColumn('rule_content', 'string', ['limit' => 32, 'comment' => '规则名'])
            ->addColumn('rule_status', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'default' => 1, 'comment' => '1正常-1停止'])
            ->addColumn('created_at', 'timestamp')
            ->addColumn('updated_at', 'timestamp')
            ->save();
    }

    public function down()
    {
        if ($this->hasTable('reply_rules')) {
            $this->dropTable('reply_rules');
        }
    }
}
