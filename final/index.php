

<?php
session_start();
$_SESSION['current_page'] = "index.php";
if (!isset($_SESSION["isLogin"]) || $_SESSION["isLogin"] !== true) {
    $_SESSION["isLogin"] = false;
}

require_once("./includes/db.php"); // Ensure the correct DB connection

// Check if `$conn` exists
if (!isset($conn)) {
    die("Database connection error.");
}

// Fetch events from the database
$sql = "SELECT event_id, event_name, event_desc, event_startdate, event_enddate, event_starttime, event_endtime FROM events";
$stmt = $conn->prepare($sql);
$stmt->execute();
$events = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $events[] = [
        'id' => $row['event_id'],
        'title' => "• " . $row['event_name'], // Add bullet point
        'description' => "Description: " . $row['event_desc'], // Store description
        'start' => $row['event_startdate'],
        'end' => $row['event_enddate'] ?: $row['event_startdate'],
        'starttime' => "Start Time:" . $row["event_starttime"],
        'endtime' => "End Time:" . $row["event_endtime"]
    ];
}

// Convert events to JSON for JavaScript use
$eventsJSON = json_encode($events);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Calendar</title>

    <!-- FullCalendar CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/footHead.css">

    <style>
           body {
            font-family: Arial, sans-serif;
            background-color: white;
            color: black;
            margin: 0;
            padding: 20px;
           
        }
        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
        }
        
        .login_logo {
            height: 50px;
            width: 55px;
        }

        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #5c9cd1;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        nav {
    display: flex;
    align-items: center; /* Aligns items vertically */
    justify-content: space-between; /* Spaces h1 and nav-right apart */
    padding: 10px 20px;
    background: white;
    border-radius: 10px;
    margin-top: 10px;
}

.nav-right {
    display: flex;
    align-items: center; /* Ensures all elements align properly */
    gap: 15px;
    margin-left:auto;
}

nav h1 {
            margin: 0;
            font-weight: bold;
            font-size: 32px;

        }


        #calendar {
            max-width: 1100px;
            margin: 20px auto;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border:2px solid black;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        /* Modal Background */
      /* Modal Background (Ensures It's Hidden on Load) */
/* Modal Styles */
/* .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%); /* Centers the modal */
   /* width: 100%;
    max-width: 400px; 
    background-color: rgba(0, 0, 0, 0.4);
    border-radius:10%;
}

.modal-content {
    background-color: #5c9cd1;
    padding: 25px;
    border-radius: 10px;
    width: 400px;
    max-width: 90%;
    text-align: center;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    position: relative;
}
.error-message {
        color: red;
        font-size: 14px;
        text-align: center;
        margin-bottom: 10px;
        font-weight:bold;
    } */

    

.close {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 22px;
    font-weight: bold;
    background: red;
    color: white;
    padding: 5px 10px;
    border-radius: 50%;
    cursor: pointer;
}

.close:hover {
    background-color: darkred;
}

.user-icon {
    width: 80px;
    height: 80px;
    margin-bottom: 15px;
    border-radius: 50%;
    background: white;
    padding: 10px;
}

    input {
        display: block;
        width: 97%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    button {
        background-color: green;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }


    .navbar {

            background-color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin-top:20px;
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1400px;
            margin: auto;
        }

        .logo img {
            height: 45px;
            display: block;
        }

        .nav-links, .nav-right {
            display: flex;
            align-items: center;
            list-style: none;
            padding: 0;
        }

        .nav-links li, .nav-right li {
            margin: 0 15px;
        }

        .nav-links a, .nav-right a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .nav-links a span {
            margin-left: 5px;
            font-size: 12px;
        }

        .itinerary-btn {
            background-color: #ffc107;
            padding: 8px 15px;
            border-radius: 5px;
            color: black;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .itinerary-btn img {
            height: 18px;
            margin-right: 8px;
        }

        .itinerary-btn:hover {
            background-color: #e6a900;
        }

        /* Footer */
        .footer {
            background-color: #222;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 10px;
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin: 0 15px;
        }

        .footer-links a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #ddd;
        }

        .footer-bottom {
            font-size: 0.9em;
        }

        #logoutModal {
    display: none;
    position: fixed;
    top: 50px; /* Adjust the vertical position */
    right: 20px; /* Move it to the right */
    width: auto;
    background-color: rgba(0, 0, 0, 0.4); /* 100% black background */
    justify-content: flex-end; /* Align to the right */
    align-items: flex-start; /* Align near the top */
    width: 100%;
    height: 100%;
    margin-top:-50px;
}

