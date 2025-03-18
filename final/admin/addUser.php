<?php
// Include the authorization file to ensure only admins can access this page
require_once("../includes/authorization.php"); // only admin authorized to this page
?>


<style>
    
    body {
        background-color: #5a9bd5;
    }

 
    form {
        display: flex;
        flex-direction: column;
        align-items: flex-start; /* Align fields to the left */
        max-width: 90%;
        width: 500px;
        padding: 20px;
        border: 1px solid white;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.5);
        background-color: #5a9bd5;
        margin: auto; /* Centers the form */
    }


    label {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }

  
    #popupContainer {
        background: #5a9bd5; /* Blue popup background */
        padding: 20px;
        border-radius: 1px;
        width: 500px;
        height: 600px;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
    }

   
    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
        width: 100%; 
        padding: 12px;
        font-size: 18px; 
        border: 1px solid #ccc;
        border-radius: 1px;
        color: black;
        background-color: white;
        margin-bottom: 10px; /* Add spacing between fields */
    }

    /* Style for the submit button */
    input[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: white;
        color: rgb(0, 0, 0);
        border: none;
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
    }

  
    input[type="submit"]:hover {
        background-color: rgb(43, 255, 0);
    }


    h1 {
        text-align: center;
    }
</style>


<h1>Add User</h1>

<!-- Form to add a new user -->
<form action="addUser.php" method="post">
    <!-- Username input field -->
    <label for="user_name">Username:</label>
    <input id="user_name" type="text" maxlength="20" name="user_name" required>

    <!-- Email input field -->
    <label for="user_email">Email:</label>
    <input id="user_email" type="email" name="user_email" required>

    <!-- Password input field -->
    <label for="user_password">Password:</label>
    <input id="user_password" type="password" name="user_password" required>

    <!-- Call a PHP function to get venues (assumed to be defined elsewhere) -->
    <?php getVenues($conn); ?>

    <!-- Dropdown to select user role -->
    <label for="perm_levels">Select a role:</label>
    <select name='perm_level' id='perm_levels'>
        <option value="1">Stakeholder</option>
        <option value="2">Admin</option>
    </select>

    <!-- Submit button -->
    <input type="submit" name="submit" value="Add User">
</form>

<?php
    // Check if the form is submitted using POST method
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Ensure all required fields are filled
        if (!empty($_POST["user_name"]) && !empty($_POST["user_email"]) && !empty($_POST["user_password"])) {
            // Trim whitespace from inputs
            $username = trim($_POST["user_name"]);
            $email = trim($_POST["user_email"]);
            $password = trim($_POST["user_password"]);
            $perm = $_POST["perm_level"];
            $selectedVenue = $_POST["venue_name"];

            // Validate and sanitize inputs
            $username = validateInput("user_name", $username); // Validate username (alphanumeric, underscores, hyphens, 3-20 chars)
            $email = validateInput("user_email", $email); // Validate and sanitize email
            $password = hashPassword($password); // Hash the password for security

            // Insert the user into the database
            insertUser($conn, $username, $email, $perm, $selectedVenue, $password);
        } else {
            // Display an error message if any required field is empty
            echo "Please enter Username, Email, and Password!";
        }
    }
?>

<?php
// Close the database connection
$pdo = null;
?>
