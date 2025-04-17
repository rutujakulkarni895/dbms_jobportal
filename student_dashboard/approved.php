<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if required tables exist, and if not, create them
checkAndCreateTables($conn);

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$query = "SELECT aj.id, j.title, j.company_name, aj.offer_letter_path 
          FROM applied_jobs aj 
          JOIN jobs j ON aj.job_id = j.id 
          WHERE aj.student_id = ? AND aj.offer_letter_path IS NOT NULL";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Function to check and create tables if they do not exist
function checkAndCreateTables($conn) {
    // Check and create the "jobs" table
    $create_jobs_table = "
        CREATE TABLE IF NOT EXISTS jobs (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            company_name VARCHAR(255) NOT NULL
        )";
    
    if (!$conn->query($create_jobs_table)) {
        die("Error creating 'jobs' table: " . $conn->error);
    }

    // Check and create the "applied_jobs" table
    $create_applied_jobs_table = "
        CREATE TABLE IF NOT EXISTS applied_jobs (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id INT(11) UNSIGNED NOT NULL,
            job_id INT(11) UNSIGNED NOT NULL,
            offer_letter_path VARCHAR(255) NOT NULL,
            FOREIGN KEY (student_id) REFERENCES students(id),
            FOREIGN KEY (job_id) REFERENCES jobs(id)
        )";
    
    if (!$conn->query($create_applied_jobs_table)) {
        die("Error creating 'applied_jobs' table: " . $conn->error);
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #eef2f3; }
        .card { margin-bottom: 20px; }
        .confetti { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999; display: none; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">🎉 Approved Applications</h2>
        
        <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="col-md-6">
                    <div class="card p-3 shadow-sm">
                        <h5><?= htmlspecialchars($row['title']) ?></h5>
                        <p><strong>Company:</strong> <?= htmlspecialchars($row['company_name']) ?></p>
                        <a class="btn btn-success download-offer" 
                           href="<?= '../' . htmlspecialchars($row['offer_letter_path']) ?>" 
                           target="_blank"
                           onclick="showCongrats()">Download Offer Letter</a>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php else: ?>
            <div class="alert alert-warning">No approved applications yet.</div>
        <?php endif; ?>
    </div>

    <!-- Confetti animation canvas -->
    <canvas id="confettiCanvas" class="confetti"></canvas>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        function showCongrats() {
            const canvas = document.getElementById('confettiCanvas');
            canvas.style.display = 'block';

            const myConfetti = confetti.create(canvas, { resize: true });
            myConfetti({
                particleCount: 150,
                spread: 120,
                origin: { y: 0.6 }
            });

            setTimeout(() => {
                canvas.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
