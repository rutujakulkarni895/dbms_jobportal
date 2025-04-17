<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "placmenet_db";

// Create DB connection
$conn = new mysqli($servername, $username, $password, $dbname);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Step 1: Ensure the 'companies' table exists
$createCompaniesTable = "
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    website VARCHAR(255),
    description TEXT,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($createCompaniesTable)) {
    die("Table creation failed: " . $conn->error);
}

// ✅ Step 2: Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form inputs safely
    $company_name = trim($_POST['companyName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $website = trim($_POST['website']);
    $description = trim($_POST['description']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hash

    // ✅ Validate phone number (10 digits)
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        die("❌ Invalid phone number. Must be exactly 10 digits.");
    }

    // ✅ Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM companies WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        die("❌ Email already registered. Please log in.");
    }
    $checkStmt->close();

    // ✅ Prepare SQL insert
    $stmt = $conn->prepare("INSERT INTO companies (name, email, phone, address, website, description, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $company_name, $email, $phone, $address, $website, $description, $password);

    if ($stmt->execute()) {
        $company_id = $stmt->insert_id;
        $_SESSION['company_id'] = $company_id;
        $_SESSION['company_name'] = $company_name;

        echo "✅ Company registered successfully! Your ID: " . $company_id;
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
