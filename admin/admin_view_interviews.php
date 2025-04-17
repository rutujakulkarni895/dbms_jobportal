<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");

$result = $conn->query("SELECT i.*, s.name AS student_name, c.name AS company_name 
                        FROM interview_schedules i
                        JOIN students s ON i.student_id = s.id
                        JOIN companies c ON i.company_id = c.id
                        ORDER BY i.scheduled_at ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Interview Schedules - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>📋 All Interview Schedules</h3>
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Student</th>
                <th>Company</th>
                <th>Date & Time</th>
                <th>Location</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
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
