<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the 'jobs' table exists
$table_check_sql = "SHOW TABLES LIKE 'jobs'";
$table_check_result = $conn->query($table_check_sql);

if ($table_check_result->num_rows == 0) {
    die("❌ Jobs table does not exist in the database.");
}

// Ensure a job ID is provided
if (!isset($_GET['id'])) {
    die("Invalid job ID.");
}

$job_id = $_GET['id'];

// Fetch existing job details
$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    die("Job not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars($_POST['job_title']); // Escape for security
    $location = htmlspecialchars($_POST['location']); // Escape for security
    $salary = $_POST['salary'];
    $description = htmlspecialchars($_POST['job_description']); // Escape for security
    $status = $_POST['status'];

    // Basic validation for salary input
    if (!is_numeric($salary)) {
        die("❌ Salary must be a valid number.");
    }

    // Update job posting in the database
    $update_sql = "UPDATE jobs SET title=?, location=?, salary=?, description=?, status=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssi", $title, $location, $salary, $description, $status, $job_id);

    if ($stmt->execute()) {
        header("Location: job_listings.php?updated=1"); // Redirect with success message
        exit();
    } else {
        echo "Error updating job: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - Placement.co</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-primary">Edit Job Posting</h2>
        <form action="" method="POST">
            <label>Job Title:</label>
            <input type="text" name="job_title" class="form-control" value="<?= $job['title'] ?>" required>

            <label>Location:</label>
            <input type="text" name="location" class="form-control" value="<?= $job['location'] ?>" required>

            <label>Salary:</label>
            <input type="text" name="salary" class="form-control" value="<?= $job['salary'] ?>" required>

            <label>Job Description:</label>
            <textarea name="job_description" class="form-control" required><?= $job['description'] ?></textarea>

            <label>Status:</label>
            <select name="status" class="form-control">
                <option value="Open" <?= $job['status'] == 'Open' ? 'selected' : '' ?>>Open</option>
                <option value="Closed" <?= $job['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
            </select>

            <button type="submit" class="btn btn-success mt-3">Update Job</button>
        </form>
    </div>
</body>
</html>
