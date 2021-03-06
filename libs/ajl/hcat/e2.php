<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alexlake
 * Date: 28/05/17
 * Time: 12:18
 * To change this template use File | Settings | File Templates.
 */

class Email {

    /* @var $params stdClass */
    public $params;
    private $templateDir;

    public $subject;
    public $destAddresses;
    public $banner;
    public $fromAddress;
    public $bodyHtml;
    public $eid;
    public $uid;

    public $secret;

    public function __construct() {
        $this->templateDir = valOr($GLOBALS['HcatConfig']['GenConfig'],'EmailTemplateDir','/etc/hcat/templates');
        $this->params = array();
        $this->secret = false;
        $this->fromAddress = array('notification@herdingcats.club'=>'herding cats');
        $this->eid = 0;
        $this->uid = 0;
    }

    public function debug($level,$msg) {
        debug($level,$msg);
    }

    // Some utility functions - perhaps should be hived
    public function mergeTemplate($templatename,$mergedata) {
        // where is the template root directory?


        // $templateBaseDir = $GLOBALS['dwfConfig']['AssetsBaseDir'].'/processes/APCustBill_1_0/templates/'; // should go into more global function

        $templatePath = $this->templateDir.'/'.$templatename."_html.php";

        if (file_exists($templatePath)) {

        $this->debug(1, "Composing body");

        extract(get_defined_vars());
        ob_start();
        include($templatePath);
        $this->bodyHtml = ob_get_contents();
        ob_end_clean();
        } else {
            $this->debug(3,"No template $templatePath");
        }
    }

    public function send() {

        if (!$this->bodyHtml) {
            return false;
        }
        $bodyHtml = $this->bodyHtml;

        $message = new Swift_Message($this->subject);
        $message->setFrom($this->fromAddress);
        $fromSummary = '';
        foreach($this->fromAddress as $fromemail=>$fromname) {
            $fromSummary.="[$fromemail($fromname)]";
        }
        $toSummary = '';
        foreach($this->destAddresses as $email=>$name) {
            $toSummary.="[$email($name)]";
        }

        if ($this->secret) {
            $message->setTo('notification@herdingcats.club');
            $message->setBcc($this->destAddresses);
        } else {
            $message->setTo($this->destAddresses);
        }


        //$bannerPath = $this->templateDir."APCustBill_$this->banner.png";
        //if (file_exists($bannerPath)) $bodyHtml = str_replace('<<banner-content-id>>', $message->embed(Swift_Image::fromPath($bannerPath)), $bodyHtml);



        $transport = Swift_SmtpTransport::newInstance('smtp-relay.sendinblue.com',587)
        ->setUsername('alex@thewinelake.com')
        ->setPassword('wcW8Rk7Z1XCyshva')
        ;
        $swiftmailer = new Swift_Mailer($transport);
        $to = '';
        foreach($message->getTo() as $e=>$n) $to.="[$e]";
        $this->debug(1,"Sending from $fromSummary to $to...");

        if ($this->banner) {
            $bannerPath = "{$this->templateDir}/banner_{$this->banner}.png";
            if (file_exists($bannerPath)) {
                $bodyHtml = str_replace('<<banner-content-id>>', $message->embed(Swift_Image::fromPath($bannerPath)), $bodyHtml);
            }
        }

        $message->setBody($bodyHtml, 'text/html');


        $x = $swiftmailer->send($message);
        $this->debug(1,"Send from $fromSummary returns $x");


        $sql="insert into hcat.emailarchive (gmt,fromaddr,toaddr,body,eid,uid) ";
        $sql.="values (:gmt,:fromaddr,:toaddr,:body,:eid,:uid); ";

        $stmt = $GLOBALS['dbh']->prepare($sql);
        $stmt->bindValue(':gmt', date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':fromaddr', $fromSummary, PDO::PARAM_STR);
        $stmt->bindValue(':toaddr', $toSummary, PDO::PARAM_STR);
        $stmt->bindValue(':body', $this->bodyHtml, PDO::PARAM_STR);

        $stmt->bindValue(':eid', $this->eid, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $this->uid, PDO::PARAM_INT);

        $r = hcatServer()->dbExecute($stmt);


        return $x;

    }
}