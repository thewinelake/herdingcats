<?php

/**
 * Created by PhpStorm.
 * User: alexlake
 * Date: 06/05/2017
 * Time: 22:27
 */
class event extends hcatUI
{

    public $id;
    public $evtKey;
    public $owneruid;
    public $title;
    public $description;
    public $descriptionmid;
    public $date;
    public $guestList; // array
    public $comments; // array

    public function event($hcatServer) {
        $this->guestList = [];
        $this->comments = [];
        parent::__construct($hcatServer);
    }



    public function handleHit() {
        // This could be expecting an html page in response, or it might be an AJAX command expecting a json


        // are we allowed to do anything with this event?
        // - yes if logged in and owner or on the guest list
        // - yes if looking with an invitation key

        $auth = '';
        if (!$this->hitAllowedAccess($auth)) {
            return false;
        }

        if (valOr($_REQUEST,'cmd','')!='') {
            // This is an AJAX hit!
            $this->handleAJAXHit();
        } else {

            $mode = valOr($_POST,'action'); // view edit save cancel invite
            switch ($mode) {
            case 'save':
                $this->loadFromForm();
                $this->saveToDB();
                $this->renderViewEdit('view');
                break;
            case 'edit':
                $this->renderViewEdit('edit');
                break;
            case 'invite':
                // note that this can't also save details?
                // a bit inconsistent in terms of who does what
                $this->inviteUser();
                $this->renderViewEdit('view');
                break;
            case 'cancel':
            case 'view':
            default:
                $this->renderViewEdit('view');
            }
        }
        return true;

    }

    public function handleAJAXHit() {
        $cmd = $_REQUEST['cmd'];
        switch ($cmd) {
            case 'GetEventJSON':
                $result = $this;
                $result->user = $this->hcatServer->user;
                unset ($result->hcatServer);
                break;
            case 'UpdateEvent':
                $this->ajax_UpdateEvent($result);
                $result = ['result'=>'OK'];
                break;
            case 'AddComment':
                $this->ajax_AddComment($result);
                break;
            default:
                $result = array();
        }
        echo json_encode($result);
    }

    public function handleEmail($inbox,$email_number) {
        $overview = imap_fetch_overview($inbox,$email_number,0);
        $body = imap_fetchbody($inbox,$email_number,'1');
        $subject = $overview[0]->subject;
        $this->debug(1,"Event handling email with subject $subject");
        $this->debug(1,"Message reads $body");

    }

    public function loadFromDB() {
        $this->debug(1,"Loading event {$this->id} from DB");
        $stmt = $this->hcatServer->dbh->prepare("select e.*,m.msgtext from hcat.event as e, hcat.message as m where e.eid=:eid and m.mid=e.descriptionmid");
        $stmt->bindValue(':eid', $this->id, PDO::PARAM_INT);
        $this->dbExecute($stmt);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            return false;
        }

        $this->loadFromAssocArray($rows[0]);

        $this->guestList = array();
        $stmt = $this->hcatServer->dbh->prepare("select * from hcat.invitation where eid=:eid");
        $stmt->bindValue(':eid', $this->id, PDO::PARAM_INT);
        $this->dbExecute($stmt);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->debug(1,sizeof($rows)." guests");
        foreach ($rows as $row) {
            $guestDetails = [
                'name' => $row['name'],
                'address' => $row['address']
            ];
            array_push($this->guestList,$guestDetails);
        }


        $this->comments = array();
        // This gets all the messages
        $stmt = $this->hcatServer->dbh->prepare("select * from hcat.message as m, hcat.user as u where m.eid=:eid and u.uid=m.eid and m.mid<>:descriptionmid");
        $stmt->bindValue(':eid', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':descriptionmid', $this->descriptionmid, PDO::PARAM_INT);

        $this->dbExecute($stmt);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $comment = new stdClass();
            $comment->commentText = $row['msgtext'];
            $comment->commentAuthor = $row['email'];
            $comment->commentGMT = $row['gmt'];

