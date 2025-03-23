<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event List</title>
    <style>
        body {
            font-family: "Hudson NY Pro", Arial, sans-serif; /* switch to arial if no hudson */
            background-color: white;
            color: black;
            margin: 0;
            padding: 20px;
           
        }

        h1 {
            text-align: center;
        }

        /* Month Navigation Bar */
        .month-bar {
            display: flex;
            justify-content: center;
            gap: 50px;
            padding: 10px;
            overflow-x: auto;
            background:#5a9bd5;
            border-radius: 10px;
            margin-bottom: 20px;
           
          
        }

        .month-btn {
            padding: 11px 20px;
            cursor: pointer;
            border: none;
            background: lightgray;
            border-radius: 20px;
            transition: 0.3s;
            background-color:white;
        }

        .month-btn.active {
            background: #ffc20e;
            color: white;
        }

        /* Event Container - FLEXBOX */
        .event-container {
            display: flex;
            flex-wrap: wrap; /* Allows wrapping */
            gap: 15px;
            justify-content: flex-start; /* Align items to the left */
        }

        .event-group {
            display: flex;
            flex-wrap: wrap; /* Ensures events wrap to next row */
            gap: 15px;
            justify-content: flex-start; /* Aligns to left */
            width: 100%;
        }

        /* Event Card */
        .event-card {
    background: #ffffff;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    width: 250px;
    flex: 1 1 150px; /* Smaller width */
    max-width: 420px; /* Prevents excessive stretching */
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    
}
.event-card img {
    width: 100%; /* Increased image size */
    height: auto;
    border-radius: 5px;
}

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    width: 400px; /* Fixed width */
    height: 400px; /* Fixed height */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}


        .close-btn {
            padding: 5px 10px;
            background: red;
            color: white;
            border: none;
            cursor: pointer;
        }
        .more-btn {
    cursor: pointer;
    color: black;
    background: lightgray;
    padding: 8px 12px;
    border-radius: 5px;
    display: inline-block;
    transition: 0.3s;
}

.more-btn:hover {
    background: blue;
    color: white;
}
.login_logo{
    height: 50px;
    width: 55px;
    
}








/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
 

}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}



nav {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Push elements apart */
    padding: 10px 20px;
    background: white;
    border-radius: 10px;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 15px; /* Space between items */
}









    </style>
</head>
<nav>
  <h1>Event List</h1>

  <div class="nav-right">
    <!-- Toggle Switch -->
    <label class="switch">
      <input type="checkbox" id="toggleSwitch">
      <span class="slider round"></span>
    </label>

    <!-- Login Icon -->
    <a href="login.php">
      <img class="login_logo" src="/assets/login.png" alt="Login Logo">
    </a>

    <?php 
      // Show Link for Admin
      if (!empty($_SESSION["isLogin"]) && $_SESSION["permLevel"] == 2) {
          echo "<li><a href='admin/dashboard'>Admin</a></li>";
      }
    ?>
  </div>
</nav>


<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleSwitch = document.getElementById("toggleSwitch");

    // Check the current page and set the toggle state
    if (window.location.href.includes("index.html")) {
      toggleSwitch.checked = true;
    }

    // Add an event listener to toggle between pages
    toggleSwitch.addEventListener("change", function () {
      if (this.checked) {
        window.location.href = "../index.php"; // Switch to index.html
      } else {
        window.location.href = ".php"; // Switch to index.php
      }
    });
  });
</script>


<body>



<!-- Month Bar -->
<div class="month-bar">
    <?php
    $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    foreach ($months as $index => $month) {
        echo "<button class='month-btn' data-month='" . ($index + 1) . "'>$month</button>";
    }
    ?>
</div>

