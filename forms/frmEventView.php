<?php

// $mode
/* @var $event event */
$onloadJS = "HCATS.event.eventbuilder.init({$event->eid});";

?>


<?php
include "furniture/header.php"
?>

<script>
var event = <?= $eventJson ?>;
console.dir(event);
</script>
<table>
    <tr id="guestHeader" class="template">
        <td>Name</td>
        <td>Address</td>
        <td>Status</td>
        <td>Actions</td>
    </tr>
    <tr id="guestRow" class="guest template">
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


<form name="event" method="POST" action="e_<?= $event->eid ?>">

<?php if ($mode=='edit') { ?>
    <p>Title:<input name="title" type="text" value="<?= $event->title ?>"></p>
    <p>Description:<textarea name="description"><?= $event->description ?></textarea></p>
    <p>Date: <input name="date" type="text" value="<?= $event->date ?>"></p>

    <p><input type="submit" name="action" value="save"></p>
<?php } else { ?>
    <h1><?= $event->title ?> <button class="editEvent">Edit</button></h1>
    <p><i><?= $event->description ?></i></p>
    <p>Date: <?= $event->date ?></p>
    <h2>Guest List</h2>
    <table border=1>
        <tbody id="guestListContainer"></tbody>
    </table>
    <a class="addGuest button">Add Guest</a>
    <div id='guestRowInput'>
        <p>
            <span>Email Address:<input type='text' name='guestEmail'></span>
            <span>Name:<input type='text' name='guestName'></span>
            <a class='button inviteGuestButton'>Invite</a>
        </p>
    </div>
<?php } ?>
</form>

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
