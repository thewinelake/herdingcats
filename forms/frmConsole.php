<?php

$onloadJS = "HCATS.console.wibble.init();";

include "furniture/header.php"
?>
<div id="myevents">
    <h1>my events</h1>


    <table border=1>
        <tbody id="myEventsContainer" >
        </tbody>
    </table>
    <p><a class="button newevent">New Event...</a></p>

    <div id='newEvent'>
        <p>
            <span>Title:<input type='text' name='title'></span>
            <span>Description:<input type='text' name='description'></span>
            <span>When:<input type='text' name='date'></span>

            <a class='button createevent'>Create Event.</a>
        </p>
    </div>

</div>

<!--- Some templates --->

<table>
    <tr id="eventHeader" class="template">
        <td>Date</td>
        <td>Title</td>
        <td>Host</td>
        <td>Status</td>
        <td>Guests</td>
        <td>Zoom</td>
    </tr>
    <tr id="noEvents" class="template">
        <td colspan="10"><i>You have no events at the moment. Create them or be invited to them</i></td>
    </tr>
    <tr id="eventRow" class="template">
        <td name="date">?</td>
        <td name="title">?</td>
        <td name="host">?</td>
        <td name="status">?</td>
        <td name="guestinfo">?</td>
        <td name="eventzoom"><button class="zoom link">Zoom</button></td>

    </tr>
</table>


<?php
include "furniture/footer.php"
?>
