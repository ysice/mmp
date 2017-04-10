<?php
namespace app\repository;

use Qsnh\think\Repository\Repository;

class HooksRepository extends Repository
{

    protected $table = 'wefee_hooks';

    protected $fields = [
        'id',
        'hook_type',
        'hook_name',
        'hook_sign',
        'hook_description',
        'hook_thinks',
        'hook_status',
        'updated_at',
    ];

}