<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $file = $_FILES["file"]["tmp_name"];

        // Define the path to the Python script
        $pythonScriptPath = __DIR__ . "/apptest.py"; // Adjust the path if needed

        // Call the Python script with the uploaded file as an argument
        $command = "py " . escapeshellarg($pythonScriptPath) . " " . escapeshellarg($file);
        $output = shell_exec($command);

        // Display the output from the Python script
        echo "<pre>$output</pre>";
    } else {
        echo "<p>Error uploading file. Please try again.</p>";
    }
} else {
    echo "<p>Invalid request method.</p>";
}
?>