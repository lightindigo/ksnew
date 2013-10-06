<?php
header('Content-Type: text/plane; charset=UTF-8');
ini_set("display_errors", 1);
error_reporting(-1);
define('_CDIR_',dirname(__FILE__).'/system');
define('_GDIR_',dirname(__FILE__));
define('_API_',true);
if(!isset($_GET['act'])) die('access denied');
require_once dirname(__FILE__).'/system/sdk.php';
if(!sdk::getConfig('sys')) die("Access Denied!");
if(!sdk::import('class','mysql')) die("Access Denied!");
if(!sdk::import('class','template')) die("Access Denied!");
if(!sdk::import('class','crtltemplate')) die("Access Denied!");
$c = _mysql::init(sdk::$conf['sys']['MYSQL_SERVER'],sdk::$conf['sys']['MYSQL_USERNAME'],sdk::$conf['sys']['MYSQL_PASSWORD'],sdk::$conf['sys']['MYSQL_DATABASE']);
if(!$c){die(_mysql::getLastError());}
_mysql::query("SET NAMES '".sdk::$conf['sys']['CHARSET']."';");
if(!sdk::import('controller','ks')) die("Access Denied!");
$api = new ctrl_ks('ks');
$display = array();
switch($_GET['act'])
{
    case 'price_item': $display = $api->price_item(); break;
    case 'ks_data': $display = $api->ks_data(); break;
    case 'ks_list': $display = $api->ks_list(); break;
    case 'save_ks': $display = $api->save_ks(); break;
    case 'catalog_price_delete': $display = $api->catalog_price_delete(); break;
    case 'catalog_price_update': $display = $api->catalog_price_update(); break;
    case 'catalog_price_getdata': $display = $api->catalog_price_getdata(); break;
    case 'catalog_price_add': $display = $api->catalog_price_add(); break;
    case 'catalog_price_list_get': $display = $api->catalog_price_list_get(); break;
    case 'catalog_contractor_add': $display = $api->catalog_contractor_add(); break;
    case 'catalog_contractor_list_get': $display = $api->catalog_contractor_list_get(); break;
    case 'catalog_contractor_get_data': $display = $api->catalog_contractor_get_data(); break;
    case 'catalog_contractor_update': $display = $api->catalog_contractor_update(); break;
    case 'catalog_contractor_delete': $display = $api->catalog_contractor_delete(); break;
    case 'address': $display = $api->address(); break;
    case 'works': $display = $api->works(); break;
    case 'addWork': $display = $api->addWork();break;
    default: $display = sdk::toApi('10', 'Invalid Action'); break;
}
echo json_encode($display);
?>
