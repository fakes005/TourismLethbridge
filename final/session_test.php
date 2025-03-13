<?php
session_start();

$_SESSION["test"] = "Session is working!";
echo "Session test value: " . $_SESSION["test"] . "<br>";

phpinfo(); // Check session settings
?>
