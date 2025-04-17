<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// ✅ Check if 'applied_jobs' table exists
$table_check_sql = "SHOW TABLES LIKE 'applied_jobs'";
$table_check_result = $conn->query($table_check_sql);

if ($table_check_result->num_rows == 0) {
    // Create the table if it doesn't exist
    $create_table_sql = "CREATE TABLE applied_jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        job_id INT NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Applied',
        offer_letter_generated BOOLEAN DEFAULT 0,
        offer_letter_path VARCHAR(255) NULL,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (job_id) REFERENCES jobs(id)
    )";

    if ($conn->query($create_table_sql) === TRUE) {
        echo "Table 'applied_jobs' created successfully.";
    } else {
        die("Error creating table: " . $conn->error);
    }
}

if (!isset($_SESSION['company_id'])) {
    die("Unauthorized Access!");
}

if (!isset($_POST['id']) || !isset($_POST['status'])) {
    die("Invalid request: Missing application ID or status.");
}

$id = $_POST['id'];
$status = $_POST['status'];

// Update status in applied_jobs
$update_sql = "UPDATE applied_jobs SET status = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);

if (!$update_stmt) {
    die("Query Preparation Failed: " . $conn->error);
}

$update_stmt->bind_param("si", $status, $id);

if ($update_stmt->execute()) {
    header("Location: view_applications.php");
    exit;
} else {
    echo "Error updating application.";
}

$update_stmt->close();
$conn->close();
?>
