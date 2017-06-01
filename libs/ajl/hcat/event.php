<?php

/**
 * Created by PhpStorm.
 * User: alexlake
 * Date: 06/05/2017
 * Time: 22:27
 */
class event extends hcatUI
{

    public $eid;
    public $evtKey;
    public $hostUid;
    public $host;
    public $title;
    public $description;
    public $descriptionMid;
    public $date;
    public $guestList; // array
    public $comments; // array

    public function event($hcatServer,$eid=0) {
        $this->host = new stdClass();
        $this->guestList = [];
        $this->comments = [];
        parent::__construct($hcatServer);
        if ($eid) {
            $this->eid = $eid;
            if (!$this->loadFromDB()) $this->eid = 0;
        }

    }

    public function myEmailAddress($guestKey=0) {

        $stmt = $this->hcatServer->dbh->prepare("select * from hcat.user where uid=:owneruid");
        $stmt->bindValue(':owneruid', $this->hostUid, PDO::PARAM_INT);
        $this->dbExecute($stmt);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = valOr($row,'name','Anonymous');

        $emailAddr = "e_{$this->eid}";
        if ($guestKey) {
            $emailAddr = "_{$guestKey}";
        }
        $emailAddr .="@herdingcats.club";
        $emailName = "$name's cat herder for {$this->title}";

        return array($emailAddr=>$emailName);
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
        $this->debug(1,"AJAX Cmd $cmd");

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
            case 'AddGuest':
                $this->ajax_AddGuest($result);
                break;
            case 'RemoveGuest':
                $this->ajax_RemoveGuest($result);
                break;
            case 'AddComment':
                $this->ajax_AddComment($result);
                break;
            case 'DeleteEvent':
                $this->ajax_SetEventStatus('Deleted',$result);
                break;
            case 'CancelEvent':
                $this->ajax_SetEventStatus('Cancelled',$result);
                break;
            case 'BroadcastComment':
                $this->ajax_BroadcastComment($result);
                break;
            case 'DeleteComment':
                $this->ajax_DeleteComment($result);
                break;
            default:
                $this->debug(1,"Unknown AJAX Cmd $cmd");
                $result = array();
        }
        echo json_encode($result);
    }



    public function loadFromDB() {
        $this->debug(1,"Loading event {$this->eid} from DB");
        $stmt = $this->hcatServer->dbh->prepare("select e.*,m.msgtext from hcat.event as e, hcat.message as m where e.eid=:eid and m.mid=e.descriptionmid");
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $this->dbExecute($stmt);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return false;

        $this->loadFromAssocArray($rows[0]);

        // Get the guest list
        //
        $this->guestList = array();
        $stmt = $this->hcatServer->dbh->prepare("select *,i.status as invstatus from hcat.invitation as i, hcat.user as u where i.eid=:eid and i.uid=u.uid");
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $this->dbExecute($stmt);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->debug(1,sizeof($rows)." guests");
        foreach ($rows as $row) {
            $guestDetails = [
                'uid' => $row['uid'],
                'name' => $row['name'],
                'email' => $row['email'],
                'status' => $row['invstatus']

            ];
            array_push($this->guestList,$guestDetails);
        }


        // This gets all the comments
        //
        $this->comments = array();
        $stmt = $this->hcatServer->dbh->prepare("select * from hcat.message as m, hcat.user as u where m.eid=:eid and u.uid=m.uid and m.mid<>:descriptionmid");
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':descriptionmid', $this->descriptionMid, PDO::PARAM_INT);

        $this->dbExecute($stmt);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $comment = new stdClass();
            $comment->commentMid = $row['mid'];
            $comment->commentUid = $row['uid'];
            $comment->commentText = $this->renderCommentCompactAsHtml($row['msgtext']);
            $comment->commentHtml = $this->renderCommentCompactAsHtml($row['msgtext']);
            $comment->commentName = $row['name'];
            $comment->commentEmail = $row['email'];
            $comment->commentGMT = $row['gmt'];

            array_push($this->comments,$comment);
        }

        $this->host = new user($this->hostUid);

        return true;

    }

    public function loadFromForm() {
        $this->title = $_POST['title'];
        $this->description = $_POST['description'];
        $this->date = $_POST['date'];

    }

    public function loadFromAssocArray($a) {
        $this->eid = $a['eid'];
        $this->status = valOr($a,'status','');
        $this->evtKey = valOr($a,'evtKey','');
        $this->hostUid = $a['owneruid'];
        $this->title = $a['title'];
        $this->description = $a['msgtext'];
        $this->date = $a['date'];
        $this->descriptionMid = $a['descriptionmid'];

    }

    public function handleEmail() {

    }

    public function hitAllowedAccess(&$auth) {

        // If you're the owner
        if ($this->hostUid == $this->hcatServer->user->uid) {
            $auth = 'owner';
            return true;
        }

        // If you're logged in as a guest
        foreach($this->guestList as $guestDetails) {
            if ($this->hcatServer->user->email==$guestDetails['email']) {
                $auth = 'guest';
                return true;
            }
        }

        // If you passed an invitation key in the URL
        $ikey = valOr($_REQUEST,'k',valOr($_SESSION,'ikey'));
        if ($ikey) {
            $sql = "select * from hcat.invitation where ikey=:ikey and eid=:eid";
            $stmt = $this->hcatServer->dbh->prepare($sql);
            $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
            $stmt->bindValue(':ikey', $ikey);

            $this->dbExecute($stmt);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($rows) {
                // This could count as a weak form of login
                $auth = 'ikey';
                $_SESSION['ikey']=$ikey;
                $_SESSION['ikey_email']=$rows[0]['ikey'];

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
        $this->descriptionMid = $rows[0]["LAST_INSERT_ID()"];


        $stmt = $this->hcatServer->dbh->prepare("insert into hcat.event (title,descriptionmid) values (:title,:descriptionmid)");
        $stmt->bindValue(':descriptionmid', $this->descriptionMid, PDO::PARAM_INT);
        $stmt->bindValue(':title', $this->title, PDO::PARAM_INT);
        $r = $this->dbExecute($stmt);

        $this->eid = $this->hcatServer->dbLastInsertID();
    }


    public function saveToDB() {
        // core params
        $stmt = $this->hcatServer->dbh->prepare("update hcat.event set owneruid=:owneruid, title=:title, date=:date, status=:status where eid=:eid");
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':owneruid', $this->hostUid, PDO::PARAM_INT);
        $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
        $stmt->bindValue(':date', $this->date, PDO::PARAM_STR);
        $stmt->bindValue(':status', $this->status, PDO::PARAM_STR);

        $r = $this->dbExecute($stmt);
        $r2 = $stmt->errorInfo (  );

        // description is held in a message
        $stmt = $this->hcatServer->dbh->prepare("update hcat.message set msgtext=:description where mid=:descriptionmid");
        $stmt->bindValue(':descriptionmid', $this->descriptionMid, PDO::PARAM_INT);
        $stmt->bindValue(':description', $this->description, PDO::PARAM_INT);

        $r = $this->dbExecute($stmt);
        $r2 = $stmt->errorInfo (  );


        foreach($this->guestList as $guestDetails) {

            // Have to find the user
            $guestEmail = $guestDetails['email'];
            $guest = new user();
            if (!$guest->loadByEmailAddr($guestEmail)) {
                // the guest doesn't already exist - we should create it
                $this->debug(1,"Creating new user with email address $guestEmail");
                $guest->email = $guestEmail;
                $guest->saveToDB();
            }

            // is this user invited?
            // Maybe this should be more OO
            $stmt = $this->hcatServer->dbh->prepare("select * from hcat.invitation as i where i.eid=:eid and i.uid=:uid");
            $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
            $stmt->bindValue(':uid', $guest->uid, PDO::PARAM_STR);
            $this->dbExecute($stmt);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $ikey = sprintf('%d',rand (100000,999999)).sprintf('%d',rand (100000,999999));
            $insert = sizeof($rows)==0;
            if ($insert) {
                // this is a new one
                $sql = "insert into hcat.invitation (eid,uid,ikey,name,crtgmt,status,statusgmt) values (:eid,:uid,:ikey,:name,:crtgmt,:status,:statusgmt);";
            } else {
                // Already in the db
                $sql = "update hcat.invitation set name=:name, crtgmt=:crtgmt, status=:status, statusgmt=:statusgmt where eid=:eid and uid=:uid;";
            }
            $stmt = $this->hcatServer->dbh->prepare($sql);
            $now = date('Y-m-d H:i:s');
            $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
            $stmt->bindValue(':uid', $guest->uid, PDO::PARAM_STR);
            if ($insert) $stmt->bindValue(':ikey', $ikey, PDO::PARAM_STR);
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
        $html .= "<a href=\"e_$this->eid\">".$this->title.'</a>';
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



    // AJAX COMMANDS





    public function ajax_UpdateEvent(&$result) {

        $guests = $_REQUEST['guests'];
        foreach($guests as $guestIdx=>$guestDetails) {
            $this->guestList[$guestIdx] = $guestDetails;
        }
        $this->saveToDB();

        $result = array('cmd'=>'UpdateEventAck');
    }

    public function ajax_AddGuest(&$result) {
        $guestDetails = json_decode($_REQUEST['guestdetails']);


        // Have to try to find the user
        $guestEmail = $guestDetails->email;
        $guest = new user();
        if (!$guest->loadByEmailAddr($guestEmail)) {
            // the guest doesn't already exist - we should create it
            $guest->email = trim($guestEmail);
            $guest->name = trim($guestDetails->name);
            $this->debug(1,"Creating new user with email address $guestEmail");
            $guest->saveToDB();
        }

        // is this user invited?
        $stmt = $this->hcatServer->dbh->prepare("select * from hcat.invitation as i where i.eid=:eid and i.uid=:uid");
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $guest->uid, PDO::PARAM_STR);
        $this->dbExecute($stmt);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ikey = sprintf('%d',rand (100000,999999)).sprintf('%d',rand (100000,999999));
        if (sizeof($rows)==1) {
            // Already in the db
            $sql = "update hcat.invitation set name=:name, ikey=:ikey, crtgmt=:crtgmt, status=:status, statusgmt=:statusgmt where eid=:eid and uid=:uid;";
        } else {
            // this is a new one
            $sql = "insert into hcat.invitation (eid,uid,ikey,name,crtgmt,status,statusgmt) values (:eid,:uid,:ikey,:name,:crtgmt,:status,:statusgmt);";
        }
        $stmt = $this->hcatServer->dbh->prepare($sql);
        $now = date('Y-m-d H:i:s');
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $guest->uid, PDO::PARAM_STR);
        $stmt->bindValue(':ikey', $ikey, PDO::PARAM_STR);
        $stmt->bindValue(':name', $guestDetails->name, PDO::PARAM_STR);
        $stmt->bindValue(':status', 'new', PDO::PARAM_STR);
        $stmt->bindValue(':crtgmt', $now, PDO::PARAM_STR);
        $stmt->bindValue(':statusgmt', $now, PDO::PARAM_STR);

        $r = $this->dbExecute($stmt);

        if ($r) {
            $result = array('cmd'=>'AddGuestAck');
        } else {
            $result = array('cmd'=>'AddGuestNak');
        }
    }

    public function ajax_RemoveGuest(&$result) {
        $uid = $_REQUEST['guestuid'];
        $sql = "update hcat.invitation set status=:status, statusgmt=:gmt where eid=:eid and uid=:uid";
        $stmt = $this->hcatServer->dbh->prepare($sql);
        $now = date('Y-m-d H:i:s');
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
        $stmt->bindValue(':gmt', $now, PDO::PARAM_STR);
        $stmt->bindValue(':status', 'deleted', PDO::PARAM_STR);

        $r = $this->dbExecute($stmt);
        if ($r) {
            $result = array('cmd'=>'RemoveGuestAck');
        } else {
            $result = array('cmd'=>'RemoveGuestNak');
        }
    }

    public function ajax_AddComment(&$result) {

        $jcomment = $_REQUEST['comment'];
        $comment = json_decode($jcomment);

        $this->debug(1,"Adding comment [$comment->commentText]");

        $sql="insert into hcat.message (uid,eid,gmt,msgtext) values (:uid,:eid,:gmt,:msgtext);";
        $stmt = $this->hcatServer->dbh->prepare($sql);
        $stmt->bindValue(':uid', $this->hcatServer->user->uid, PDO::PARAM_INT);
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
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

    public function ajax_SetEventStatus($newStatus,&$result) {
            $sql="update hcat.event set status=:newstatus,statusgmt=:gmt where eid=:eid;";
            $stmt = $this->hcatServer->dbh->prepare($sql);
            $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
            $stmt->bindValue(':gmt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->bindValue(':newstatus', $newStatus, PDO::PARAM_STR);

            $r = $this->dbExecute($stmt);
            $r2 = $stmt->errorInfo (  );
            if ($r) {
                $result = array('cmd'=>'SetEventStatusAck');
            } else {
                $result = array('cmd'=>'SetEventStatusNak');
            }
    }

    public function ajax_BroadcastComment(&$result) {

        $commentMID = $_REQUEST['commentmid'];

        $this->debug(2,"BroadcastComment $commentMID");

        $stmt = $this->hcatServer->dbh->prepare("select * from hcat.message as m, hcat.user as u where m.eid=:eid and u.uid=m.uid and m.mid=:mid");
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':mid', $commentMID, PDO::PARAM_INT);

        $this->dbExecute($stmt);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        $sql = "SELECT * FROM hcat.invitation as i left join hcat.user as u on u.uid=i.uid";
        $sql .= " where i.eid=:eid and i.status not in ('UNSUBSCRIBED','DECLINED','DELETED')";
        $stmt = $this->hcatServer->dbh->prepare($sql);
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);

        $this->dbExecute($stmt);
        while ($guestrow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $guestEmail = $guestrow['email'];

            $guestUid = $guestrow['uid'];
            if ($guestUid) {
                $guest = new user($guestUid);
                $guestKey = $guestrow['ikey'];
            } else {
                $guest = new stdClass();
                $guestKey = '';
            }


            $mergedata = new stdClass();
            $mergedata->comment = new stdClass();
            $mergedata->comment->Text = $row['msgtext'];
            $mergedata->comment->Author = $row['email'];
            $mergedata->comment->GMT = $row['gmt'];
            $mergedata->event = $this;
            $mergedata->user = $guest;
            $mergedata->mid = $commentMID;

            $email = new Email();
            $email->fromAddress = $this->myEmailAddress($guestKey);
            $email->destAddresses = array($guestEmail);
            $email->mergeTemplate('e1',$mergedata);
            $email->send();

        }
    }

    public function ajax_DeleteComment(&$result) {

        $commentMID = $_REQUEST['commentmid'];

        $this->debug(2,"DeleteComment $commentMID");

        $stmt = $this->hcatServer->dbh->prepare("delete from hcat.message where eid=:eid and mid=:mid");
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':mid', $commentMID, PDO::PARAM_INT);

        $this->dbExecute($stmt);

        $result = array('refresh'=>'1');
    }

    public function GuestURL($guestUid=0) {

        $baseURL = $GLOBALS['HcatConfig']['GenConfig']['BaseURL'];
        $baseURL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_ADDR'].'/';

        $url = $baseURL.'e_'.$this->eid;

        if ($guestUid) {
            $sql = "select * from hcat.invitation where uid=:uid and eid=:eid";
            $stmt = $this->hcatServer->dbh->prepare($sql);
            $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
            $stmt->bindValue(':uid', $guestUid, PDO::PARAM_INT);

            $this->dbExecute($stmt);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $invitationKey = $row['ikey'];
            $url .= '?k='.$invitationKey;
        }
        return $url;
    }

    public function GuestUnsubscribeURL($guestUid,$eid=0) {
        $url = $this->GuestURL($guestUid);
        if ($eid=='ALL') {
            $url = AddURLParam($url,"action=unsubscribe_all");
        } elseif ($eid>0) {
            $url = AddURLParam($url,"action=unsubscribe");
        } else {
            $url =  ''; // or maybe something else?
        }
        return $url;
    }

    private function renderCommentCompactAsHtml($msgText) {
        $msgHtml = '';
        $lines = explode("\r\n",$msgText);
        foreach ($lines as $line) {
            if (substr(trim($line),0,1)!='>') {
                if (($line!='') || (substr($msgHtml,-5)!='<br/>')) {
                    $msgHtml.=htmlentities($line).'<br/>';
                }
            }
        }
        return $msgHtml;
    }

    public function makeUserCSS() {
        $colIdx = -1;
        $BGColPalette = array('white::#666','#f66','#c60','#0f0','pink','#00f','#88f');
        $css = "<style>\n";
        array_unshift($this->guestList,array('uid'=>$this->hostUid));

        foreach ($this->guestList as $guest) {
            $guid = $guest['uid'];
            if ($guid!=$this->hostUid || $colIdx==-1) {
                if ((++$colIdx) >= sizeof($BGColPalette)) $colIdx = 1;
                $guestInfoCols = explode(':',$BGColPalette[$colIdx].'::');
                $guestBGCol = $guestInfoCols[0];
                $guestInfoTextCol = valOr($guestInfoCols,1);
                $guestInfoBGCol = valOr($guestInfoCols,2);

                $css .=".u{$guid},td.u{$guid} { background-color: {$guestBGCol} }\n";
                if ($guestInfoTextCol) {
                    $css .=".u{$guid} .commentInfo { color: {$guestInfoTextCol} }\n";
                }
                if ($guestInfoBGCol) {
                    $css .=".u{$guid} .commentFooter{ background-color: {$guestInfoBGCol} }\n";
                }
            }
        }
        $css .="</style>";
        return $css;
    }


}
