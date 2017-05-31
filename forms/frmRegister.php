
<?php
    $message = valOr($_SESSION,'message');
    switch (substr($message,0,1)) {
        case '!':
            $message=substr($message,1);
            $messageStyle = "warning";
            break;
        default:
            $messageStyle = "";
    }

    $email = valOr($_POST,'email');
    $name = valOr($_POST,'name');
    $pwd = valOr($_POST,'pwd');



    $onloadJS = "HCATS.register.js.init();";
?>

<?php
include "furniture/header.php"
?>

<h1>HerdingCats.club registration</h1>
<div id='message' class="consolemessage <?= $messageStyle ?>"><?= $message ?></div>
<form name="register" id="register" method="POST" action="register">
    <p><div class="formParamName">Primary Email</div><div class="formParamValue"><input type="text" name="email" value="<?= $email ?>"></div></p>

    <p><div class="formParamName">Name</div><div class="formParamValue"><input type="text" name="name" value="<?= $name ?>"></div></p>

    <p><div class="formParamName">Password</div><div class="formParamValue"><input type="password" name="pwd"></div></p>


    <p><button class="register">Register</button></p>

</form>

<?php
 $_SESSION['message']='';
?>