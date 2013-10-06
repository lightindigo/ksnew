<?php
class model_ks
{
    public function getAddress($type, $dat)
    {
        $q = '';
        $args = array();
        switch ($type) {
            case 'ats':
                return sdk::toApi(200, 'ok', $this->getAtsList());
                break;
            case 'cluster-by-ats':
                $q = "SELECT `cluster` FROM `address` WHERE `cable` LIKE '%{0}-%' GROUP BY `cluster` ORDER BY `cluster` ASC;";
                $args[0] = $dat['ats'];
                break;
            case 'address-by-ats-cluster':
                $q = "SELECT `address` FROM `address` WHERE `cable` LIKE '%{0}-%' AND `cluster`='{1}' ORDER BY `address` ASC;";
                $args[0] = $dat['ats'];
                $args[1] = $dat['cluster'];
                break;
            case 'address':
                $q = "SELECT * FROM `address` WHERE `address` LIKE '{0}';";
                $args[0] = str_replace("'", "", $dat['address']);
                break;
        }
        if (!($q = _mysql::query($q, $args))) {
            return sdk::toApi('10', 'Invalid query [getAddress][1]', array(_mysql::getLastError()));
        }
        $res = array();
        while ($r = _mysql::read_row($q)) {
            $res[] = array();
            foreach ($r as $key => $val) {
                if (!is_numeric($key)) {
                    $res[count($res) - 1][$key] = $val;
                }
            }
        }
        return sdk::toApi(200, 'ok', $res);
    }

    public function getAtsList()
    {

        $q = "SELECT `cable` FROM `address`  ORDER BY `id` ASC;";
        if (!($q = _mysql::query($q))) {
            die(_mysql::getLastError());
        }
        while ($r = _mysql::read_row($q)) {
            if (strpos($r['cable'], 'PON') === false && strpos($r['cable'], 'P-') === false) continue;
            if (substr($r['cable'], 0, 3) == 'PON') {
                $tmp = explode('-', $r['cable']);
                $res[trim($tmp[3])] = '';
            } else {
                $tmp = explode('-', $r['cable']);
                $res[trim($tmp[0])] = '';
            }
        }
        $out = array();
        foreach ($res as $item => $z) {
            $out[] = trim($item);
        }
        return $out;
    }
    public function getAts($cable)
    {
            if (count(explode('-',$cable)) == 2)
            {
                $res = explode('-',$cable);
                return $res[0];
            }
            if (strpos($cable, 'PON') === false && strpos($cable, 'P-') === false && strpos($cable,'-P-') === false) return;
            if (substr($cable, 0, 3) == 'PON')
            {
                $tmp = explode('-', $cable);
                return trim($tmp[3]);
            } else if(substr($cable, 0, 1) == 'P-')
            {
                $tmp = explode('-', $cable);
                return trim($tmp[3]);
            }
            else
            {
                $tmp = explode('-', $cable);
                return trim($tmp[0]);
            }

    }
    public function getCluster1($cable)
    {
        $q = "SELECT * FROM `address` WHERE `cable` = '".$cable."'";
        if (!$q = _mysql::query($q,null))
            return sdk::toApi("101","Invalid query getCluster",_mysql::getLastError());
        if (_mysql::count($q) < 1)
            return $q;
        $q = _mysql::read_row($q);
        return $q['cluster'];
    }

    public function works($parent)
    {
        if ($parent == '') {
            $parent = "`item_id` NOT LIKE '%.%'";
        } elseif ($parent == 'all') {
            $parent = '`id` > 0';
        } else {
            $parent = "`item_id` LIKE '" . $parent . ".%' AND `item_id` NOT LIKE '" . $parent . ".%.%'";
        }
        $q = "SELECT * FROM `works` WHERE {0} ORDER BY `item_id`;";
        if (!($q = _mysql::query($q, array($parent)))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) return sdk::toApi('200', 'OK', array());
        $res = array();
        while ($r = _mysql::read_row($q)) {
            $res[] = array();
            foreach ($r as $key => $value) {
                if (!is_numeric($key)) $res[count($res) - 1][$key] = $value;
            }
            $ret = null;
            if (isset($_GET['contractor']))
            {
                $q2 = "SELECT `retail`
                FROM `price_content`
                WHERE `item` = '".$res[count($res) - 1]['id']."'
                AND `parent` = '".$_GET['contractor']."'";
                if (!($q2 = _mysql::query($q2)))
                    return sdk::toApi('10', 'Invalid query get contr price', array(_mysql::getLastError(),$res));
                if (!_mysql::count($q2)<1)
                {
                    $ret_q = _mysql::read_row($q2);
                    $ret = $ret_q['retail'];
                }
                if ($ret != null)
                    $res[count($res) - 1]['retail'] = $ret;
            }
        }

        return sdk::toApi('200', 'OK', $res);
    }