<div class="event-container">
    <?php
    require_once("../includes/db.php");
   

    $events_by_month = [];

    try {
        $query = "SELECT e.event_id, e.event_name, e.event_desc, e.event_startdate, e.event_starttime, e.event_endtime, 
                         v.venue_name, v.venue_address, t.type_name, e.image_name
                  FROM events e
                  LEFT JOIN venues v ON e.venue_id = v.venue_id
                  LEFT JOIN event_type t ON e.type_id = t.type_id
                  ORDER BY e.event_startdate ASC";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($events as $row) {
            $event_month = date("n", strtotime($row['event_startdate']));
            $events_by_month[$event_month][] = $row;
        }
    } catch (PDOException $err) {
        echo "<p>Error fetching events: " . $err->getMessage() . "</p>";
        exit();
    }

    foreach ($events_by_month as $month => $events) {
        echo "<div class='event-group' data-month='$month' style='display: none;'>";
        foreach ($events as $row) {
            echo "<div class='event-card'>";
            
            if (!empty($row['image_name'])) {
                echo "<img src='../assets/upload/image/" . htmlspecialchars($row['image_name']) . "' alt='Event Image' width='100'>";
            }
            
            echo "<div class='event-details'>";
            echo "<h2>" . htmlspecialchars($row['event_name']) . "</h2>";
            echo "<p><strong>Venue:</strong> " . htmlspecialchars($row['venue_name']) . " (" . htmlspecialchars($row['venue_address']) . ")</p>";
            echo "<p><strong>Date:</strong> " . htmlspecialchars($row['event_startdate']) . "</p>";
            echo "<p><strong>Time:</strong> " . htmlspecialchars($row['event_starttime']) . " - " . htmlspecialchars($row['event_endtime']) . "</p>";
            echo "<p><strong>Category:</strong> " . htmlspecialchars($row['type_name']) . "</p>";

            echo "<p class='more-btn' data-title='" . htmlspecialchars($row['event_name'], ENT_QUOTES) . "' 
                                data-description='" . htmlspecialchars($row['event_desc'], ENT_QUOTES) . "' 
                                onclick='openModal(this)'>More</p>";

            echo "</div></div>";
        }
        echo "</div>";
    }
    ?>
</div>

<!-- Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <h2 id="modal-title"></h2>
        <p id="modal-description"></p>
        <button class="close-btn" onclick="closeModal()">Close</button>
    </div>
</div>

<script>
 document.addEventListener("DOMContentLoaded", function () {
    let monthButtons = document.querySelectorAll(".month-btn");
    let eventGroups = document.querySelectorAll(".event-group");

    // Default to current month
    let today = new Date();
    let currentMonth = today.getMonth() + 1;

    // Show events for the current month
    showMonthEvents(currentMonth);

    // Mark the corresponding month button as active
    let activeButton = document.querySelector(`.month-btn[data-month='${currentMonth}']`);
    if (activeButton) {
        activeButton.classList.add("active");
    }

    monthButtons.forEach(button => {
        button.addEventListener("click", function () {
            let selectedMonth = this.getAttribute("data-month");
            showMonthEvents(selectedMonth);

            // Remove active class from all buttons and add it to the clicked one
            monthButtons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");
        });
    });

    function showMonthEvents(month) {
        eventGroups.forEach(group => {
            if (group.getAttribute("data-month") == month) {
                group.style.display = "flex";
            } else {
                group.style.display = "none";
            }
        });
    }

    // Open Modal
    document.querySelectorAll(".more-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            let title = this.getAttribute("data-title");
            let description = this.getAttribute("data-description");

            document.getElementById("modal-title").innerText = title;
            document.getElementById("modal-description").innerText = description;

            document.getElementById("eventModal").style.display = "flex";
        });
    });

    // Close Modal
    function closeModal() {
        document.getElementById("eventModal").style.display = "none";
    }

    document.querySelector(".close-btn").addEventListener("click", closeModal);

    window.onclick = function (event) {
        var modal = document.getElementById("eventModal");
        if (event.target === modal) {
            closeModal();
        }
    };
});


</script>

</body>
</html>
