<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$students = $conn->query("SELECT * FROM view_student_activity_summary");

// Update `jobs_viewed` to 0 if it's NULL for any student
while ($row = $students->fetch_assoc()) {
    if (is_null($row['jobs_viewed'])) {
        $update = $conn->prepare("UPDATE student_activity_view SET jobs_viewed = 0 WHERE id = ?");
        $update->bind_param("i", $row['id']);
        $update->execute();
    }
}

$students->data_seek(0); // Reset the pointer to the beginning of the result set
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Activity</title>
    <style>
        body { font-family: 'Segoe UI'; background: #f4f6f8; padding: 20px; }
        .card { background: white; padding: 20px; margin: 15px auto; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); max-width: 600px; }
        h2 { text-align: center; color: #4e73df; }
        .stat { font-size: 16px; margin: 10px 0; }
    </style>
</head>
<body>

<h2>📊 Student Activity Dashboard</h2>

<?php while ($row = $students->fetch_assoc()): ?>
    <div class="card">
        <h3>👤 <?= htmlspecialchars($row['name']) ?></h3>
        <div class="stat">🕒 Last Login: <strong><?= !empty($row['last_login']) ? $row['last_login'] : 'Never' ?></strong></div>
        <div class="stat">🔎 Jobs Viewed: <strong><?= number_format($row['jobs_viewed']) ?></strong></div>
        <div class="stat">📄 Applications Submitted: <strong><?= number_format($row['applications_submitted']) ?></strong></div>
        <div class="stat">🎁 Offers Received: <strong><?= number_format($row['offers_received']) ?></strong></div>
    </div>
<?php endwhile; ?>

</body>
</html>
