

<?php
require_once("../includes/authorizeUser.php"); // only admin authorized to this page
$home_url = isset($_SESSION['current_page']) ? "../" . $_SESSION['current_page'] : 'index.php';

    // Handle form submission when the user updates an event
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve and sanitize form inputs
        $id = $_POST["event_id"];
        $event_name = trim($_POST["event_name"]);
        $event_desc = trim($_POST["event_desc"]);
        $event_startdate = $_POST["event_startdate"];
        $event_enddate = $_POST["event_enddate"];
        $event_starttime = $_POST["event_starttime"];
        $event_endtime = $_POST["event_endtime"];
        $event_attendance = !empty($_POST["event_attendance"]) ? trim($_POST["event_attendance"]) : null;

        // Check if 'type_name' exists before accessing it (venue or event category)
        $venue_id = isset($_POST["type_name"]) ? $_POST["type_name"] : null;

        // Handle image upload
        $imageTmpName = isset($_FILES['image']['tmp_name']) ? $_FILES['image']['tmp_name'] : null;
        $imageName = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : null;
        $folder = (!empty($imageName)) ? "../assets/upload/image/" . $imageName : null;
        $isImgEmpty = empty($imageName);

        // Validate inputs
        if (!empty($event_desc) && !isValid("event_desc", $event_desc)) {
            echo "<script>alert('Invalid description');</script>";
        } elseif (!empty($event_attendance) && (!is_numeric($event_attendance) || $event_attendance < 0)) {
            echo "<script>alert('Invalid attendance');</script>";
        } else {
            // Construct SQL query for updating event details
            $sql = "UPDATE events SET 
            event_name = :e_name, 
            event_desc = :e_desc, 
            event_startdate = :e_start,
            event_enddate = :e_end, 
            event_starttime = :e_st, 
            event_endtime = :e_et, 
            event_attendance = :e_attendance,
            venue_id = :e_venue";  // ✅ Use venue_id instead of type_name
        
        if (!$isImgEmpty) {
            $sql .= ", image_name = :i_name";
        }
        $sql .= " WHERE event_id = :e_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":e_venue", $venue_id, PDO::PARAM_INT);
            // Prepare SQL statement
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":e_name", $event_name, PDO::PARAM_STR);
            $stmt->bindParam(":e_desc", $event_desc, PDO::PARAM_STR);
            $stmt->bindParam(":e_start", $event_startdate, PDO::PARAM_STR);
            $stmt->bindParam(":e_end", $event_enddate, PDO::PARAM_STR);
            $stmt->bindParam(":e_st", $event_starttime, PDO::PARAM_STR);
            $stmt->bindParam(":e_et", $event_endtime, PDO::PARAM_STR);
            $stmt->bindParam(":e_attendance", $event_attendance, PDO::PARAM_INT);
            $stmt->bindParam(":e_type", $venue_id, PDO::PARAM_INT);
            
            if (!$isImgEmpty) {
                $stmt->bindParam(":i_name", $imageName, PDO::PARAM_STR);
            }
            
            $stmt->bindParam(":e_id", $id, PDO::PARAM_STR);

            // Execute SQL query
            if ($stmt->execute()) {
                // Move uploaded file to the specified directory
                if (!$isImgEmpty && $imageTmpName) {
                    move_uploaded_file($imageTmpName, $folder);
                }
                echo "<script>alert('Event updated successfully!');</script>";
            } else {
                echo "<script>alert('Error updating event.');</script>";
            }
        }
    }
?>

