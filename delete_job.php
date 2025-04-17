<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Validate job ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid job ID.");
}

$job_id = intval($_GET['id']); // Sanitized integer

// ✅ Optional: Check if job exists before deletion
$checkStmt = $conn->prepare("SELECT id FROM jobs WHERE id = ?");
$checkStmt->bind_param("i", $job_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows === 0) {
    die("❌ Job not found.");
}
$checkStmt->close();

// ✅ Delete job
$sql = "DELETE FROM jobs WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $job_id);

if ($stmt->execute()) {
    header("Location: job_listings.php?deleted=1");
    exit();
} else {
    echo "❌ Error deleting job: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
