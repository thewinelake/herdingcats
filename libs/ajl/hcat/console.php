<?php

/**
 * Created by PhpStorm.
 * User: alexlake
 * Date: 06/05/2017
 * Time: 22:27
 */
class console extends hcatUI
{

    function render() {
        include 'forms/frmConsole.php';
    }

    public function handleHit() {
        if (valOr($_REQUEST,'cmd','')!='') {
            // This is an AJAX hit!
            $this->handleAJAXHit();
        } else {
            // But I don't currently have any other methods!
        }

    }

    public function handleAJAXHit() {
        $cmd = $_REQUEST['cmd'];
        switch ($cmd) {
            case 'CreateEvent':
                $evt = new event($this->hcatServer);
                $evt->allocateNewEventID();
                $evt->hostUid = $_SESSION['uid'];
                $evt->title = $_REQUEST['title'];
                $evt->description = $_REQUEST['description'];
                $evt->date = $_REQUEST['date'];
                $evt->status = 'Planning';

                $evt->saveToDB();
                $result = $evt;
                break;
            case "GetEvents":
                $events = [];
                $myevents = [];
                $sql="select e.*,m.msgtext,m.uid from hcat.event as e,hcat.message as m";
                $sql.=" where e.owneruid=:myuid and e.descriptionmid=m.mid and e.eid is not null";
                $sql.=" and e.status not in ('deleted')";
                $sql.=" order by e.eid";

                // @var $stmt PDOStatement
                $stmt = $this->hcatServer->dbh->prepare($sql);
                $uid = $this->hcatServer->user->uid;
                $stmt->bindValue(':myuid', $uid, PDO::PARAM_INT);
                $r = $this->dbExecute($stmt);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    /** @var $event event */
                    $event = new event($this->hcatServer);
                    $event->loadFromAssocArray($row);
                    $eid = $event->eid;
                    $myevents[$eid] = $event;
                }
                $events['myEventsList'] = $myevents;
                $result = $events;
                break;
            case "SessInfo":
                $result = array('session'=>$_SESSION);
                $result['user']  = $this->hcatServer->user;
                break;
            case "Ping":
                $result = array('Cmd'=>'Pong');
                break;
            default:
                $result = array();
        }
        echo json_encode($result);
    }
}