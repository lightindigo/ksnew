<?php
class _mysql
{
    public static $err = array();
    private static $lnk = false;
    public static function isConnect()
    {
        return (self::$lnk) ? true:false;
    }
    public static function connect($server, $username, $password)
    {
        
        try
        {
            self::$lnk = @mysql_connect($server, $username, $password);
            if(!(self::$lnk = @mysql_connect($server, $username, $password))) return self::getError();
            return true;
        }
        catch(Exception $e)
        {
            self::$err[] = $e->getMessage();
            var_dump($server, $username, $password);
            return false;
        }
        return (!(self::$lnk = @mysql_connect($server, $username, $password)))  ? self::getError():true;
    }
    public static function select_db($database_name)
    {
        return (!@mysql_select_db($database_name, self::$lnk)) ? self::getError():true;
    }
    public static function init($server, $username, $password,$database_name)
    {
        if(!self::connect($server, $username, $password)) return false;
        if(!self::select_db($database_name)) {self::getError(); self::close(); return false;}
        return true;
    }
    public static function query($query,$args=false)
    {
        if(is_array($args)){
            for($i=0;$i<count($args);$i++){
                $query = str_replace('{'.$i.'}',$args[$i],$query);
            }
        }
        $q = false;
        if(!($q = @mysql_query($query, self::$lnk))) return self::getError();
        return $q;
    }
    public static function count($result)
    {
        $count = false;
        if(($count = @mysql_num_rows($result)) === false) return self::getError();
        return $count;
    }
    public static function read_row($result)
    {
        return @mysql_fetch_array($result);
    }
    public static function close()
    {
        if(self::$lnk) @mysql_close(self::$lnk);
    }
    public static function getError()
    {
        self::$err[] = mysql_error();
        return false;
    }
    public static function getLastError()
    {
        return (count(self::$err) > 0) ? self::$err[count(self::$err)-1]:false; 
    }
}
?>
