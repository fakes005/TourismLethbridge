<?php
session_start();

// Save the toggle state to the session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $state = $_POST['state'];
    $_SESSION['toggleState'] = $state;
    echo "State saved: " . $state; // Optionally, you can log or debug here
} else {
    // Fetch the current toggle state from the session
    echo isset($_SESSION['toggleState']) ? $_SESSION['toggleState'] : 'calendar';
}
?>
