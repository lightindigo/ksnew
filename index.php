<?php
header('Content-Type: text/html; charset=UTF-8');
ini_set("display_errors", 1);
error_reporting(-1);
define('_CDIR_',dirname(__FILE__).'/system');
require_once dirname(__FILE__).'/system/sdk.php';
if(!sdk::getConfig('sys')) die("Access Denied!");
if(!sdk::import('class','template')) die("Access Denied!");
if(!sdk::import('class','crtltemplate')) die("Access Denied!");
if(!sdk::import('class','fdb')) die("Access Denied!");
if(!sdk::import('controller','ks')) die("Access Denied!");
$core = new ctrl_ks('ks');
$display = '';
$act = (!isset($_GET['act'])) ? '':$_GET['act'];
switch($act)
{
    case 'ks_print':
        $display = $core->ks_print();
        break;
    case 'catalog':
        $display = $core->catalog();
        break;
    case 'catalog_contractor':
        $display = $core->catalog_contractor();
        break;
    case 'catalog_price':
        $display = $core->catalog_price();
        break;
    case 'catalog_address':
        $display = $core->catalog_address();
        break;
    case 'catalog_ks':
        $display = $core->catalog_ks();
        break;
    case 'editor_ks_drs':
        if (isset($_GET['cable']))
            echo "<script>
            var edit_id = ".$_GET['cable'].";
            var edit_data;
            </script>";
        $display = $core->edirot_ks_drs();
        break;
    case 'editor_ks_ms':
        if (isset($_GET['cable']))
            echo "<script>
            var edit_id = ".$_GET['cable'].";
            var edit_data;
            ;</script>";
        $display = $core->edirot_ks_ms();
        break;
    case 'edirot_ks':
        $display = $core->edirot_ks();
        break;
    default: $display = $core->index(); break;
}
echo $display;
?>
