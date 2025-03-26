<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "testcal";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Fetch upcoming events
$sql = "SELECT 
e.event_id, 
e.event_name, 
e.event_desc, 
e.event_startdate, 
e.event_starttime, 
v.venue_name,
t.type_name

FROM events  e
LEFT JOIN venues v ON e.venue_id = v.venue_id
LEFT JOIN event_type t ON e.type_id = t.type_id
 WHERE event_startdate >= CURDATE() 
ORDER BY event_startdate ASC";

$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);

$conn->close();
?>
