<?php
abstract class crtltemplate
{
    public $currentName;
    public $model = false;
    public $error = array();
    public $fatalError = false;
    public function __construct($name)
    {
        $this->currentName = $name;
        try
        {
            sdk::import('model',$name);
            $model = 'model_'.$name;
            if(!class_exists($model)) {throw new Exception('Model: internal error');}
            $this->model = new $model();
        }
        catch(Exception $e)
        {
            $this->error[] = $e->getMessage();
        }
    }
    abstract function index();
}
?>
