<?php

/**
 * Created by PhpStorm.
 * User: alexlake
 * Date: 06/05/2017
 * Time: 22:28
 */
require_once 'package.php';

class hcatUI
{
    /** @var $hcatServer hcatServer **/
    public $hcatServer;

    function __construct($hcatServer) {
        $this->hcatServer = $hcatServer;
    }

    function render() {
        // This needs to be overridden!
    }

    public function debug($level,$msg) {
        $this->hcatServer->debug($level,$msg);
    }

    public function dbExecute($stmt) {
        return $this->hcatServer->dbExecute($stmt);
    }
}