<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'resume' and 'status' columns exist in the 'students' table
$columnsToCheck = ['resume', 'status'];
foreach ($columnsToCheck as $column) {
    $columnCheckQuery = "SHOW COLUMNS FROM students LIKE '$column'";
    $columnCheckResult = $conn->query($columnCheckQuery);

    if ($columnCheckResult->num_rows == 0) {
        // If the column doesn't exist, skip adding it in the CSV export
        $$column = null;
    }
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students_export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Course', 'Year', 'Resume', 'Status']);

// Fetch and write student data to CSV
$result = $conn->query("SELECT * FROM students");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'], $row['name'], $row['email'], $row['phone'],
        $row['course'], $row['year'], $row['resume'] ?? 'N/A', // If 'resume' doesn't exist, use 'N/A'
        $row['status'] ?? 'Active' // Default status if missing
    ]);
}

fclose($output);
?>
