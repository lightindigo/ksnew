<?php
class template
{
    public static function open($name)
    {
        $src = _CDIR_.'/view/'.$name.'.html';
        if(!file_exists($src))
        {
            $src = _CDIR_.'/view/'.$name.'.php';
            if(!file_exists($src))
                return sdk::_error('View `'.$name.'` not found',true);
        }
        $src = join('',file($src));
        return $src;
    }
    public static function setKey($key,$value,$src)
    {
        return str_replace('{'.$key.'}',$value,$src);
    }
    public static function setKeys($keys,$src)
    {
        foreach($keys as $key => $value)
            $src = self::setKey($key,$value,$src);
        return $src;
    }
    public static function setSpecialKey($key,$value,$src)
    {
        return str_replace($key,$value,$src);
    }
    public static function setSpecialKeys($keys,$src)
    {
        foreach($keys as $key => $value)
            $src = self::setSpecialKey($key,$value,$src);
        return $src;
    }
    
}
?>
