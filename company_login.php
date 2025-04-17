<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "placmenet_db";

// Create DB connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Ensure companies table exists
$createCompaniesTable = "
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    location VARCHAR(255),
    industry VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($createCompaniesTable)) {
    die("Error creating companies table: " . $conn->error);
}

// ✅ If form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, password FROM companies WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // ✅ If company found
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($company_id, $company_name, $hashed_password);
        $stmt->fetch();

        // ✅ Verify password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['company_id'] = $company_id;
            $_SESSION['name'] = $company_name;

            header("Location: company_dashboard.php");
            exit();
        } else {
            echo "❌ Invalid email or password.";
        }
    } else {
        echo "❌ Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>
