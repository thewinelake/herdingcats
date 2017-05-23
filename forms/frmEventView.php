<?php

// $mode

$onloadJS = "HCATS.event.eventbuilder.init({$event->id});";

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
    <tr id="guestRow" class="template">
        <td name="name">?</td>
        <td name="address">?</td>
        <td name="style">?</td>
        <td name="actions"><a class="button remove">Remove</a><a class="button nudge">Nudge</a></td>
    </tr>
</table>
<div id="comment" class="template">
    <div class="commentText"></div>
    <div class="commentAuthor"></div>
</div>


<form name="event" method="POST" action="e_<?= $event->id ?>">

<?php if ($mode=='edit') { ?>
    <p>Title:<input name="title" type="text" value="<?= $event->title ?>"></p>
    <p>Description:<textarea name="description"><?= $event->description ?></textarea></p>
    <p>Date: <input name="date" type="text" value="<?= $event->date ?>"></p>

    <p><input type="submit" name="action" value="save"></p>
<?php } else { ?>
    <h1><?= $event->title ?></h1>
    <p><i><?= $event->description ?></i></p>
    <p>Date: <?= $event->date ?></p>

    <p><input type="submit" name="action" value="edit"></p>

    <h2>Invited</h2>
    <table border=1>
        <tbody id="guestListContainer"></tbody>
    </table>
    <a class="addGuest button">Add Guest</a>
    <div id='guestRowInput'>
        <p>
            <span>Name:<input type='text' name='guestName'></span>
            <span>Address:<input type='text' name='guestAddress'></span>
            <a class='button inviteGuestButton'>Invite</a>
        </p>
    </div>
<?php } ?>
</form>

<h2>Comments</h2>
<div id="commentContainer"></div>


<form name="comment" id="comment" method="POST" action="e_<?= $event->id ?>">
    <p><textarea name="comment" class="comment"></textarea></p>
    <a class='button addcomment'>Add Comment</a>
</form>


<?php
include "furniture/footer.php"
?>
