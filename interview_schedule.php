<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if (!isset($_SESSION['company_id'])) {
    die("Unauthorized access.");
}

$company_id = $_SESSION['company_id'];

// Fetch hired students
$students = $conn->query("SELECT aj.student_id, s.name 
                          FROM applied_jobs aj 
                          JOIN students s ON aj.student_id = s.id 
                          WHERE aj.company_id = $company_id AND aj.status = 'Selected'");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $scheduled_at = $_POST['scheduled_at'];
    $location = $_POST['location'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("CALL ScheduleInterview(?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $student_id, $company_id, $scheduled_at, $location, $notes);
    $stmt->execute();

    echo "<script>alert('Interview Scheduled!'); window.location='company_schedule_interview.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Schedule Interview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>📅 Schedule Interview for Hired Students</h3>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Select Student</label>
            <select class="form-select" name="student_id" required>
                <?php while ($row = $students->fetch_assoc()) { ?>
                    <option value="<?= $row['student_id'] ?>"><?= $row['name'] ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Scheduled At</label>
            <input type="datetime-local" class="form-control" name="scheduled_at" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" class="form-control" name="location" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes (optional)</label>
            <textarea class="form-control" name="notes"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Schedule Interview</button>
    </form>
</div>
</body>
</html>
