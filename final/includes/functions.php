<?php
    try{
        // Validate Input
        function validateInput($x, $input) {
            switch($x){
                case "user_name":
                    // Allow only alphanumeric characters, underscores, and hyphens, with a length between 3-20 characters
                    if (!preg_match("/^[a-zA-Z0-9_-]{3,20}$/", $input)) {
                        exit("Username: Only alphanumeric characters, underscores, and hyphens, with a length between 3-20 characters"); // invalid username
                    }
                    return $input;
                    break;
                case "user_email":
                    // Sanitize the email by removing invalid characters
                    $sanitizedEmail = filter_var($input, FILTER_SANITIZE_EMAIL);

                    if (!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
                        exit("Invalid Email"); // Exit script if email invalid
                    }
                    return $sanitizedEmail; //Return valid email
                    break;
                case "event_name":
                    // Regex Validation (only letters and spaces, length 3-100)
                    if (!preg_match("/^[A-Za-z\s]{3,50}$/", $input)) {
                        exit("Event name must be between 3 and 50 characters and contain only letters and spaces.");
                    }
                    return $input;
                    break;
                case "event_desc":
                    if (strlen($input) < 5 || strlen($input) > 500) {
                        exit("Error: Event description must be between 10 and 200 characters.");
                    }
                    return $input;
                    break;
                case "venue_name":
                    // Regex Validation (only letters and spaces, length 3-100)
                    if (!preg_match("/^[A-Za-z\s]{3,50}$/", $input)) {
                        exit("Venue name must be between 3 and 50 characters and contain only letters and spaces.");
                    }
                    return $input;
                    break;
                case "venue_address":
                    // Regex for Address Validation (allow letters, numbers, spaces, commas, hyphens, periods)
                    if (!preg_match("/^[A-Za-z0-9\s,.-]{3,150}$/", $input)) {
                        exit("Address must be between 3 and 150 characters and only contain letters, numbers, spaces, commas, periods, and hyphens.");
                    }
                    return $input;
                    break;
                default:
                    exit("Invalid Data Type");
            }

        }



        function hashPassword($pass){
            $password = password_hash($pass, PASSWORD_DEFAULT);
            return $password; // return encrypted password
        }

        // Insert Data into db
        function insertVenue($conn, $name, $address){
            if(!checkRow($conn, "venues", $name)){
                $sql = "INSERT INTO `venues` (`venue_name`, `venue_address`) VALUES ('$name', '$address')";
                $stmt = $conn->prepare($sql);
                if($stmt->execute()){ // continue if venue added
                    echo "Successfully Added Venue!";
               
                    exit();
                }
            }else {
                "Venue already exists!";
                exit();
            }
        }
        function insertUser($conn, $username, $email, $perm, $selectedVenue, $password){
            if(!checkRow($conn, "users", $email)){
                $sql = "INSERT INTO `users` (`user_name`, `user_email`, `perm_level`, `venue_id`) 
                VALUES ('$username', '$email', '$perm', '$selectedVenue')";
                $stmt = $conn->prepare($sql);

                if($stmt->execute()){ // continue if user added
                    //add credential after user is added
                    insertCredentials($conn, $email, $password);
                    echo "Successfully Added User!";
                    header("Refresh: 1"); 
                    exit();
                }
            }else {
                "Email already used. Please choose another!";
                exit();
            }
        }
        function insertCredentials($conn, $email, $password ){
            $id = null;
            $sql = "SELECT user_id FROM users WHERE user_email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            if($stmt->execute()){
                // Fetch password and store in variable
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $id = $row["user_id"]; // password from db
                }
            }

            $sql = "INSERT INTO `credentials` (`user_email`, `user_id`, `user_password`) 
            VALUES ('$email', '$id', '$password')";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        function insertEvent($conn, $event_name, $event_desc, $event_startdate,
        $event_enddate, $event_starttime, $event_endtime, $user_id, $venue_id, $type_id, $img){
        
            $sql = "INSERT INTO `events` 
            (`event_name`, `event_desc`, `event_startdate`, `event_enddate`, `event_starttime`, 
            `event_endtime`, `user_id`, `venue_id`, `type_id`, `image_name`) 
            VALUES (:event_name, :event_desc, :event_startdate, :event_enddate, :event_starttime, 
            :event_endtime, :user_id, :venue_id, :type_id, :img)";
    
            $stmt = $conn->prepare($sql);
            
            // Bind all values properly
            $stmt->bindParam(":event_name", $event_name, PDO::PARAM_STR);
            $stmt->bindParam(":event_desc", $event_desc, PDO::PARAM_STR);
            $stmt->bindParam(":event_startdate", $event_startdate, PDO::PARAM_STR);
            $stmt->bindParam(":event_enddate", $event_enddate, PDO::PARAM_STR);
            $stmt->bindParam(":event_starttime", $event_starttime, PDO::PARAM_STR);
            $stmt->bindParam(":event_endtime", $event_endtime, PDO::PARAM_STR);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam(":venue_id", $venue_id, PDO::PARAM_INT);
            $stmt->bindParam(":type_id", $type_id, PDO::PARAM_INT);
            $stmt->bindParam(":img", $img, PDO::PARAM_STR);
            
            if($stmt->execute()){
                echo "Successfully Added Event!";
                header("Refresh: 1");
                return true; 
                exit();
            }
        }

        //For Login authentication
        function verifyPassword($conn, $email, $pass){
            $sql = "SELECT user_password FROM credentials WHERE user_email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);

            if($stmt->execute()){
                $ps = null;
                // Fetch password and store in variable
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $ps = $row["user_password"]; // password from db
                }
            }

            if(!empty($ps)){
                if(password_verify($pass, $ps)){ // compare passwords
                    $_SESSION["permLevel"] = getPermLevel($conn, $email); // 1 for Stakeholder, 2 for Admin
                    return true; // correct password
                }else{
                    return false; // incorrect password
                }
            } else {
                exit("No account found!");
            }
        }
        function getPermLevel($conn, $email){
            $sql = "SELECT perm_level FROM users WHERE user_email = :email";
            $stmt = $conn->prepare($sql);
            $stmt ->bindParam(":email", $email, PDO::PARAM_STR);
            
            if($stmt->execute()){
                $pmL = null;
                // Fetch perm level and store in variable
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $pmL = $row["perm_level"]; // perm level from db
                }
            }
            if(!empty($pmL)){
                return $pmL;
            } else{
                exit("No permission found!");
            }
        }

        //Show Data
        function showUsers($conn){
            $userColumns = ["user_id", "user_name", "user_email", "perm_level","venue_id" ];

            // obtain keys and values of $table_name
            $sql = "SELECT * FROM users";
            $stmt = $conn->prepare($sql);

            if($stmt->execute()){
                $rows = [];
                // Fetch records and store them in the array
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rows[] = $row;
                }

                if(!empty($rows)){
                    //display
                    echo "<table><tr>";
                    foreach($userColumns as $column){ // print column names
                        echo "<th>". htmlspecialchars($column, ENT_QUOTES, 'UTF-8'). "</th>"; // htmlspecialchars() prevents XSS 
                    }
                    echo "</tr>";
            
                    foreach($rows as $row){ // print columns's value;
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["user_id"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row["user_name"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row["user_email"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row["perm_level"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row["venue_id"], ENT_QUOTES, 'UTF-8') . "</td>";    
                        echo "</tr>";
                    }  

                    echo "</table>";
                } else{
                    "No User(s) found!";
                }
            }
        }
        function showEvents($conn){
            $eventColumns = ["event_id", "event_name", "event_desc", "event_startdate",
                            "event_enddate", "event_starttime", "event_endtime", "event_attendance",
                            "user_id", "venue_id", "type_id"];

            // obtain keys and values of $table_name
            $sql = "SELECT * FROM events";
            $stmt = $conn->prepare($sql);

            if($stmt->execute()){
                $rows = [];
                // Fetch records and store them in the array
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rows[] = $row;
                }

                if(!empty($rows)){
                    //display
                    echo "<table><tr>";
                    foreach($eventColumns as $column){ // print column names
                        echo "<th>". htmlspecialchars($column, ENT_QUOTES, 'UTF-8') . "</th>";
                    }
                    echo "</tr>";

                    foreach($rows as $row){ // print columns's value;
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["event_id"], ENT_QUOTES, 'UTF-8') . "</td>";  
                        echo "<td>". htmlspecialchars($row["event_name"], ENT_QUOTES, 'UTF-8') . "</td>";  
                        echo "<td>". htmlspecialchars($row["event_desc"], ENT_QUOTES, 'UTF-8') . "</td>"; 
                        echo "<td>". htmlspecialchars($row["event_startdate"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>". htmlspecialchars($row["event_enddate"], ENT_QUOTES, 'UTF-8') . "</td>"; 
                        echo "<td>". htmlspecialchars($row["event_starttime"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>". htmlspecialchars($row["event_endtime"], ENT_QUOTES, 'UTF-8') . "</td>"; 
                        echo "<td>". htmlspecialchars($row["event_attendance"], ENT_QUOTES, 'UTF-8') . "</td>";  
                        echo "<td>". htmlspecialchars($row["user_id"], ENT_QUOTES, 'UTF-8') . "</td>"; 
                        echo "<td>". htmlspecialchars($row["venue_id"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>". htmlspecialchars($row["type_id"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "</tr>";
                    }  

                    echo "</table>";
                } else{
                    "No Event(s) found!";
                }
            }
        }

        function showEventType($conn){
            $eventTypeColumns = ["type_id", "type_name"];

            // obtain keys and values of $table_name
            $sql = "SELECT * FROM event_type";
            $stmt = $conn->prepare($sql);

            if($stmt->execute()){
                $rows = [];
                // Fetch records and store them in the array
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rows[] = $row;
                }

                if(!empty($rows)){
                    //display
                    echo "<table><tr>";
                    foreach($eventTypeColumns as $column){ // print column names
                        echo "<th>". htmlspecialchars($column, ENT_QUOTES, 'UTF-8') . "</th>";
                    }
                    echo "</tr>";
            
                    foreach($rows as $row){ // print columns's value;
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["type_id"], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row["type_name"], ENT_QUOTES, 'UTF-8') . "</td>"; 
                        echo "</tr>";
                    }  

                    echo "</table>";
                } else{
                    "No Event Type(s) found!";
                }
            } 
        }
        function showVenues($conn){
            $venueColumns = ["venue_id", "venue_name", "venue_address"];

            // obtain keys and values of $table_name
            $sql = "SELECT * FROM venues";
            $stmt = $conn->prepare($sql);

            if($stmt->execute()){
                $rows = [];
                // Fetch records and store them in the array
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rows[] = $row;
                }

                if(!empty($rows)){
                    //display
                    echo "<table><tr>";
                    foreach($venueColumns as $column){ // print column names
                        echo "<th>" . htmlspecialchars($column, ENT_QUOTES, 'UTF-8') . "</th>";
                    }
                    echo "</tr>";
            
                    foreach($rows as $row){ // print columns's value;
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["venue_id"], ENT_QUOTES, 'UTF-8') . "</td>"; 
                        echo "<td>" . htmlspecialchars($row["venue_name"], ENT_QUOTES, 'UTF-8') . "</td>"; 
                        echo "<td>" . htmlspecialchars($row["venue_address"], ENT_QUOTES, 'UTF-8') . "</td>";  
                        echo "</tr>";
                    }  

                    echo "</table>";
                } else{
                    "No venue(s) found!";
                }
            }
        }

        // Select Option
        function getVenues($conn){
            $sql = "SELECT venue_id, venue_name FROM venues";
            $stmt = $conn->prepare($sql);
            $venues = [];

            // Show Veneue as selectable option
            echo "<label for='venue_names'>Select a venue: </label>";
            echo "<select name='venue_name' id='venue_names' required>\n";
            if($stmt->execute()){
                // Fetch records and store them in the array
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $venues[] = $row;
                }
                if(!empty($venues)){
                    //display
                    foreach($venues as $venue){ // print values
                    echo "<option value='". htmlspecialchars($venue['venue_id'], ENT_QUOTES, 'UTF-8') . "'>" 
                    . htmlspecialchars($venue['venue_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
                    }     
                }
            }
            echo "</select>";
        }
        function getUsers($conn){
            $sql = "SELECT user_id, user_name FROM users";
            $stmt = $conn->prepare($sql);
            $users = [];

            // Show User as selectable option
            echo "<label for='user_names'>Assign a User: </label>";
            echo "<select name='user_name' id='user_names' required>\n";
            if($stmt->execute()){
                // Fetch records and store them in the array
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $users[] = $row;
                }
                if(!empty($users)){
                    //display
                    foreach($users as $user){ // print values
                    echo "<option value='". htmlspecialchars($user['user_id'], ENT_QUOTES, 'UTF-8') . "'>" 
                    . htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
                    }     
                }
            }
            echo "</select>";
        }


        function getTypes($conn){
            $sql = "SELECT type_id, type_name FROM event_type";
            $stmt = $conn->prepare($sql);
            $venues = [];

            // Show Veneue as selectable option
            $noneType = 1;
            echo "<label for='type_names'>Select a Event Type: </label>";
            echo "<select name='type_name' id='type_names'>\n";
            echo "<option value='". htmlspecialchars($noneType, ENT_QUOTES, 'UTF-8') . "'>None</option>"; 
            if($stmt->execute()){
                // Fetch records and store them in the array
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $venues[] = $row;
                }
                if(!empty($venues)){
                    //display
                    foreach($venues as $venue){ // print values
                    echo "<option value='". htmlspecialchars($venue['type_id'], ENT_QUOTES, 'UTF-8') . "'>" 
                    . htmlspecialchars($venue['type_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
                    }     
                }
            }
            echo "</select>";
        }
        
        //check for record
        function checkRow($conn, $table_name, $data){
            switch($table_name){
                case "users":
                    $sql = "SELECT * FROM users WHERE user_email = :email";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":email", $data, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        return true;
                    }
                    break;
                case "venues":
                    $sql = "SELECT * FROM venues WHERE venue_name = :v_Name";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":v_Name", $data, PDO::PARAM_STR);
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) {
                        return true;
                    }
                    break;
                default:
                    echo "Table does not exist!";
            }
        }
        
        // Check for ID, to make sure selected option exists
        function checkID($conn, $table_name, $data){
            switch($table_name){
                case "users":
                    $sql = "SELECT * FROM users WHERE user_id = :u_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":u_id", $data, PDO::PARAM_INT);
                    $stmt->execute();
                    if (!($stmt->rowCount() > 0)) {
                        exit("Selected User not found!");
                    }
                    break;
                case "venues":
                    $sql = "SELECT * FROM venues WHERE venue_id = :v_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":v_id", $data, PDO::PARAM_INT);
                    $stmt->execute();
                    if (!($stmt->rowCount() > 0)) {
                        exit("Selected Venue not found!");
                    }
                    break;
                case "event_type":
                    if($data){
                        $sql = "SELECT * FROM event_type WHERE type_id = :t_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":t_id", $data, PDO::PARAM_INT);
                        $stmt->execute();
                        if (!($stmt->rowCount() > 0)) {
                            exit("Selected Type not found!");
                        }
                    } else {return $data;}
                    break;   
                default:
                    echo "Table does not exist!";
            }
        }
    
    }catch(PDOException $e){
        echo "Oops! There was a SQL error.";
        error_log("SQL Error: " . $e->getMessage()); // Log error for debugging
        exit();  // exit the script 
    }

   
?>