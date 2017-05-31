<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alexlake
 * Date: 30/05/17
 * Time: 21:27
 * To change this template use File | Settings | File Templates.
 */

class message {
    public $mid;
    public $uid;
    public $parentMid;

    public $txtMessage;
    public $htmlMessage;
    private $hcatServer;
    public $dbh;

    public function __construct($hcatServer) {
        $this->hcatServer = $hcatServer;
        $this->dbh = $GLOBALS['dbh'];
    }

    public function saveToDB() {
        if ($this->mid) {
            $sql = 'update hcat.message ';
            $sql .= 'set eid=:eid, uid=:uid, gmt=:gmt, parentmid=:parentmid, msgtext=:msgtext, msghtml=:msghtml) ';
            $sql .= 'where mid=:mid ';

        } else {
            $sql = 'insert into hcat.message (eid,uid,gmt,parentmid,msgtext,msghtml) ';
            $sql .= 'values (:eid, :uid, :gmt, :parentmid, :msgtext, :msghtml)';

        }
        $now = date('Y=m-d H:i:s');
        $stmt = $this->dbh->prepare($sql);
        if ($this->mid) $stmt->bindValue(':mid', $this->mid, PDO::PARAM_INT);
        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $this->uid, PDO::PARAM_INT);
        $stmt->bindValue(':parentmid', $this->parentMid, PDO::PARAM_INT);
        $stmt->bindValue(':gmt', $now, PDO::PARAM_STR);
        $stmt->bindValue(':msgtext', $this->txtMessage, PDO::PARAM_STR);
        //$stmt->bindValue(':msghtml', $this->htmlMessage, PDO::PARAM_STR);
        $stmt->bindValue(':msghtml', '', PDO::PARAM_STR);


        $r = $this->hcatServer->dbExecute($stmt);

        if (!$this->mid) $this->mid = $this->hcatServer->dbLastInsertID();


    }

}