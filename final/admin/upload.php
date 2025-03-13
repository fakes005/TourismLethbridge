<?php
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['file']['name']);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
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
                        'eventSource' => $matches[1],
                        'Title' => $matches[2],
                        'eventDescription' => $matches[3],
                        'location' => $matches[4],
                        'eventdate' => $matches[5],
                        'moredetails' => $matches[6],
                        'redundant' => $matches[7]  // Redundant status
                    ];
                }
            }
        }

        // Display modified data in a table with sorting functionality
        if (!empty($modifiedData)) {
            echo "<h2>Data Being Sent to Database</h2>";
            echo "<table border='1' cellpadding='10' cellspacing='0' id='dataTable'>";
            echo "<thead>";
            echo "<tr>
                    <th>Event Source</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>More Details</th>
                    <th onclick='sortTable()' style='cursor: pointer;'>Redundant ▲▼</th>
                  </tr>";
            echo "</thead>";
            echo "<tbody id='tableBody'>";
            foreach ($modifiedData as $row) {
                // Highlight redundant data in red
                $redundantStyle = ($row['redundant'] === 'Yes') ? ' style="color:red;"' : '';
                echo "<tr>";
                echo "<td>{$row['eventSource']}</td>";
                echo "<td{$redundantStyle}>{$row['Title']}</td>";
                echo "<td{$redundantStyle}>{$row['eventDescription']}</td>";
                echo "<td>{$row['location']}</td>";
                echo "<td>{$row['eventdate']}</td>";
                echo "<td>{$row['moredetails']}</td>";
                echo "<td>{$row['redundant']}</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";

            // JavaScript for sorting
            echo "<script>
                let sortOrder = 1; // 1 for ascending, -1 for descending

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
            </script>";
        } else {
            echo "<p>No modified data found in the Python script's output.</p>";
        }
    } else {
        echo "Failed to upload file.";
    }
} else {
    echo "No file uploaded.";
}
?>