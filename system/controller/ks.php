<?php
class ctrl_ks extends crtltemplate
{
    public function index()
    {
        $workspace = template::open('workspace');
        $workspace = template::setKeys($this->staticKeys(), $workspace);
        return $workspace;
    }
    public function edirot_ks()
    {
        $workspace = template::open('workspace_editor_ks');
        $workspace = template::setKeys($this->staticKeys(), $workspace);
        return $workspace;
    }
    public function edirot_ks_drs()
    {
        $workspace = template::open('workspace_editor_ks_drs');
        $workspace = template::setKeys($this->staticKeys(), $workspace);
        return $workspace;
    }
    public function edirot_ks_ms()
    {
        $workspace = template::open('workspace_editor_ks_ms');
        $workspace = template::setKeys($this->staticKeys(), $workspace);
        return $workspace;
    }
    public function catalog_address()
    {
        $workspace = template::open('workspace_catalog_address');
        $workspace = template::setKeys($this->staticKeys(), $workspace);
        return $workspace;
    }
    public function catalog_contractor()
    {
        $workspace = template::open('workspace_catalog_contractor');
        $workspace = template::setKeys($this->staticKeys(), $workspace);
        return $workspace;
    }
    public function catalog_price()
    {
        $workspace = template::open('workspace_catalog_price');
        $workspace = template::setKeys($this->staticKeys(), $workspace);
        return $workspace;
    }
    public function catalog_ks()
    {
        $workspace = template::open('workspace_catalog_ks');
        $workspace = template::setKeys($this->staticKeys(), $workspace);
        return $workspace;
    }
    ///
    public function staticKeys()
    {
        $args = array();
        return $args;
    }
    ////
    public function address()
    {
        $get  = (!isset($_GET['get']))  ? 'ats':trim($_GET['get']);
        switch($get)
        {
            case 'ats':
                return $this->model->getAddress('ats',array());
                break;
            case 'cluster-by-ats':
                if(!isset($_GET['ats'])) return sdk::toApi(100,'Param Not Found: ats');
                $ats = trim($_GET['ats']);
                $dat = array('ats'=>$ats);
                return $this->model->getAddress('cluster-by-ats',$dat);
                break;
            case 'address-by-ats-cluster':
                if(!isset($_GET['ats'])) return sdk::toApi(100,'Param Not Found: ats');
                $ats = trim($_GET['ats']);
                if(!isset($_GET['cluster'])) return sdk::toApi(100,'Param Not Found: cluster');
                $cluster = trim($_GET['cluster']);
                $dat = array('ats'=>$ats,'cluster'=>$cluster);
                return $this->model->getAddress('address-by-ats-cluster',$dat);
                break;
            case 'address':
                if(!isset($_GET['address'])) return sdk::toApi(100,'Param Not Found: address');
                $address = trim($_GET['address']);
                $dat = array('address'=>$address);
                return $this->model->getAddress('address',$dat);
                break;
        }
    }
    public function works()
    {
        $parent = (!isset($_GET['parent'])) ? '':trim(str_replace("'",'',$_GET['parent']));
        $res = $this->model->works($parent);
        return $res;
    }
    //catalog contactor
    public function catalog_contractor_add()
    {
        $fields = array('name'=>'','cciso'=>'','riso'=>'','sroca'=>'','srocm'=>'','director'=>'','accountant'=>'','legal_address'=>'','actual_address'=>'','pastal_address'=>'','inn'=>'','kpp'=>'','ogrn'=>'','okpo'=>'','okvd'=>'','r_s'=>'','in'=>'','k_s'=>'','bik'=>'','phone'=>'','fax'=>'','mail'=>'');
        $dat = array();
        foreach($fields as $key => $val)
        {
            $dat[$key] = (!isset($_POST[$key])) ? $key:str_replace("'",'', trim(strip_tags(trim($_POST[$key]))));
        }
        $res = $this->model->catalog_contractor_add($dat);
        return $res;
    }
    public function catalog_contractor_update()
    {
        $fields = array('id'=>'','name'=>'','cciso'=>'','riso'=>'','sroca'=>'','srocm'=>'','director'=>'','accountant'=>'','legal_address'=>'','actual_address'=>'','pastal_address'=>'','inn'=>'','kpp'=>'','ogrn'=>'','okpo'=>'','okvd'=>'','r_s'=>'','in'=>'','k_s'=>'','bik'=>'','phone'=>'','fax'=>'','mail'=>'');
        $dat = array();
        foreach($fields as $key => $val)
        {
            $dat[$key] = (!isset($_POST[$key])) ? $key:str_replace("'",'', trim(strip_tags(trim($_POST[$key]))));
        }
        $res = $this->model->catalog_contractor_update($dat);
        return $res;
    }
    public function catalog_contractor_list_get()
    {
        $res = $this->model->catalog_contractor_list_get();
        return $res;
    }
    public function catalog_contractor_get_data()
    {
        if(!isset($_GET['id'])) return sdk::toApi('100','Param Not Found: id');
        $id = (int)$_GET['id'];
        $res = $this->model->catalog_contractor_get_data($id);
        return $res;
    }
    public function catalog_contractor_delete()
    {
        if(!isset($_GET['id'])) return sdk::toApi('100','Param Not Found: id');
        $id = (int)$_GET['id'];
        $res = $this->model->catalog_contractor_delete($id);
        return $res;
    }
    public function catalog_price_list_get()
    {
        if(isset($_GET['type']))
        {
            $args = array('type'      =>$_GET['type'],
                          'contractor'=>(int)$_GET['contractor'],
                          'constomer' =>(int)$_GET['constomer']);
            $res = $this->model->catalog_price_list_get($args);
            return $res;
        }
        $res = $this->model->catalog_price_list_get();
        return $res;
    }
    public function catalog_price_add()
    {
        $fields = array('name'=>'','contact_number'=>'','contact_date'=>'','accord_number'=>'','accord_date'=>'','type'=>'','customer'=>'','contactor'=>'');
        $dat = array();
        foreach($fields as $key => $val)
        {
            $dat[$key] = (!isset($_POST[$key])) ? $key:str_replace("'",'', trim(strip_tags(trim($_POST[$key]))));
        }
        $res = $this->model->catalog_price_add($dat);
        return $res;
    }
    public function catalog_price_getdata()
    {
            if(!isset($_GET['id'])) return sdk::toApi('100','Param Not Found: id');
            $id = (int)$_GET['id'];
            $res = $this->model->catalog_price_getdata($id);
            return $res;
            
    }
    public function catalog_price_update()
    {
        $fields = array('id'=>'','name'=>'','contact_number'=>'','contact_date'=>'','accord_number'=>'','accord_date'=>'','type'=>'','customer'=>'','contactor'=>'');
        $dat = array();
        foreach($fields as $key => $val)
        {
            $dat[$key] = (!isset($_POST[$key])) ? $key:str_replace("'",'', trim(strip_tags(trim($_POST[$key]))));
        }
        $res = $this->model->catalog_price_update($dat);
        return $res;
    }
    public function catalog_price_delete()
    {
        if(!isset($_GET['id'])) return sdk::toApi('100','Param Not Found: id');
        $id = (int)$_GET['id'];
        $res = $this->model->catalog_price_delete($id);
        return $res;
    }
    public function save_ks()
    {
        if(!isset($_GET['type'])) return sdk::toApi('100','Param Not Found: type');
        if(trim($_GET['type']) == 'drs')
        {
            $fields = array('project'=>'','work_name'=>'','doc_n'=>'','price'=>'','date_a'=>'','date_b'=>'','works'=>'','material'=>'','prepayment'=>'');
            $dat = array();
            foreach($fields as $key => $val)
            {
                $dat[$key] = (!isset($_POST[$key])) ? $key:str_replace("'",'', trim(strip_tags(trim($_POST[$key]))));
            }
            $res = $this->model->save_ks_drs($dat);
            return $res;
        }
        elseif(trim($_GET['type']) == 'ms')
        {
            $fields = array('ats'=>'','cluster'=>'','work_name'=>'','doc_n'=>'','price'=>'','date_a'=>'','date_b'=>'','works'=>'','material'=>'','compens'=>'','prepayment'=>'');
            $dat = array();
            foreach($fields as $key => $val)
            {
                $dat[$key] = (!isset($_POST[$key])) ? $key:str_replace("'",'', trim(strip_tags(trim($_POST[$key]))));
            }
            $dat['prepayment']=$_POST['prepayment'];
            $res = $this->model->save_ks_ms($dat);
            return $res;
        }
    }
    public function ks_list()
    {
        $fields = array('ats'=>'','cluster'=>'','type'=>'');
        $dat = array();
        foreach($fields as $key => $val)
        {
           $dat[$key] = (!isset($_GET[$key])) ? $val:str_replace("'",'', trim(strip_tags(trim($_GET[$key]))));
        }
        $res = $this->model->ks_list($dat);
        return $res;
    }
    public function ks_data()
    {
        if(!isset($_GET['id'])) return sdk::toApi('100','Param Not Found: id');
        $id = (int)$_GET['id'];
        $res = $this->model->ks_data($id);
        return $res;
    }
    public function ks_print()
    {
        if(!isset($_GET['id'])) return sdk::toApi('100','Param Not Found: id');
        $id = (int)$_GET['id'];
        if(!isset($_GET['type'])) return sdk::toApi('100','Param Not Found: type');
        $type = (int)$_GET['type'];
        $res = $this->model->ks_print($id,$type);
        return $res;
    }
    public function price_item()
    {
        if(!isset($_GET['do'])) return sdk::toApi('100','Param Not Found: do');
        $do = trim($_GET['do']);
        $args = array();
        $args['parent'] = (!isset($_GET['parent'])) ? '':(int)$_GET['parent'];
        $args['id'] = (!isset($_GET['id'])) ? '':(int)$_GET['id'];
        $args['retail'] = (!isset($_GET['retail'])) ? '0':trim(str_replace("'",'',strip_tags(trim($_GET['retail']))));
        $res = $this->model->price_item($do,$args);
        return $res;
    }
    public function addWork()
    {
        $args = array();
        $args['name'] = $_POST['name'];
        $args['min'] = $_POST['min'];
        $args['type'] = $_POST['type'];
        $args['retail'] = $_POST['retail'];
        $args['item_id'] = $_POST['parent'].".".$_POST['next_num'];
        $res = $this->model->addWork($args);
        return $res;
    }
}
?>