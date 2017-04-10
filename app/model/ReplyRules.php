<?php namespace app\model;

class ReplyRules extends Wefee
{

    public function replies()
    {
        return $this->hasMany('ReplyContents', 'rule_id');
    }

}