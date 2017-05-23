<?php
/**
 * Created by PhpStorm.
 * User: AJL
 * Date: 08-Jun-2011
 * Time: 13:53:06
 * To change this template use File | Settings | File Templates.
 */

set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT']);


// Compact privacy policy for IE. Allows IE to accept 'third-party' cookies (like ours)
// when eComponents is running in an iframe from a different domain
//
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

error_reporting(E_ALL );

ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');

require_once 'libs/ajl/hcat/package.php'; // This defines all the processes and includes all dependencies

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));

ini_set('short_open_tag', 'On');

if (isset($_REQUEST['q']) && substr($_REQUEST['q'], -11) == 'favicon.ico') exit;

include '/etc/hcat/hcatini.php';

date_default_timezone_set('UTC');
$cacheDefeat    = '?time='.date('His');
//$cacheDefeat    = '';

function valOr($arr,$key,$default='') {
    if (isset($arr[$key])) return $arr[$key];
    return $default;
}