#logoutModal .modal-content {
    background: #5c9cd1;
    padding: 15px;
    border-radius: 8px;
    width: 110px; 
    height:100px;
    text-align: center;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    position: absolute;
    right: 20px;
    top: 50px;
    margin-top:150px;
}

#logoutModal h2 {
    font-size: 18px;
    margin-bottom: 15px;
    color: white;
}

#logoutModal .close-btn {
    background: red;
    color: white;
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    margin: 5px;
    display: inline-block;
}

#logoutModal .close-btn:hover {
    background-color: darkred;
}


/* Login Modal */
#loginModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
#loginModal .modal-content {
    background-image: linear-gradient(to bottom right,#5c9cd1 , #ffffff);
    padding: 25px;
    border-radius: 10px;
    width: 400px;
    height:400px;
    max-width: 90%;
    text-align: center;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    position: relative;
    margin: auto; /* Ensure the modal is centered */
    margin-top:16%;
}

#loginModal .close {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 22px;
    font-weight: bold;
    background: red;
    color: white;
    padding: 5px 10px;
    border-radius: 50%;
    cursor: pointer;
}

#loginModal .close:hover {
    background-color: darkred;
}

#loginModal .user-icon {
    width: 80px;
    height: 80px;
    margin-top: 21.5px;
    border-radius: 50%;
    background: white;
    padding: 10px;
}

#loginModal input[type="email"],
#loginModal input[type="password"],
#loginModal input[type="submit"] {
    width: 90%;
    padding: 10px;
    margin: 20px 0;
    margin-left:10px;
    border-radius: 5px;
   
    
}


#loginModal input[type="submit"] {
    background-color: green;
    color: white;
    border: none;
    cursor: pointer;
    margin-left:20px;
}
.error-message {
    color: red;
    font-size: 14px;
    text-align: center;
    margin-bottom: 10px;
    font-weight: bold;
}


#eventModal {
    display: none; /* Initially hidden */
    position: fixed;
    z-index: 1000;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 80%;
    max-width: 400px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    padding: 20px;
    text-align: center;
    background-image: linear-gradient(to bottom right,#5c9cd1 , #ffffff);
}

#eventModal .modal-content {
    padding: 15px;
}

#eventModal .close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 20px;
    cursor: pointer;
    background: red;
    color: white;
    padding: 5px;
    border-radius: 50%;
}

/* Responsive Styles */
@media (max-width: 1024px) {
    /* Hide the login option on smaller screens */
    .nav-right {
        display: none;
    }

    /* Adjust the header for smaller screens */
    .header-container {
        flex-direction: column;
        align-items: flex-start;
    }

    /* Adjust the calendar for smaller screens */
    #calendar {
        max-width: 100%;
        margin: 10px;
        padding: 5px;
    }

    /* Adjust modal for smaller screens */
    .modal-content {
        width: 90%;
        height: auto;
        padding: 15px;
    }
}

@media (max-width: 768px) {
    /* Further adjustments for tablets and mobile */
    #calendar {
        margin: 5px;
    }

    /* Adjust the footer for smaller screens */
    .footer-links {
        flex-direction: column;
        align-items: center;
    }

    .footer-links li {
        margin: 10px 0;
    }
}

@media (max-width: 480px) {
    /* Adjustments for mobile devices */
    #calendar {
        margin: 0;
    }

    /* Adjust the header text for mobile */
    nav h1 {
        font-size: 24px;
    }

    /* Adjust the footer for mobile */
    .footer {
        padding: 10px 0;
    }

    .footer-links {
        gap: 5px;
    }
}
</style>
   
</head>

