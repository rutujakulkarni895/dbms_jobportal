<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access.");
}

$student_id = $_SESSION['student_id'];

$result = $conn->query("SELECT i.*, c.name AS company_name 
                        FROM interview_schedules i 
                        JOIN companies c ON i.company_id = c.id 
                        WHERE i.student_id = $student_id 
                        ORDER BY i.scheduled_at ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Interview Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>📅 My Interview Schedules</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Company</th>
                <th>Date & Time</th>
                <th>Location</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['company_name']) ?></td>
                    <td><?= $row['scheduled_at'] ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= htmlspecialchars($row['notes']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
