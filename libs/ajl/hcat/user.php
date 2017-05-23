<?php

/**
 * Created by PhpStorm.
 * User: alexlake
 * Date: 06/05/2017
 * Time: 22:27
 */

require_once 'package.php';

class user
{
    public $uid;
    public $username;
    public $email;
    public $name;
    public $pwdHash;
    private $inDB;
    private $dbh;

    function __construct($dbh, $uid=0) {
        $this->dbh = $dbh;
        $this->inDB=false;
        if ($uid) {
            $this->uid = $uid;
            // Let's try to load it in
            $this->loadFromDB();
        }
    }

    public function setPassword($newPassword) {
        $this->pwdHash = password_hash ( $newPassword, PASSWORD_DEFAULT );

    }


    public function loadFromDB() {
        $stmt = $this->dbh->prepare("select * from hcat.user where uid=:uid");
        $stmt->bindValue(':uid', $this->uid, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            return false;
        }

        $this->loadFromAssocArray($rows[0]);
        $this->inDB = true;
        return true;

    }

    public function loadFromAssocArray($a) {
        $this->uid = $a['uid'];
        $this->username = valOr($a,'username','?');
        $this->email = valOr($a,'email','?');

    }

    public function saveToDB() {
        if (!$this->inDB) {
            $sql = "insert into hcat.user (email) values (:email);";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $r = $stmt->execute();
            if ($r==0) {

            }

            $stmt = $this->dbh->prepare("SELECT LAST_INSERT_ID() as id;");
            $r = $stmt->execute();
            if ($r) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->uid = $rows[0]["id"];
            }

        }
        $stmt = $this->dbh->prepare("update hcat.user set email=:email, name=:name, pwdhash=:pwdhash where uid=:uid");
        $stmt->bindValue(':uid', $this->uid, PDO::PARAM_INT);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindValue(':pwdhash', $this->pwdHash, PDO::PARAM_STR);

        $r = $stmt->execute();
        if ($r) {
            $this->inDB = true;
            return true;
        } else {
            $r2 = $stmt->errorInfo (  );
            $_SESSION['message']="Unable to create account for {$this->email}.";
            $this->debug(1,'result details:'.$r2);
        }


    }
}