<style>
    /* Container holding all event cards */
    .event-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: flex-start;
    }

    /* Individual event card styling */
    .event-card {
        background: #ffffff;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        width: 100%; /* Ensure cards take full column width */
        height: 590px; /* Fixed height for uniformity */
        max-width: 450px; /* Prevents excessive stretching */
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        align-items: flex-start; /* Align content to the left */
        text-align: left; /* Align text to the left */
        justify-content: space-between; /* Keeps spacing consistent */
        overflow: hidden; /* Hide overflow */
        position: relative; /* For absolute positioning of child elements */
    }

    .event-details {
        flex-grow: 1; /* Ensures details use remaining space */
        display: flex;
        flex-direction: column;
        justify-content: flex-start; /* Align content to the top */
        padding: 1px 0;
        width: 100%; /* Ensure it takes full width */
        overflow-y: auto; /* Make the content scrollable */
        margin-bottom: -10px;
    }

    .event-details h2 {
        margin: 0 0 10px 0; /* Reduce margin for closer spacing */
        font-size: 1.5em; /* Adjust heading size if needed */
    }

    .event-details p {
        margin: 1px 0; /* Reduce margin for closer spacing */
        font-size: 0.9em; /* Adjust text size if needed */
    }

    .event-image {
        width: 100%;
        height: auto;
        max-height: 200px;
        object-fit: cover;
        border-radius: 5px;
        display: block;
        margin-bottom: 10px;
    }

    .edit-icon {
        width: 25px;
        height: auto;
        position: absolute;
        bottom: 10px; /* Positioned at the bottom of the card */
        right: 10px; /* Aligned to the right side of the card */
        cursor: pointer;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
       
    }

    .modal-content {
        background-image: linear-gradient(to bottom right,#5c9cd1 ,white);
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 20%;
        max-width: 600px;
        border-radius: 8px;
        position: relative;
       
        border-radius:20px;
    }

    .close {
        color: Red;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
        }

        nav h1 {
            margin: 0;
            font-size: 24px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-right li {
            margin: 0;
            padding: 5px;
        }

        .nav-right a {
            text-decoration: none;
            font-size: 18px;
            padding: 10px 15px;
            transition: 0.3s;
            display: flex;
            align-items: center;
        }

        .nav-right a:hover {
            background-color: #ffc20e;
        }

</style>
<body>
<nav>
        <h1>My Events</h1>
        <ul class="nav-right">
            <li><a href="<?php echo $home_url; ?>">Home</a></li>
            <li><a href="../logout.php" id="logoutLink">Logout</a></li>
        </ul>
    </nav>

<?php
    // Get logged-in user's ID
    $id = $_SESSION["user_id"];
    $editImg = "../assets/button/editPencil.jpg"; // Path to the edit pencil icon
    $defaultImg = "../assets/img/default.jpg";
    $encodedDefaultPath = str_replace(" ", "%20", $defaultImg); // Replace spaces with %20 for URL compatibility
    // Query to fetch event details including venue and category
    $sql = "SELECT 
    e.event_id, 
    e.event_name, 
    e.event_desc, 
    e.event_startdate, 
    e.event_enddate, 
    e.event_starttime, 
    e.event_endtime, 
    e.event_attendance,
    e.image_name,  
    COALESCE(v.venue_name, 'Unknown Venue') AS venue_name, 
    COALESCE(v.venue_address, 'No Address') AS venue_address,
    COALESCE(t.type_name, 'No Category') AS type_name
FROM events e
LEFT JOIN venues v ON e.venue_id = v.venue_id
LEFT JOIN event_type t ON e.type_id = t.type_id 
WHERE e.user_id = :u_id";


    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":u_id", $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            echo "<div class='event-container'>";
            foreach ($rows as $row) {
                echo "<div class='event-card'>";
                
                // Display event image if available
                if (!empty($row["image_name"])) {
                    $imagePath = "../assets/upload/image/" . htmlspecialchars($row["image_name"], ENT_QUOTES, 'UTF-8');
                    $encodedPath = str_replace(" ", "%20", $imagePath); // Replace spaces with %20 for URL compatibility
                    

                    // Check if the image file exists before displaying it
                    if (file_exists($imagePath)) {
                        echo "<img class='event-image' src='$encodedPath' alt='Event Image'>";
                    } else {
                        echo "<p>Image Not Found</p>"; // Displays message if image file is missing
                    }
                } else {
                    echo "<img class='event-image' src='$encodedDefaultPath' alt='Event Image'>"; // Displays if no image is assigned
                }

                // Display event details
                echo "<h2>" . htmlspecialchars($row["event_name"], ENT_QUOTES, 'UTF-8') . "</h2>";
                echo "<p><strong>Description:</strong> " . htmlspecialchars($row["event_desc"], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Start:</strong> " . htmlspecialchars($row["event_startdate"], ENT_QUOTES, 'UTF-8') . " at " . htmlspecialchars($row["event_starttime"], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>End:</strong> " . htmlspecialchars($row["event_enddate"], ENT_QUOTES, 'UTF-8') . " at " . htmlspecialchars($row["event_endtime"], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Attendance:</strong> " . htmlspecialchars($row["event_attendance"], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Venue:</strong> " . htmlspecialchars($row["venue_address"], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Category:</strong> " . htmlspecialchars($row["type_name"], ENT_QUOTES, 'UTF-8') . "</p>";
                
                // Edit button (pencil icon) positioned at bottom right of card
                echo "<img class='edit-icon' src='$editImg' alt='Edit' onclick='openModal(" . htmlspecialchars($row["event_id"], ENT_QUOTES, 'UTF-8') . ", \"" . htmlspecialchars($row["event_enddate"], ENT_QUOTES, 'UTF-8') . "\")'>";

                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>No Event(s) found!</p>"; // Display message if no events exist for the user
        }
    }
?>

<!-- Modal -->
<!-- Modal -->

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <form id="editForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="event_id" id="event_id">
            <label for="event_name">Event Name:</label><br>
            <input type="text" id="event_name" name="event_name"><br><br>
            <label for="event_desc">Event Description:</label><br>
            <textarea id="event_desc" name="event_desc" maxlength="500"></textarea><br><br>
            <div id="dateTimeFields">
                <label for="event_startdate">Event Start Date:</label><br>
                <input type="date" id="event_startdate" name="event_startdate"><br><br>
                <label for="event_enddate">Event End Date:</label><br>
                <input type="date" id="event_enddate" name="event_enddate"><br><br>
                <label for="event_starttime">Start Time:</label><br>
                <input type="time" id="event_starttime" name="event_starttime"><br><br>
                <label for="event_endtime">End Time:</label><br>
                <input type="time" id="event_endtime" name="event_endtime"><br><br>
            </div>
            <label for="type_name">Event Type:</label><br>
            <select id="type_name" name="type_name">
                <?php
                    $stmt = $conn->query("SELECT * FROM event_type");
                    while ($type = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($type['type_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($type['type_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                ?>
            </select><br><br>
            <label for="event_attendance">Attendance:</label><br>
            <input type="number" id="event_attendance" name="event_attendance"><br><br>
            <label for="image">Select Image:</label><br>
            <input type="file" name="image" id="image"><br><br>
            <input type="submit" value="Update Event">
        </form>
    </div>
</div>

<!-- JavaScript to handle modal functionality -->
<script>
    // Open modal and populate form with event data
    function openModal(eventId, eventEndDate) {
        const currentDate = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
        const isEventEnded = eventEndDate < currentDate; // Check if the event has already ended

        // Populate form fields with event data
        document.getElementById('event_id').value = eventId;
        
        // Disable input fields if event has ended
        if (isEventEnded) {
            document.getElementById('dateTimeFields').style.display = 'none';
            document.getElementById('type_name').disabled = true;
            document.getElementById('image').disabled = true;
        } else {
            document.getElementById('dateTimeFields').style.display = 'block';
            document.getElementById('type_name').disabled = false;
            document.getElementById('image').disabled = false;
        }

        // Show the modal
        document.getElementById('editModal').style.display = 'block';
    }

    // Close modal function
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        var modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeModal();
        }
    };
</script>


</body>
</html>