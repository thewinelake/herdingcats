<?php
/**
 * Created by PhpStorm.
 * User: AJL
 * Date: 09-Jun-2011
 * Time: 15:00:40
 * To change this template use File | Settings | File Templates.
 */

include "common.php";

try
{
    // RESTServer handles everything
    $hcatServer = new hcatServer();
    $GLOBALS['hcatServer'] = $hcatServer;
    $hcatServer->handleHit();
}
catch (Exception $e)
{
    echo "ERROR : ".$e->getMessage();
}

function hcatServer() {
    return $GLOBALS['hcatServer'];
}

/**
 * View console (my events/my invitations)
 * View an event as organiser
 * Edit event details
 * Add/Remove guests
 * Send invitations
 *
 *
 * Email reception/event messages
 * - poll mailbox (e_2@herdingcats.club)
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */