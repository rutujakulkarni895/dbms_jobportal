<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "placmenet_db"; // Ensure correct database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Check if the 'jobs' table exists
$table_check_sql = "SHOW TABLES LIKE 'jobs'";
$table_check_result = $conn->query($table_check_sql);

if ($table_check_result->num_rows == 0) {
    // Create the jobs table if it doesn't exist
    $create_table_sql = "CREATE TABLE jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        location VARCHAR(255) NOT NULL,
        salary DECIMAL(10, 2) NOT NULL
    )";

    if ($conn->query($create_table_sql) === TRUE) {
        echo "Table 'jobs' created successfully.";
    } else {
        die("Error creating table: " . $conn->error);
    }
}

// Fetch job postings
$sql = "SELECT id, title, company_name, location, salary FROM jobs";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings - Placement.co</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Placement.co</a>
        </div>
    </nav>

    <!-- Job Listings -->
    <section class="container py-5">
        <h3 class="text-center text-primary">Latest Job Openings</h3>
        <div class="row mt-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-lg">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($row['company_name']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                                <p><strong>Salary:</strong> ₹<?php echo htmlspecialchars($row['salary']); ?> LPA</p>
                                <a href="apply.php?job_id=<?php echo $row['id']; ?>" class="btn btn-success">Apply Now</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No job listings available.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p class="mt-3">&copy; 2025 Placement.co. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

<?php
$conn->close();
?>
