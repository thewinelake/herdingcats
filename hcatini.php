<?php
/**
 * Created by PhpStorm.
 * User: AJL
 * Date: 10-Jun-2011
 * Time: 10:01:56
 * To change this template use File | Settings | File Templates.
 */

$GLOBALS['HcatConfig'] = array (

    'GenConfig' => array (
        'DeployGMT'   => '!deploy-gmt!',
        'ReleaseVersion'   => '2012-04-30 16:11',
        'EmailTemplateDir' => '/Users/alexlake/Sites/herdingcats/templates',
        'BaseURL' => 'http://127.0.0.1/',
    ),


    'Db' => array(
         'hcat'    => array(
             'db_dsn' => "mysql:dbname=hcat;host=hcatjun17.herdingcats.com",
             'db_user' =>'garfield',
             'db_password' => 'JimDavis'
         ),
     ),

);


// $http_host = collValOr($_SERVER,'HTTP_HOST','');
// There's a trick that dwfsvr0_xxx.dmclub.net is equivalent to dwfsvr0.xxx.dmclub.net
// $http_host = str_replace('_','.',$http_host);
// $GLOBALS['dwfConfig']['SERVER']['http_host'] = $http_host;
// $GLOBALS['webRoot'] = '/home/default/dwf.dmclub.net/user/htdocs/';
