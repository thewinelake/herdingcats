<?php

$onloadJS = "HCATS.console.wibble.init();";

include "furniture/header.php"
?>
<div id="myevents">
    <h1>my events</h1>


    <table border=1>
        <tbody id="myEventsContainer" >
        <tr>
            <td>Date</td>
            <td>Title</td>
            <td>Guests ??</td>
        </tr>
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

<div id="otherevents">
    <h1>other events</h1>
</div>

<!--- Some templates --->

<table>
    <tr id="eventHeader" class="template">
        <td>Date</td>
        <td>Title</td>
        <td>Guests</td>
        <td>Zoom</td>
    </tr>
    <tr id="eventRow" class="template">
        <td name="date">?</td>
        <td name="title">?</td>
        <td name="guestinfo">?</td>
        <td name="eventzoom"><a class="button zoom" href="#">Zoom</a></td>

    </tr>
</table>


<?php
include "furniture/footer.php"
?>
