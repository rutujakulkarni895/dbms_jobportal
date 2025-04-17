<?php
$servername = "localhost";  // Change if needed
$username = "root";  // Default XAMPP user
$password = "";  // Default is empty in XAMPP
$dbname = "placmenet_db"; // Corrected database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $password = $_POST['password']; // Get password from form

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // File upload handling
    $resume = $_FILES['resume']['name'];
    $resume_tmp = $_FILES['resume']['tmp_name'];
    $resume_folder = "uploads/" . basename($resume);

    // Validate phone number (exactly 10 digits)
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        die("Invalid phone number. Must be 10 digits.");
    }

    // Ensure uploads directory exists
    if (!is_dir("uploads")) {
        mkdir("uploads", 0777, true);
    }

    // Move the uploaded file
    if (move_uploaded_file($resume_tmp, $resume_folder)) {
        // Insert data into database (including password)
        $stmt = $conn->prepare("INSERT INTO students (name, email, phone, course, year, password, resume) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $phone, $course, $year, $hashed_password, $resume_folder);

        if ($stmt->execute()) {
            echo "Student registered successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Failed to upload resume.";
    }
}

// Close connection
$conn->close();
?>
