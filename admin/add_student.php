<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check and create 'students' table if it doesn't exist
$tableCheckQuery = "SHOW TABLES LIKE 'students'";
$result = $conn->query($tableCheckQuery);
if ($result->num_rows == 0) {
    $createTableSQL = "CREATE TABLE students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(15) NOT NULL,
        course VARCHAR(100) NOT NULL,
        year INT NOT NULL,
        resume VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($createTableSQL)) {
        die('❌ Failed to create students table: ' . $conn->error);
    }
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $course = $_POST["course"];
    $year = $_POST["year"];
    $resume = $_POST["resume"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO students (name, email, phone, course, year, resume, password) 
            VALUES ('$name', '$email', '$phone', '$course', $year, '$resume', '$password')";
    
    if ($conn->query($sql)) {
        $msg = "✅ Student added successfully!";
    } else {
        $msg = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <style>
        body { background: #edf2f6; font-family: 'Segoe UI'; padding: 30px; }
        .form-box { max-width: 500px; background: #fff; padding: 25px; border-radius: 10px; margin: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 10px; margin-top: 6px; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #1cc88a; padding: 10px 20px; color: white; border: none; margin-top: 20px; border-radius: 6px; cursor: pointer; }
        .msg { text-align: center; margin-top: 15px; color: green; }
    </style>
</head>
<body>
<div class="form-box">
    <h2>👤 Add New Student</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Phone:</label>
        <input type="text" name="phone" required>

        <label>Course:</label>
        <input type="text" name="course" required>

        <label>Year:</label>
        <input type="number" name="year" required>

        <label>Resume (Path):</label>
        <input type="text" name="resume" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Add Student</button>
    </form>
    <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
</div>
</body>
</html>
