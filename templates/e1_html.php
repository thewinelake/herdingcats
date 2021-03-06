<?php
/* @var $mergedata stdClass */
/* @var $mergedata->event event */
/* @var $mergedata->user user */

$this->banner = 'hcats';
$this->subject = "Update for {$mergedata->event->title} [$mergedata->mid]";
$guestURL = $mergedata->event->GuestURL($mergedata->user->uid);
$guestUnsubscribeURL = $mergedata->event->GuestUnsubscribeURL($mergedata->user->uid, $mergedata->event->eid);
$guestUnsubscribeAllURL = $mergedata->event->GuestUnsubscribeURL($mergedata->user->uid,'ALL');

$this->debug(1,'e1_html template');
?>
<html>
<head>
    <style>
        p.hcaticon {
            float: left
        }
        .bigbutton {
            color: #fff;
            padding: 5px 50px;
        }
        .green {
            background-color: #009816;
        }
        .green {
            background-color: darkred;
        }
    </style>
</head>

<body>

<p class=hcaticon><img src="<<banner-content-id>>" alt="herding cats" width="73" height="120" /></p>
<p>Event Update</p>
<h1><?= $mergedata->event->title ?></h1>
<p>Host is <?= $mergedata->event->host->name ?></p>
<p><i><?= $mergedata->event->description ?></i></p>
<div style="border:1px solid">
<p><?= $mergedata->comment->Text ?></p>
</div>
<p><small>Posted by <?= $mergedata->comment->Author ?> at <?= $mergedata->comment->GMT ?></small></p>

<p>You are on the guest list for this event.
    <a class="bigbutton green" href="<?= $guestURL ?>">More Info</a></p>
<p>If this email is unwelcome, sorry! You can raise a complaint and unsubscribe from this event by
    <a href="<?= $guestUnsubscribeURL ?>">clicking here</a>
    or block all herding cats emails by
    <a href="<?= $guestUnsubscribeAllURL ?>">clicking here</a></p>
</body>
</html>