<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testcal";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['event_id'])) {
        $event_id = $_GET['event_id'];

        $query = "SELECT e.*, v.venue_name 
                  FROM events e
                  LEFT JOIN venues v ON e.venue_id = v.venue_id
                  WHERE e.event_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            echo "Event not found!";
            exit;
        }

        $event_name = $event['event_name'] ?? '';
        $venue_name = $event['venue_name'] ?? '';
        $event_startdate = $event['event_startdate'] ?? '';
        $event_desc = $event['event_desc'] ?? '';
        $event_starttime = $event['event_starttime'] ?? '';
        $event_endtime = $event['event_endtime'] ?? '';
        $event_enddate = $event['event_enddate'] ?? '';
    } else {
        echo "Invalid request!";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $event_id = $_POST['event_id'];
        $event_name = $_POST['event_name'];
        $venue_name = $_POST['venue_name'];
        $event_startdate = $_POST['event_startdate'];
        $event_desc = $_POST['event_desc'];
        $event_starttime = $_POST['event_starttime'];
        $event_endtime = $_POST['event_endtime'];
        $event_enddate = $_POST['event_enddate'];

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

        $update_query = "UPDATE events SET event_name = ?, venue_id = ?, event_startdate = ?, event_desc = ?, event_starttime = ?, event_endtime = ?, event_enddate = ? WHERE event_id = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$event_name, $venue_id, $event_startdate, $event_desc, $event_starttime, $event_endtime, $event_enddate, $event_id]);

        echo "<script>alert('Event updated successfully!'); window.location.href='index.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Event</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      
        .modal-container {
            background: #5c9cd1;
            padding: 20px;
            border-radius: 10px;
            width: `100%;
            max-width: 900px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-height: 1000px;
         
        }
        .btn-save {
            background-color: green;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
        }
        .btn-save:hover {
            background-color: green;
        }
        label {
            font-weight: bold;
        }
		
		
    </style>
</head>

<body>

    <div class="modal-container">
        
        <form method="POST">
            <input type="hidden" name="event_id" value="<?= $event_id ?>">

            <div class="mb-3">
                <label class="form-label">Event Name:</label>
                <input type="text" class="form-control" name="event_name" value="<?= htmlspecialchars($event_name) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Venue:</label>
                <input type="text" class="form-control" name="venue_name" value="<?= htmlspecialchars($venue_name) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Event Description:</label>
                <textarea class="form-control" name="event_desc" required><?= htmlspecialchars($event_desc) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Start Date:</label>
                <input type="date" class="form-control" name="event_startdate" value="<?= $event_startdate ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">End Date:</label>
                <input type="date" class="form-control" name="event_enddate" value="<?= $event_enddate ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Start Time:</label>
                <input type="time" class="form-control" name="event_starttime" value="<?= $event_starttime ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">End Time:</label>
                <input type="time" class="form-control" name="event_endtime" value="<?= $event_endtime ?>" required>
            </div>

            <button type="submit" class="btn-save">Save Changes</button>
        </form>
    </div>

</body>
</html>
