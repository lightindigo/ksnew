<?php
class model_cat
{
    public function savePriceItem()
    {
        if(!isset($_POST['price_object'])) return sdk::toApi('100','Invalid Object price_object');
        if(!isset($_POST['item_id'])) return sdk::toApi('100','Invalid Object item_id');
        if(!isset($_POST['retail'])) return sdk::toApi('100','Invalid Object retail');
        $parent = ((int)trim($_POST['price_object']));
        $item = str_replace("'", '', trim($_POST['item_id']));
        $retail = str_replace("'", '', trim($_POST['retail']));
        $select = "SELECT *  FROM `price_content` WHERE `parent`='{0}' AND `item_id`='{1}';";
        if(!($q = _mysql::query($select,array($parent,$item))))
        {
            return sdk::toApi('10','Invalid query [savePriceItem][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($q) < 1)
        {
            $insert = "INSERT INTO `price_content` (`parent`,`item_id`,`retail`) VALUES('{0}','{1}','{2}');";
            if(!($q = _mysql::query($insert,array($parent,$item,$retail))))
            {
                return sdk::toApi('10','Invalid query [savePriceItem][2]',array(_mysql::getLastError()));
            }
        }
        else
        {
            $update = "UPDATE `price_content` SET `retail`='{2}' WHERE `parent`='{0}' AND `item_id`='{1}';";
            if(!($q = _mysql::query($update,array($parent,$item,$retail))))
            {
                return sdk::toApi('10','Invalid query [savePriceItem][3]',array(_mysql::getLastError()));
            }
        }
        return sdk::toApi('200','OK');
    }
    public function getWork()
    {
        $q = "SELECT * FROM `ks_wl` ORDER BY `item_id` ASC;";
        if(!($q = _mysql::query($q)))
        {
            return sdk::toApi('10','Invalid query [getWork][1]',array(_mysql::getLastError()));
        }
        $res = array();
        while($r = _mysql::read_row($q))
        {
            $res[] = array('id'=>$r['id'],
                           'item_id'=>$r['item_id'],
                           'text'=>$r['text'],
                           'type'=>$r['type'],
                           'min'=>$r['min'],
                           'retail'=>$r['retail']);
        }
        return sdk::toApi('200','OK',$res);
    }
    public function getPrice()
    {
        if(isset($_GET['contractor']) && isset($_GET['customer']) && isset($_GET['type']))
        {
            $contractor = ((int)trim($_GET['contractor']));
            $customer   = ((int)trim($_GET['customer']));
            $type       = str_replace("'", '', trim($_GET['type']));
            $q = "SELECT * FROM `ks_price` WHERE `contractor`='{0}' AND `customer`='{1}' AND `type`='{2}';";
            if(!($q = _mysql::query($q,array($contractor,$customer,$type))))
            {
                return sdk::toApi('10','Invalid query [getPrice][5]',array(_mysql::getLastError()));
            }
            $res = array();
            while($r = _mysql::read_row($q))
            {
                $res[] = array('id'=>$r['id'],
                               'name'=>$r['name'],
                               'contract'=>$r['contract'],
                               'contract_date'=>$r['contract_date'],
                               'accord'=>$r['accord'],
                               'accord_date'=>$r['accord_date'],
                               'type'=>$r['type'],
                               'customer'=>$r['customer'],
                               'contractor'=>$r['contractor']);
            }
            return sdk::toApi('200','OK',$res);
        }
        if(isset($_GET['id']))
        {
            $id = ((int)trim($_GET['id']));
            $q = "SELECT * FROM `ks_price_content` WHERE `parent`='{0}' ORDER BY `item_id` ASC;";
            if(!($q = _mysql::query($q,array($id))))
            {
                return sdk::toApi('10','Invalid query [getPrice][4]',array(_mysql::getLastError()));
            }
            $res = array();
            while($r = _mysql::read_row($q))
            {
                $res[] = array('item_id'=>$r['item_id'],
                             'retail'=>$r['retail'],
                             'id'=>$r['id']);
            }
            return sdk::toApi('200','OK',$res);
        }
        $q = "SELECT * FROM `ks_price` ORDER BY `id` ASC;";
        $select = "SELECT * FROM `ks_contractor` WHERE `id`='{0}' LIMIT 1;";
        if(!($q = _mysql::query($q)))
        {
            return sdk::toApi('10','Invalid query [getPrice][1]',array(_mysql::getLastError()));
        }
        $res = array();
        while($r = _mysql::read_row($q))
        {
            $tmp = array();
            $tmp['id']=$r['id'];
            $tmp['name']=$r['name'];
            $tmp['contract']=$r['contract'];
            $tmp['contract_date']=$r['contract_date'];
            $tmp['accord']=$r['accord'];
            $tmp['accord_date']=$r['accord_date'];
            $tmp['type']=$r['type'];
            $tmp['customer_id']=$r['customer'];
            $tmp['contractor_id']=$r['contractor'];
            if(!($qc = _mysql::query($select,array($tmp['customer_id']))))
            {
                return sdk::toApi('10','Invalid query [getPrice][2]',array(_mysql::getLastError()));
            }
            $qrc = _mysql::read_row($qc);
            $tmp['customer_name']=$qrc['name'];
            if(!($qc = _mysql::query($select,array($tmp['contractor_id']))))
            {
                return sdk::toApi('10','Invalid query [getPrice][3]',array(_mysql::getLastError()));
            }
            $qrc = _mysql::read_row($qc);
            $tmp['contractor_id']=$qrc['name'];
            $res[] = $tmp;
        }
        return sdk::toApi('200','OK',$res);
    }
    public function addNewPrice()
    {
        if(!isset($_POST['src'])) return sdk::toApi('100','Invalid Object Src');
        $insert = "INSERT INTO `ks_price` (`name`,`contract`,`contract_date`,`accord`,`accord_date`,`type`,`customer`,`contractor`) 
                                   VALUES ('{price_name}','{price_contract}','{price_contract_date}','{price_accord}','{price_accord_date}','{price_type}','{price_customer}','{price_contractor}');";
        foreach(explode("\n",trim($_POST['src'])) as $ln)
        {
            if(strlen(trim($ln)) < 2) continue;
            $item = explode("\t", trim($ln));
            $item[0] = str_replace('`', '', $item[0]);
            $item[1] = str_replace("'", '', $item[1]);
            $insert = str_replace('{'.trim($item[0]).'}',trim($item[1]),$insert);
        }
        if(!(_mysql::query($insert)))
        {
            return sdk::toApi('10','Invalid query [updateContractor][1]',array(_mysql::getLastError()));
        }
        return sdk::toApi('200','OK');
    }
    public function updateContractor()
    {
        if(!isset($_POST['src'])) return sdk::toApi('100','Invalid Object Src');
        if(!isset($_POST['id'])) return sdk::toApi('100','Invalid Object Id');
        $update = "UPDATE `ks_contractor` SET `name`='{name}',`okyd`='{okyd}', `cciso`='{cciso}', `riso`='{riso}', `sroca`='{sroca}', `srocm`='{srocm}', `director`='{director}', `accountant`='{accountant}', `legal_address`='{legal_address}', `actual_address`='{actual_address}', `postal_address`='{postal_address}', `inn_kpp`='{inn_kpp}', `ogrn`='{ogrn}', `okpo_okvd`='{okpo_okvd}', `r_s`='{r_s}', `in`='{in}', `k_s`='{k_s}', `bik`='{bik}', `phone_fax`='{phone_fax}', `mail`='{mail}' WHERE `id`='{0}';";
        foreach(explode("\n",trim($_POST['src'])) as $ln)
        {
            if(strlen(trim($ln)) < 2) continue;
            $item = explode("\t", trim($ln));
            $item[0] = str_replace('`', '', $item[0]);
            $item[1] = str_replace("'", '', $item[1]);
            //update
            $update = str_replace('{'.trim($item[0]).'}',trim($item[1]),$update);
        }
        if(!(_mysql::query($update,array(((int)$_POST['id'])))))
        {
            return sdk::toApi('10','Invalid query [updateContractor][1]',array(_mysql::getLastError()));
        }
        return sdk::toApi('200','OK');
    }
    public function addNewContractor()
    {
        if(!isset($_POST['src'])) return sdk::toApi('100','Invalid Object Src');
        $select = "SELECT * FROM `ks_contractor` WHERE `name`='{name}' AND `okyd`='{okyd}' AND `cciso`='{cciso}' AND `riso`='{riso}' AND `sroca`='{sroca}' AND `srocm`='{srocm}' AND `director`='{director}' AND `accountant`='{accountant}' AND `legal_address`='{legal_address}' AND `actual_address`='{actual_address}' AND `postal_address`='{postal_address}' AND `inn_kpp`='{inn_kpp}' AND `ogrn`='{ogrn}' AND `okpo_okvd`='{okpo_okvd}' AND `r_s`='{r_s}' AND `in`='{in}' AND `k_s`='{k_s}' AND `bik`='{bik}' AND `phone_fax`='{phone_fax}' AND `mail`='{mail}' ORDER BY `name` ASC;";
        $insert = "INSERT INTO `ks_contractor`
(`id`, `name`, `cciso`, `riso`, `sroca`, `srocm`, `director`, `accountant`, `legal_address`, `actual_address`, `postal_address`, `inn_kpp`, `ogrn`, `okpo_okvd`, `r_s`, `in`, `k_s`, `bik`, `phone_fax`, `mail`,`okyd`) 
VALUES 
(NULL, '{name}', '{cciso}', '{riso}', '{sroca}', '{srocm}', '{director}', '{accountant}', '{legal_address}', '{actual_address}', '{postal_address}', '{inn_kpp}', '{ogrn}', '{okpo_okvd}', '{r_s}', '{in}', '{k_s}', '{bik}', '{phone_fax}', '{mail}', '{okyd}');";
        $select_q = '';
        foreach(explode("\n",trim($_POST['src'])) as $ln)
        {
            if(strlen(trim($ln)) < 2) continue;
            $item = explode("\t", trim($ln));
            $item[0] = str_replace('`', '', $item[0]);
            $item[1] = str_replace("'", '', $item[1]);
            //insert
            $insert = str_replace('{'.trim($item[0]).'}',trim($item[1]),$insert);
            //select
            $select= str_replace('{'.trim($item[0]).'}',trim($item[1]),$select);
        }
        if(!($q = _mysql::query($select)))
        {
            return sdk::toApi('10','Invalid query [addNewContractor][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($q) > 0) return sdk::toApi ('500','Object Already Exists');
        if(!($q = _mysql::query($insert)))
        {
            return sdk::toApi('10','Invalid query [addNewContractor][2]',array(_mysql::getLastError()));
        }
        return sdk::toApi('200','OK');
    }
    public function getDiscount()
    {
        if(!isset($_GET['id']))
        {
            $q = "SELECT * FROM `ks_discount` ORDER BY `name` ASC;";
            if(!($q = _mysql::query($q)))
            {
                return sdk::toApi('10','Invalid query [getDiscount][1]',array(_mysql::getLastError()));
            }
            $res = array();
            while($r = _mysql::read_row($q))
            {
                $res = array('id'=>$r['id'],'name'=>$r['name']);
            }
            return sdk::toApi('200','OK',$res);
        }
        else
        {
            $id = str_replace("'",'',trim($_GET['id']));
            $q = "SELECT `ks_discount_content`.*,`ks_wl`.* FROM `ks_discount_content`,`ks_wl` WHERE `parent`='".$id."' AND `ks_discount_content`.`item_id`=`ks_wl`.`item_id` ORDER BY `id` ASC;";
            
        }
    }
    public function getContractor()
    {
        if(!isset($_GET['id']))
        {
            $q = "SELECT * FROM `ks_contractor` ORDER BY `name` ASC;";
        }
        else
        {
            $id = str_replace("'",'',trim($_GET['id']));
            $q = "SELECT * FROM `ks_contractor` WHERE `id`='".$id."' ORDER BY `name` ASC;";
        }
        if(!($q = _mysql::query($q)))
        {
            return sdk::toApi('10','Invalid query [getContractor][1]',array(_mysql::getLastError()));
        }
        $res = array();
        while($r = _mysql::read_row($q))
        {
            $tmp = array();
            $tmp['id']             = $r['id'];
            $tmp['name']           = $r['name'];
            $tmp['cciso']          = $r['cciso'];
            $tmp['riso']           = $r['riso'];
            $tmp['sroca']          = $r['sroca'];
            $tmp['srocm']          = $r['srocm'];
            $tmp['director']       = $r['director'];
            $tmp['accountant']     = $r['accountant'];
            $tmp['legal_address']  = $r['legal_address'];
            $tmp['actual_address'] = $r['actual_address'];
            $tmp['postal_address'] = $r['postal_address'];
            $tmp['inn_kpp']        = $r['inn_kpp'];
            $tmp['ogrn']           = $r['ogrn'];
            $tmp['okpo_okvd']      = $r['okpo_okvd'];
            $tmp['r_s']            = $r['r_s'];
            $tmp['in']             = $r['in'];
            $tmp['k_s']             = $r['k_s'];
            $tmp['bik']            = $r['bik'];
            $tmp['phone_fax']      = $r['phone_fax'];
            $tmp['mail']           = $r['mail'];
            $tmp['okyd']           = $r['okyd'];
            $res[] = $tmp;
        }
        return sdk::toApi('200','OK',$res);
    }
}
?>