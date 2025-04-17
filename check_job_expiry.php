<?php
// ✅ Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "placmenet_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Create jobs table if not exists
$createTableSQL = "
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    salary VARCHAR(100),
    requirements TEXT,
    expiry_date DATE,
    status VARCHAR(50) DEFAULT 'Active',
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
)";
if (!$conn->query($createTableSQL)) {
    die("Error creating jobs table: " . $conn->error);
}

// ✅ Get today's date
$today = date("Y-m-d");

// ✅ Step 1: Find expired jobs
$sql = "SELECT id, company_id, title FROM jobs WHERE expiry_date < ? AND status != 'Expired'";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$expiredJobs = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expiredJobs[] = $row;
    }
    $result->free();
}

$stmt->close();

// ✅ Step 2: Update job status to "Expired"
if (!empty($expiredJobs)) {
    $updateSql = "UPDATE jobs SET status = 'Expired' WHERE expiry_date < ? AND status != 'Expired'";
    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {
        die("Update preparation failed: " . $conn->error);
    }

    $updateStmt->bind_param("s", $today);
    $updateStmt->execute();
    $updateStmt->close();
}

// ✅ Step 3: Notify companies
if (!empty($expiredJobs)) {
    foreach ($expiredJobs as $job) {
        echo "🔴 Job Expired: <strong>" . htmlspecialchars($job['title']) . "</strong> (Company ID: " . $job['company_id'] . ")<br>";
    }
} else {
    echo "✅ No jobs expired today.";
}

// ✅ Close connection
$conn->close();
?>
