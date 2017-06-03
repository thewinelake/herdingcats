<?php
/* @var $mergedata stdClass */
/* @var $mergedata->event event */
/* @var $mergedata->user user */

$this->banner = 'hcats';
$this->subject = "Update from {$mergedata->comment->AuthorName} for {$mergedata->event->title} [$mergedata->mid]";
$guestURL = $mergedata->event->GuestURL($mergedata->user->uid);
$guestUnsubscribeURL = $mergedata->event->GuestUnsubscribeURL($mergedata->user->uid, $mergedata->event->eid);
$guestUnsubscribeAllURL = $mergedata->event->GuestUnsubscribeURL($mergedata->user->uid,'ALL');
$hostName = $mergedata->event->host->name;
$invitationGMT = $mergedata->invitation->crtgmt;

$this->debug(1,'e1_html template');
?>
<html>
<head>
    <style>
        .eventtitle {
            font-size: 20px;
        }
        .eventzoom {
            float:right;
            background-color: #009816;
            color: #fff;
            padding: 5px 20px;
            border-radius: 3px;
        }
        .eventdescription {
        }
        .eventinfo {
            font-style: oblique;
        }
        .hcaticon {
            float: left;
            margin-right: 10px;
        }
        .comment {
            border:1px solid;
            padding: 5px;
        }
        .commentheader {
            background-color: saddlebrown;
            clear: left;
            color:white;
            padding-left: 5px;
        }

        .emailfooter {
            opacity: 0.5;
        }
    </style>
</head>

<body>

<p><img class="hcaticon" src="<<banner-content-id>>" alt="herding cats icon" />
<a href="<?= $guestURL ?>" class="eventzoom">More Info</a>
<div class="eventtitle"><?= $mergedata->event->title ?></div>
<div class="eventinfo">Hosted by <?= $hostName ?></div>
</p>
<p class=eventdescription><?= $mergedata->event->description ?></p>
\
<div class="commentheader">
Comment made by <?= $mergedata->comment->AuthorName ?> on <?= Date('D d-M-Y',$mergedata->comment->GMT) ?> at <?= Date('H:i',$mergedata->comment->GMT) ?>
</div>
<div class="comment">
<?= $mergedata->comment->Text ?>
</div>

<div class='emailfooter'>
<p>You were invited to this event by <?= $hostName ?> on Sunday 29th May 2017 at 21:19.</p>
<p>You can...<br/>
    <ul>
        <li><b>Reply to this email</b> and this will be added to the online event discussion</li>
        <li><b>Press the More Info button</b> to see more about the event including the discussion, and contribute there</li>
    </ul>

<p>If this email is unwelcome, sorry! You can raise a complaint and unsubscribe from this event by
    <a href="<?= $guestUnsubscribeURL ?>">clicking here</a>
    or block all herding cats emails by
    <a href="<?= $guestUnsubscribeAllURL ?>">clicking here</a></p>
</div>
</body>
</html>