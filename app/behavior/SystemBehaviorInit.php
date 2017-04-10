<?php
namespace app\behavior;

use app\wefee\Tree;

class SystemBehaviorInit
{
    /** 应用初始化 */
    public function appInit(&$param)
    {
        Tree::hook()->listen('app-init', $param);
    }

    /** 应用开始标签位 */
    public function appBegin(&$param)
    {
        Tree::hook()->listen('app-begin', $param);
    }

    /** 模块初始化标签位 */
    public function moduleInit(&$param)
    {
        Tree::hook()->listen('module-init', $param);
    }

    /** 控制器开始标签位 */
    public function actionBegin(&$param)
    {
        Tree::hook()->listen('action-begin', $param);
    }

    /** 视图输入过滤标签位 */
    public function viewFilter(&$param)
    {
        Tree::hook()->listen('view-filter', $param);
    }

    /** 应用结束标签位 */
    public function appEnd(&$param)
    {
        Tree::hook()->listen('app-end', $param);
    }

    /** 日志Write标签位 */
    public function logWrite(&$param)
    {
        Tree::hook()->listen('log-write', $param);
    }

    /** 输出结束标签位 */
    public function responseEnd(&$param)
    {
        Tree::hook()->listen('response-end', $param);
    }

}