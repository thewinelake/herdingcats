<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alexlake
 * Date: 30/05/17
 * Time: 21:42
 * To change this template use File | Settings | File Templates.
 */

class emailParser {

    private $hcatServer;

    public function __construct($hcatServer) {
        $this->hcatServer = $hcatServer;

    }


    public function poll() {
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

                $header = imap_headerinfo($inbox, $email_number);


                $toMailbox = $header->to[0]->mailbox;
                // This could use angle bracket notation


                // the toAddr is like the REST hit
                // At the moment, we can only handle to-addresses of the form e_<evtId>@herdingcats.club
                // or the more secure e_<evtId>_<Key>@herdingcats.club

                $toAddrBits2 = explode("_",$toMailbox);

                switch ($toAddrBits2[0]) {
                    case 'e':
                        $eid = $toAddrBits2[1];
                        $evtKey = valOr($toAddrBits2,2,'');
                        $e = new event($this->hcatServer,$eid);
                        if ($e->eid) {
                            if ($e->validateKey($evtKey)) {
                                $this->handleEmail($inbox,$email_number,$e);
                            }
                        } else {
                            $this->hcatServer->errorPage("Unable to find event $eid");
                        }
                        break;
                    default:
                        // Ignore it
                        $this->debug(1,"Binning spam to $toMailbox");
                }

                //$r = imap_delete($inbox,$email_number); // But does this really not shuffle up?
                //$this->debug(1,"Delete $email_number returns $r");

            }


        } else {
            $this->debug(1,"No emails to handle");
        }

        /* close the connection */
        imap_close($inbox,CL_EXPUNGE);

    }

    public function handleEmail($inbox,$email_number,$e) {
        $header = imap_headerinfo($inbox, $email_number);
        $overview = imap_fetch_overview($inbox,$email_number,0);
        $body = imap_fetchbody($inbox,$email_number,'1');


        $structure = imap_fetchstructure($inbox, $email_number);

        if(!(isset($structure->parts) && is_array($structure->parts) && isset($structure->parts[1]))) {
            return false;
        }

       $part = $structure->parts[1];

       $txtMessage = imap_fetchbody($inbox,$email_number,1);


       $htmlMessage = imap_fetchbody($inbox,$email_number,2);

       if($part->encoding == 3) {
           $htmlMessage = imap_base64($htmlMessage);
           $txtMessage = imap_base64($txtMessage);
       } else if($part->encoding == 1) {
           $htmlMessage = imap_8bit($htmlMessage);
           $txtMessage = imap_8bit($txtMessage);
       } else {
           $htmlMessage = imap_qprint($htmlMessage);
           $txtMessage = imap_qprint($txtMessage);
       }


        if ($txtMessage) {
            // Let's put this in as a message
            $uid = $this->getUidFromEmail($header);
            $parentMid = $this->getParentMIDFromEmail($header);
            $emailComment = new message($this->hcatServer);
            $emailComment->uid = $uid;
            $emailComment->eid = $e->eid;
            $emailComment->parentMid = $parentMid;
            $emailComment->txtMessage = $txtMessage;
            $emailComment->htmlMessage = $htmlMessage;
            $emailComment->saveToDB();

        }
        $subject = $overview[0]->subject;
        $this->debug(1,"Event handling email with subject $subject");
        $this->debug(1,"Message reads $body");


        // Is this an auto-responder?

    }

    public function debug($lvl,$msg) {
        debug($lvl,$msg);
    }

    private function getUidFromEmail($header) {
        $subject = $header->subject;
        $from = $header->from[0]->mailbox.'@'.$header->from[0]->host;
        // Can we find this user
        $user = new user();
        $user->loadByEmailAddr($from);

        $uid = $user->uid;
        return $uid;
    }

    private function getParentMIDFromEmail($header) {
        // we may be able to find a parentMID in the subject
        $subject = $header->subject;
        preg_match_all("/\[([^\]]*)\]/", $subject, $matches);

        if (sizeof($matches[1])) {
            $parentMid = $matches[1][sizeof($matches[1])-1];
        } else {
            $parentMid = 0;
        }
        return $parentMid;
    }

}