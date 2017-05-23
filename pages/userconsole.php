<?php

include_once "../global.php";

include '../furniture/header.php';

// now render the userheader

echo ("<h1>userheader</h1>");

// myevents
echo ("<h1>my events</h1>");


// events I'm invited to
echo ("<h1>other events</h1>");


// select * from event,invitation where event.eventid=invitation.eventid and invitation.uid=<myid>

