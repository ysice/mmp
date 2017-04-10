<?php namespace app\wefee\components\taglib;

use think\template\TagLib;

class Wefee extends TagLib
{

    protected $tags = [
        'image' => ['attr' => 'title,name,value', 'close' => 0],
        'multiimage' => ['attr' => 'title,name,value', 'close' => 0],
        'date' => ['attr' => 'title,name,value', 'close' => 0],
        'daterange' => ['attr' => 'title,name,value', 'close' => 0],
        'color' => ['attr' => 'title,name,value', 'close' => 0],
        'umeditor' => ['attr' => 'title,name,content', 'close' => 0],
        'province' => ['attr' => 'title,province,city,area', 'close' => 0],
    ];

    /**
     * 上传单个图片组件
     * 调用方式：
     * {wefee:uploadsingleimage name="" title="" value=""/}
     * $name string 字段名
     * $title string 提示文本
     * $value mixed 默认值
     */
    public function tagImage($tag)
    {
        /** 上传地址 */
        $url = url('index/uploader/image');
        /** 属性 */
        $title = isset($tag['title']) ? $tag['title'] : '';
        $name  = isset($tag['name']) ? $tag['name'] : 'image';
        $flag = substr($tag['value'], 0, 1);
        if ('$' == $flag || ':' == $flag) {
            $value = $this->autoBuildVar($tag['value']);
        } else {
            $value = '"'.$tag['value'].'"';
        }
        /** // */
        $id = mt_rand(1, 100) . $name;

        $html = <<<HTML
<div class="form-group">
    <label class="col-sm-2 control-label">$title</label>
    <div class="col-sm-10">
        <div class="input-group">
            <input type="text" name="$name" value="<?php echo $value; ?>" class="form-control" placeholder="请选择图片">
            <div class="input-group-addon $id">选择图片</div>
        </div>
        <div style="display: none" id="$id"></div>
    </div>
    <div class="col-sm-10 col-sm-offset-2" style="margin-top: 5px">
        <img src="<?php echo $value; ?>" width="150" height="150" id="{$id}preview" class="img-thumbnail">
    </div>
</div>
<script >
require(['jquery', 'webuploader'], function ($, wu) {
    $('.$id').click(function () {
        $('#$id input[name="file"]').click();
    });
    var uploader = wu.create({
        auto: true,
        server: '$url',
        pick: {
            id: '#{$id}',
            multiple: false
        },
        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/*'
        }
    });
    uploader.on('uploadSuccess', function(file, response) {
        if (response.status != 0) {
            alert(response.message);
            return ;
        }
        $('input[name="$name"]').val(response.message);
        $('#{$id}preview').attr('src', response.message);
    });
});
</script>
HTML;

        return $html;
    }

    /**
     * 多图片上传组件
     * 调用方式：同上
     */
    public function tagMultiimage($tag)
    {
        /** 上传地址 */
        $url = url('index/uploader/image');
        /** 属性 */
        $title = isset($tag['title']) ? $tag['title'] : '';
        $name  = isset($tag['name']) ? $tag['name'] : 'image';
        /** 默认值循环遍历 */
        $flag = substr($tag['value'], 0, 1);
        if ('$' == $flag || ':' == $flag) {
            $value = $this->autoBuildVar($tag['value']);
        } else {
            $value = '[]';
        }
        /** // */
        $id = mt_rand(1, 100) . $name;

        $html = <<<HTML
<div class="form-group">
    <label class="col-sm-2 control-label">$title</label>
    <div class="col-sm-10">
        <div class="input-group">
            <input type="text" disabled class="form-control" placeholder="请选择图片">
            <div class="input-group-addon $id">选择图片</div>
        </div>
        <div style="display: none" id="$id"></div>
    </div>
    <div class="col-sm-10 col-sm-offset-2" style="margin-top: 5px">
        <div class="row {$id}-preview">
        <?php
        foreach ({$value} as \$val) {
            echo '<div class="col-sm-3" style="display: relative;">
                      <b class="delete-image-{$id}" style="position: absolute; top: 0px; right: 0px; z-index: 3; color: red;">删除</b>
                      <img src="'.\$val.'" width="150" height="150" class="img-thumbnail">
                      <input type="hidden" name="{$name}[]" value="'.\$val.'" >
                  </div>';
        }
        ?>
        </div>
    </div>
</div>
<script >
require(['jquery', 'webuploader'], function ($, wu) {
    $('.$id').click(function () {
        $('#$id input[name="file"]').click();
    });
    var uploader = wu.create({
        auto: true,
        server: '{$url}',
        pick: {
            id: '#{$id}',
            multiple: true
        },
        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/*'
        }
    });
    uploader.on('uploadSuccess', function(file, response) {
        if (response.status != 0) {
            alert(response.message);
            return ;
        }
        var html = '<div class="col-sm-3"><b class="delete-image-{$id}" style="position: absolute; top: 0px; right: 0px; z-index: 3; color: red;">删除</b>';
        html += '<img src="' + response.message + '" width="150" height="150" class="img-thumbnail" >';
        html += '<input type="hidden" name="{$name}[]" value="' + response.message + '" >';
        html += '</div>';

        $('.{$id}-preview').append(html);
    });

    $('body').on('click', '.delete-image-{$id}',function () {
        $(this).parent().remove();
    });
});
</script>
HTML;

        return $html;
    }

    public function tagDate($tag)
    {
        /** 属性 */
        $title = isset($tag['title']) ? $tag['title'] : '';
        $name  = isset($tag['name']) ? $tag['name'] : 'date';
        /** 默认值循环遍历 */
        $flag = substr($tag['value'], 0, 1);
        if ('$' == $flag || ':' == $flag) {
            $value = $this->autoBuildVar($tag['value']);
        } else {
            $value = '"'.$tag['value'].'"';
        }

        $html = <<<HTML
<div class="form-group">
    <label class="control-label col-sm-2">{$title}</label>
    <div class="col-sm-10">
        <input type="text" name="{$name}" class="form-control" id="{$name}" value="<?php echo $value; ?>">
    </div>
</div>
<script >
require(['flatpickr', 'flatpickrzh'], function () {
    flatpickr('input#{$name}', {locale: "zh"});
});
</script>
HTML;

        return $html;
    }

    public function tagDaterange($tag)
    {
        /** 属性 */
        $title = isset($tag['title']) ? $tag['title'] : '';
        $name  = isset($tag['name']) ? $tag['name'] : 'date';
        /** 默认值循环遍历 */
        $flag = substr($tag['value'], 0, 1);
        if ('$' == $flag || ':' == $flag) {
            $value = $this->autoBuildVar($tag['value']);
        } else {
            $value = '[]';
        }

        $html = <<<HTML
<div class="form-group">
    <label class="control-label col-sm-2">{$title}</label>
    <div class="col-sm-10">
        <input type="text" name="{$name}" class="form-control" id="{$name}" value="<?php echo implode(' to ', {$value}); ?>">
    </div>
</div>
<script >
require(['flatpickr', 'flatpickrzh'], function () {
    flatpickr('input#{$name}', {locale: "zh", mode: "range"});
});
</script>
HTML;

        return $html;
    }

    public function tagColor($tag)
    {
        /** 属性 */
        $title = isset($tag['title']) ? $tag['title'] : '';
        $name  = isset($tag['name']) ? $tag['name'] : 'date';
        /** 默认值循环遍历 */
        $flag = substr($tag['value'], 0, 1);
        if ('$' == $flag || ':' == $flag) {
            $value = $this->autoBuildVar($tag['value']);
        } else {
            $value = '"'.$tag['value'].'"';
        }

        $html = <<<HTML
<div class="form-group">
    <label class="control-label col-sm-2">{$title}</label>
    <div class="col-sm-10">
        <input type="text" name="{$name}" class="form-control jscolor" value="<?php echo $value; ?>">
    </div>
</div>
<script >
require(['jquery', 'jscolor']);
</script>
HTML;

        return $html;
    }

    public function tagUmeditor($tag)
    {
        /** 属性 */
        $title = isset($tag['title']) ? $tag['title'] : '';
        $name  = isset($tag['name']) ? $tag['name'] : 'content';
        /** 默认值循环遍历 */
        $flag = substr($tag['content'], 0, 1);
        if ('$' == $flag || ':' == $flag) {
            $content = $this->autoBuildVar($tag['content']);
        } else {
            $content = '"'. $tag['content'] . '"';
        }

        $html = <<<HTML
<div class="form-group">
    <label class="control-label col-sm-2">{$title}</label>
    <div class="col-sm-10">
          <textarea name="{$name}" id="{$name}" style="width: 100%; height: 450px;"><?php echo $content; ?></textarea>
    </div>
</div>
<script >
require(['ueditor'], function () {
    window.UE.getEditor('{$name}', {
        UEDITOR_HOME_URL: '/static/js/ueditor/',
        serverUrl: '/static/js/ueditor/php/controller.php'
    });
});
</script>
HTML;

        return $html;
    }

    public function tagProvince($tag)
    {
        /** 属性 */
        $title = isset($tag['title']) ? $tag['title'] : '';
        /** 省份 */
        $province = substr($tag['province'], 0, 1);
        if ('$' == $province || ':' == $province) {
            $province = $this->autoBuildVar($tag['province']);
        } else {
            $province = $tag['province'];
        }
        /** 城市 */
        $city = substr($tag['city'], 0, 1);
        if ('$' == $city || ':' == $city) {
            $city = $this->autoBuildVar($tag['city']);
        } else {
            $city = $tag['city'];
        }
        /** 地区 */
        $area = substr($tag['area'], 0, 1);
        if ('$' == $area || ':' == $area) {
            $area = $this->autoBuildVar($tag['area']);
        } else {
            $area = $tag['area'];
        }
        $random = mt_rand(0,99);

        $html = <<<HTML

<div class="form-group">
    <label class="control-label col-sm-2">{$title}</label>
    <div class="col-sm-10">
        <div class="row pca-box">
            <div class="col-sm-4"><select name="province" data-value="{$province}" class="form-control province_{$random}"></select></div>
            <div class="col-sm-4"><select name="city" data-value="{$city}" class="form-control city_{$random}"></select></div>
            <div class="col-sm-4"><select name="area" data-value="{$area}" class="form-control area_{$random}"></select></div>
        </div>
    </div>
</div>
<script >
require(["cxselect"], function () {
    $('.pca-box').cxSelect({
        url: '/static/js/cx-select/cityData.min.json',
        selects: ['province_{$random}', 'city_{$random}', 'area_{$random}'],
    });
});
</script>

HTML;

        return $html;
    }

}