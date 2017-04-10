<?php namespace app\model;

use think\Model;

class Hooks extends Model
{

    public function addons()
    {
        return $this->hasMany('app\model\AddonsHooks', 'hook_id');
    }

}