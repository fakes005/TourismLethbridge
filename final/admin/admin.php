<?php
// Include the authorization script to ensure only admins can access this page
require_once("../includes/authorizeAdmin.php"); 

// Determine the home URL based on the session variable 'current_page', defaulting to 'index.php' if not set
$home_url = isset($_SESSION['current_page']) ? "../" . $_SESSION['current_page'] : 'index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/upload/styles.css"> <!-- Link to external stylesheet -->

    <script>
        // Function to open a popup overlay containing an iframe for loading an external page
        function openPopup(pageURL) {
            let overlay = document.createElement("div");
            overlay.id = "popupOverlay";
            overlay.innerHTML = `
                <div id="popupContainer">
                    <iframe src="${pageURL}" frameborder="0"></iframe>
                    <button onclick="closePopup()">Close</button>
                </div>
            `;
            document.body.appendChild(overlay); // Append overlay to body
        }

        // Function to close the popup overlay
        function closePopup() {
            document.getElementById("popupOverlay").remove(); // Remove the overlay element
        }

        // Function to display the logout confirmation modal
        function openLogoutModal() {
            document.getElementById("logoutModal").style.display = "block";
        }

        // Function to close the logout confirmation modal
        function closeLogoutModal() {
            document.getElementById("logoutModal").style.display = "none";
        }

        // Function to confirm logout and redirect to logout page
        function confirmLogout() {
            window.location.href = "../logout.php"; // Redirect to logout script
        }
    </script>

    <style>
     
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            text-align: center;
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
</head>

<!-- Navigation bar with home and logout links -->
<nav>
    <h1>Administrator</h1>
    <ul class="nav-right">
        <li><a href="<?php echo $home_url; ?>">Home</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<body>
    <main>
        <div class="dashboard">
            <!-- Upload CSV Panel -->
            <div class="panel" onclick="openPopup('uploadCSV.php')">
                <img src="images/upload.png" alt="Upload CSV">
                <h3>Upload CSV</h3>
            </div>
            
            <!-- Add Users Panel -->
            <div class="panel" onclick="openPopup('addUser.php')">
                <img src="images/addUser.png" alt="Add Users">
                <h3>Add Users</h3>
            </div>
            
            <!-- Add Event Panel -->
            <div class="panel" onclick="openPopup('addEvent.php')">
                <img src="images/addEvent.png" alt="Add Event">
                <h3>Add Event</h3>
            </div>
            
            <!-- Event Validation Panel (Disabled) -->
            <div class="panel disabled">
                <img src="images/validate.png" alt="Event Validation">
                <h3>Event Validation</h3>
            </div>
            
            <!-- Add Venue Panel -->
            <div class="panel" onclick="openPopup('addVenue.php')">
                <div class="circle-container">
                    <img class="circle-image" src="images/addVenue.png" alt="Add Venue">
                </div>
                <h3>Add Venue</h3>
            </div>
            
            <!-- View Data (Reports) Panel -->
            <div class="panel" onclick="window.location.href='viewData.php'">
                <img src="images/viewData.png" alt="View Data">
                <p>Reports</p>
            </div>
        </div>
    </main>
</body>
</html>
