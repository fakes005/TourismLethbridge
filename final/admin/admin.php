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
            font-family: "Hudson NY Pro", Arial, sans-serif; /* switch to arial if no hudson */
            margin: 0;
            padding: 0;
            background-color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

       
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            width: 100%;
            padding: 10px 20px;
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
            gap: 10px;
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
            border-radius: 8px;
            padding: 12px 17px;
        }
container {
            display: flex;
            justify-content: space-between;
            width: 90%;
            margin-top: 20px;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(3, 225px);
            gap: 60px;
            padding: 20px;
            justify-content: left;
            flex: 1;
        }

        .card {
            background: black;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.2s ease-in-out;
            cursor: pointer;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 10px 0 0;
            font-size: 16px;
            color: white;
        }

        #event-list-container {
            background: black;
            color: white;
            padding: 15px;
            border-radius: 10px;
            width: 30%;
            max-height: 500px;
            overflow-y: auto;
            position: relative;
			text-align: left;
        }
		
		.event-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid white;
    font-size: 14px;
}

.event-date {
    font-weight: bold;
    width: 50px;
    text-align: center;
}

.event-details {
    flex-grow: 1;
    padding-left: 10px;
}

.event-details strong {
    display: inline-block;
    margin-right: 10px;
}

        .login_logo {
            width: 55px;
            height: auto;
        }

        input[type="file"] {
            display: none;
        }
		
		nav h1 {
    margin-left: 90px;
	
}

table {
    width: 100%;
    border-collapse: collapse;  /* Removes the space between borders */
}

th, td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #fff; /* Horizontal line between rows */
}

th {
    background-color: black;
    color: #ffc20e;
    font-weight: bold;
}

td {
    color: white;
}

td .event-name {
    font-weight: bold;
}



#popupContainer {
    width: 218px;
    max-width: 300px;
    height: 101vh;
    max-height: 101vh;
    
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #5c9cd1;
    padding: 20px;
	overflow: auto;
    
    border-radius: 10px;
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
 <!-- Upcoming Events Section -->
        <div id="event-list-container">
            <h2>Upcoming Events</h2>
            <table id="event-list">
				<thead>
					<tr>
						
					<tr>
				</thead>
				<tbody>
				
				</tbody>
			</table>
			</div>
						
        
    </div>

    <script>
 async function loadEvents() {
            try {
                const response = await fetch("http://localhost/projectFolder/admin/get_events.php");
                const events = await response.json();
                const eventList = document.getElementById("event-list").getElementsByTagName("tbody")[0];
                eventList.innerHTML = "";  // Clear existing events

                if (events.length === 0) {
                    const noEventsRow = document.createElement("tr");
                    noEventsRow.innerHTML = "<td colspan='3'>No upcoming events.</td>";
                    eventList.appendChild(noEventsRow);
                    return;
                }

                events.forEach(event => {
                    const row = document.createElement("tr");

                    // Format date (Day and Month)
                    const eventDate = new Date(event.event_startdate);
                    const day = eventDate.getDate();
                    const month = eventDate.toLocaleString('default', { month: 'short' });

                    row.innerHTML = `
                        <td>${day} ${month}</td>
                        <td><span class="event-name">${event.event_name}</span></td>
                        <td>${event.venue_name}</td>
						<td><button onclick="openPopup(${event.event_id})">Edit</button></td>
                    `;

                    eventList.appendChild(row);
                });
            } catch (error) {
                console.error("Error loading events:", error);
            }
        }
		
		function openPopup(eventId) {
    let overlay = document.createElement("div");
    overlay.id = "popupOverlay";
    overlay.innerHTML = `
        <div id="popupContainer">
            <button id="closePopup" onclick="closePopup()">✖</button>
            <iframe id="popupFrame" src="edit_event.php?event_id=${eventId}" frameborder="0"></iframe>
        </div>
    `;

    document.body.appendChild(overlay);
}

function closePopup() {
    document.getElementById("popupOverlay").remove();
}


        document.addEventListener("DOMContentLoaded", loadEvents);

    </script>
</body>
</html>
