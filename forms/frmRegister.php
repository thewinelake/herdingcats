
<?php
    $message = valOr($_SESSION,'message');
    $email = valOr($_POST,'email');
    $name = valOr($_POST,'name');
    $pwd = valOr($_POST,'pwd');



    $onloadJS = "HCATS.register.js.init();";
?>

<?php
include "furniture/header.php"
?>

<h1>HerdingCats.club registration</h1>
<div id='message'><?= $message ?></div>
<form name="register" id="register" method="POST" action="register">
    <p>Primary Email:<input type="text" name="email" value="<?= $email ?>"></p>

    <p>Name:<input type="text" name="Name" value="<?= $name ?>"></p>

    <p>Password:<input type="password" name="pwd"></p>


    <p><a class="button register">Register</a></p>

</form>

<?php
 $_SESSION['message']='';
?>