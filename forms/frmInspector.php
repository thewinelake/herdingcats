<?php
include "furniture/header.php"

?>

<?php if ($this->event) { ?>
<h1>Event</h1>
<?= $this->event->inspectorHtml() ?>
<?php } ?>

<?php if ($this->user) { ?>
<h1>User</h1>
<?= $this->user->inspectorHtml() ?>
<?php } ?>

<?php
include "furniture/footer.php"
?>
