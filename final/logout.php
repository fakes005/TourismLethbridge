<?php
session_start();
$redirect_url = $_SESSION['current_page'];
session_destroy();
header("Location: {$redirect_url}");
exit();
?>
