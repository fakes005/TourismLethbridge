<?php
session_start();

if (!isset($_SESSION['filePath'])) {
    die("No file uploaded. Please upload a CSV file first.");
}

$filePath = $_SESSION['filePath'];
unset($_SESSION['filePath']); // Clear the session data after use

// Execute the Python script
$command = "py apptest.py " . escapeshellarg($filePath) . " 2>&1"; // Capture errors
$output = shell_exec($command);

// Parse the output to extract modified data
$modifiedData = [];
$lines = explode("\n", $output);
$section = null;

foreach ($lines as $line) {
    if (trim($line) === "=== MODIFIED DATA ===") {
        $section = 'modified';
        continue;
    }

    if ($section === 'modified') {
        // Extract modified data using regex
        if (preg_match('/EventSource: (.+), Title: (.+), Description: (.+), Location: (.+), Date: (.+), MoreDetails: (.+), Redundant: (.+)/', $line, $matches)) {
            $modifiedData[] = [
                'EventSource' => $matches[1],
                'Title' => $matches[2],
                'Description' => $matches[3],
                'Location' => $matches[4],
                'Date' => $matches[5],
                'MoreDetails' => $matches[6],
                'Redundant' => $matches[7]
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Data Table</title>
    <link rel="stylesheet" href="../assets/upload/styles.css">
    <style>
        /* Additional styles for the table */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white; /* White background for the table */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            cursor: pointer;
            font-weight: bold;
            color: #333;
        }

        th:hover {
            background-color: #ddd;
        }

        .redundant-yes {
            color: red;
        }

        /* Style for the sort indicator */
        th[onclick] {
            position: relative;
        }

        th[onclick]::after {
            content: ' ▲▼';
            font-size: 12px;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <h2>Data Being Sent to Database</h2>
    <table border="1" cellpadding="10" cellspacing="0" id="dataTable">
        <thead>
            <tr>
                <?php
                // Dynamically generate table headers based on the keys of the first row
                foreach (array_keys($modifiedData[0]) as $columnName) {
                    if ($columnName === 'Redundant') {
                        echo "<th onclick='sortTable()' style='cursor: pointer;'>$columnName</th>";
                    } else {
                        echo "<th>$columnName</th>";
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php
            foreach ($modifiedData as $row) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    // Highlight redundant data in red
                    $redundantClass = ($key === 'Redundant' && $value === 'Yes') ? ' class="redundant-yes"' : '';
                    echo "<td$redundantClass>$value</td>";
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        let sortOrder = 1; // 1 for ascending (Yes first), -1 for descending (No first)

        function sortTable() {
            const table = document.getElementById('dataTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Sort rows based on the Redundant column (6th column, index 6)
            rows.sort((a, b) => {
                const redundantA = a.cells[6].textContent.trim();
                const redundantB = b.cells[6].textContent.trim();
                if (redundantA === redundantB) return 0;
                return sortOrder * (redundantA === 'Yes' ? 1 : -1);
            });

            // Toggle sort order for next click
            sortOrder *= -1;

            // Clear the table and re-append sorted rows
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));

            // Update the sort indicator in the header
            const redundantHeader = table.querySelector('th:nth-child(7)');
            redundantHeader.innerHTML = sortOrder === 1 ? 'Redundant ▲' : 'Redundant ▼';
        }
    </script>
</body>
</html>