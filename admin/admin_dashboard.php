<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "placmenet_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tableCheckQuery = "SHOW TABLES LIKE 'admin'";
$result = $conn->query($tableCheckQuery);
if ($result->num_rows == 0) {
    $createAdminTable = "CREATE TABLE admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($createAdminTable)) {
        die('❌ Failed to create admin table: ' . $conn->error);
    }
}

$admin_id = $_SESSION['id'];
$adminQuery = $conn->query("SELECT name FROM admin WHERE id = $admin_id");
$admin = $adminQuery->fetch_assoc();

// Metrics Queries
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$totalCompanies = $conn->query("SELECT COUNT(*) as count FROM companies")->fetch_assoc()['count'];
$totalJobs = $conn->query("SELECT COUNT(*) as count FROM jobs")->fetch_assoc()['count'];
$placedStudents = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM offers")->fetch_assoc()['count'];

$recentLogs = $conn->query("SELECT * FROM admin_logs ORDER BY performed_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 15px; }
        .metric-card h3 { font-size: 2rem; }
        .metric-card p { font-size: 1rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <div class="d-flex">
            <span class="navbar-text text-white me-3">Logged in as: <strong><?= $admin['name'] ?></strong></span>
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4 animate__animated animate__fadeInDown">Welcome, <?= $admin['name'] ?> 👋</h2>

    <!-- METRIC CARDS -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card p-4 text-center metric-card bg-primary text-white animate__animated animate__fadeInUp">
                <h3><?= $totalStudents ?></h3>
                <p>Students</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 text-center metric-card bg-success text-white animate__animated animate__fadeInUp">
                <h3><?= $totalCompanies ?></h3>
                <p>Companies</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 text-center metric-card bg-warning text-white animate__animated animate__fadeInUp">
                <h3><?= $totalJobs ?></h3>
                <p>Jobs Posted</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 text-center metric-card bg-info text-white animate__animated animate__fadeInUp">
                <h3><?= $placedStudents ?></h3>
                <p>Placed Students</p>
            </div>
        </div>
    </div>

    <!-- ACTION CARDS -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-3 animate__animated animate__fadeIn">
                <h5>Manage Students</h5>
                <p>View, edit, or delete student accounts.</p>
                <a href="manage_students.php" class="btn btn-primary">Go</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 animate__animated animate__fadeIn">
                <h5>Manage Companies</h5>
                <p>Approve or remove registered companies.</p>
                <a href="manage_companies.php" class="btn btn-primary">Go</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 animate__animated animate__fadeIn">
                <h5>Interview Schedules</h5>
                <p>Look into interviews scheduled</p>
                <a href="admin_view_interviews.php" class="btn btn-primary">Go</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 animate__animated animate__fadeIn">
                <h5>Admin Settings</h5>
                <p>Update your profile settings or change password.</p>
                <a href="admin_settings.php" class="btn btn-primary">Go</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3 animate__animated animate__fadeIn">
                <h5>📝 Recent Admin Logs</h5>
                <ul class="list-group list-group-flush">
                    <?php while ($log = $recentLogs->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <?= $log['action'] ?> - <?= $log['status'] ?> on Job ID <?= $log['job_id'] ?> / Student ID <?= $log['student_id'] ?>
                            <small class="d-block text-muted">at <?= $log['performed_at'] ?></small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- CHART SECTION -->
    <div class="card mt-5 p-4 animate__animated animate__fadeInUp">
        <h5 class="mb-3">📊 Job Postings Over Time</h5>
        <canvas id="jobChart" height="100"></canvas>
    </div>
</div>

<script>
    const ctx = document.getElementById('jobChart').getContext('2d');
    fetch('admin_job_chart_data.php')
        .then(response => response.json())
        .then(data => {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Jobs Posted',
                        data: data.counts,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
</script>

</body>
</html>
