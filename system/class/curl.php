<?php
class _curl
{
    private $args  = array();
    public  $url   = '';
    public  $err   = false;
    public function add_key($key,$value)
    {
        if(trim($value) === NULL) return;
        $this->args[trim($key)]=trim($value);
    }
    public function add_keys($keys)
    {
        foreach($keys as $key => $value)
        {
            if(trim($value) === NULL) continue;
            $this->add_key($key,$value);
        }
    }
    public function get_key($key)
    {
        return (!isset($this->args[trim($key)])) ? NULL:$this->args[trim($key)];
    }
    public function export()
    {
        return $this->args;
    }
    public function __set($key,$value)
    {
        $this->add_key($key, $value);
    }
    public function __get($key)
    {
        return $this->get_key($key);
    }
    public function reset($save=array())
    {
        $tmp = array();
        $dump = false;
        
        if(count($save) > 0)
            foreach($save as $key)
                if(strlen($key) < 1 || ($dump = $this->get_key($key)) === NULL)
                    continue;
                else
                    $tmp[trim($key)] = $dump;
        
        $this->args = $tmp;
    }
    public function count()
    {
        return count($this->args);
    }
    public function mk_query()
    {
        $tmp = '';
        foreach($this->args as $key => $value)
        {
            if(is_array($value))
            {
                foreach($value as $v)
                    $tmp .= '&'.$key.'='.$v;
            }
            else
            {
                    $tmp .= '&'.$key.'='.$value;
            }
        }
        return substr($tmp,1);
    }
    public function build($get=false,$timeout=30)
    {
        if(!$get)
            return $this->send_query($this->mk_query(),$timeout);
        $this->url .='?'.$this->mk_query();
        return $this->send_query(fasle,$timeout);
    }
    private function send_query($request=false,$timeout=30)
	{
		try
		{
			$ch = curl_init($this->url);
			curl_setopt($ch,CURLOPT_URL,$this->url);
			curl_setopt($ch,CURLOPT_HEADER,false);
			curl_setopt($ch,CURLOPT_POST,1);	
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$request);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$req = curl_exec($ch);
			curl_close($ch);
			return $req;
		}
		catch(Exception $ex)
		{
                    $this->err = $ex->getMessage();
                    return false;
		}
	}
}
?>