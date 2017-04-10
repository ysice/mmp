<?php namespace app\wechat\controller;

use think\Request;
use app\wefee\Tree;
use app\common\controller\Base;

class Service extends Base
{

    protected $voiceDir = '';

    protected $videoDir = '';

    protected $thumbDir = '';

    public function _initialize()
    {
        parent::_initialize();

        $this->voiceDir = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'voices';
        $this->voiceDir = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'video';
        $this->thumbDir = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'thumb';
    }

    /** 上传图片到微信 */
    public function uploadImage(Request $request)
    {
        $file = $request->param('file');

        if ($file == '') {
            return json(['error' => '请选择文件。']);
        }

        $tmp = explode('images', $file);

        $path = realpath(ROOT_PATH . DS . 'public' . DS . 'images' . DS . $tmp[1]);

        if (! $path) {
            return json(['error' => '文件不存在']);
        }

        $res = json_decode(Tree::wechat()->material_temporary->uploadImage($path), true);

        if (isset($res['errcode'])) {
            return json(['error' => '获取MediaID失败！可能原因：文件过大，文件敏感。']);
        }

        return json(['success' => $res['media_id']]);
    }

    public function uploadVoice(Request $request)
    {
        $file = $request->file('file');

        $result = $file->validate([
            'size' => 1024 * 2048,
            'ext'  => 'mp3,amr',
        ])->move($this->voiceDir);

        if (! $result) {
            return json(['error' => $file->getError()]);
        }

        $path = $this->voiceDir . DS . $result->getSaveName();

        /** 上传到Wechat */
        $res = json_decode(Tree::wechat()->material_temporary->uploadVoice($path), true);

        if (isset($res['errcode'])) {
            return json(['error' => '获取音频MediaID失败！可能原因：文件过大。']);
        }

        return json(['success' => $res['media_id']]);
    }

    public function uploadVideo(Request $request)
    {
        $file = $request->file('file');

        if (is_null($file)) {
            return json(['error' => '请上传文件']);
        }

        $result = $file->validate([
            'size' => 1024 * 1024 * 10,
            'ext'  => 'mp4',
        ])->move($this->videoDir);

        if (! $result) {
            return json(['error' => $file->getError()]);
        }

        $path = $this->videoDir . DS . $result->getSaveName();

        /** 上传到Wechat */
        $res = json_decode(Tree::wechat()->material_temporary->uploadVideo($path), true);

        if (isset($res['errcode'])) {
            return json(['error' => '获取视频MediaID失败！可能原因：文件过大。']);
        }

        return json(['success' => $res['media_id']]);
    }

    public function uploadThumb(Request $request)
    {
        $file = $request->file('file');

        $result = $file->validate([
            'size' => 1024 * 64,
            'ext'  => 'jpg',
        ])->move($this->thumbDir);

        if (! $result) {
            return json(['error' => $file->getError()]);
        }

        $path = $this->thumbDir . DS . $result->getSaveName();

        $res = json_decode(Tree::wechat()->material_temporary->uploadThumb($path), true);

        if (isset($res['errcode'])) {
            return json(['error' => '获取缩率图MediaID失败！可能原因：文件过大，文件敏感。']);
        }

        return json(['success' => $res['thumb_media_id']]);
    }

}