<body class="cr-gallery">
					<div data-fetch-key="data-v-2dccef2e:0" class="main-header" data-v-2dccef2e="" data-v-f9c455fe=""> 
						<div data-fetch-key="data-v-599d2d24:0" class="mobile-nav background--white" data-v-599d2d24="" data-v-2dccef2e="" style="translate: none; rotate: none; scale: none;">
							<nav class="inner" data-v-599d2d24="">
								<a class="close" data-v-599d2d24=""></a> 
								<ul class="primary-nav" data-v-599d2d24="">
									<li class="item" data-v-599d2d24="">
										<a class="link link--large" data-v-599d2d24="">
											<span data-v-599d2d24="">Things To Do</span> 
											<svg xmlns="http://www.w3.org/2000/svg" class="drop-arrow icon sprite-icons" data-v-599d2d24=""></svg>
										</a> 
									</li>
									<li class="item" data-v-599d2d24="">
										<a class="link link--large" data-v-599d2d24="">
										<span data-v-599d2d24="">Food + Drink</span> 
										<svg xmlns="http://www.w3.org/2000/svg" class="drop-arrow icon sprite-icons" data-v-599d2d24=""></svg>
										</a>
									</li>
									<li class="item" data-v-599d2d24="">
										<a class="link link--large" data-v-599d2d24="">
										<span data-v-599d2d24="" >Events</span>
										<svg xmlns="http://www.w3.org/2000/svg" class="drop-arrow icon sprite-icons" data-v-599d2d24=""></svg>
										</a>
									</li>
									<li class="item" data-v-599d2d24="">
										<a class="link link--large" data-v-599d2d24="">
										<span data-v-599d2d24="">Plan Your Trip</span> 
										<svg xmlns="http://www.w3.org/2000/svg" class="drop-arrow icon sprite-icons" data-v-599d2d24=""></svg>
										</a>
									</li>
									<li class="item" data-v-599d2d24="">
										<a class="link link--large" data-v-599d2d24="">
											<span data-v-599d2d24="">Blogs</span> 
											<svg xmlns="http://www.w3.org/2000/svg" class="drop-arrow icon sprite-icons" data-v-599d2d24=""></svg>
										</a>
									</li>
							</ul> 
							<ul class="secondary-nav" data-v-599d2d24="">
								<li class="item" data-v-599d2d24="">
								<a href="https://tourismlethbridge.com/media" class="link" data-v-599d2d24="">Media</a>
								</li>
								<li class="item" data-v-599d2d24="">
								<a href="https://tourismlethbridge.com/sports" class="link" data-v-599d2d24="">Sports</a>
								</li>
								<li class="item" data-v-599d2d24="">
								<a href="https://tourismlethbridge.com/meetings" class="link" data-v-599d2d24="">Meeting Spaces</a>
								</li>
								<li class="item" data-v-599d2d24="">
								<a href="https://tourismlethbridge.com/travel-trade" class="link" data-v-599d2d24="">Travel Trade</a>
								</li> 
								<!-- SEARCH TOURISMLETHBRIDGE WEBSITE -->
								<li class="item" data-v-599d2d24="">
									<div class="search" data-v-599d2d24="">
										<form action="https://www.tourismlethbridge.com/search" method="get">
										<input type="text" id="searchQuery" name="q" placeholder="Search Lethbridge" required onkeydown="if(event.key === 'Enter'){this.form.submit();}">
										</form>
									</div>
								</li>
							</ul>
						</nav>
					</div>
					<header data-v-2dccef2e="" class="on-white">
						<div class="lower" data-v-2dccef2e="">
							<a class="burger" data-v-2dccef2e="">
								<span data-v-2dccef2e=""></span><span data-v-2dccef2e=""></span>
								<span data-v-2dccef2e=""></span>
							</a> 
							<a href="https://tourismlethbridge.com" aria-current="page" class="logo nuxt-link-exact-active nuxt-link-active" data-v-1c38f1fe="" data-v-2dccef2e="">
							<img src="images\TL_logo.png" alt="TourismLethbridge Logo">
							</a> 
							<a href="/my-itinerary" class="breif-case" data-v-2dccef2e="">
							<span height="20" class="case-wrap" style="height:20px;" data-v-bf30d5be="" data-v-2dccef2e="">
								<svg width="22" height="20" viewBox="4 0 22 20" xmlns="http://www.w3.org/2000/svg" class="case color--black" data-v-bf30d5be="">
								<path d="M23 5H7C5.89543 5 5 5.89543 5 7V17C5 18.1046 5.89543 19 7 19H23C24.1046 19 25 18.1046 25 17V7C25 5.89543 24.1046 5 23 5Z" data-v-bf30d5be=""></path> 
								<path d="M19 19V3C19 2.46957 18.7893 1.96086 18.4142 1.58579C18.0391 1.21071 17.5304 1 17 1H13C12.4696 1 11.9609 1.21071 11.5858 1.58579C11.2107 1.96086 11 2.46957 11 3V19" data-v-bf30d5be=""></path>
								</svg> 
								<span class="number background--black color--white" data-v-bf30d5be="">
									<svg viewBox="0 0 0 0" xmlns="http://www.w3.org/2000/svg" class="plus" data-v-bf30d5be="">
									<path d="M4 1V7" data-v-bf30d5be=""></path> 
									<path d="M1.00024 4H7.00025" data-v-bf30d5be=""></path>
									</svg>
								</span>
							</span>
							</a> 
							<nav data-fetch-key="data-v-7a99d213:0" data-v-7a99d213="" data-v-2dccef2e="">
								<ul class="ul--reset" data-v-7a99d213="" style= "padding-top: 20px;">
									<li class="item item--top item--has_drop" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/125-things-to-do-in-lethbridge" class="h--3" data-v-7a99d213="">Things To Do</a> 
										<svg viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg" class="drop-arrow" data-v-7a99d213="">
										<path d="M1 1L5 5L9 1" data-v-7a99d213=""></path>
										</svg> 
										<div class="child-list" data-v-7a99d213="">
											<a href="https://tourismlethbridge.com/unesco" data-v-7a99d213="" class=""></a>
											<a href="https://tourismlethbridge.com/major-attractions" data-v-7a99d213="" class=""></a>
											<a href="https://tourismlethbridge.com/indigenous-lethbridge" data-v-7a99d213="" class=""></a>
											<a href="https://tourismlethbridge.com/arts-and-culture" data-v-7a99d213="" class=""></a>
											<a href="https://tourismlethbridge.com/outdoors" data-v-7a99d213="" class=""></a>
											<a href="https://tourismlethbridge.com/family" data-v-7a99d213="" class=""></a>
											<a href="https://tourismlethbridge.com/sports/lethbridge-sports-teams" data-v-7a99d213="" class=""></a>
										</div>
									</li>
									<li class="item item--top item--has_drop" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/culinary-experiences/lethbridge-restaurants" class="h--3" data-v-7a99d213="">Food + Drink</a> 
										<svg viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg" class="drop-arrow" data-v-7a99d213="">
										<path d="M1 1L5 5L9 1" data-v-7a99d213=""></path>
										</svg> 
										<div class="child-list" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/culinary-experiences/lethbridge-restaurants" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/culinary-experiences/food" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/ale-trail" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/culinary-experiences/sip-taste-explore" data-v-7a99d213="" class=""></a>
										</div>
									</li>
									<li class="item item--top item--has_drop" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/events" class="h--3" data-v-7a99d213="" style="color: black; text-decoration: underline; text-decoration-color: #fbbf24;">Events</a> 
										<svg viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg" class="drop-arrow" data-v-7a99d213="">
										<path d="M1 1L5 5L9 1" data-v-7a99d213=""></path>
										</svg> 
										<div class="child-list" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/events" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/signature-events" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/upcoming-events" data-v-7a99d213="" class=""></a>
										</div>
									</li>
									<li class="item item--top item--has_drop" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/plan" class="h--3" data-v-7a99d213="">Plan Your Trip</a> 
										<svg viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg" class="drop-arrow" data-v-7a99d213="">
										<path d="M1 1L5 5L9 1" data-v-7a99d213=""></path>
										</svg> 
										<div class="child-list" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/accommodations" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/guides" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/transportation" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/plan" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/visitor-centre" data-v-7a99d213="" class=""></a>
										</div>
									</li>
									<li class="item item--top item--has_drop" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/stories" class="h--3" data-v-7a99d213="">Blogs</a> 
										<svg viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg" class="drop-arrow" data-v-7a99d213="">
										<path d="M1 1L5 5L9 1" data-v-7a99d213=""></path>
										</svg> 
										<div class="child-list" data-v-7a99d213="">
										<a href="https://tourismlethbridge.com/stories" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/newsletter" data-v-7a99d213="" class=""></a>
										<a href="https://tourismlethbridge.com/locals" data-v-7a99d213="" class=""></a>
										</div>
									</li>
								</ul>
							</nav>
						</div> 
					</header>
						<div class="upper" data-v-2dccef2e="">
						<nav>
							<ul class="ul--reset" data-v-115fdfea="">
								<li data-v-115fdfea="">
								<a href="https://tourismlethbridge.com/media" data-v-115fdfea="" class="">Media</a>
								</li>
								<li data-v-115fdfea="">
								<a href="https://tourismlethbridge.com/sports" data-v-115fdfea="" class="">Sports</a>
								</li>
								<li data-v-115fdfea="">
								<a href="https://tourismlethbridge.com/meetings" data-v-115fdfea="" class="">Meeting Spaces</a>
								</li>
								<li data-v-115fdfea="">
								<a href="https://tourismlethbridge.com/travel-trade" data-v-115fdfea="" class="">Travel Trade</a>
								</li> 
								<li style="height:auto;" data-v-115fdfea="">
								<form action="https://www.tourismlethbridge.com/search" method="get">
								<input type="text" id="searchQuery" name="q" placeholder="Search Lethbridge" required onkeydown="if(event.key === 'Enter'){this.form.submit();}">								
								</form>
								</li>
							</ul>
							<a href="https://tourismlethbridge.com/my-itinerary" class="button button--primary" data-v-115fdfea="">
							<span data-v-115fdfea="">My Itinerary</span> 
							<span class="case-wrap breif-case" style="height:20px;" data-v-bf30d5be="" data-v-115fdfea="">
								<svg width="22" height="20" viewBox="4 0 22 20" xmlns="http://www.w3.org/2000/svg" class="case color--black" data-v-bf30d5be="">
									<path d="M23 5H7C5.89543 5 5 5.89543 5 7V17C5 18.1046 5.89543 19 7 19H23C24.1046 19 25 18.1046 25 17V7C25 5.89543 24.1046 5 23 5Z" data-v-bf30d5be=""></path> 
									<path d="M19 19V3C19 2.46957 18.7893 1.96086 18.4142 1.58579C18.0391 1.21071 17.5304 1 17 1H13C12.4696 1 11.9609 1.21071 11.5858 1.58579C11.2107 1.96086 11 2.46957 11 3V19" data-v-bf30d5be=""></path>
								</svg> 
								<span class="number background--black color--white" data-v-bf30d5be="">
									<svg viewBox="0 0 8 8" xmlns="http://www.w3.org/2000/svg" class="plus" data-v-bf30d5be="">
										<path d="M4 1V7" data-v-bf30d5be=""></path> 
										<path d="M1.00024 4H7.00025" data-v-bf30d5be=""></path>
									</svg>
								</span>
							</span>
							</a>
						</nav>
						</div> 
						<div data-v-6588faee="" data-v-658c125a="">
							<div class="min" style="max-height:25px;min-height:0px;height:1.48vw;" data-v-6588faee=""></div> 
							<div class="max" style="max-height:25px;min-height:0px;height:1.48vw;" data-v-6588faee=""></div>
						</div>				
				</div>

