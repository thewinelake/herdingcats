<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alexlake
 * Date: 02/06/17
 * Time: 07:43
 * To change this template use File | Settings | File Templates.
 */

class inspector extends hcatUI {

    public $eid;
    public $uid;
    public $mid;

    public $user;
    public $event;
    public $message;


    public $mode; // what am I inspecting? event user message

    public function inspector($hcatServer) {

        parent::__construct($hcatServer);

        if (valOr($_REQUEST,'e')) {
            $eid = $_REQUEST['e'];
            $this->event = new event($hcatServer,$eid);
            if ($this->event->eid==0) unset ($this->event);
        }


    }

    public function handleHit() {

        $this->render();
    }

    function render() {
        include 'forms/frmInspector.php';
    }
}