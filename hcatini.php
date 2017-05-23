<?php
/**
 * Created by PhpStorm.
 * User: AJL
 * Date: 10-Jun-2011
 * Time: 10:01:56
 * To change this template use File | Settings | File Templates.
 */

$GLOBALS['hcatConfig'] = array (

    'GenConfig' => array (
        'svn_revision'      => '!svn-revision!',
        'deploy_gmt'   => '!deploy-gmt!',
        'release_version'   => '2012-04-30 16:11'
    ),


    'Db' => array(
         'hcat'    => array(
             'db_dsn' => "mysql:dbname=hcat;host=localhost",
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