<nav>
    <h1>Calendar</h1>
    <div class="nav-right">
        <!-- Toggle Switch -->
        <label class="switch">
            <input type="checkbox" id="toggleSwitch">
            <span class="slider round"></span>
        </label>

        <!-- Login Icon goes here -->
    <?php if (isset($_SESSION['permLevel'])): ?>
                <?php if ($_SESSION['permLevel'] == 1): ?>
                    <li><a href="./stakeholder/myEvents.php">My Events</a></li>
                <?php elseif ($_SESSION['permLevel'] == 2): ?>
                    <li><a href="./admin/admin.php">Admin Panel</a></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_email'])): ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><img style="cursor:pointer;" src="./admin/images/login.png" alt="Login" class="login_logo" id="loginIcon" ></li>
            <?php endif; ?>
    </div>
</nav>

<!-- Login Modal -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLoginModal()">&times;</span>
        <img src="./admin/images/login.png" alt="User Icon" class="user-icon">
        <h2>Login</h2>
        <?php
            if (isset($_SESSION['login_error'])) {
                echo '<p class="error-message">' . $_SESSION['login_error'] . '</p>';
                unset($_SESSION['login_error']); // Remove error message after displaying
            }
        ?>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <input type="submit" name="submit" value="Login">
        </form>
    </div>
