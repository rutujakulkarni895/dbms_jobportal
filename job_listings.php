<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "placmenet_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Check if the 'jobs' table exists
$table_check_sql = "SHOW TABLES LIKE 'jobs'";
$table_check_result = $conn->query($table_check_sql);

if ($table_check_result->num_rows == 0) {
    // Drop if needed and create the jobs table with required structure
    $create_table_sql = "CREATE TABLE jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        company_id INT NOT NULL,
        location VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        salary DECIMAL(10, 2) NOT NULL,
        status ENUM('Open', 'Closed', 'Filled') DEFAULT 'Open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )";

    if ($conn->query($create_table_sql) === TRUE) {
        echo "<div style='padding:10px;background-color:#d4edda;color:#155724;'>Table 'jobs' created successfully.</div>";
    } else {
        die("Error creating table: " . $conn->error);
    }
}

// ✅ Only logged-in companies can access
if (!isset($_SESSION['company_id'])) {
    die("Unauthorized access. Please log in as a company.");
}

// ✅ Fetch jobs for the logged-in company
$sql = "SELECT id, title, location, description, salary, status,posted_on FROM jobs WHERE company_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['company_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings - Placement.co</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">Placement.co</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="company_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="job_listings.php">Job Listings</a></li>
                <li class="nav-item"><a class="nav-link" href="job_posting.php">Post a Job</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Job Listings -->
<div class="container mt-5">
    <h2 class="text-center text-primary">Your Job Listings</h2>
    <table class="table table-striped table-bordered mt-3">
        <thead class="table-primary">
            <tr>
                <th>Job Title</th>
                <th>Location</th>
                <th>Description</th>
                <th>Salary</th>
                <th>Status</th>
                <th>Posted On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['salary']) ?></td>
                    <td>
                        <form class="status-form" method="post">
                            <input type="hidden" name="job_id" value="<?= $row['id'] ?>">
                            <select name="status" class="form-select form-select-sm status-dropdown">
                                <option value="Open" <?= ($row['status'] == "Open") ? "selected" : "" ?>>Open</option>
                                <option value="Closed" <?= ($row['status'] == "Closed") ? "selected" : "" ?>>Closed</option>
                                <option value="Filled" <?= ($row['status'] == "Filled") ? "selected" : "" ?>>Filled</option>
                            </select>
                        </form>
                    </td>
                    <td><?= htmlspecialchars($row['posted_on']) ?></td>
                    <td>
                        <a href="edit_job.php?id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete_job.php?id=<?= urlencode($row['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this job?');">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <div class="container">
        <p class="mt-3">&copy; 2025 Placement.co. All Rights Reserved.</p>
    </div>
</footer>

<!-- Script to handle status update -->
<script>
    $(document).ready(function () {
        $(".status-dropdown").change(function () {
            var form = $(this).closest("form");
            var jobId = form.find("input[name='job_id']").val();
            var newStatus = $(this).val();

            $.ajax({
                url: "update_job_status.php",
                type: "POST",
                data: { job_id: jobId, status: newStatus },
                success: function (response) {
                    alert(response);
                },
                error: function () {
                    alert("Error updating status.");
                }
            });
        });
    });
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
