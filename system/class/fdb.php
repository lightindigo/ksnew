<?php
class db
{
    public static $db = array();
    public static function create($name)
    {
        self::$db[$name] = new fdb;
    }
}
class fdb
{
	public $error = array();
	public $name = false;
	public $tables = array();
	public $src = false;
	private $path = false;
	/*open database
	 * EXAMPLE USE:

		if(!fdb::open('/fdb/db1.xml'))
			echo 'Error';
		else
			echo 'Success'
	
	* RETURN:
		True(bool) - Success
		False (bool) - Error
	*/
	public function open($path)
	{
		if(!file_exists($path)) return $this->setError(1,'Database cant be found');
		if(!is_readable($path)) return $this->setError(2,'Database cant be read');
		try
		{
			$this->src = new SimpleXMLElement(join('',file($path)));
			if(!$this->init()) return false;
			$this->path = $path;
			return true;
		}
		catch(Exception $e)
		{
			return $this->setError(1,$e->getMessage());
		}
	}
	private function init()
	{
		if(!isset($this->src->name) || strlen(trim($this->src->name)) < 3) return $this->setError(2,'Invalid database name');
		$this->name = (string)$this->src->name;
		if(!isset($this->src->tables) || !isset($this->src->tables->item)) return $this->setError(3,'Tables cant be found');
		foreach($this->src->tables->item as $item)
		{
			$this->tables[((string)$item->name)] = array();
			foreach($item->struct->field as $field)
			{
				$this->tables[((string)$item->name)][] = ((string)$field);
			}
		}
		return true;
	}
	private function setError($id,$message)
	{
		$this->error[] = array($id,$message);
		return false;
	}
        public function change_db_name($new_name)
        {
            $this->src->name = $new_name;
        }
	/*insert new record into table database
	 * EXAMPLE USE:
	   $db = fdb::open('/fdb/db1.xml');
	 * RETURN
	   True(bool) - Success
	   False (bool) - Error
	*/
	public function insert($table,$field_value)
	{
		if(!isset($this->tables[$table]) || !isset($this->src->content->$table)) return $this->setError(4,'Invalid table name');
                if(!is_array($field_value))
                {
                    $field_value = explode(',',trim($field_value));
                    $f = array();
                    for($i=0;$i<count($field_value);$i++)
                    {
                            $x = strpos(trim($field_value[$i]),'=');
                            $field = trim(substr(trim($field_value[$i]),0,$x));
                            $value = trim(substr(trim($field_value[$i]),$x+1));
                            $f[$field] = $value;
                    }
                }
                else
                {
                    $f = $field_value;
                }
		$this->src->content->$table->addChild('item');
		$value = '';
		foreach($this->tables[$table] as $field_name)
		{
			foreach($f as $f_name => $f_value)
			{
				if($field_name == $f_name)
				{
					$value = $f_value;
					break;
				}
			}
			$this->src->content->$table->item[count($this->src->content->$table->item)-1]->addChild($field_name);
			$this->src->content->$table->item[count($this->src->content->$table->item)-1]->$field_name = (string)$value;
		}
		return true;
	}
	public function delete($table,$condition,$limit=0)
	{
		if(!isset($this->tables[$table]) || !isset($this->src->content->$table)) return $this->setError(4,'Invalid table name');
		$condition = explode(',',trim($condition));
		$c = array();
		for($i=0;$i<count($condition);$i++)
		{
			if(strpos($condition[$i],'!=')){$operation = '!=';}
			elseif(strpos($condition[$i],'>')){$operation = '>';}
			elseif(strpos($condition[$i],'<')){$operation = '<';}
			elseif(strpos($condition[$i],'=')){$operation = '=';}
			else{continue;}
			list($field,$value) = explode($operation,trim($condition[$i]));
			$c[] = array($operation,trim($field),trim($value));
		}
		$total = 0;
		$rm_list = array();
		for($i=0;$i<count($this->src->content->$table->item);$i++)
		{
			$status = true;
			foreach($c as $condition)
			{
				if(!isset($this->src->content->$table->item[$i]->$condition[1])) return $this->setError(7,'Invalid field name');
				switch($condition[0])
				{
					case '=':
						if($this->src->content->$table->item[$i]->$condition[1] == $condition[2]){$status = true;}else{$status = false;}
						break;
					case '>':
						if($this->src->content->$table->item[$i]->$condition[1] > $condition[2]){$status = true;}else{$status = false;}
						break;
					case '<':
						if($this->src->content->$table->item[$i]->$condition[1] < $condition[2]){$status = true;}else{$status = false;}
						break;
					case '!=':
						if($this->src->content->$table->item[$i]->$condition[1] != $condition[2]){$status = true;}else{$status = false;}
						break;
					default: break;
				}
			}
			if(!$status) continue;
			$rm_list[] = $i;
			$total++;
			if($limit > 0 && $total >= $limit) break;
		}
		$top = 0;
		for($i=0;$i<count($rm_list);$i++)
		{
			unset($this->src->content->$table->item[$rm_list[$i]-$top]);
			$top++;
		}
		return $total;
	}
	public function update($table,$set,$condition,$limit=0)
	{
		if(!isset($this->tables[$table]) || !isset($this->src->content->$table)) return $this->setError(4,'Invalid table name');
		$condition = explode(',',trim($condition));
		$c = array();
		for($i=0;$i<count($condition);$i++)
		{
			if(strpos($condition[$i],'!=')){$operation = '!=';}
			elseif(strpos($condition[$i],'>')){$operation = '>';}
			elseif(strpos($condition[$i],'<')){$operation = '<';}
			elseif(strpos($condition[$i],'=')){$operation = '=';}
			else{continue;}
			list($field,$value) = explode($operation,trim($condition[$i]));
			$c[] = array($operation,trim($field),trim($value));
		}
		$set = explode(',',trim($set));
		$s = array();
		for($i=0;$i<count($set);$i++)
		{
			list($field,$value) = explode('=',trim($set[$i]));
			$s[] = array(trim($field),trim($value));
		}
		$totla = 0;
		for($i=0;$i<count($this->src->content->$table->item);$i++)
		{
			$item = &$this->src->content->$table->item[$i];
			$status = true;
			foreach($c as $condition)
			{
				if(!isset($item->$condition[1])) return $this->setError(5,'Invalid field name');
				switch($condition[0])
				{
					case '=':
						if($item->$condition[1] == $condition[2]){$status = true;}else{$status = false;}
						break;
					case '>':
						if($item->$condition[1] > $condition[2]){$status = true;}else{$status = false;}
						break;
					case '<':
						if($item->$condition[1] < $condition[2]){$status = true;}else{$status = false;}
						break;
					case '!=':
						if($item->$condition[1] != $condition[2]){$status = true;}else{$status = false;}
						break;
					default: break;
				}
			}
			if(!$status) continue;
			foreach($s as $element)
			{
				if(!isset($item->$element[0])) return $this->setError(6,'Invalid field name');
				$item->$element[0] = $element[1];
			}
			$total++;
			if($limit > 0 && $total >= $limit) return $total;
		}
		return $total;
	}
	public function select($table,$condition='',$limit=0)
	{
		if(!isset($this->tables[$table]) || !isset($this->src->content->$table)) return $this->setError(4,'Invalid table name');
		
		$c = array();
                if(!is_array($condition))
                {$condition = explode(',',trim($condition));}
		for($i=0;$i<count($condition);$i++)
		{
			if(strpos($condition[$i],'!=')){$operation = '!=';}
			elseif(strpos($condition[$i],'>')){$operation = '>';}
			elseif(strpos($condition[$i],'<')){$operation = '<';}
			elseif(strpos($condition[$i],'=')){$operation = '=';}
			else{continue;}
			list($field,$value) = explode($operation,trim($condition[$i]));
			$c[] = array($operation,trim($field),trim($value));
		}
		$total = 0;
		$collection = array();
		foreach($this->src->content->$table->item as $item)
		{
			$status = true;
			foreach($c as $condition)
			{
				switch($condition[0])
				{
					case '=':
						if($item->$condition[1] == $condition[2]){$status = true;}else{$status = false;}
						break;
					case '>':
						if($item->$condition[1] > $condition[2]){$status = true;}else{$status = false;}
						break;
					case '<':
						if($item->$condition[1] < $condition[2]){$status = true;}else{$status = false;}
						break;
					case '!=':
						if($item->$condition[1] != $condition[2]){$status = true;}else{$status = false;}
						break;
					default: break;
				}
			}
			if(!$status) continue;
			$collection[] = (array)$item;
			$total++;
			if($limit > 0 && $total >= $limit) return $collection;
			
		}
		return $collection;
	}
        public function save($path='')
        {
            $path = ($path == '') ? $this->path:$path;
            $src = $this->src->asXML();
            $f = fopen($path,'w');
            fwrite($f, $src);
            fclose($f);
        }
}
?>