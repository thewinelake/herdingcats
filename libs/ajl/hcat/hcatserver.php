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

            $this->setLogin();



            $verb = strtoupper($_SERVER['REQUEST_METHOD']);

            $this->urlSegment = array_slice(explode('/',$_SERVER['PATH_INFO']),1);

            // Do something!!!
            $action = $this->urlSegment[0];
            $actionchunks = explode('_',$action);

            // echo "[$verb:$action]";

            switch ($actionchunks[0]) {
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
    }

    public function errorPage($errMsg) {
        include "furniture/header.php";

        echo $errMsg;

        include "furniture/footer.php";
    }

    public function pollEmail() {
        $hostname = 'mail3.gridhost.co.uk:993/imap/ssl';
        $username = 'all@herdingcats.club';
        $password = 'Cool4cats';



        $inbox = imap_open("{{$hostname}}INBOX",$username,$password) or die('Cannot connect to IMAP['.$hostname.']: ' . imap_last_error());


        /* grab emails */
        $emails = imap_search($inbox,'ALL');



        /* if emails are returned, cycle through each... */
        if($emails) {


            /* put the newest emails on top */
            rsort($emails);

            /* for every email... */
            foreach($emails as $email_number) {

                /* get information specific to this email */
                $overview = imap_fetch_overview($inbox,$email_number,0);

                $toAddr = strtolower($overview[0]->to);

                // the toAddr is like the REST hit
                // At the moment, we can only handle to-addresses of the form e_<evtId>@herdingcats.club

                $toAddrBits1 = explode("@",$toAddr);
                $toAddrBits2 = explode("_",$toAddrBits1[0]);

                switch ($toAddrBits2[0]) {
                    case 'e':
                        $eid = $toAddrBits2[1];
                        $evtKey = valOr($toAddrBits2,2,'');
                        $e = new event($this);
                        $e->eid = $eid;
                        if($e->loadFromDB()) {
                            if ($e->validateKey($evtKey)) {
                                $e->handleEmail($inbox,$email_number);
                            }
                        } else {
                            $this->errorPage("Unable to find event $eid");
                        }
                        break;
                    default:
                        // Ignore it
                        $this->debug(1,"Binning spam to $toAddr");
                }

                $r = imap_delete($inbox,$email_number); // But does this really not shuffle up?
                $this->debug(1,"Delete $email_number returns $r");

            }


        } else {
            $this->debug(1,"No emails to handle");
        }

        /* close the connection */
        imap_close($inbox,CL_EXPUNGE);

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