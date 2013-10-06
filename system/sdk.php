<?php
define('MAIN_ACCESS', true);
class sdk
{
    public static $exception = false;
    public static $conf = array();
    public static $core;
    public static function import($type,$NObject)
    {
        switch ($type)
        {
            case 'class': $type = 'class'; break;
            case 'plugin': $type = 'plugin'; break;
            case 'model': $type = 'model'; break;
            case 'view': $type = 'view'; break;
            case 'controller': $type = 'controller'; break;
            default: return self::_error('Invalid type',self::$exception);
        }
        $src = $type.'/'.$NObject.'.php';
        if(!self::getResource($src)) return self::_error ('Object: [`'.$type.'=>'.$NObject.'`] not found', self::$exception);
        return true;
    }
    public static function getConfig($NObject)
    {
        $src = dirname(__FILE__).'/configuration/'.$NObject.'.php';
        if(!file_exists($src)) return self::_error('Configuration `'.$NObject.'` not found',self::$exception);
        include($src);
        if(!isset($conf)) return self::_error('Configuration `'.$NObject.'` have got errors',self::$exception);
        self::$conf[$NObject] = $conf;
        return true;
    }
    public static function getResource($path)
    {
        $src = dirname(__FILE__).'/'.$path;
        if(!file_exists($src)) return false;
        require_once($src);
        return true;
    }
    public static function exceptionError(){
        //Echo 'Fatal unxepted error';
    }
    public static function _error($message,$exception)
    {
        if($exception) throw new Exception($message);
        return false;
    }
    public static function toApi($code,$decs='',$content='')
    {
        return array('code'=>$code,'desc'=>$decs,'data'=>$content);
    }
}
?>
