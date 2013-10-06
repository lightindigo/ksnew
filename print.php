<?php
header('Content-Type: text/html; charset=UTF-8');
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
    case 'print': $display = $api->ks_print(); break;
    default: $display = 'Invalid Action'; break;
}
echo $display;
?>
