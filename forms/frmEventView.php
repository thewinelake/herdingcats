<?php

// $mode
/* @var $event event */
$onloadJS = "HCATS.event.eventbuilder.init({$event->eid});";

$userCSS = $event->makeUserCSS();

?>


<?php
include "furniture/header.php"
?>

<?= $userCSS ?>


<script>
var event = <?= $eventJson ?>;
console.dir(event);
</script>
<table>
    <tr id="guestHeader" class="template">
        <th>&nbsp;</th>
        <th>Name</th>
        <th>Address</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <tr id="guestRow" class="guest template">
        <td name="colourkey">&nbsp;</td>
        <td name="name">?</td>
        <td name="email">?</td>
        <td name="status">?</td>
        <td name="actions"><button class="button remove">Remove</button><button class="button nudge">Nudge</button></td>
    </tr>
</table>
<div id="comment" class="comment template">
    <div class="commentHtml"></div>
    <div class="commentFooter">
    <div class="commentInfo"></div>
    <div class="commentActions"><button class="broadcastComment">Broadcast</button><button class="deleteComment">Delete</button></div>
    </div>
</div>



<?php if ($mode=='edit') { ?>
<form name="event" method="POST" action="e_<?= $event->eid ?>">

    <p class="label">Title:<input name="title" type="text" value="<?= $event->title ?>"></p>
    <p class="label">Description:<textarea name="description"><?= $event->description ?></textarea></p>
    <p class="label">Date: <input name="date" type="text" value="<?= $event->date ?>"></p>

    <p><input type="submit" name="action" value="save"></p>
</form>

<?php } else { ?>
    <h1>
        <?= $event->title ?>
        <span><button class="editEvent rightalign">Edit</button></span>
    </h1>
<hr/>
    <div class="eventinfo"><div class="label">Host:</div> <div class="tooltip"><?= $event->host->name ?><span class="tooltiptext"><?= $event->host->email ?></span></div>
    <div class="label">Date:</div> <?= $event->date ?>

    <div class="eventDescription"><?= $event->description ?></div>
    <h2>
        Guest List
        <span><button class="addGuest rightalign">Add Guest...</button></span>
    </h2>
    <table border=1>
        <tbody id="guestListContainer"></tbody>
    </table>
    <div id='guestRowInput'>
        <p>
            <span>Email Address:<input type='text' name='guestEmail'></span>
            <span>Name:<input type='text' name='guestName'></span>
            <button class='inviteGuest'>Invite</button>
        </p>
    </div>
<?php } ?>

<h2>Comments</h2>
<div id="commentContainer"></div>


<form name="comment" id="comment" method="POST" action="e_<?= $event->eid ?>">
    <p><textarea name="comment" class="newComment"></textarea></p>
    <button class='addcomment'>Add Comment</button>
</form>

<button class='deleteevent'>Delete Event</button>
<button class='cancelevent'>Cancel Event</button>



<?php
include "furniture/footer.php"
?>
