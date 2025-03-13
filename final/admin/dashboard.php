<?php
require_once("../includes/authorization.php"); // only admin authorized to this page
try{
    // Initialize session variables if not set
    if (!isset($_SESSION['showUsers'])) {
        $_SESSION['showUsers'] = false;
    }

    if (!isset($_SESSION['showEvents'])) {
        $_SESSION['showEvents'] = false;
    }

    if (!isset($_SESSION['showEventType'])) {
        $_SESSION['showEventType'] = false;
    }

    if (!isset($_SESSION['showVenues'])) {
        $_SESSION['showVenues'] = false;
    }

    // Toggle visibility based on the form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        switch($_POST['column']){
            case "users":
                $_SESSION['showUsers'] = !$_SESSION['showUsers'];
                break;
            case "events":
                $_SESSION['showEvents'] = !$_SESSION['showEvents']; 
                break;
            case "event_type":
                $_SESSION['showEventType'] = !$_SESSION['showEventType']; 
                break;
            case "venues":
                $_SESSION['showVenues'] = !$_SESSION['showVenues']; 
            default:
        }
    }
    }
    catch(PDOException $e){
        echo "Oops! There was a database error.";
        error_log("SQL Error: " . $e->getMessage()); // Log error for debugging
        exit();
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
    <h1>Admin</h1>
    <ul>
        <li><a href='../index'>Home</a></li>
        <li><a href="addUser">Add User</a></li>     
        <li><a href="addVenue">Add Venue</a><li> 
        <li><a href="addEvent">Add Event</a><li> 
        <li><a href="../login">Login</a></li>       
</nav>
<main>
    <br>
    <!-- Show Users Button -->
    <form action="dashboard" method="post">
        <input type="hidden" name="column" value="users">
        <input type="submit" value="<?php echo $_SESSION['showUsers'] ? 'Hide Users' : 'Show Users'; ?>">
    </form>

    <!-- Show Events Button -->
    <form action="dashboard" method="post">
        <input type="hidden" name="column" value="events">
        <input type="submit" value="<?php echo $_SESSION['showEvents'] ? 'Hide Events' : 'Show Events'; ?>">
    </form>

    <!-- Show Event_Type Button -->
    <form action="dashboard" method="post">
            <input type="hidden" name="column" value="event_type">
            <input type="submit" value="<?php echo $_SESSION['showEventType'] ? 'Hide Event Type' : 'Show Event Type'; ?>">
    </form>

    <!-- Show Venues Button -->
    <form action="dashboard" method="post">
        <input type="hidden" name="column" value="venues">
        <input type="submit" value="<?php echo $_SESSION['showVenues'] ? 'Hide Venues' : 'Show Venues'; ?>">
    </form>

    <div>
        <?php
        // Show Users Table
        if ($_SESSION['showUsers']) {
            echo "<h2>Users Table</h2>";
            showUsers($conn);
        }

        // Show Events Table
        if ($_SESSION['showEvents']) {
            echo "<h2>Events Table</h2>";
            showEvents($conn);
        }

        // Show EventType Table
        if ($_SESSION['showEventType']) {
            echo "<h2>Event Type Table</h2>";
            showEventType($conn);
        }
        
        // Show Venues Table
        if ($_SESSION['showVenues']) {
            echo "<h2>Venues Table</h2>";
            showVenues($conn);
        }    
        ?>
    </div>
</main>

<?php
// Close the connection
$pdo = null;
?>