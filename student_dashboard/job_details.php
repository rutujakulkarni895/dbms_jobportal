<?php
// Database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "placement_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check and create tables if they do not exist
checkAndCreateTables($conn);

// Get the job ID from URL parameter
$job_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;
if (!$job_id) {
    die("Invalid Job ID.");
}

// Query to fetch job details
$sql = "SELECT title, company_name, description, requirements, salary FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Statement preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<h4>" . htmlspecialchars($row['title']) . "</h4>";
    echo "<h6 class='text-muted'>" . htmlspecialchars($row['company_name']) . "</h6>";
    echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($row['description'])) . "</p>";
    echo "<p><strong>Requirements:</strong> " . nl2br(htmlspecialchars($row['requirements'])) . "</p>";
    echo "<p><strong>Salary:</strong> " . htmlspecialchars($row['salary']) . "</p>";
} else {
    die("Job not found.");
}

$stmt->close();
$conn->close();

// Function to check if tables exist and create them if they don't
function checkAndCreateTables($conn) {
    // Check and create the "jobs" table
    $create_jobs_table = "
        CREATE TABLE IF NOT EXISTS jobs (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            requirements TEXT NOT NULL,
            salary DECIMAL(10, 2) NOT NULL
        )";
    
    if (!$conn->query($create_jobs_table)) {
        die("Error creating 'jobs' table: " . $conn->error);
    }

    // Optionally, check and create the "students" table if it doesn't exist
    $create_students_table = "
        CREATE TABLE IF NOT EXISTS students (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE
        )";

    if (!$conn->query($create_students_table)) {
        die("Error creating 'students' table: " . $conn->error);
    }
}
?>
