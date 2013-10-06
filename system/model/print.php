<?php
class model_print
{
    public function ks2()
    {
        if(!isset($_GET['id']))  return sdk::toApi('100','Invalid Object Id');
        $s_id = (int)trim($_GET['id']);
        $arch = $this->getFromArch($s_id);
        if($arch['code'] != '200'){return $arch;}else{$arch = $arch['data'];}
        $price = $this->getPrice($arch['contract']);
        if($price['code'] != '200'){return $price;}else{$price = $price['data'];}
        $price_content = $this->getPriceContent($price['id']);
        if($price_content['code'] != '200'){$price_content = array();}else{$price_content = $price_content['data'];}
        $customer = $this->getContractor($price['customer']);
        if($customer['code'] != '200'){return $customer;}else{$customer = $customer['data'];}
        $contractor = $this->getContractor($price['contractor']);
        if($contractor['code'] != '200'){return $contractor;}else{$contractor = $contractor['data'];}
        $arch_content = $this->getArchivContent($arch['id']);
        if($arch_content['code'] != '200'){return $arch_content;}else{$arch_content = $arch_content['data'];}
        if($arch['type'] == 'drs'){
        $address = $this->getAddressByCable($arch['cable']);
        if($address['code'] != '200'){return $address;}else{$address = $address['data'];};}
        $args = array();
        $args['customer_name'] = $customer['name'];
        $args['customer_actual_address'] = $customer['actual_address'];
        $args['customer_phone'] = $customer['phone_fax'];
        $args['customer_okpo'] = $customer['okpo_okvd'];
        $args['customer_director'] = $customer['director'];
        $args['contractor_name'] = $contractor['name'];
        $args['contractor_actual_address'] = $contractor['actual_address'];
        $args['contractor_phone'] = $contractor['phone_fax'];
        $args['contractor_okpo'] = $contractor['okpo_okvd'];
        $args['contractor_director'] = $contractor['director'];
        $args['contract'] = $price['contract'];
        $args['contract_date'] = $price['contract_date'];
        $args['accord'] = $price['accord'];
        $args['accord_date'] = $price['accord_date'];
        $args['nn'] = $arch['nn'];
        $args['date_stop'] = $arch['date_stop'];
        $args['date_start'] = $arch['date_start'];
        $args['work_name'] = $arch['name'];
        if($arch['type'] == 'drs')
        {
            $args['address'] = $address['address'];
            $args['cable'] = $address['cable'];
        }
        else
        {
            $atmp = explode('-',$arch['cable']);
            $args['address'] = 'АТС: '.trim($atmp[0]);
            $args['cable'] = 'Кластер: '.trim($atmp[1]);
        }
        $ct = $this->makeContent($arch_content,$price_content,$arch['type']);
        if($arch['type'] == 'ms')
        {
         $ctc = $this->calcCompens($arch_content,$price_content);
         $ct[1] .= $ctc[1];
        }
        $args['content'] = $ct[1];
        $args['total_nds'] = $ct[0];
        $args['nds'] = round(($args['total_nds']*18)/118,2);
        $args['total'] =$args['total_nds']- $args['nds'];
        
        return $this->mkKs2($args);
    }
    private function calcCompens($content,$price)
    {
        $tpl = template::open('ks2_conetent');
        $itmp = '';
        $res = '';
        $targs = array();
        $x=1;
        $total = 0;
        
        foreach($content as $item)
        {
            $itmp = $this->getWorkItem($item['item_id']);
            if($itmp['code'] != '200'){return $itmp;}else{$itmp = $itmp['data'];}
            ///
            if(isset($targs[$item['item_id']]) && strpos($item['value'],'.') !== false)
            {
                $xxs = explode('.',$item['value']);
                $xxs = '0.'.trim($xxs[1]);
                $targs[$item['item_id']]['value'] += (1-$xxs);
                if(!isset($targs[$item['item_id']]['retail'])){$targs[$item['item_id']]['retail'] = $itmp['retail'];}
                $targs[$item['item_id']]['mathcalc'] = $targs[$item['item_id']]['value']*$targs[$item['item_id']]['retail'];
            }
            elseif(strpos($item['value'],'.') !== false)
            {
                $targs[$item['item_id']]['id'] = $x;
                $targs[$item['item_id']]['text'] = $itmp['text'];
                $targs[$item['item_id']]['text'] = "<b>".$item['cable']."</b>: ".$itmp['text'];
                $targs[$item['item_id']]['item_id'] = $itmp['item_id'];
                $targs[$item['item_id']]['type'] = $itmp['type'];
                $xxs = explode('.',$item['value']);
                $xxs = '0.'.trim($xxs[1]);
                $targs[$item['item_id']]['value'] = (1-$xxs);
                for($z=0;$z<count($price);$z++)
                {
                    if($targs[$item['item_id']]['type'] == $price[$z]['item_id'])
                    {
                        $targs[$item['item_id']]['retail'] = $price[$z]['retail'];
                        break;
                    }
                }
                //$targs[$item['item_id']]['value'] = $item['value'];
                if(!isset($targs[$item['item_id']]['retail'])){$targs[$item['item_id']]['retail'] = $itmp['retail'];}
                $targs[$item['item_id']]['mathcalc'] = $targs[$item['item_id']]['value']*$targs[$item['item_id']]['retail'];
            }
            else
            {
                continue;
            }
            $x++;
            
        }
        $res = '';
        foreach($targs as $x => $item)
        {
            
            $total +=$targs[$x]['mathcalc'];
            $res .= template::setKeys($item, $tpl);
        }
        return array($total,$res);
    }
    private function makeContent($content,$price,$type='drs')
    {
        $tpl = template::open('ks2_conetent');
        $itmp = '';
        $res = '';
        $targs = array();
        $x=1;
        $total = 0;
        foreach($content as $item)
        {
            $itmp = $this->getWorkItem($item['item_id']);
            if($itmp['code'] != '200'){return $itmp;}else{$itmp = $itmp['data'];}
            $targs = array();
            $targs['id'] = $x;
            if($type=='drs'){
                $targs['text'] = $itmp['text'];
            }else
            {
                $targs['text'] = "<b>".$item['cable']."</b>: ".$itmp['text'];
            }
            $targs['item_id'] = $itmp['item_id'];
            $targs['type'] = $itmp['type'];
            for($z=0;$z<count($price);$z++)
            {
                if($targs['type'] == $price[$z]['item_id']){
                    $targs['retail'] = $price[$z]['retail'];
                    break;
                }
            }
            if(!isset($targs['retail'])){$targs['retail'] = $itmp['retail'];}
            $targs['value'] = $item['value'];
            $targs['mathcalc'] = $targs['value']*$targs['retail'];
            $total +=$targs['mathcalc'];
            $res .= template::setKeys($targs, $tpl);$x++;
        }
        return array($total,$res);
    }
    private function getWorkItem($item_id)
    {
        $q = "SELECT * FROM `ks_wl` WHERE `item_id`='{0}';";
        if(!($q = _mysql::query($q,array($item_id))))
        {
            return sdk::toApi('10','Invalid query [getWorkItem][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($q) < 1) return sdk::toApi('404','NOT FOUND');
        $r = _mysql::read_row($q);
        return sdk::toApi('200','OK',$r);
    }
    private function getAddressByCable($cable)
    {
        $q = "SELECT * FROM `ap` WHERE `cable`='{0}';";
        if(!($q = _mysql::query($q,array($cable))))
        {
            return sdk::toApi('10','Invalid query [getAddressByCable][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($q) < 1) return sdk::toApi('404','NOT FOUND');
        $r = _mysql::read_row($q);
        return sdk::toApi('200','OK',$r);
    }
    private function getArchivContent($parent)
    {
         $q = "SELECT * FROM `ks_archiv_content` WHERE `parent`='{0}' ORDER BY `id` ASC;";
        if(!($q = _mysql::query($q,array($parent))))
        {
            return sdk::toApi('10','Invalid query [getArchivContent][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($q) < 1) return sdk::toApi('404','NOT FOUND');
        $res = array();
        while($r = _mysql::read_row($q))
        {
            $res[] = array('item_id'=>$r['item_id'],'value'=>$r['value'],'cable'=>$r['cable']);
        }
        return sdk::toApi('200','OK',$res);
    }
    private function getContractor($id)
    {
        $q = "SELECT * FROM `ks_contractor` WHERE `id`='{0}';";
        if(!($q = _mysql::query($q,array($id))))
        {
            return sdk::toApi('10','Invalid query [getContractor][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($q) < 1) return sdk::toApi('404','NOT FOUND');
        $r = _mysql::read_row($q);
        return sdk::toApi('200','OK',$r);
    }
    private function getPriceContent($parent)
    {
        $q = "SELECT * FROM `ks_price_content` WHERE `parent`='{0}' ORDER BY `id` ASC;";
        if(!($q = _mysql::query($q,array($parent))))
        {
            return sdk::toApi('10','Invalid query [getPriceContent][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($q) < 1) return sdk::toApi('404','NOT FOUND');
        $res = array();
        while($r = _mysql::read_row($q))
        {
            $res[] = array('item_id'=>$r['item_id'],'retail'=>$r['retail']);
        }
        return sdk::toApi('200','OK',$res);
    }
    private function getPrice($contract)
    {
        $q = "SELECT * FROM `ks_price` WHERE `id`='{0}';";
        if(!($q = _mysql::query($q,array($contract))))
        {
            return sdk::toApi('10','Invalid query [getPrice][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($q) < 1) return sdk::toApi('404','NOT FOUND');
        $r = _mysql::read_row($q);
        return sdk::toApi('200','OK',$r);
    }
    private function getFromArch($id)
    {
        $s_arch = "SELECT * FROM `ks_archiv` WHERE `id`='{0}' AND `archiv`='0000-00-00';";
        if(!($s_arch = _mysql::query($s_arch,array($id))))
        {
            return sdk::toApi('10','Invalid query [getFromArch][1]',array(_mysql::getLastError()));
        }
        if(_mysql::count($s_arch) < 1) return sdk::toApi('404','NOT FOUND');
        $s_arch = _mysql::read_row($s_arch);
        return sdk::toApi('200','OK',$s_arch);
    }
    private function mkKs2($args)
    {
        $sh = new mPDF('A4');
        
        $tpl =template::open('ks2');
        echo template::setKeys($args,$tpl);
        return '';
    }
}
?>
