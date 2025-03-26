<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testcal";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $event_id = $_POST['event_id'];
        $event_name = $_POST['event_name'];
        $venue_name = $_POST['venue_name'];
        $event_startdate = $_POST['event_startdate'];
        $event_desc = $_POST['event_desc'];
        $event_starttime = $_POST['event_starttime'];
        $event_endtime = $_POST['event_endtime'];
        $event_enddate = $_POST['event_enddate'];

        // Get the venue_id based on the venue_name
        $venue_query = "SELECT venue_id FROM venues WHERE venue_name = ?";
        $venue_stmt = $pdo->prepare($venue_query);
        $venue_stmt->execute([$venue_name]);
        $venue = $venue_stmt->fetch(PDO::FETCH_ASSOC);

        if ($venue) {
            $venue_id = $venue['venue_id'];
        } else {
            echo "Venue not found!";
            exit;
        }

        // Update the event in the database
        $query = "UPDATE events SET event_name = ?, venue_id = ?, event_startdate = ?, event_desc = ?, event_starttime = ?, event_endtime = ?, event_enddate = ? WHERE event_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$event_name, $venue_id, $event_startdate, $event_desc, $event_starttime, $event_endtime, $event_enddate, $event_id]);

        echo "Event updated successfully!";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
