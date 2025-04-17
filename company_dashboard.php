<?php 
session_start();
include "check_job_expiry.php"; 

$conn = new mysqli("localhost", "root", "", "placmenet_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION["company_id"])) {
    die("Session not set. Please log in again.");
}

$company_id = $_SESSION["company_id"];

// Fetch company name
$query = "SELECT name FROM companies WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $company_id);
$stmt->execute();
$stmt->bind_result($company_name);
$stmt->fetch();
$stmt->close();

// Fetch dashboard statistics using stored procedure
$total_jobs = $applications_received = $interviews_scheduled = $students_hired = 0;

$stats_stmt = $conn->prepare("CALL get_company_dashboard_stats(?)");
if ($stats_stmt) {
    $stats_stmt->bind_param("i", $company_id);
    if ($stats_stmt->execute()) {
        $result = $stats_stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            $total_jobs = $row['total_jobs'];
            $applications_received = $row['applications_received'];
            $interviews_scheduled = $row['interviews_scheduled'];
            $students_hired = $row['students_hired'];
        }
        $result->close();
    }
    $stats_stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($company_name); ?> - Placement.co</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="newweb/style.css">
    <style>
        .card {
            animation: fadeInUp 0.8s ease-in-out;
        }
        @keyframes fadeInUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
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
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="student.html">Student Registration</a></li>
                <li class="nav-item"><a class="nav-link" href="company.html">Company Registration</a></li>
                <li class="nav-item"><a class="nav-link" href="job_listings.php">Job Listings</a></li>
                <li class="nav-item"><a class="nav-link" href="placement_results.php">Placement Results</a></li>
                <li class="nav-item"><a class="nav-link active" href="company_dashboard.php">Company Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_panel.php">Admin Panel</a></li>
                <li class="nav-item"><a class="nav-link" href="student_dashboard.php">Student Dashboard</a></li>
                <li class="nav-item"><a class="btn btn-warning" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Company Dashboard -->
<div class="container mt-4">
    <h2 class="text-primary text-center">Welcome, <?php echo htmlspecialchars($company_name); ?></h2>

    <div class="row mt-4">
        <div class="col-md-3"><a href="job_posting.php" class="btn btn-success d-block">Post a Job</a></div>
        <div class="col-md-3"><a href="job_listings.php" class="btn btn-info d-block">View Job Listings</a></div>
        <div class="col-md-3"><a href="view_applications.php" class="btn btn-warning d-block">View Applications</a></div>
        <div class="col-md-3"><a href="edit_company_profile.php" class="btn btn-primary d-block">Edit Company Profile</a></div>
        <div class="col-md-3 mt-3"><a href="hired_students.php" class="btn btn-danger d-block">Hired Students</a></div>
        <div class="col-md-3 mt-3"><a href="interview_schedule.php" class="btn btn-secondary d-block">Schedule Interviews</a></div>
    </div>

    <!-- Stats Section -->
    <div class="row text-center mt-5">
        <h3 class="text-secondary">📊 Your Company Statistics</h3>
        <div class="col-md-3 mt-4">
            <div class="card shadow-sm border-start border-primary border-3">
                <div class="card-body">
                    <h5 class="card-title text-primary">Jobs Posted</h5>
                    <p class="display-6"><?php echo $total_jobs; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mt-4">
            <div class="card shadow-sm border-start border-info border-3">
                <div class="card-body">
                    <h5 class="card-title text-info">Applications</h5>
                    <p class="display-6"><?php echo $applications_received; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mt-4">
            <div class="card shadow-sm border-start border-warning border-3">
                <div class="card-body">
                    <h5 class="card-title text-warning">Interviews</h5>
                    <p class="display-6"><?php echo $interviews_scheduled; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mt-4">
            <div class="card shadow-sm border-start border-success border-3">
                <div class="card-body">
                    <h5 class="card-title text-success">Students Hired</h5>
                    <p class="display-6"><?php echo $students_hired; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <div class="container">
        <p class="mt-3">&copy; 2025 Placement.co. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>
