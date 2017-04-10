<?php namespace app\model;

class ReplyContents extends Wefee
{

    public function rule()
    {
        return $this->belongsTo('ReplyRules', 'rule_id');
    }

}