    public function catalog_price_add($args)
    {
        $q = "SELECT * FROM `price` WHERE `name`='" . $args['name'] . "';";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) > 0) {
            return sdk::toApi('404', 'Name Already Used');
        }
        $q = "INSERT INTO `price` (`id` ,`name` ,`contact_number` ,`contact_date` ,`accord_number` ,`accord_date` ,
        `type` ,`customer` ,`contactor`)
        VALUES(NULL,'" . $args['name'] . "','" . $args['contact_number'] . "','" . $args['contact_date'] . "',
        '" . $args['accord_number'] . "','" . $args['accord_date'] . "','" . $args['type'] . "','" . $args['customer'] . "',
        '" . $args['contactor'] . "');";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][2]', array(_mysql::getLastError()));
        }
        $q = "UPDATE `price` SET ";
        foreach ($args as $key => $val) {
            if ($key == 'name') continue;
            $q .= "`" . $key . "`='" . $val . "',";
        }
        $q = trim($q, ',');
        $q .= " WHERE `name`='" . $args['name'] . "';";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][3]', array(_mysql::getLastError()));
        }
        return sdk::toApi('200', 'ok');
    }

    public function catalog_price_getdata($id)
    {
        $q = "SELECT * FROM `price` WHERE `id`='{0}';";
        if (!($q = _mysql::query($q, array($id)))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }

        if (_mysql::count($q) < 1) {
            return sdk::toApi('404', 'Not Found');
        }
        $r = _mysql::read_row($q);
        $res = array();
        foreach ($r as $key => $val) {
            if (!is_numeric($key)) {
                $res[$key] = $val;
            }
        }
        $cont = $this->catalog_contractor_get_data($res['contactor']);
        if ($cont['code'] != '200') {
            return $cont;
        } else {
            $res['contactor'] = $cont['data'];
        }
        $cont = $this->catalog_contractor_get_data($res['customer']);
        if ($cont['code'] != '200') {
            return $cont;
        } else {
            $res['customer'] = $cont['data'];
        }

        return sdk::toApi('200', 'ok', $res);
    }

    public function catalog_price_update($args)
    {
        $q = "SELECT * FROM `price` WHERE `id`='" . $args['id'] . "';";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) {
            return sdk::toApi('404', 'Not found');
        }
        $q = "UPDATE `price` SET ";
        foreach ($args as $key => $val) {
            if ($key == 'id') continue;
            $q .= "`" . $key . "`='" . $val . "',";
        }
        $q = trim($q, ',');
        $q .= " WHERE `id`='" . $args['id'] . "';";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        return sdk::toApi('200', 'ok');
    }

    public function catalog_price_delete($id)
    {
        $q = "DELETE FROM `price` WHERE `id`='{0}';";
        if (!($q = _mysql::query($q, array($id)))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        return sdk::toApi('200', 'ok');
    }

    public function catalog_contractor_add($args)
    {

        $q = "SELECT * FROM `contract` WHERE `name`='" . $args['name'] . "';";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) > 0) {
            return sdk::toApi('404', 'Name Already Used');
        }
        $q = "INSERT INTO `contract` (`id`,`name`) VALUES(NULL,'" . $args['name'] . "');";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        $q = "UPDATE `contract` SET ";
        foreach ($args as $key => $val) {
            if ($key == 'name') continue;
            $q .= "`" . $key . "`='" . $val . "',";
        }
        $q = trim($q, ',');
        $q .= " WHERE `name`='" . $args['name'] . "';";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        return sdk::toApi('200', 'ok');
    }

    public function catalog_contractor_update($args)
    {
        $q = "SELECT * FROM `contract` WHERE `id`='" . $args['id'] . "';";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) {
            return sdk::toApi('404', 'Not found');
        }
        $q = "UPDATE `contract` SET ";
        foreach ($args as $key => $val) {
            if ($key == 'id') continue;
            $q .= "`" . $key . "`='" . $val . "',";
        }
        $q = trim($q, ',');
        $q .= " WHERE `id`='" . $args['id'] . "';";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        return sdk::toApi('200', 'ok');
    }

    public function catalog_contractor_list_get()
    {
        $q = "SELECT * FROM `contract` ORDER BY `id` DESC;";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) {
            return sdk::toApi('200', 'ok', array());
        }
        $res = array();
        while ($r = _mysql::read_row($q)) {
            $res[] = array();
            foreach ($r as $key => $val) {
                if (!is_numeric($key)) {
                    $res[count($res) - 1][$key] = $val;
                }
            }
        }
        return sdk::toApi('200', 'ok', $res);
    }

    public function catalog_contractor_get_data($id)
    {
        $q = "SELECT * FROM `contract` WHERE `id`='{0}';";
        if (!($q = _mysql::query($q, array($id)))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) {
            return sdk::toApi('404', 'Not Found');
        }
        $r = _mysql::read_row($q);
        $res = array();
        foreach ($r as $key => $val) {
            if (!is_numeric($key)) {
                $res[$key] = $val;
            }
        }
        return sdk::toApi('200', 'ok', $res);
    }

    public function catalog_contractor_delete($id)
    {
        $q = "DELETE FROM `contract` WHERE `id`='{0}';";
        if (!($q = _mysql::query($q, array($id)))) {
            return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
        }
        return sdk::toApi('200', 'ok');
    }

    public function catalog_price_list_get($id = false)
    {
        if (!$id) {
            $q = "SELECT * FROM `price` ORDER BY `id` DESC;";
            if (!($q = _mysql::query($q))) {
                return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
            }
        } else {
            $q = "SELECT * FROM `price` WHERE `customer`='{0}' AND `contactor`='{1}' AND `type`='{2}';";
            $args = array($id['constomer'], $id['contractor'], $id['type']);
            if (!($q = _mysql::query($q, $args))) {
                return sdk::toApi('10', 'Invalid query [getCatObject][1]', array(_mysql::getLastError()));
            }
        }

        if (_mysql::count($q) < 1) {
            return sdk::toApi('200', 'ok', array());
        }
        $res = array();
        while ($r = _mysql::read_row($q)) {
            $res[] = array();
            foreach ($r as $key => $val) {
                if (!is_numeric($key)) {
                    $res[count($res) - 1][$key] = $val;
                }
            }
            $cont = $this->catalog_contractor_get_data($res[count($res) - 1]['contactor']);
            if ($cont['code'] != '200') {
                return $cont;
            } else {
                $res[count($res) - 1]['contactor'] = $cont['data'];
            }
            $cont = $this->catalog_contractor_get_data($res[count($res) - 1]['customer']);
            if ($cont['code'] != '200') {
                return $cont;
            } else {
                $res[count($res) - 1]['customer'] = $cont['data'];
            }
        }
        return sdk::toApi('200', 'ok', $res);
    }

    public function save_ks_drs($args)
    {
        //ks
        $q = "SELECT * FROM `ks` WHERE `cable`='" . $args['project'] . "' AND `contract`='" . $args['price'] . "' AND `name`='" . $args['work_name'] . "' AND `number`='" . $args['doc_n'] . "'";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [save_ks_drs][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) > 0) {
            $q = "DELETE FROM `ks` WHERE `cable`='" . $args['project'] . "' AND `contract`='" . $args['price'] . "' AND `name`='" . $args['work_name'] . "' AND `number`='" . $args['doc_n'] . "';";
            if (!($q = _mysql::query($q))) {
                return sdk::toApi('10', 'Invalid query [save_ks_drs][2]', array(_mysql::getLastError()));
            }
        }
        $q = "INSERT INTO `ks` (`cable`,`contract`,`number`,`date_start`,`date_stop`,`name`, `prepayment`) VALUES('{0}','{1}','{2}','{3}','{4}','{5}','{6}');";
        $qArgs = array($args['project'], $args['price'], $args['doc_n'], $args['date_a'], $args['date_b'], $args['work_name'],$args['prepayment']);
        if (!($q = _mysql::query($q, $qArgs))) {
            return sdk::toApi('10', 'Invalid query [save_ks_drs][3]', array(_mysql::getLastError()));
        }
        $q = "SELECT * FROM `ks` WHERE `cable`='" . $args['project'] . "' AND `contract`='" . $args['price'] . "' AND `name`='" . $args['work_name'] . "' AND `number`='" . $args['doc_n'] . "'";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [save_ks_drs][4]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) {
            return sdk::toApi('101', 'Invalid query [save_ks_drs][i][3]', array(_mysql::getLastError()));
        }
        $id = _mysql::read_row($q);
        $id = $id['id'];
        foreach (explode("\n", $args['works']) as $ln) {
            $tmp = explode(';', trim($ln));
            $q = "INSERT INTO `ks_content` (`parent`,`item`,`value`) VALUES('{0}','{1}','{2}');";
            $qArgs = array($id, trim($tmp[0]), trim($tmp[1]));
            if (!($q = _mysql::query($q, $qArgs))) {
                return sdk::toApi('10', 'Invalid query [save_ks_drs][4]', array(_mysql::getLastError()));
            }
        }
        foreach (explode("\n", $args['material']) as $ln) {
            $qArgs = array($id);
            foreach (explode(';', trim($ln)) as $item) {
                $qArgs[] = trim($item);
            }
            $q = "INSERT INTO `material` (`parent`,`number`,`party`,`name`,`type`,`value`,`price`,`retail`) VALUES('{0}','{1}','{2}','{3}','{4}','{5}','{6}','{7}');";
            if (!($q = _mysql::query($q, $qArgs))) {
                return sdk::toApi('10', 'Invalid query [save_ks_drs][5]', array(_mysql::getLastError()));
            }
        }
        return sdk::toApi('200', 'ok', array('ks' => $id));
    }

    public function save_ks_ms($args)
    {
        $args['project'] = $args['ats'] . '-' . $args['cluster'];
        $q = "SELECT * FROM `ks` WHERE `cable`='" . $args['project'] . "' AND `contract`='" . $args['price'] . "' AND `name`='" . $args['work_name'] . "' AND `number`='" . $args['doc_n'] . "'";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [save_ks_drs][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) > 0) {
            $q = "DELETE FROM `ks` WHERE `cable`='" . $args['project'] . "' AND `contract`='" . $args['price'] . "' AND `name`='" . $args['work_name'] . "' AND `number`='" . $args['doc_n'] . "';";
            if (!($q = _mysql::query($q))) {
                return sdk::toApi('10', 'Invalid query [save_ks_drs][2]', array(_mysql::getLastError()));
            }
        }
        $q = "INSERT INTO `ks` (`cable`,`contract`,`number`,`date_start`,`date_stop`,`name`,`prepayment`) VALUES('{0}','{1}','{2}','{3}','{4}','{5}','{6}');";
        $qArgs = array($args['project'], $args['price'], $args['doc_n'], $args['date_a'], $args['date_b'], $args['work_name'],$args['prepayment']);
        if (!($q = _mysql::query($q, $qArgs))) {
            return sdk::toApi('10', 'Invalid query [save_ks_drs][3]', array(_mysql::getLastError(),$args['[prepayment']));
        }
        $q = "SELECT * FROM `ks` WHERE `cable`='" . $args['project'] . "' AND `contract`='" . $args['price'] . "' AND `name`='" . $args['work_name'] . "' AND `number`='" . $args['doc_n'] . "'";
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [save_ks_drs][4]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) {
            return sdk::toApi('101', 'Invalid query [save_ks_drs][i][3]', array(_mysql::getLastError()));
        }
        $id = _mysql::read_row($q);
        $id = $id['id'];
        ////
        foreach (explode("\n", $args['works']) as $ln) {
            $tmp = explode(';', trim($ln));
            $q = "INSERT INTO `ks_content` (`parent`,`item`,`value`,`address`) VALUES('{0}','{1}','{2}','{3}');";
            $qArgs = array($id, trim($tmp[1]), trim($tmp[2]), trim($tmp[0]));
            if (!($q = _mysql::query($q, $qArgs))) {
                return sdk::toApi('10', 'Invalid query [save_ks_drs][6]', array(_mysql::getLastError()));
            }
        }
        foreach (explode("\n", $args['material']) as $ln) {
            $qArgs = array($id);
            foreach (explode(';', trim($ln)) as $item) {
                $qArgs[] = trim($item);
            }
            $q = "INSERT INTO `material` (`parent`,`number`,`party`,`name`,`type`,`value`,`price`,`retail`) VALUES('{0}','{1}','{2}','{3}','{4}','{5}','{6}','{7}');";
            if (!($q = _mysql::query($q, $qArgs))) {
                return sdk::toApi('10', 'Invalid query [save_ks_drs][6]', array(_mysql::getLastError()));
            }
        }
        if ($args['compens']==null)
        {
            return sdk::toApi('200', 'ok', array('ks' => $id));
        }
        foreach (explode("\n", $args['compens']) as $ln) {
            $tmp = explode(';', trim($ln));
            $q = $q = "INSERT INTO `compens` (`parent`,`item_id`,`value`,`price`,`retail`) VALUES('{0}','{1}','{2}','{3}','{4}');";
            $qArgs = array($id);
            foreach (explode(';', trim($ln)) as $item) {
                $qArgs[] = trim($item);
            }

            if (!($q = _mysql::query($q, $qArgs))) {
                return sdk::toApi('10', 'Invalid query [save_ks_drs][7]', array(_mysql::getLastError(),$ln));
            }
        }
        return sdk::toApi('200', 'ok', array('ks' => $id));
    }

    public function ks_list($args)
    {
        if ($args['type'] == 'drs') {
            $q = "SELECT `ks`.*,`address`.`address` FROM `ks`,`address` WHERE `address`.`cable` LIKE '%{0}-%' AND `address`.`cluster` = '{1}' AND `ks`.`cable`=`address`.`cable` ORDER BY `id` DESC;";
        } else {
            $q = "SELECT `ks`.* FROM `ks` WHERE `ks`.`cable`='{0}-{1}' ORDER BY `id` DESC;";
        }
        if (!($q = _mysql::query($q, array($args['ats'], $args['cluster'])))) {
            return sdk::toApi('10', 'Invalid query [ks_list][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) return sdk::toApi('200', 'ok', array());
        $res = array();
        while ($r = _mysql::read_row($q)) {
            $res[] = array();
            foreach ($r as $key => $val) {
                if (!is_numeric($key)) {
                    $res[count($res) - 1][$key] = $val;
                }
            }
            if ($args['type'] == 'ms') {
                $res[count($res) - 1]['address'] = $args['ats'] . '-' . $args['cluster'];
            }
        }
        return sdk::toApi('200', 'ok', $res);
    }

    public function ks_data($id)
    {
        $q = "SELECT * FROM `ks` WHERE `id`='{0}';";
        if (!($q = _mysql::query($q, array($id)))) {
            return sdk::toApi('10', 'Invalid query [ks_data][1]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) < 1) return sdk::toApi('404', 'Not found');
        $r = _mysql::read_row($q);
        $res = array();
        foreach ($r as $key => $val) {
            if (!is_numeric($key)) {
                $res[$key] = $val;
            }
        }
        $mkPrice = $this->price_item('get', array('parent' => $res['contract'], 'id' => '', 'retail' => ''));
        if ($mkPrice['code'] != '200') {
            return $mkPrice;
        }
        $mkPrice = $mkPrice['data'];
        $q = "SELECT `address` FROM `address` WHERE `cable`='{0}';";
        if (!($q = _mysql::query($q, array($res['cable'])))) {
            return sdk::toApi('10', 'Invalid query [ks_data][2]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) > 0) {
            $r = _mysql::read_row($q);
            $res['address'] = $r['address'];
        } else {
            $res['address'] = '';
        }
        //get content;
        $q = "SELECT `works`.*,`ks_content`.`value`,`ks_content`.`address` FROM `ks_content`,`works` WHERE `ks_content`.`parent`='{0}' AND `ks_content`.`item`=`works`.`id` ORDER BY `ks_content`.`id` ASC;";
        if (!($q = _mysql::query($q, array($res['id'])))) {
            return sdk::toApi('10', 'Invalid query [ks_data][2]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) > 0) {
            $res_tmp = array();
            while ($r = _mysql::read_row($q)) {
                $res_tmp[] = array();
                foreach ($r as $key => $val) {
                    if (!is_numeric($key)) {
                        $res_tmp[count($res_tmp) - 1][$key] = $val;
                    }
                }
                if ($res_tmp[count($res_tmp) - 1]['address'] != '0') {
                    $q_tmp = "SELECT * FROM `address` WHERE `id`='{0}';";
                    if (!($q_tmp = _mysql::query($q_tmp, array($res_tmp[count($res_tmp) - 1]['address'])))) {
                        return sdk::toApi('10', 'Invalid query [ks_data][3]', array(_mysql::getLastError()));
                    }
                    if (_mysql::count($q_tmp) > 0) {
                        $r_tmp = _mysql::read_row($q_tmp);
                        $res_tmp[count($res_tmp) - 1]['address'] = array('address' => $r_tmp['address'], 'cable' => $r_tmp['cable']);
                    }
                }
                foreach ($mkPrice as $itm) {
                    if ($itm['work']['id'] == $res_tmp[count($res_tmp) - 1]['id']) {
                        $res_tmp[count($res_tmp) - 1]['retail'] = $itm['retail'];
                    }
                }
            }
            $res['tmp'] = $mkPrice;
            $res['content'] = $res_tmp;
        } else {
            $res['content'] = array();
        }
        $q = "SELECT * FROM `material` WHERE `parent`='{0}' ORDER BY `id` ASC;";
        if (!($q = _mysql::query($q, array($res['id'])))) {
            return sdk::toApi('10', 'Invalid query [ks_data][4]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) > 0) {
            $res_tmp = array();
            while ($r = _mysql::read_row($q)) {
                $res_tmp[] = array();
                foreach ($r as $key => $val) {
                    if (!is_numeric($key)) {
                        $res_tmp[count($res_tmp) - 1][$key] = $val;
                    }
                }
            }
            $res['material'] = $res_tmp;
        } else {
            $res['material'] = array();
        }
        $q = "SELECT `works`.*,`compens`.`value`,`compens`.`price`,`compens`.`retail` FROM `compens`,`works` WHERE `compens`.`parent`='{0}' AND `works`.`id`=`compens`.`item_id` ORDER BY `id` ASC;";
        if (!($q = _mysql::query($q, array($res['id'])))) {
            return sdk::toApi('10', 'Invalid query [ks_data][5]', array(_mysql::getLastError()));
        }
        if (_mysql::count($q) > 0) {
            $res_tmp = array();
            while ($r = _mysql::read_row($q)) {
                $res_tmp[] = array();
                foreach ($r as $key => $val) {
                    if (!is_numeric($key)) {
                        $res_tmp[count($res_tmp) - 1][$key] = $val;
                    }
                }
            }
            $res['compens'] = $res_tmp;
        } else {
            $res['compens'] = array();
        }

        $tmp = $this->catalog_price_getdata($res['contract']);
        $res['contract'] = $tmp['data'];
        $res['ats'] = model_ks::getAts($res['cable']);
        $type_det = explode("-",$res['cable']);
        if (isset($type_det[2]))
        $res['cluster'] = model_ks::getCluster1($res['cable']);
        else
            $res['cluster'] = $type_det[1];
        return sdk::toApi('200', 'ok', $res);
    }

    public function ks_print($id, $type)
    {
        $ks = $this->ks_data($id);
        switch ($type) {
            case '2':
                $ks = $this->display_ks2($ks);
                break;
            case '3':
                $ks = $this->display_ks3($ks);
                break;
            case '11':
                $ks = $this->display_ks11($ks);
                break;
        }
        return $ks;
    }

    ///
    private function display_ks11($res)
    {
        //sdk::import('class', 'mpdf/mpdf');
        $res = $res['data'];
        $body = template::open('print_ks11');
        $args = array();
        $args['date_start'] = new DateTime($res['date_start'], new DateTimeZone("Europe/Moscow"));
        $args['date_start'] = $args['date_start']->format("d-m-Y");
        $args['date_stop'] = new DateTime($res['date_stop'], new DateTimeZone("Europe/Moscow"));
        $args['date_stop'] = $args['date_stop']->format("d-m-Y");
        $args['work_name'] = $res['name'];
        $args['nn'] = $res['number'];
        $args['cable'] = $res['cable'];
        $args['address'] = $res['address'];
        //customer
        $args['customer_name'] = $res['contract']['customer']['name'];
        $args['customer_actual_address'] = $res['contract']['customer']['actual_address'];
        $args['customer_phone'] = $res['contract']['customer']['phone'];
        $args['customer_okpo'] = $res['contract']['customer']['okpo'];
        $args['customer_director'] = $res['contract']['customer']['director'];
        //contractor
        $args['contractor_name'] = $res['contract']['contactor']['name'];
        $args['contractor_actual_address'] = $res['contract']['contactor']['actual_address'];
        $args['contractor_phone'] = $res['contract']['contactor']['phone'];
        $args['contractor_okpo'] = $res['contract']['contactor']['okpo'];
        $args['contractor_director'] = $res['contract']['contactor']['director'];
        //contract
        $args['contract'] = $res['contract']['contact_number'];
        $args['contract_date'] = new DateTime($res['contract']['contact_date'], new DateTimeZone("Europe/Moscow"));
        $args['contract_date'] = $args['contract_date']->format("d-m-Y");
        $args['accord'] = $res['contract']['accord_number'];
        $args['accord_date'] = new DateTime($res['contract']['accord_date'], new DateTimeZone("Europe/Moscow"));
        $args['accord_date'] = $args['accord_date']->format("d-m-Y");
        //content
        $args['total_nds'] = 0;
        $args['nds'] = 0;
        $args['total'] = 0;
        $item = '';
        for ($i = 0; $i < count($res['content']); $i++) {
            $item .= '<tr>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . ($i + 1) . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;"></td>';
            $item .= '<td style="border: 1px solid #000;">' . $res['content'][$i]['text'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['item_id'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['min'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['value'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['retail'] . '</td>';
            $r = (float)((str_replace(',', '.', $res['content'][$i]['value'])/str_replace(',', '.', $res['content'][$i]['min'])) * str_replace(',', '.', $res['content'][$i]['retail']));
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . round($r, 2) . '</td>';
            $item .= '</tr>';
            $args['total_nds'] += $r;
        }
        $args['nds'] = (float)(($args['total_nds'] * 18) / 100);
        $args['nds'] = round($args['nds'], 2);
        $args['total'] = (float)($args['total_nds'] - $args['nds']);
        $args['total'] = round($args['total'], 2);
        $args['content'] = $item;
        $item = '';
        $args['material_total'] = 0;
        for ($i = 0; $i < count($res['material']); $i++) {
            $item .= '<tr>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . ($i + 1) . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['number'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['party'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['name'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['type'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['value'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['price'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['retail'] . '</td>';
            $item .= '</tr>';
            $args['material_total'] += (float)$res['material'][$i]['retail'];
        }
        $args['material_total'] = round($args['material_total'], 2);
        $args['material'] = $item;
        // $pdf = new mPDF('utf-8', 'A4');
        // $pdf->WriteHTML(template::setKeys($args, $body));
        // $pdf->Output();
        $out_result = template::setKeys($args, $body);
        echo $out_result;

        return '';
        //return template::setKeys($args, $body);
    }

    ///
    private function display_ks3($res)
    {
        //sdk::import('class', 'mpdf/mpdf');
        $res = $res['data'];
        $body = template::open('print_ks3');
        $args = array();
        $args['date_start'] = new DateTime($res['date_start'], new DateTimeZone("Europe/Moscow"));
        $args['date_start'] = $args['date_start']->format("d-m-Y");
        $args['date_stop'] = new DateTime($res['date_stop'], new DateTimeZone("Europe/Moscow"));
        $args['date_stop'] = $args['date_stop']->format("d-m-Y");
        $args['work_name'] = $res['name'];
        $args['nn'] = $res['number'];
        $args['cable'] = $res['cable'];
        $args['address'] = $res['address'];
        $args['prep'] = $res['prepayment'];
        $args['nds_prep'] =round($res['prepayment']*0.18);
        //customer
        $args['customer_name'] = $res['contract']['customer']['name'];
        $args['customer_actual_address'] = $res['contract']['customer']['actual_address'];
        $args['customer_phone'] = $res['contract']['customer']['phone'];
        $args['customer_okpo'] = $res['contract']['customer']['okpo'];
        $args['customer_director'] = $res['contract']['customer']['director'];
        //contractor
        $args['contractor_name'] = $res['contract']['contactor']['name'];
        $args['contractor_actual_address'] = $res['contract']['contactor']['actual_address'];
        $args['contractor_phone'] = $res['contract']['contactor']['phone'];
        $args['contractor_okpo'] = $res['contract']['contactor']['okpo'];
        $args['contractor_director'] = $res['contract']['contactor']['director'];
        //contract
        $args['contract'] = $res['contract']['contact_number'];
        $args['contract_date'] = new DateTime($res['contract']['contact_date'], new DateTimeZone("Europe/Moscow"));
        $args['contract_date'] = $args['contract_date']->format("d-m-Y");
        $args['accord'] = $res['contract']['accord_number'];
        $args['accord_date'] = new DateTime($res['contract']['accord_date'], new DateTimeZone("Europe/Moscow"));
        $args['accord_date'] = $args['accord_date']->format("d-m-Y");
        //content
        $args['total_nds'] = 0;
        $args['nds'] = 0;
        $args['total'] = 0;
        $item = '';
        for ($i = 0; $i < count($res['content']); $i++) {
            $item .= '<tr>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . ($i + 1) . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;"></td>';
            $item .= '<td style="border: 1px solid #000;">' . $res['content'][$i]['text'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['item_id'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['min'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['value'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['retail'] . '</td>';
            $r = (float)((str_replace(',', '.', $res['content'][$i]['value'])/str_replace(',', '.', $res['content'][$i]['min'])) * str_replace(',', '.', $res['content'][$i]['retail']));
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . round($r, 2) . '</td>';
            $item .= '</tr>';
            $args['total_nds'] += $r;
        }
        $args['nds'] = (float)(($args['total_nds'] * 18) / 100);
        $args['nds'] = round($args['nds'], 2);
        $args['total'] = (float)($args['total_nds'] - $args['nds']);
        $args['total'] = round($args['total'], 2);
        $args['content'] = $item;
        $args['total_pay'] = $args['total_nds']-$args['prep'];
        $item = '';
        $args['material_total'] = 0;
        for ($i = 0; $i < count($res['material']); $i++) {
            $item .= '<tr>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . ($i + 1) . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['number'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['party'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['name'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['type'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['value'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['price'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['retail'] . '</td>';
            $item .= '</tr>';
            $args['material_total'] += (float)$res['material'][$i]['retail'];
        }
        $args['material_total'] = round($args['material_total'], 2);
        $args['material'] = $item;
        // $pdf = new mPDF('utf-8', 'A4');
        // $pdf->WriteHTML(template::setKeys($args, $body));
        // $pdf->Output();
        $out_result = template::setKeys($args, $body);
        echo $out_result;
        return '';
        //return template::setKeys($args, $body);
    }

    ///
    private function display_ks2($res)
    {
        //sdk::import('class', 'mpdf/mpdf');
        $res = $res['data'];
        $body = template::open('print_ks2');
        $args = array();

        $args['date_start'] = new DateTime($res['date_start'], new DateTimeZone("Europe/Moscow"));
        $args['date_start'] = $args['date_start']->format("d-m-Y");
        $args['date_stop'] = new DateTime($res['date_stop'], new DateTimeZone("Europe/Moscow"));
        $args['date_stop'] = $args['date_stop']->format("d-m-Y");
        $args['work_name'] = $res['name'];
        $args['nn'] = $res['number'];
        $args['cable'] = $res['cable'];
        $args['address'] = $res['address'];
        //customer
        $args['customer_name'] = $res['contract']['customer']['name'];
        $args['customer_actual_address'] = $res['contract']['customer']['actual_address'];
        $args['customer_phone'] = $res['contract']['customer']['phone'];
        $args['customer_okpo'] = $res['contract']['customer']['okpo'];
        $args['customer_director'] = $res['contract']['customer']['director'];
        //contractor
        $args['contractor_name'] = $res['contract']['contactor']['name'];
        $args['contractor_actual_address'] = $res['contract']['contactor']['actual_address'];
        $args['contractor_phone'] = $res['contract']['contactor']['phone'];
        $args['contractor_okpo'] = $res['contract']['contactor']['okpo'];
        $args['contractor_director'] = $res['contract']['contactor']['director'];
        //contract
        $args['contract'] = $res['contract']['contact_number'];
        $args['contract_date'] = new DateTime($res['contract']['contact_date'], new DateTimeZone("Europe/Moscow"));
        $args['contract_date'] = $args['contract_date']->format("d-m-Y");
        $args['accord'] = $res['contract']['accord_number'];
        $args['accord_date'] = new DateTime($res['contract']['accord_date'], new DateTimeZone("Europe/Moscow"));
        $args['accord_date'] = $args['accord_date']->format("d-m-Y");
        //content
        $args['total_nds'] = 0;
        $args['nds'] = 0;
        $args['total'] = 0;
        $item = '';
        for ($i = 0; $i < count($res['content']); $i++) {
            /*$item .= '<tr>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">'.($i+1).'</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;"></td>';
            $item .= '<td style="border: 1px solid #000;">'.$res['content'][$i]['text'].'</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">'.$res['content'][$i]['item_id'].'</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">'.$res['content'][$i]['min'].'</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">'.$res['content'][$i]['value'].'</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">'.$res['content'][$i]['retail'].'</td>';
            $r = (float)(str_replace(',','.',$res['content'][$i]['value'])*str_replace(',','.',$res['content'][$i]['retail']));
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">'.round($r,2).'</td>';
            $item .='</tr>';
            $args['total_nds'] += $r;*/

            /*$item .= '<tr style="height:50px">';
            $item .= '<td style="border: 1px solid #000;"></td>';
            $item .= '<td style="border: 1px solid #000;"></td>';
            $item .= '<td style="border: 1px solid #000;"></td>';
            $item .= '<td style="border: 1px solid #000;"></td>';
            $item .= '<td style="border: 1px solid #000;"></td>';
            $item .= '<td style="border: 1px solid #000;"></td>';
            $item .= '<td style="border: 1px solid #000;"></td>';
            $item .= '<td style="border: 1px solid #000;"></td>';
            $item .= '</tr >';*/
            if(($i == 0) || ($res['content'][$i]['address']['address'] != $res['content'][$i-1]['address']['address']))
            {
                $item .= '<tr><td style="border: 1px solid #000; text-align: center; vertical-align: middle;"></td>';
                $item .= '<td style="border: 1px solid #000; vertical-align: middle;" colspan = "7">';
                $item .= $res['content'][$i]['address']['address'];
                $item .= '</td></td>';
            }
            $item .= '<tr>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . ($i + 1) . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;"></td>';
            $item .= '<td style="border: 1px solid #000;">' . $res['content'][$i]['text'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['item_id'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['min'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['value'] . '</td>';
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['content'][$i]['retail'] . '</td>';
            $r = (float)((str_replace(',', '.', $res['content'][$i]['value'])/str_replace(',', '.', $res['content'][$i]['min'])) * str_replace(',', '.', $res['content'][$i]['retail']));
            $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . round($r, 2) . '</td>';
            $item .= '</tr>';
            $args['total_nds'] += $r;
        }
        $args['nds'] = (float)(($args['total_nds'] * 18) / 100);
        $args['nds'] = round($args['nds'], 2);
        $args['total'] = (float)($args['total_nds'] - $args['nds']);
        $args['total'] = round($args['total'], 2);
        $args['content'] = $item;
        $item = '';
        $args['material_total'] = 0;
        if (count($res['material']) > 0)
        {
            $item.= '
            <table style="width: 100%;" align="center" CELLSPACING="0">
                <tr>
                    <th colspan="8" style="text-align: left; border: 1px solid #000;">Раздел 2. Материалы, поставляемые заказчиком:</th>
                </tr>
                <tr>
                    <th style="border: 1px solid #000;">№№</th>
                    <th style="border: 1px solid #000;">Номер материала</th>
                    <th style="border: 1px solid #000;">Партия</th>
                    <th style="border: 1px solid #000;">Наименование материала</th>
                    <th style="border: 1px solid #000;">Ед. измерения</th>
                    <th style="border: 1px solid #000;">Кол-во</th>
                    <th style="border: 1px solid #000;">Цена</th>
                    <th style="border: 1px solid #000;">Сумма</th>
                </tr>
                ';

            for ($i = 0; $i < count($res['material']); $i++) {
                $item .= '<tr>';
                $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . ($i + 1) . '</td>';
                $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['number'] . '</td>';
                $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['party'] . '</td>';
                $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['name'] . '</td>';
                $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['type'] . '</td>';
                $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['value'] . '</td>';
                $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['price'] . '</td>';
                $item .= '<td style="border: 1px solid #000; text-align: center; vertical-align: middle;">' . $res['material'][$i]['retail'] . '</td>';
                $item .= '</tr>';
                $args['material_total'] += (float)$res['material'][$i]['retail'];
            }
            $item.= '<tr>
                    <th colspan="4" style="text-align: right;">Итог по разделу 2 без НДС</th>
                    <th colspan="4" style="text-align: right; border: 1px solid #000;">'.$args['material_total'].'</th>
                </tr>
            </table>';
        }
        $args['material_total'] = round($args['material_total'], 2);
        $args['material'] = $item;
     // $pdf = new mPDF('utf-8', 'A4');
     // $pdf->WriteHTML(template::setKeys($args, $body));
     // $pdf->Output();
        $out_result = template::setKeys($args, $body);
        echo $out_result;
        return '';
        //return template::setKeys($args, $body);
    }

    public function price_item($act, $args)
    {
        $q = '';
        switch ($act) {
            case 'append':
                $q = "INSERT INTO `price_content` (`parent`,`item`,`retail`) VALUES('" . $args['parent'] . "','" . $args['id'] . "','" . $args['retail'] . "');";
                break;
            case 'remove':
                $q = "DELETE FROM `price_content` WHERE `parent`='" . $args['parent'] . "' AND `item`='" . $args['id'] . "'";
                break;
            case 'get':
                $q = "SELECT * FROM `price_content` WHERE `parent`='" . $args['parent'] . "' ORDER BY `item` ASC;";
                break;
        }
        if (!($q = _mysql::query($q))) {
            return sdk::toApi('10', 'Invalid query [price_item][1]', array(_mysql::getLastError()));
        }
        if ($act != 'get') {
            return sdk::toApi('200', 'ok');
        }
        $res = array();
        while ($r = _mysql::read_row($q)) {
            $res[] = array();
            foreach ($r as $key => $value) {
                if (!is_numeric($key)) {
                    $res[count($res) - 1][$key] = $value;
                }
            }
            $q2 = "SELECT * FROM `works` WHERE `id`='" . $res[count($res) - 1]['item'] . "';";
            if (!($q2 = _mysql::query($q2))) {
                return sdk::toApi('10', 'Invalid query [price_item][2]', array(_mysql::getLastError()));
            }
            $res2 = array();
            while ($r2 = _mysql::read_row($q2)) {
                foreach ($r2 as $key => $value) {
                    if (!is_numeric($key)) {
                        $res2[$key] = $value;
                    }
                }
            }
            $res[count($res) - 1]['work'] = $res2;
        }
        return sdk::toApi('200', 'ok', $res);
    }

    public function addWork($args)
    {
        $q = "SELECT * FROM  `works` WHERE `text` = '".$args['name']."'";
        if(!$q = _mysql::query($q))
            return sdk::toApi('101',"invalid query1",_mysql::getLastError());
        if(_mysql::count($q)>0)
            return sdk::toApi('500','already exists');
        $q = "INSERT INTO `works` (`item_id`, `text`,`type`,`min`,`retail`) VALUES ('".$args['item_id']."','".$args['name']."','".$args['type']."','".$args['min']."','".$args['retail']."')";
        $r = false;
        if(!$r = _mysql::query($q))
            return sdk::toApi('101',"invalid query2",array(_mysql::getLastError()),$q);
        return sdk::toApi('200','Ok');
    }
}

?>