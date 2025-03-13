<?php
    session_start(); //Helps stay logged in and access, main state across sites

    if(!empty($_SESSION["isLogin"]) && $_SESSION["permLevel"] == 2){ //Authorize Admin
        require_once("../includes/db.php");
        require_once("../includes/functions.php");
    }
    else { //Unauthorized 
        echo "You are not authorized to this page.";
        echo "<meta http-equiv='refresh' content='3;url=../index.php'><br>";
        echo "Redirecting in 3 seconds....";
        session_destroy();
        exit();
    }
?>
