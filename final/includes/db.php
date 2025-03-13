<?php
$dbhost = "localhost";
$dbname = "testcal";
$dbuser = "root";
$dbpass = "Sixmile43drive";

try {
    // Attempt to establish the connection
    $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

    // Set the PDO error mode to exception (so we can catch errors)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $err) {
    // Get the error message from the exception
    $error_message = $err->getMessage();

    // Log the exception error message to the error log file with a new line
    error_log($error_message . PHP_EOL, 3, "error_log.txt");

    // Show a generic message to the user, without exposing sensitive info
    echo "There was a problem connecting to the database. Please try again later. <br>";
    
    // echo "Database error: " . $err->getMessage();
    exit();  // Stop further execution if connection fails
}
?>









