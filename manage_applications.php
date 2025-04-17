<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "placmenet_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Check if company is logged in
if (!isset($_SESSION['company_id'])) {
    echo "Unauthorized Access!";
    exit;
}

$company_id = $_SESSION['company_id'];

if (!isset($_GET['application_id']) || !is_numeric($_GET['application_id'])) {
    echo "Invalid request!";
    exit;
}

$application_id = $_GET['application_id'];

// Fetch application details
$sql = "SELECT aj.id, s.name AS student_name, s.email, s.phone, s.course, s.year,
           j.title AS job_title, j.company_name, s.resume, aj.applied_at, aj.status 
    FROM applied_jobs aj
    JOIN students s ON aj.student_id = s.id
    JOIN jobs j ON aj.job_id = j.id
    WHERE aj.id = ? AND j.company_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query Preparation Failed: " . $conn->error);
}
$stmt->bind_param("ii", $application_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

if (!$application) {
    echo "Application not found!";
    exit;
}

// Handle Approve/Reject
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['status']) || !in_array($_POST['status'], ['Approved', 'Rejected'])) {
        echo "Invalid status!";
        exit;
    }
    
    $status = $_POST['status'];
    $update_sql = "UPDATE applied_jobs SET status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $status, $application_id);

    if ($update_stmt->execute()) {
        header("Location: view_applications.php?msg=Application+{$status}+Successfully");
        exit;
    } else {
        echo "Error updating application.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Application</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Manage Application for <?php echo htmlspecialchars($application['student_name']); ?></h2>
        <p><strong>Job Title:</strong> <?php echo htmlspecialchars($application['job_title']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($application['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($application['phone']); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($application['course']); ?> - Year <?php echo htmlspecialchars($application['year']); ?></p>
        <p><strong>Applied At:</strong> <?php echo htmlspecialchars($application['applied_at']); ?></p>
        <p><strong>Current Status:</strong> 
            <span class="badge bg-<?php echo $application['status'] == 'Approved' ? 'success' : ($application['status'] == 'Rejected' ? 'danger' : 'secondary'); ?>">
                <?php echo htmlspecialchars($application['status'] ?: 'Pending'); ?>
            </span>
        </p>

        <a href="<?php echo htmlspecialchars($application['resume']); ?>" class="btn btn-success" download>Download Resume</a>
        <a href="view_applications.php" class="btn btn-secondary ms-2">Back to Applications</a>

        <form method="POST" class="mt-4">
            <button type="submit" name="status" value="Approved" class="btn btn-primary me-2">Approve</button>
            <button type="submit" name="status" value="Rejected" class="btn btn-danger">Reject</button>
        </form>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
