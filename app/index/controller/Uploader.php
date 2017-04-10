<?php namespace app\index\controller;

use Qsnh\think\Upload\Upload;
use app\common\controller\Base;

class Uploader extends Base
{

    public function image()
    {
        $upload = new Upload(config('upload'));

        $result = $upload->upload();

        if (!$result) {
            exit(json_encode([
                'status' => 1,
                'message' => $upload->getError(),
            ]));
        }

        exit(json_encode([
            'status' => 0,
            'message' => $result,
        ]));
    }

}