<?php namespace app\behavior;

class InstallCheck
{

    public function run(&$params)
    {
        if (! file_exists(ROOT_PATH . DS . 'data' . DS . 'install' . DS . 'install.lock')) {
            if (strtolower(substr($_SERVER['REQUEST_URI'], 1, 7)) != 'install') {
                header('Location:' . url('install/index/step1'));
                exit;
            }
        }
    }

}