</div>

<!-- Event Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle"></h2>
        <p id="modalDescription"></p>
        <p id="modalStartTime"></p>
        <p id="modalEndTime"></p>
    </div>
</div>


<!-- Calendar Container -->
<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: <?php echo $eventsJSON; ?>,
        eventClick: function(info) {
            // Set event details inside the modal
            document.getElementById("modalTitle").innerText = info.event.title;
            document.getElementById("modalDescription").innerText = info.event.extendedProps.description;
            document.getElementById("modalStartTime").innerText = info.event.extendedProps.starttime;
            document.getElementById("modalEndTime").innerText = info.event.extendedProps.endtime;
            // Make sure the modal is displayed
            document.getElementById("eventModal").style.display = "flex";
        },
        eventContent: function(arg) {
            return { html: `<span style="white-space: pre-line">${arg.event.title}</span>` };
        }
    });
    calendar.render();

    // Function to close the modal when clicking the "X" button
    function closeModal() {
        document.getElementById("eventModal").style.display = "none";
    }

    // Ensure modal is hidden when the page loads
    window.onload = function () {
        document.getElementById("eventModal").style.display = "none";
    };

    const toggleSwitch = document.getElementById("toggleSwitch");

    // Check if the user is coming from login
    const urlParams = new URLSearchParams(window.location.search);
    const justLoggedIn = urlParams.has("loggedIn");

    if (window.location.href.includes("event_listView.php")) {
        toggleSwitch.checked = true;
    } else {
        toggleSwitch.checked = false;
    }

    let errorMessage = document.querySelector(".error-message");
    if (errorMessage) {
        document.getElementById("loginModal").style.display = "block";
    }

    // Only trigger redirection if the user *manually* changes the toggle
    toggleSwitch.addEventListener("change", function () {
        if (!justLoggedIn) {
            if (this.checked) {
                window.location.href = "event_listView.php"; 
            } else {
                window.location.href = "index.php";
            }
        }
    }
);


    // Login Modal Logic
    let loginModal = document.getElementById("loginModal");
    let loginIcon = document.getElementById("loginIcon");

    if (loginIcon) {
        loginIcon.addEventListener("click", function () {
            loginModal.style.display = "flex";
        });
    }

    function closeLoginModal() {
        loginModal.style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target === loginModal) {
            closeLoginModal();
        }
        if (event.target === document.getElementById("eventModal")) {
            closeModal();
        }
    };

    // Attach closeModal function to the close button in the event modal
    document.querySelector("#eventModal .close").addEventListener("click", closeModal);

    // Attach closeLoginModal function to the close button in the login modal
    document.querySelector("#loginModal .close").addEventListener("click", closeLoginModal);
});
</script>
 

