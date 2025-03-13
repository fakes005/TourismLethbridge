<?php
require_once("../includes/db.php");
require_once("../includes/authorizeAdmin.php"); // only admin authorized to this page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    
    <title>Report</title>

    <style>
    /* General Table Styling */
    #reportTable {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
    }

    /* Table Header */
    #reportTable thead {
        background-color: #5c9cd1;  /* Dark Header */
        color: white;
        text-align: center;
    }

    /* Table Rows */
    #reportTable tbody tr:nth-child(even) {
        background-color: #f2f2f2;  /* Alternate row color */
    }

    #reportTable tbody tr:hover {
        background-color: #d1ecf1;  /* Light blue on hover */
    }

    /* Table Footer */
    #reportTable tfoot {
        background-color: #5c9cd1;
        font-weight: bold;
        text-align: right;
    }

    /* Column Alignment */
    #reportTable th, #reportTable td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: center;
    }

    /* Attendance Column - Make numbers bold */
    #reportTable td.attendance {
        font-weight: bold;
        color:rgb(255, 0, 0);
    }

    nav {
            display: flex;
            justify-content: right;
            padding:10px;
            margin-bottom: 50px;
            margin-right:50px;
        }
    nav ul {
        list-style-type:none;
        padding:0;
        margin-top:10px;
        display:flex;
        gap:15px;
    }


    .nav a:hover {
            background-color: #ffc20e;
            border-radius: 8px;
            padding: 12px 17px;
        }
       
 .nav-right a {
            text-decoration: none;
            font-size: 18px;
            padding: 10px 15px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            color:black;
        }

     .nav-right a:hover {
            background-color: #ffc20e;
           
     }





</style>


</head>
<nav class="nav-right">
    <ul>
        <li><a href="admin.php"> Admin </a></li>
        <li><a href="../index.php"> Home </a></li>
</nav>
<body>
    <div class="container">
        <h1 class="mt-4">View Report</h1>
        <br>
        <table class="table table-bordered dataTable" id="reportTable">
            <thead >
                <tr>
                    <th>Event Name</th>
                    <th>Event Venue</th>
                    <th>Event Location</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Organizer</th>
                    <th>Attendance</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $query = "SELECT events.event_name, events.event_startdate, events.event_enddate, 
                                     venues.venue_name, venues.venue_address, users.user_name, events.event_attendance
                              FROM events 
                              JOIN venues ON events.venue_id = venues.venue_id
                              JOIN users ON events.user_id = users.user_id";

                    $stmt = $conn->prepare($query);
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlentities($row['event_name']) . "</td>";
                        echo "<td>" . htmlentities($row['venue_name']) . "</td>"; 
                        echo "<td>" . htmlentities($row['venue_address']) . "</td>"; 
                        echo "<td>" . htmlentities($row['event_startdate']) . "</td>";
                        echo "<td>" . htmlentities($row['event_enddate']) . "</td>";
                        echo "<td>" . htmlentities($row['user_name']) . "</td>";
                        echo "<td class='attendance'>" . htmlentities($row['event_attendance']) . "</td>";
                        echo "</tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='7' class='text-danger'>Error retrieving data</td></tr>";
                    error_log("SQL Error: " . $e->getMessage());
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-end fw-bold">Total Attendance:</td>
                    <td id="totalAttendance" class="fw-bold"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- jQuery and DataTables Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            let table = $('#reportTable').DataTable({
                "order": [[3, "asc"]],  // Sort by Start Date by default
                "columnDefs": [
                    { "type": "date", "targets": [3, 4] },  // Ensure correct date sorting
                    { "type": "num", "targets": [6] }  // Ensure numeric sorting for Attendance
                ],
                "footerCallback": function(row, data, start, end, display) {
                    let api = this.api();
                    let total = api.column(6, { page: 'current' }).data().reduce((a, b) => {
                        return (parseInt(a) || 0) + (parseInt(b) || 0);
                    }, 0);
                    $(api.column(6).footer()).html(total); // Update the footer with total
                }
            });
        });
    </script>
</body>
</html>
