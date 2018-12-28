<?php
session_start();
setcookie(session_name(), '', 100);
$_SESSION = [];
session_destroy();
header("location: index.php");
?>