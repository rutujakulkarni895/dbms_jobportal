<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "placmenet_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Create 'jobs' table if it doesn't exist
$createTableSQL = "
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255),
    salary VARCHAR(100),
    requirements TEXT,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($createTableSQL)) {
    die("Error creating table: " . $conn->error);
}

// Ensure only logged-in companies can post jobs
if (!isset($_SESSION['company_id']) || !isset($_SESSION['name'])) {
    die("Unauthorized access. Please log in as a company.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_title = $_POST['job_title'];
    $job_description = $_POST['job_description'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    //$requirements = $_POST['requirements'];
    $company_name = $_SESSION['name'];
    $company_id = $_SESSION['company_id'];

    if (empty($company_name) || empty($company_id)) {
        die("Error: Company details missing in session.");
    }

    $sql = "INSERT INTO jobs (company_id, company_name, title, description, location, salary) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error in SQL Query: " . $conn->error);
    }

    $stmt->bind_param("isssss", $company_id, $company_name, $job_title, $job_description, $location, $salary);

    if ($stmt->execute()) {
        echo "Job posted successfully!";
        header("Location: job_listings.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
