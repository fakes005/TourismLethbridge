<?php
require_once("../includes/authorization.php"); // only admin authorized to this page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Venue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #5a9bd5; /* Match admin panel theme */
        }
        .popup-content {
            max-width: 500px;
            margin: auto;
            background: #5a9bd5;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 1px 10px rgb(255, 255, 255);
            
        }


        h1 {
            text-align: center;
            color: black;
        }
        
       
        form {
            display: flex;
            flex-direction: column;
            width: 100%;
            overflow:hidden;
            
        }
        label {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        input[type="text"] {
            width: 97%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 10px;
         
        }
        input[type="submit"] {
            width: 100%;
            padding: 20px;
            background-color:rgb(255, 255, 255);
            color: black;
            border: none;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.7);
            font-size: 16px;
            cursor: pointer;
            overflow: hidden;
            
        }
        input[type="submit"]:hover {
            background-color:rgb(43, 255, 0);
        }



    </style>
</head>
<body>
    <div class="popup-content">
       
        <h1>Add Venue</h1>
        
      
        <form action="addVenue.php" method="post">
            
            <!-- Input field for venue name -->
            <label for="venue_name">Venue Name:</label>
            <input id="venue_name" type="text" maxlength="50" name="venue_name" required>
            
          
            <label for="venue_address">Venue Address:</label>
            <input id="venue_address" type="text" maxlength="150" name="venue_address">
          
            <input type="submit" name="submit" value="Add Venue">
        </form>

        <?php
        // Check if the form is submitted using POST method
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            
            // Ensure the venue name field is not empty before processing
            if(!empty($_POST["venue_name"])){
                
                // Trim extra spaces from input values
                $vName = trim($_POST["venue_name"]);
                $vAddress = trim($_POST["venue_address"]);

                // Validate input data
                validateInput("venue_name", $vName);
                validateInput("venue_address", $vAddress);
                
                // Insert the venue into the database
                insertVenue($conn, $vName, $vAddress);
                
                // Display success message
                echo "<p style='color:green; text-align:center;'>Venue added successfully!</p>";
            } else {
                // Display error message if venue name is empty
                echo "<p style='color:red; text-align:center;'>Please enter a Venue Name at least!</p>";
            }
        }
        ?>

        <?php
        // Close the database connection
        $pdo = null;
        ?>
    </div>
</body>
</html>

