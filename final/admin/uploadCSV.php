<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['file']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        // Store the file path in the session
        $_SESSION['filePath'] = $filePath;

        // Redirect to displayTable.php in a new tab
        echo "<script>
            window.open('displayTable.php', '_blank');
            window.close(); // Close the popup
        </script>";
        exit();
    } else {
        echo "<p>Failed to upload file.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload CSV</title>
    <link rel="stylesheet" href="../assets/upload/styles.css">
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            background-color: #5a9bd5;
            color: black;
            overflow-x: hidden; /* Prevent horizontal scrolling */
            width: 100vw;
        
        }
        
    </style>
</head>
<body>
    <form action="uploadCSV.php" method="post" enctype="multipart/form-data">
        <label for="file">Select CSV File:</label>
        <input type="file" name="file" id="file" accept=".csv" required>
        <input type="submit" name="submit" value="Upload and Process">
    </form>
</body>
</html>