<div data-v-6588faee="" data-v-b4996216="">
					<div class="min" style="max-height:80px;min-height:0px;height:4.76vw;" data-v-6588faee=""></div> 
					<div class="max" style="max-height:80px;min-height:0px;height:4.76vw;" data-v-6588faee=""></div>
				</div>
				</div>
			</div>
		</div> 

<footer data-fetch-key="data-v-b8f25348:0" class="main-footer" data-v-b8f25348="" data-v-f9c455fe="">
			<div class="info" data-v-b8f25348="">
				<a href="https://tourismlethbridge.com" aria-current="page" class="nuxt-link-exact-active nuxt-link-active" data-v-1c38f1fe="" data-v-b8f25348="">
				<img src="images\TL_logo2.png" alt="TourismLethbridge Logo">
				</a> 
				<div class="contact" data-v-b8f25348="">2805 Scenic Drive South, Lethbridge, Alberta<br>Phone: (403) 394-2403</div> 
					<div class="copyright" data-v-b8f25348="">Tourism Lethbridge (Niita’paini’pi Sikoohkotok) is legally known as the Lethbridge Destination Management Organization, a non-profit society. © 2024. All Rights Reserved.</div>
			</div> 
			<div class="navigations" data-v-b8f25348="">
					<ul class="ul--reset" data-v-12a622c6="">
						<li data-v-12a622c6="">
						<a href="https://tourismlethbridge.com/meetings" data-v-12a622c6="" class="">Meeting Spaces</a>
						</li>
						<li data-v-12a622c6="">
						<a href="https://tourismlethbridge.com/sports" data-v-12a622c6="" class="">Sports</a>
						</li>
						<li data-v-12a622c6="">
						<a href="https://tourismlethbridge.com/travel-trade" data-v-12a622c6="" class="">Travel Trade</a>
						</li>
						<li data-v-12a622c6="">
						<a href="https://tourismlethbridge.com/about-us" data-v-12a622c6="" class="">About Us</a>
						</li>
						<li data-v-12a622c6="">
						<a href="https://tourismlethbridge.com/our-vision-and-mission" data-v-12a622c6="" class="">Our Vision and Mission</a>
						</li>
						<li data-v-12a622c6="">
						<a href="https://tourismlethbridge.com/privacy-policy" data-v-12a622c6="" class="">Privacy Policy</a>
						</li>
						<li data-v-12a622c6="">
						<a href="https://tourismlethbridge.com/contact" data-v-12a622c6="" class="">Contact</a>
						</li>
					</ul> 
				<ul data-fetch-key="data-v-32cd023e:0" class="ul--reset social-navigation" data-v-32cd023e="" data-v-b8f25348="">
					<li data-v-32cd023e="">
						<a href="https://www.tripadvisor.ca/Attraction_Review-g154919-d19844632-Reviews-Tourism_Lethbridge-Lethbridge_Alberta.html" target="_blank" data-v-32cd023e="">
							<button class="color-change-button">
							<img src="images\tripadvisorlogo.png" alt="TripAdvisor Logo" class="button-tripadvisor">
							</button>
						</a>
					</li>
					<li data-v-32cd023e="">
						<a href="https://wwww.facebook.com/tourismlethbridge" target="_blank" data-v-32cd023e="">
							<button class="color-change-button">
							<img src="images\facebooklogo.png" alt="TripAdvisor Logo" class="button-facebook">
							</button>
						</a>
					</li>
					<li data-v-32cd023e="">
						<a href="https://x.com/tourismleth" target="_blank" data-v-32cd023e="">
							<button class="color-change-button">
							<img src="images\twitter.png" alt="TripAdvisor Logo" class="button-twitter">
							</button>
						</a>
					</li>
					<li data-v-32cd023e="">
						<a href="https://www.youtube.com/channel/UCLpCPSJ5C_AIOuaEoT5vGYg" target="_blank" data-v-32cd023e="">
							<button class="color-change-button">
							<img src="images\youtube.png" alt="YouTube Logo" class="button-youtube">
							</button>
						</a>
					</li>
					<li data-v-32cd023e="">
						<a href="https://www.instagram.com/tourismlethbridge" target="_blank" data-v-32cd023e="">
							<button class="color-change-button">
							<img src="images\instagram.png" alt="TripAdvisor Logo" class="button-instagram">
							</button>
						</a>
					</li>
				</ul>
			</div>
		</footer> 
</body>
</html>

<?php
// Close the connection
$conn = null;
?>














































