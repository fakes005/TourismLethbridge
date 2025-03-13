<?php
require_once("../includes/authorization.php"); // only admin authorized to this page
?>
<style>
    body{
        background-color: #5a9bd5;
    }
   
    /* Style for form elements */
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

/* Label styles */
label {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 5px;
}
/* Popup container background */
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

/* Input fields */
input[type="text"], 
input[type="email"], 
input[type="password"], 
select {
    width: 100%; /* Make fields take full width */
    padding: 12px; /* Increase padding for bigger fields */
    font-size: 18px; /* Increase font size */
    border: 1px solid #ccc;
    border-radius: 1px;
    color: black;
    background-color: white;
    margin-bottom: 10px; /* Add spacing between fields */
}

/* Submit button */
input[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: white;
    color:rgb(0, 0, 0);
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
              

<form action="addUser.php" method="post">
    <label for="user_name">Username:</label>
    <input id="user_name" type="text" maxlength="20" name="user_name" required>
    <label for="user_email">Email:</label>
    <input id="user_email" type="email" name="user_email" required>
    <label for="user_password">Password:</label>
    <input id="user_password" type="password" name="user_password" required>
    <?php getVenues($conn);?>

    <label for="perm_levels">Select a role:</label>
    <select name='perm_level' id='perm_levels'>
        <option value="1">Stakeholder</option>
        <option value="2">Admin</option>
    </select>
    <input type="submit" name="submit" value="Add User">
</form>

<?php
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(!empty($_POST["user_name"]) && !empty($_POST["user_email"]) && !empty($_POST["user_password"])){
            // trim() removes beg. and end space before further validation/sanitization
            $username = trim($_POST["user_name"]);
            $email = trim($_POST["user_email"]);
            $password = trim($_POST["user_password"]);
            $perm = $_POST["perm_level"];
            $selectedVenue = $_POST["venue_name"];

            $username = validateInput("user_name",$username); // Alphanumeric characters, underscores, and hyphens, with a length between 3-20 characters are allowed
            $email = validateInput("user_email", $email); // Remove unwanted chars and ensure it is valid email
            $password = hashPassword($password); // Get encrypted password
            insertUser($conn, $username, $email, $perm, $selectedVenue, $password); // Insert user into db
        }else{
            echo "Please enter Username, Email, and Password!";
        }
    }
?>


<?php
// Close the connection
$pdo = null;
?>