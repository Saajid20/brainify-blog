<?php

session_start();
// Unset all of the session variables
$_SESSION = array();

header("Location: login.php");
exit();

?>