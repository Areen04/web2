<?php
session_start();
session_destroy();
header("Location: logIn.php");
exit();
?>
