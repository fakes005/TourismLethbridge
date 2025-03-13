<?php
    require_once("../includes/authorizeUser.php"); // only stakeholder
?>
<style>
ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
}
li {
    display: inline;
    font-size: 20px;
}
body{
    text-align: center;
            font-family: Arial, sans-serif;
            background-color: #5a9bd5;
            color: black;
            overflow-x: hidden; /* Prevent horizontal scrolling */
            width: 100vw;
}
</style>
<nav>
    <h1>Update Event</h1>
    <ul>
        <li><a href='../index'>Home</a></li>
        <li><a href='myEvents'>My Events</a></li>
        <li><a href="../login">Login</a></li>
    </ul>          
</nav>
<br>
<?php
    try{
        $id = $_GET['id'];
        $e_row = [];
        $sql = "SELECT 
        events.event_name, 
        events.event_desc, 
        events.event_startdate, 
        events.event_enddate, 
        events.event_starttime, 
        events.event_endtime,
        events.event_attendance,
        venues.venue_address, 
        event_type.type_name
    FROM events
    LEFT JOIN venues ON events.venue_id = venues.venue_id
    LEFT JOIN event_type ON events.type_id = event_type.type_id 
    WHERE events.event_id = :e_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":e_id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $e_row = [];
        $type_row = [];
        $current_date = date("Y-m-d");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $e_row[0] = $row["event_name"]; $e_row[1] = $row["event_desc"]; $e_row[2] = $row["event_startdate"];
            $e_row[3] = $row["event_enddate"]; $e_row[4] = $row["event_starttime"]; $e_row[5] = $row["event_endtime"];
            $e_row[6] = $row["event_attendance"]; $e_row[7] = $row["venue_address"]; $e_row[8] = $row["type_name"];
        }

        // Get all types
        $stmt = $conn->prepare("SELECT * FROM event_type");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
           $type_row[] = $row;
        }

        // Determine if the event has ended
        $isEnded = strtotime($e_row[3]) < strtotime($current_date);

        echo "<form action='update_Event' method='post' enctype='multipart/form-data'>";
        echo "<input type='hidden' name='event_id' value='" . htmlspecialchars($id) . "'>";
        // echo "
        // <label for='event_name'>Event Name:</label>
        // <input type='text' id='event_name' value={$e_row[0]} disabled><br>
        //  <label for='venue_address'>Event Address:</label>
        // <input type='text' id='venue_address' value='{$e_row[7]}' disabled><br>";
        echo "
        <label for='event_desc'>Event Description:</label><br>
        <textarea id='event_desc' name='event_desc' maxlength='500'}>{$e_row[1]}</textarea><br><br>";
        echo "<div " . ($isEnded ? "style='display: none;'" : "") . ">
            <label for='event_startdate'>Event Start Date:</label>
            <input type='date' id='event_startdate' name='event_startdate' value='{$e_row[2]}'><br><br>
            <label for='event_enddate'>Event End Date:</label>
            <input type='date' id='event_enddate' name='event_enddate' value='{$e_row[3]}'><br><br>

            <label for='event_starttime'>Start Time:</label>
            <input type='time' id='event_starttime' name='event_starttime' value='{$e_row[4]}'><br><br>
            <label for='event_endtime'>Select End Time:</label>
            <input type='time' id='event_endtime' name='event_endtime'  value='{$e_row[5]}'><br><br>";
            
            echo "<label for='type_names'>Select an Event Type: </label>";
            echo "<select name='type_name' id='type_names' required>\n";
                //display

            foreach ($type_row as $type) {
                $selected = ($e_row[8] == $type['type_name']) ? "selected" : "";
                echo "<option value='" . htmlspecialchars($type['type_id'], ENT_QUOTES, 'UTF-8') . "' $selected>" // Show current type as preselected
                    . htmlspecialchars($type['type_name'], ENT_QUOTES, 'UTF-8') . "</option>\n";
            }
            echo "</select></div>";
        
        if($isEnded){
         echo "<label for='event_attendance'>Attendance:</label>
            <input type='text' id='event_attendance' name='event_attendance' value={$e_row[6]}><br><br>";
        }
        
        echo "<label for='image'>Select Image:</label>
        <input type='file' name='image' id='image'>";
        
        echo "<input type='submit' name='submit' value='Update Event'></form>";
        
        //Reset not Undo
        echo "<form method='post'><input type='submit' name='reset' value='Reset'></form>";
        if(isset($_POST['reset'])){
            header("Location: updateEventForm.php?id={$id}&Reset=1");
        }
    }catch(PDOException $e){
        echo "Oops! There was a SQL error.";
        error_log("SQL Error: " . $e->getMessage()); // Log error for debugging
        exit();  // exit the script 
    }
?>

<?php
// Close the connection
$pdo = null;
?>