<?php

/**
 * Created by PhpStorm.
 * User: alexlake
 * Date: 06/05/2017
 * Time: 23:07
 *
 * hcatServer is responsible for choreography between the objects (each of which have their own views and actions)
 *
 */

require_once 'package.php';


class hcatServer
{
    // This is the main point of entry for all hcat hits
    //

    private $urlSegment = array();
    private $target; // Where should any subsequent hits be targetted?

    /** @var $dbh PDO **/
    public $dbh;

    public $uid;
    /** @var $user user **/

    public $user;

    public function __construct() {
        $dsn  = 'mysql:host=localhost;dname=hcat';
        $dbu = 'garfield';
        $dbp = 'JimDavis';
        try {
            $this->dbh = new PDO($dsn,$dbu,$dbp);
            $GLOBALS['dbh'] = $this->dbh;
        } catch (exception $e) {
            $this->debug(3, 'Error connecting DB');
            exit(0);
        }
        $this->user = new user();
    }

    public function dbExecute($stmt,$data=null) {
        $this->debug(1,'dbExecute '.$stmt->queryString);
        if ($data) {
            $dataParams='';
            foreach($data as $k=>$v) {
                $dataParams.="[$k=$v]";
            }
            $this->debug(1,"Params $dataParams");

        }
        $r = $stmt->execute();
        if ($r) {
            $this->debug(2,'OK');
        } else {
            $r2=$stmt->errorInfo();
            $this->debug(2,$r2[2]);
        }
        return $r;
    }

    public function dbLastInsertID() {
        $stmt = $this->dbh->prepare("SELECT LAST_INSERT_ID();");
        $r = $this->dbExecute($stmt);
        if ($r) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows[0]["LAST_INSERT_ID()"];
        } else {
            return 0;
        }
    }

    public function debug($level,$msg) {
        $d = date('H:i:s');

        if (isset($_REQUEST['debug']) && $_REQUEST['debug']=='Y') {
            echo "<p>$d : $level : $msg";
        }
        if ($level==2) {
            $level+=0;
        }
        // Need a side-channel in a log file (use tail -f in separate console)
        $logPath = '/var/log/hcat.log';
        $l="$d : $level : $msg\n";
        $fp=fopen($logPath,'a');
        if ($fp) {
            fputs($fp,$l);
            fclose($fp);
        }

    }


    public function handleHit()
    {
        // need some instrumentation on this
        $timeIn = time() + microtime();
        //$timeIn = microtime();

        session_start();


        try {

            $cmd = '?';

            $this->setLogin();



            $verb = strtoupper($_SERVER['REQUEST_METHOD']);

            $this->urlSegment = array_slice(explode('/',$_SERVER['PATH_INFO']),1);

            // Do something!!!
            $action = $this->urlSegment[0];
            $actionchunks = explode('_',$action);

            // echo "[$verb:$action]";
            $cmd = $actionchunks[0];
            switch ($cmd) {
                case '':
                    if (isset($_SESSION['uid']) && $_SESSION['uid']) {
                        $l = new console($this);
                        $l->render();
                    } else {
                        $l = new login($this);
                        $l->render();
                    }
                    break;
                case'showlogin':
                    $l = new login($this);
                    $l->render();
                    break;
                case 'login': // the user is trying to login
                    $lm = new loginmanager($this);
                    if ($lm->validateLogin()) {
                        $l = new console($this);
                        $l->render();
                    } else {
                        $l = new login($this);
                        $l->render();
                    }
                    break;
                case 'showregister':
                    $r = new register($this);
                    $r->render();
                    break;
                case 'register':
                    $r = new register($this);
                    $r->doregister();
                    break;
                case 'createuser':
                    $lm = new loginmanager($this);
                    $lm->createUser();
                    break;
                case 'phpinfo':
                    phpInfo();
                    break;
                case 'request':
                    print "<p>REQUEST</p><pre>";
                    print_r($_REQUEST);
                    print "</pre><pre>@";
                    print_r($_SERVER);
                    print "</pre>";
                    break;
                case 'poll':
                    $this->pollEmail();
                    break;
                case 'console':
                    $c = new console($this);
                    $c->handleHit();
                    break;
                case 'logout':
                    $_SESSION['uid']='';
                    include "furniture/loggedout.php";
                    break;
                case 'inspector': // event (should this code live in the event class itself?)
                    $i = new inspector($this);
                    $i->render();
                    break;
                case 'emailbody':
                    $i = new inspector($this);
                    $idx = valOr($_REQUEST,'i');
                    include 'furniture/emailinspectorheader.php';
                    print $i->getEmailArchiveBody($idx);
                    include 'furniture/emailinspectorfooter.php';
                    break;
                case 'e': // event (should this code live in the event class itself?)
                    $eid = $actionchunks[1];
                    $this->debug(1,"hit to event $eid. path_info=".$_SERVER['PATH_INFO']);
                    $e = new event($this);
                    $e->eid = $eid;
                    if($e->loadFromDB()) {

                       if (!$e->handleHit()) {
                           $this->errorPage("Insufficient authority to view event $eid");
                       }
                    } else {
                        $this->errorPage("Unable to load event $eid");
                    }
                    break;

            }

        } catch (Exception $e) {
            $msg = $e->getMessage();
            $this->debug(3, $msg);
        }
        $timeOut = time() + microtime();
        $duration = $timeOut - $timeIn;
        $this->debug(1, "Handling $cmd took {$duration}ms");

    }

    public function errorPage($errMsg) {
        include "furniture/header.php";

        echo $errMsg;

        include "furniture/footer.php";
    }


    public function pollEmail() {
        $emailParser = new emailParser($this);
        $emailParser->poll();
    }

    public function setLogin() {
        if(isset($_SESSION['uid'])) {
            $this->user = new user($_SESSION['uid']);
        } else {
            $guest = null;
            $this->usr = $guest;
        }
    }



};

function debug($level,$msg) {
    $d = date('H:i:s');

    if (isset($_REQUEST['debug']) && $_REQUEST['debug']=='Y') {
        echo "<p>$d : $level : $msg";
    }
    if ($level==2) {
        $level+=0;
    }
    // Need a side-channel in a log file (use tail -f in separate console)
    $logPath = '/var/log/hcat.log';
    $l="$d : $level : $msg\n";
    $fp=fopen($logPath,'a');
    if ($fp) {
        fputs($fp,$l);
        fclose($fp);
    }

}

function AddURLParam($baseURL,$extraKVP) {
    if (strpos($baseURL,'?')!== false) {
        // There's already an & in the URL
        return $baseURL.'&'.$extraKVP;
    } else {
        return $baseURL.'?'.$extraKVP;
    }
}