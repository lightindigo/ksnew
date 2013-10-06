<?php
session_start();
class ctrl_account extends crtltemplate
{
    public function index() {}
    public function login()
    {
        $api = $this->model->sign_in();
        if($api['code'] != 200)
        {//alert
            if(defined('_API_')) return sdk::toApi($api['code'],$api['desc']);
            $msg = ($api['desc'] == 'NULL') ? '':template::setKey('content',$api['desc'],template::open('msg_error'));
            $page = template::setKey('alert',$msg,template::open('page_login'));
            $page = template::setKey('login',$this->model->login,$page);
            return $page;
        }
        return (defined('_API_')) ? sdk::toApi(200,'OK'):true;
    }
    public function logout()
    {
        session_destroy();
        return template::set('alert','',template::open('page_login'));
    }
}
?>
