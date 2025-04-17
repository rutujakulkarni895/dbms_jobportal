<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['company_id'])) {
    header("Location: ../login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// ✅ Check if the 'view_hired_students' view exists
$view_check_sql = "SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_placmenet_db = 'view_hired_students'";
$view_check_result = $conn->query($view_check_sql);

if ($view_check_result->num_rows == 0) {
    // Create the view if it doesn't exist
    $create_view_sql = "CREATE VIEW view_hired_students AS
SELECT 
    aj.id, 
    s.name AS student_name, 
    j.title AS job_title, 
    aj.applied_at, 
    aj.offer_letter_path, 
    c.name AS company_name
FROM applied_jobs aj
JOIN students s ON aj.student_id = s.id
JOIN jobs j ON aj.job_id = j.id
JOIN companies c ON aj.company_id = c.id
WHERE aj.status = 'Selected';";

    if ($conn->query($create_view_sql) === TRUE) {
        echo "View 'view_hired_students' created successfully.";
    } else {
        die("Error creating view: " . $conn->error);
    }
}

// Get the company name for filtering
$companyQuery = $conn->prepare("SELECT name FROM companies WHERE id = ?");
$companyQuery->bind_param("i", $company_id);
$companyQuery->execute();
$companyResult = $companyQuery->get_result();
$companyRow = $companyResult->fetch_assoc();
$company_name = $companyRow['name'];

// Get hired students using the view
$query = $conn->prepare("SELECT * FROM view_hired_students WHERE company_name = ?");
$query->bind_param("s", $company_name);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hired Students - Company Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f9ff;
        }
        .card {
            margin-bottom: 20px;
        }
        .title-bar {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="title-bar">
            <h2>👨‍🎓 Hired Students (Approved Applications)</h2>
            <p class="text-muted">Here are the students hired by your company.</p>
        </div>

        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card p-3 shadow-sm">
                            <h5 class="card-title"><?= htmlspecialchars($row['student_name']) ?></h5>
                            <p><strong>Job Title:</strong> <?= htmlspecialchars($row['job_title']) ?></p>
                            <p><strong>Applied At:</strong> <?= $row['applied_at'] ?></p>
                            <a class="btn btn-success" href="../<?= $row['offer_letter_path'] ?>" target="_blank">📄 View Offer Letter</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">No students have been hired yet.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
