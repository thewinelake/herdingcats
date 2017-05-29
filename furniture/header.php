<?php
    if (get_class($this)=='hcatServer') {
        $hs = $this;
    } else {
        $hs =$this->hcatServer;
    }
    $uid = $hs->user->uid;
    $email = $hs->user->email;
?>

<html>

<head>
    <link rel="stylesheet" type="text/css" href="css/hcats.css"/>
    <script src="jquery-3.2.1.min.js"></script>
    <script src="js/event.js"></script>
    <script src="js/console.js"></script>
    <script src="js/login.js"></script>
    <script src="js/register.js"></script>
    <script src="js/hcats.js"></script>

<?php if (isset($onloadJS)) { ?>
    <script>
        $( document ).ready(function() {
            <?= $onloadJS ?>
        });
    </script>
<?php } ?>

</head>
<body>
<?php if ($uid) { ?>
<table  width="100%">
    <tr class="header1">
        <td colspan="4">HerdingCats.club</td>
    </tr>
    <tr class='header2'>
        <td><button class="link console" href="/">console</button></td>
        <td><?= $email ?></td>
        <td class="rCell"><button class="link" href="pages/sessioninfo.php">details</button></td>
        <td class="rCell"><button class="link" href='/logout'>logout</button></td>

    </tr>
</table>
<?php } ?>