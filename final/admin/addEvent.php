<?php
require_once("../includes/authorizeAdmin.php");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
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
    
        <h1 style>Add Event</h1>
    

    <form action="addEvent.php" method="post" enctype="multipart/form-data">
        <label for="event_name">Event Name:</label>
        <input type="text" id="event_name" name="event_name" maxlength="50" required><br>
        
        <label for="event_desc">Event Description:</label>
        <textarea id="event_desc" name="event_desc" maxlength="500"></textarea>

        <label for="event_startdate">Select Event Start Date:</label>
        <input type="date" id="event_startdate" name="event_startdate" required><br>
        
        <label for="event_enddate">Select Event End Date:</label>
        <input type="date" id="event_enddate" name="event_enddate"><br>

        <label for="event_starttime">Select Start Time:</label>
        <input type="time" id="event_starttime" name="event_starttime" required><br>
        
        <label for="event_endtime">Select End Time:</label>
        <input type="time" id="event_endtime" name="event_endtime"><br>

        <?php 
            getVenues($conn); // Print Venues as Selectable options
            getUsers($conn); // Print Users as Selectable options
            getTypes($conn); // Print Types as Selectable options
        ?> 

        <label for="image">Select Image:</label>
        <input type="file" name="image" id="image">
        
        <input type="submit" name="submit" value="Add Event">
    </form>

    <?php
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            if(!empty($_POST["event_name"]) && !empty($_POST["event_startdate"]) && !empty($_POST["event_starttime"]) &&
                !empty($_POST["venue_name"]) && !empty($_POST["user_name"])){

                // trim() removes leading and trailing spaces
                $event_name = trim($_POST["event_name"]);
                $event_desc = trim($_POST["event_desc"]);
                $event_startdate = $_POST["event_startdate"];
                $event_enddate = $_POST["event_enddate"];
                $event_starttime = $_POST["event_starttime"];
                $event_endtime = $_POST["event_endtime"];
                
                // ID values from dropdown
                $venue_id = $_POST["venue_name"];
                $user_id = $_POST["user_name"];
                $type_id = $_POST["type_name"];
                
                // Image upload handling
                $imageTmpName = $_FILES['image']['tmp_name'];
                $imageName = $_FILES['image']['name'];
                $folder = "../assets/upload/image/" . $imageName;

                $event_name = validateInput("event_name", $event_name);
                $event_desc = validateInput("event_desc", $event_desc);
                
                // Validate selected options
                checkID($conn, "users", $user_id);
                checkID($conn, "venues", $venue_id);
                checkID($conn, "event_type", $type_id);
                
                if(insertEvent($conn, $event_name, $event_desc, $event_startdate,
                $event_enddate, $event_starttime, $event_endtime, $user_id, $venue_id, $type_id, $imageName)){
                    if (!move_uploaded_file($imageTmpName, $folder)) {
                        echo "<h3>&nbsp; Failed to upload image!</h3>";
                    }
                    // Redirect to display_list.php after successful insertion
                  echo"Event Added";
                    exit();
                }
            }
        }
    ?>
    

    <?php
    // Close the connection
    $pdo = null;
    ?>
</body>
</html>
