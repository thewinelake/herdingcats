
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
    <p><a class="button login">login</a></p>
    <p><a class="button showregister">register...</a></p>

</form>

<?php
 $_SESSION['message']='';
?>