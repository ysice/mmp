<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAddonsHooksTable extends Migrator
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
        $table = $this->table('addons_hooks');

        $table
            ->addColumn('addons_id', 'integer')
            ->addColumn('hook_id', 'integer')
            ->save();
    }


    public function down()
    {
        if ($this->hasTable('addons_hooks')) {
            $this->dropTable('addons_hooks');
        }
    }

}