            array_push($this->comments,$comment);
        }

        return true;

    }

    public function loadFromForm() {
        $this->title = $_POST['title'];
        $this->description = $_POST['description'];
        $this->date = $_POST['date'];

    }

    public function loadFromAssocArray($a) {
        $this->id = $a['eid'];
        $this->evtKey = valOr($a,'evtKey','');
        $this->owneruid = $a['owneruid'];
        $this->title = $a['title'];
        $this->description = $a['msgtext'];
        $this->date = $a['date'];
        $this->descriptionmid = $a['descriptionmid'];

    }

    public function hitAllowedAccess(&$auth) {

        // If you're the owner
        if ($this->owneruid == $this->hcatServer->user->uid) {
            $auth = 'owner';
            return true;
        }

        // If you're logged in as a guest
        foreach($this->guestList as $guestDetails) {
            if ($this->hcatServer->user->email==$guestDetails['address']) {
                $auth = 'guest';
                return true;
            }
        }

        // If you passed an invitation key in the URL
        $ikey = valOr($_REQUEST,'k',valOr($_SESSION,'ikey'));
        if ($ikey) {
            $sql = "select * from hcat.invitation where ikey=:ikey and eid=:eid";
            $stmt = $this->hcatServer->dbh->prepare($sql);
            $stmt->bindValue(':eid', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':ikey', $ikey);

            $this->dbExecute($stmt);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) {
                // This could count as a weak form of login
                $auth = 'ikey';
                $_SESSION['ikey']=$ikey;
                $_SESSION['ikey_address']=$rows[0]['ikey'];

                return true;
            }
        }
        return false;
    }

    public function allocateNewEventID() {
        // First get a message for description
        $d = date('Y-m-d H:i:s');
        $description = 'Event created $d';
        $stmt = $this->hcatServer->dbh->prepare("insert into hcat.message (msgtext) values (:description)");
        $stmt->bindValue(':description', $this->description, PDO::PARAM_STR);
        $r = $this->dbExecute($stmt);
        $stmt = $this->hcatServer->dbh->prepare("SELECT LAST_INSERT_ID();");
        $r = $this->dbExecute($stmt);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->descriptionmid = $rows[0]["LAST_INSERT_ID()"];


        $stmt = $this->hcatServer->dbh->prepare("insert into hcat.event (title,descriptionmid) values (:title,:descriptionmid)");
        $stmt->bindValue(':descriptionmid', $this->descriptionmid, PDO::PARAM_INT);
        $stmt->bindValue(':title', $this->title, PDO::PARAM_INT);
        $r = $this->dbExecute($stmt);

        $stmt = $this->hcatServer->dbh->prepare("SELECT LAST_INSERT_ID();");
        $r = $this->dbExecute($stmt);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $newEID = $rows[0]["LAST_INSERT_ID()"];
        $this->id = $newEID;
    }

    public function saveToDB() {
        $stmt = $this->hcatServer->dbh->prepare("update hcat.event set owneruid=:owneruid, title=:title, date=:date where eid=:eid");
        $stmt->bindValue(':eid', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':owneruid', $this->owneruid, PDO::PARAM_INT);
        $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
        $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);

        $r = $this->dbExecute($stmt);
        $r2 = $stmt->errorInfo (  );


        $stmt = $this->hcatServer->dbh->prepare("update hcat.message set msgtext=:description where mid=:descriptionmid");
        $stmt->bindValue(':descriptionmid', $this->descriptionmid, PDO::PARAM_INT);
        $stmt->bindValue(':description', $this->description, PDO::PARAM_INT);

        $r = $this->dbExecute($stmt);
        $r2 = $stmt->errorInfo (  );


        foreach($this->guestList as $guestDetails) {
            // Have to find the uid
            $stmt = $this->hcatServer->dbh->prepare("select * from hcat.invitation as i where i.eid=:eid and i.address=:email");
            $stmt->bindValue(':eid', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':email', $guestDetails['address'], PDO::PARAM_STR);
            $this->dbExecute($stmt);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ikey = sprintf('%d',rand (100000,999999)).sprintf('%d',rand (100000,999999));
            if (sizeof($rows)==1) {
                // Already in the db
                $sql = "update hcat.invitation set name=:name, crtgmt=:crtgmt, status=:status, statusgmt=:statusgmt where eid=:eid and address=:email;";
            } else {
                // this is a new one
                $sql = "insert into hcat.invitation (eid,address,ikey,name,crtgmt,status,statusgmt) values (:eid,:email,:ikey,:name,:crtgmt,:status,:statusgmt);";
            }
            $stmt = $this->hcatServer->dbh->prepare($sql);
            $now = date('Y-m-d H:i:s');
            $stmt->bindValue(':eid', $this->id, PDO::PARAM_INT);
            $stmt->bindValue(':email', $guestDetails['address'], PDO::PARAM_STR);
            $stmt->bindValue(':ikey', $ikey, PDO::PARAM_STR);
            $stmt->bindValue(':name', $guestDetails['name'], PDO::PARAM_STR);
            $stmt->bindValue(':status', 'new', PDO::PARAM_STR);
            $stmt->bindValue(':crtgmt', $now, PDO::PARAM_STR);
            $stmt->bindValue(':statusgmt', $now, PDO::PARAM_STR);

            $r = $this->dbExecute($stmt);
            $r2 = $stmt->errorInfo (  );
        }

    }

    public function validateKey($key) {
        return ($this->evtKey == $key);
    }

    public function renderForConsole() {
        $html = '<p>';
        $html .= "<a href=\"e_$this->id\">".$this->title.'</a>';
        $html .= '</p>';
        return $html;
    }

    public function renderViewEdit($mode) {
        $event = $this;
        $eventJson = json_encode($event);
        include "forms/frmEventView.php";
    }

    public function inviteUser() {
        // the user is specified in form data
    }

    public function ajax_UpdateEvent(&$result) {

        $guests = $_REQUEST['guests'];
        foreach($guests as $guestIdx=>$guestDetails) {
            $this->guestList[$guestIdx] = $guestDetails;
        }
        $this->saveToDB();

        $result = array('cmd'=>'UpdateEventAck');
    }

    public function ajax_AddComment(&$result) {

        $jcomment = $_REQUEST['comment'];
        $comment = json_decode($jcomment);

        $sql="insert into hcat.message (uid,eid,gmt,msgtext) values (:uid,:eid,:gmt,:msgtext);";
        $stmt = $this->hcatServer->dbh->prepare($sql);
        $stmt->bindValue(':uid', $this->hcatServer->user->uid, PDO::PARAM_INT);
        $stmt->bindValue(':eid', $this->id, PDO::PARAM_INT);
        $stmt->bindValue(':gmt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':msgtext', $comment->commentText, PDO::PARAM_STR);

        $r = $this->dbExecute($stmt);
        $r2 = $stmt->errorInfo (  );
        if ($r) {
            $result = array('cmd'=>'AddCommentAck');
        } else {
            $result = array('cmd'=>'AddCommentNak');
        }
    }

}
