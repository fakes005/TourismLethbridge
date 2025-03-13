<?php
session_start(); 

// Ensure the user is logged in and has the correct permission level
if (empty($_SESSION["isLogin"])) {
    echo "You are not logged in!";
    echo "<meta http-equiv='refresh' content='3;url=../login.php'><br>";
    echo "Redirecting in 3 seconds...";
    exit();
}

// Check user permission level
if ($_SESSION["permLevel"] != 1) {
    echo "Unauthorized access!";
    echo "<meta http-equiv='refresh' content='3;url=../index.php'><br>";
    echo "Redirecting in 3 seconds...";
    exit();
}

// If authorized, include database and functions
require_once("../includes/db.php");
require_once("../includes/functions.php");
?>
