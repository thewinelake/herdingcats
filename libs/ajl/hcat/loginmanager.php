<?php

/**
 * Created by PhpStorm.
 * User: alexlake
 * Date: 06/05/2017
 * Time: 22:27
 */
class loginmanager extends hcatUI
{
    public function validateLogin() {

        // data must be in $_POST
        $email = $_POST['email'];
        $password = $_POST['pwd'];

        $stmt = $this->hcatServer->dbh->prepare("select * from hcat.user where email=:email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $_SESSION['email']='';
        $_SESSION['uid']=0;

        $message = '';
        if (sizeof($rows)==1) {
            $hash = $rows[0]['pwdhash'];
            if ( password_verify (  $password ,  $hash )) {
                $_SESSION['email']=$email;
                $_SESSION['uid']=$rows[0]['uid'];
                $this->hcatServer->setLogin();

            } else {
                $message = 'Bad password';
            }

        } else {
            $message = 'Bad email';
        }


        $_SESSION['message']=$message;
        return ($_SESSION['email']!='');
    }

    public function createUser() {

        $username = 'alexlake';
        $forename = 'Alex';
        $surname = 'Lake';
        $password = 'indigo';

        $pwdhash = password_hash ( $password, PASSWORD_DEFAULT );

        $sql = "insert into hcat.user (username,crtgmt,status,statusgmt,forename,surname,pwdhash)";
        $sql.= " values ( :username, :crtgmt, :status, :statusgmt, :forename, :surname, :pwdhash )";
        $stmt = $this->hcatServer->dbh->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':crtgmt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':status', 'ACTIVE', PDO::PARAM_STR);
        $stmt->bindValue(':statusgmt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':forename', $forename, PDO::PARAM_STR);
        $stmt->bindValue(':surname', $surname, PDO::PARAM_STR);
        $stmt->bindValue(':pwdhash', $pwdhash, PDO::PARAM_STR);

        $r = $stmt->execute();
        $r2 = $stmt->errorInfo (  );
    }
}