<?php
// Include the file that checks if the user is an authorized admin
require_once("../includes/authorizeAdmin.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../assets/upload/styles.css">
    
    <style>
        /* Styling for the page */
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
    

    <h1>Add Event</h1>
    
    <!-- Event submission form -->
    <form action="addEvent.php" method="post" enctype="multipart/form-data">
        
        <!-- Input field for event name -->
        <label for="event_name">Event Name:</label>
        <input type="text" id="event_name" name="event_name" maxlength="50" required><br>
        
        <!-- Input field for event description -->
        <label for="event_desc">Event Description:</label>
        <textarea id="event_desc" name="event_desc" maxlength="500"></textarea>

        <!-- Input field for event start date -->
        <label for="event_startdate">Select Event Start Date:</label>
        <input type="date" id="event_startdate" name="event_startdate" required><br>
        
        <!-- Input field for event end date -->
        <label for="event_enddate">Select Event End Date:</label>
        <input type="date" id="event_enddate" name="event_enddate"><br>

        <!-- Input field for event start time -->
        <label for="event_starttime">Select Start Time:</label>
        <input type="time" id="event_starttime" name="event_starttime" required><br>
        
        <!-- Input field for event end time -->
        <label for="event_endtime">Select End Time:</label>
        <input type="time" id="event_endtime" name="event_endtime"><br>

        <?php 
            // Fetch and display venues, users, and event types as dropdown options
            getVenues($conn); 
            getUsers($conn);
            getTypes($conn); 
        ?> 

        <!-- Input field for event image upload -->
        <label for="image">Select Image:</label>
        <input type="file" name="image" id="image">
        
        <!-- Submit button -->
        <input type="submit" name="submit" value="Add Event">
    </form>

    <?php
        // Check if the form is submitted using POST method
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            
            // Ensure required fields are not empty before processing
            if(!empty($_POST["event_name"]) && !empty($_POST["event_startdate"]) && !empty($_POST["event_starttime"]) &&
                !empty($_POST["venue_name"]) && !empty($_POST["user_name"])){

                // Trim extra spaces from input values
                $event_name = trim($_POST["event_name"]);
                $event_desc = trim($_POST["event_desc"]);
                $event_startdate = $_POST["event_startdate"];
                $event_enddate = $_POST["event_enddate"];
                $event_starttime = $_POST["event_starttime"];
                $event_endtime = $_POST["event_endtime"];
                
                // Retrieve selected IDs from dropdowns
                $venue_id = $_POST["venue_name"];
                $user_id = $_POST["user_name"];
                $type_id = $_POST["type_name"];
                
                // Handle image upload
                $imageTmpName = $_FILES['image']['tmp_name'];
                $imageName = $_FILES['image']['name'];
                $folder = "../assets/upload/image/" . $imageName;

                // Validate input data
                $event_name = validateInput("event_name", $event_name);
                $event_desc = validateInput("event_desc", $event_desc);
                
                // Ensure that selected IDs exist in the database
                checkID($conn, "users", $user_id);
                checkID($conn, "venues", $venue_id);
                checkID($conn, "event_type", $type_id);
                
                // Insert the event into the database
                if(insertEvent($conn, $event_name, $event_desc, $event_startdate,
                $event_enddate, $event_starttime, $event_endtime, $user_id, $venue_id, $type_id, $imageName)){
                    
                    // Move the uploaded image to the designated folder
                    if (!move_uploaded_file($imageTmpName, $folder)) {
                        echo "<h3>&nbsp; Failed to upload image!</h3>";
                    }
                    
                    // Display success message and exit
                    echo "Event Added";
                    exit();
                }
            }
        }
    ?>
    
    <?php
    // Close the database connection
    $pdo = null;
    ?>
</body>
</html>
