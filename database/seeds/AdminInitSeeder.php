<?php

use think\helper;
use Phinx\Seed\AbstractSeed;

class AdminInitSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $init = [
            'username'      => 'admin',
            'password'      => helper\Hash::make('ilovewefee'),
            'last_login_ip' => '127.0.0.1',
        ];

        $table = $this->table('admins');

        $table->insert($init)->save();
    }
}
