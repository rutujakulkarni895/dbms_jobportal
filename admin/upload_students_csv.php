<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";

if (isset($_POST["upload"])) {
    if ($_FILES["csv"]["error"] == 0) {
        $filename = $_FILES["csv"]["tmp_name"];

        // Check if the file is a CSV
        $fileType = mime_content_type($filename);
        if ($fileType !== "text/csv" && $fileType !== "application/vnd.ms-excel") {
            $msg = "Please upload a valid CSV file.";
        } else {
            $file = fopen($filename, "r");

            // Skip header
            fgetcsv($file);

            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
                $name = $conn->real_escape_string($data[0]);
                $email = $conn->real_escape_string($data[1]);
                $phone = $conn->real_escape_string($data[2]);
                $course = $conn->real_escape_string($data[3]);
                $year = (int)$data[4];
                $resume = $conn->real_escape_string($data[5]);
                $password = password_hash($data[6], PASSWORD_DEFAULT);

                // Validate the data
                if (empty($name) || empty($email) || empty($phone) || empty($course) || empty($resume) || empty($password)) {
                    $msg = "Missing required fields in the CSV data.";
                    break;
                }

                // Check if the student already exists by email
                $checkQuery = $conn->prepare("SELECT id FROM students WHERE email = ?");
                $checkQuery->bind_param("s", $email);
                $checkQuery->execute();
                $checkResult = $checkQuery->get_result();
                
                if ($checkResult->num_rows > 0) {
                    $msg = "Student with email $email already exists.";
                    break;
                }

                // Insert the student into the database
                $insertQuery = $conn->prepare("INSERT INTO students (name, email, phone, course, year, resume, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insertQuery->bind_param("ssssiss", $name, $email, $phone, $course, $year, $resume, $password);
                $insertQuery->execute();
            }

            fclose($file);
            if (!$msg) {
                $msg = "Students uploaded successfully.";
            }
        }
    } else {
        $msg = "Error uploading file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Students CSV</title>
    <style>
        body { background: #f8f9fc; font-family: 'Segoe UI'; padding: 30px; }
        .container { max-width: 600px; background: #fff; padding: 20px; margin: auto; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        .msg { color: green; margin-top: 10px; text-align: center; }
        .btn { padding: 10px 20px; background: #4e73df; color: white; border: none; border-radius: 6px; cursor: pointer; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h2>📥 Upload Students via CSV</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="csv" accept=".csv" required><br>
        <button class="btn" type="submit" name="upload">Upload</button>
    </form>
    <?php if ($msg) echo "<div class='msg'>$msg</div>"; ?>
    <p>💡 CSV Format: <strong>Name, Email, Phone, Course, Year, Resume (file path), Password</strong></p>
</div>
</body>
</html>
