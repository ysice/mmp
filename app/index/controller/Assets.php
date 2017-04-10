<?php namespace app\index\controller;

use app\common\controller\Base;
use think\Request;

class Assets extends Base
{

    protected $imageDir = '';

    public function _initialize()
    {
        parent::_initialize();

        $this->imageDir = ROOT_PATH . 'public' . DS . 'images' . DS;

    }

    public function images()
    {
        $images = get_all_files($this->imageDir);

        $images = array_reverse($images);

        foreach ($images as $key => $item) {
            $images[$key] = str_replace($this->imageDir, '', $item);
        }

        return view('', compact('images'));
    }

    public function voices()
    {
        return view('');
    }

    public function videos()
    {
        return view('');
    }

    public function thumbs()
    {
        return view('');
    }

}