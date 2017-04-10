<?php namespace app\index\controller;

use think\Request;
use app\model\ReplyRules;
use think\Validate;
use app\common\controller;
use app\model\ReplyContents;

class Reply extends controller\Base
{

    public function add(Request $request)
    {
        $rule = $this->getRule($request->param('rule_id'));

        $title = '添加回复内容';

        return view('', compact('title', 'rule'));
    }

    public function create(Request $request)
    {
        $data = $request->only([
            'rule_id', 'sort', 'type', 'status',
        ]);

        $this->validator($data);

        $data['content'] = serialize($this->getMessageContent($request));

        $reply = new ReplyContents($data);
        $reply->save();

        $this->success('操作成功');
    }

    public function edit(Request $request)
    {
        $reply = $this->getReply($request->param('id'));

        $reply->content = unserialize($reply->content);

        $title = '编辑回复内容';

        return view('', compact('title', 'reply'));
    }

    public function update(Request $request)
    {
        $reply = $this->getReply($request->param('id'));

        $data = $request->only([
            'sort', 'type', 'status',
        ]);

        $this->validator($data);

        $data['content'] = serialize($this->getMessageContent($request));

        $reply->save($data);

        $this->success('操作成功');
    }

    protected function getMessageContent(Request $request)
    {
        $box = [];
        switch ($request->param('type')) {
            case 'text':
                $box['content'] = $request->param('content');
                ($box['content'] == '') && $this->error('请填写完成数据！');
                break;
            case 'image':
                $box['media_id'] = $request->param('image_media_id');
                ($box['media_id'] == '') && $this->error('请填写完成数据！');
                break;
            case 'voice':
                $box['media_id'] = $request->param('voice_media_id');
                ($box['media_id'] == '') && $this->error('请填写完成数据！');
                break;
            case 'video':
                $box = [
                    'title'       => $request->param('video_title'),
                    'description' => $request->param('video_des'),
                    'media_id'    => $request->param('video_media_id'),
                ];

                if ($box['title'] == '' || $box['description'] == '' || $box['media_id'] == '') {
                    $this->error('请填写完整数据！');
                }
                break;
            case 'news':
                $title = $request->param('news_title/a');
                $des   = $request->param('news_des/a');
                $image = $request->param('news_image/a');
                $url   = $request->param('news_url/a');

                foreach ($title as $key => $val) {
                    $tmp = [
                        'title'       => $val,
                        'description' => $des[$key],
                        'image'       => $image[$key],
                        'url'         => $url[$key],
                    ];
                    if ($tmp['title'] == '' || $tmp['description'] == '' || $tmp['url'] == '') {
                        continue;
                    }
                    $box[] = $tmp;
                    /** 最多8条数据 */
                    if (count($box) >= 8) {
                        break;
                    }
                }

                if (! $box) {
                    $this->error('请填写完整数据！');
                }
                break;
        }

        return $box;
    }

    protected function validator(array $data)
    {
        $validator = new Validate([
            'sort|排序' => 'require',
            'type|类型' => 'require',
            'status|状态' => 'require',
        ]);

        if (! $validator->check($data)) {
            $this->error($validator->getError());
        }
    }

    /**
     * 获取单条规则
     * @param integer $id 规则ID
     * @return \app\model\Rule
     */
    protected function getRule($id)
    {
        $rule = ReplyRules::get($id);

        if (! $rule) {
            $this->error('规则不存在');
        }

        return $rule;
    }

    /**
     * 获取单条回复内容
     * @param integer $id 回复内容ID
     * @return \app\model\ReplyContent
     */
    protected function getReply($id)
    {
        $reply = ReplyContents::get($id);

        if (! $reply) {
            $this->error('回复内容不存在');
        }

        return $reply;
    }

    public function delete(Request $request)
    {
        ReplyContents::destroy($request->param('id'));

        $this->success('操作成功');
    }

}