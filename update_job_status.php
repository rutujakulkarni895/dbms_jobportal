<?php
session_start();

// ✅ Manual database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "placmenet_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Ensure company is logged in
if (!isset($_SESSION['company_id'])) {
    die(json_encode(["error" => "Unauthorized access. Please log in."]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = $_POST['job_id'];
    $new_status = $_POST['status'];
    $company_id = $_SESSION['company_id'];

    // ✅ Ensure the company owns the job before updating
    $sql = "UPDATE jobs SET status = ? WHERE id = ? AND company_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die(json_encode(["error" => "SQL error: " . $conn->error]));
    }

    $stmt->bind_param("sii", $new_status, $job_id, $company_id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "✅ Job status updated successfully!"]);
    } else {
        echo json_encode(["error" => "❌ Error updating job status: " . $stmt->error]);
    }

    $stmt->close();
}

// ✅ Close database connection
$conn->close();
exit();
?>
