<?php
namespace app\repository;

use Qsnh\think\Repository\Repository;

class AddonsRepository extends Repository
{
    protected $table = 'wefee_addons';

    protected $fields = [
        'id',
        'addons_sign',
        'addons_name',
        'addons_description',
        'addons_author',
        'addons_version',
        'addons_config',
        'addons_status',
        'created_at',
        'updated_at',
    ];

}