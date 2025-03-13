<?php
session_start();
require_once("../includes/db.php"); // Ensure this is the correct path to your database connection

if (!isset($_SESSION['user_id']) || $_SESSION['perm_level'] != 1) {
    echo "You are not authorized to access this page! Redirecting in 3 seconds....";
    header("refresh:3;url=../index.php");
    exit();
}

// Debugging - Check if the connection is working
if (!$conn) {
    die("Database connection error!");
}
?>


<!-- Html Page --> 
<style>
    table {
    width: 100%;
    border-collapse: collapse; /* Makes borders collapse into one */
}
th, td {
    border: 1px solid black;
    padding: 8px;
    text-align: center; /* Horizontally centers the content */
    vertical-align: middle; /* Vertically centers the content */
}
ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
}
li {
    display: inline;
    font-size: 20px;
}
</style>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<nav>
    <h1>User</h1>
    <ul>
        <li><a href='../index'>Home</a></li>
        <li><a href="../login">Login</a></li>       
</nav>
<br>
<?php
// Ensure database connection is included
require_once("../includes/db.php");

// Get the logged-in user's ID
$id = $_SESSION["user_id"];

// Prepare the SQL statement
$sql = "SELECT 
            e.event_id, e.event_name, e.event_desc, 
            e.event_startdate, e.event_enddate, 
            e.event_starttime, e.event_endtime, 
            e.event_attendance, v.venue_address, t.type_name
        FROM events e
        LEFT JOIN venues v ON e.venue_id = v.venue_id
        LEFT JOIN event_type t ON e.type_id = t.type_id
        WHERE e.user_id = :u_id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(":u_id", $id, PDO::PARAM_INT);
$stmt->execute();

// Fetch all events
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($events)) {
    echo "No events found!";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Description</th><th>Start Date</th><th>End Date</th><th>Start Time</th><th>End Time</th><th>Attendance</th><th>Address</th><th>Category</th></tr>";
    foreach ($events as $event) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($event["event_id"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["event_name"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["event_desc"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["event_startdate"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["event_enddate"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["event_starttime"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["event_endtime"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["event_attendance"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["venue_address"]) . "</td>";
        echo "<td>" . htmlspecialchars($event["type_name"]) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
?>


<?php
// Close the connection
$pdo = null;
?>