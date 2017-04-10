<?php namespace app\model;

use think\Model;

class AddonsHooks extends Model
{

    public function addons()
    {
        return $this->belongsTo('app\model\Addons', 'addons_id');
    }

    public function hook()
    {
        return $this->belongsTo('app\model\Hooks', 'hook_id');
    }

}