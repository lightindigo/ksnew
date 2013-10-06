<?php
class model_account
{
    public $conf = array();
    public $login = '';
    public function login()
    {
        $this->login = '';
        $login = '';
        $password = '';
        $type = 0;
        if(!isset($_POST['login']) || !isset($_POST['pwd']))
        {
            if(!isset($_SESSION['name']) || !isset($_SESSION['pwd']))
            {
                return sdk::toApi('400','NULL');
            }
            else
            {
                $type = 2;
                $login = htmlspecialchars(trim(strip_tags(trim($_SESSION['name']))),ENT_QUOTES);
                $this->login = $login;
                $password = $_SESSION['pwd'];
            }
        }
        else
        {
            $type = 1;
            $login = htmlspecialchars(trim(strip_tags(trim($_POST['login']))),ENT_QUOTES);
            $this->login = $login;
            $password = md5($_POST['pwd']);
        }
        return sdk::toApi(200,'OK',array($login,$password));
        //-->AUTH
    }
    public function sign_in()
    {
        $q = "SELECT * FROM `client` WHERE `login`='{0}' AND `password`='{1}' AND `block`='0';";
        $args = $this->login();
        if($args['code'] != 200) return $args;
        if(!($q = _mysql::query($q,$args['data']))) return sdk::toApi(300,'Internal Error');
        if(_mysql::count($q) != 1) return sdk::toApi(400,'Access denied');
        $row = _mysql::read_row($q);
        $_SESSION['name'] = $args['data'][0];
        $_SESSION['pwd'] = $args['data'][1];
        $this->conf['login'] = $row['login'];
        $this->conf['uid'] = $row['id'];
        $this->conf['lavel'] = $row['lavel'];
        $this->conf['name'] = $row['name'];
        $this->conf['email'] = $row['email'];
        return sdk::toApi(200,'OK',$this->conf);
    }
}
?>
