<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "placmenet_db");
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Create 'jobs' table if it doesn't exist
$createJobsTable = "
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    requirements TEXT,
    salary VARCHAR(100),
    location VARCHAR(255),
    company_name VARCHAR(255),
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createJobsTable);

// Create 'applied_jobs' table if it doesn't exist
$createAppliedJobsTable = "
CREATE TABLE IF NOT EXISTS applied_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    offer_letter_generated BOOLEAN DEFAULT 0,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
)";
$conn->query($createAppliedJobsTable);

// Ensure student is logged in
session_start();
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized Access!");
}

$student_id = $_SESSION['student_id'];

// Prepare SQL to fetch job applications
$sql = "
    SELECT aj.id, j.title AS job_title, 
           j.company_name, 
           aj.status, aj.applied_at, aj.offer_letter_generated
    FROM applied_jobs aj
    JOIN jobs j ON aj.job_id = j.id
    WHERE aj.student_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query Preparation Failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Status</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="text-primary">Application Status</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Offer Letter</th>
                    <th>Applied On</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['job_title']) ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] == 'approved'): ?>
                                <?php if ($row['offer_letter_generated']): ?>
                                    <a href="offer_letters/offer_letter_<?= $row['id'] ?>.pdf" 
                                       class="btn btn-success btn-sm" target="_blank">Download</a>
                                <?php else: ?>
                                    <a href="generate_offer_letter.php?application_id=<?= $row['id'] ?>" 
                                       class="btn btn-primary btn-sm">Generate</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not Available</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['applied_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-muted">No applications found.</p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
