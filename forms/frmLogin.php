
<?php
    $message = valOr($_SESSION,'message');
    $email = valOr($_POST,'email');

    $onloadJS = "HCATS.login.js.init();";
?>

<?php
include "furniture/header.php"
?>

<h1>HerdingCats.club login</h1>
<div id='message'><?= $message ?></div>
<form name="login" id="login" method="POST" action="login">
    <p>Email:<input type="text" name="email" value="<?= $email ?>"></p>
    <p>Password:<input type="password" name="pwd"></p>
    <p><button class="login">login</button></p>
    <p>Or if you're new around here, <a class="button showregister">register...</a></p>

</form>

<?php
 $_SESSION['message']='';
?>