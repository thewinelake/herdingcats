<?php

/**
 * Created by PhpStorm.
 * User: alexlake
 * Date: 06/05/2017
 * Time: 22:28
 */

class register extends hcatUI
{
    function render() {
        include 'furniture/header.php';
        include 'forms/frmRegister.php';
        include 'furniture/footer.php';

    }

    function doregister() {
        $email = valOr($_POST,'email');
        $name = valOr($_POST,'name');
        $pwd = valOr($_POST,'pwd');

        // Check if params valid

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->message="Bad email address format";   // This should be on the form, actually
            $this->render();
            return;
        }


        // Check if it already exists
        $sql = 'select * from hcat.user where email=:email;';
        $stmt = $this->hcatServer->dbh->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (sizeof($row)==0) {
            // That's encouraging?
            $user = new user();
            $user->email = $email;
            $user->name = $name;
            $user->setPassword($pwd);
            if ($user->saveToDB()) {
                // So it worked...
                // ...now what?
                $_SESSION['message']="Account created for {$user->email}. Now you can login.";

            } else {
                $_SESSION['message']="Couldn't create Account for {$user->email}.";
            }
            $l = new login($this->hcatServer);
            $l->render();
        } else {
            $user = new user($row['uid']);
            $_SESSION['message']="!Account already created for {$user->email}.";
            $r = new register($this->hcatServer);
            $r->render();
        }
    }
}