<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'students' table exists
$tableCheckQuery = "SHOW TABLES LIKE 'students'";
$tableCheckResult = $conn->query($tableCheckQuery);

if ($tableCheckResult->num_rows == 0) {
    // Table does not exist, create the table
    $createTableQuery = "
    CREATE TABLE students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        course VARCHAR(100) NOT NULL,
        year INT NOT NULL
    )";
    if ($conn->query($createTableQuery) === TRUE) {
        echo "Table 'students' created successfully.<br>";
    } else {
        echo "Error creating table: " . $conn->error;
    }
} else {
    // Table exists, check for missing columns and add them if necessary
    $columnsToAdd = [];

    // Check if 'name' column exists
    $columnCheckQuery = "SHOW COLUMNS FROM students LIKE 'name'";
    $columnCheckResult = $conn->query($columnCheckQuery);
    if ($columnCheckResult->num_rows == 0) {
        $columnsToAdd[] = "name VARCHAR(255) NOT NULL";
    }

    // Check if 'email' column exists
    $columnCheckQuery = "SHOW COLUMNS FROM students LIKE 'email'";
    $columnCheckResult = $conn->query($columnCheckQuery);
    if ($columnCheckResult->num_rows == 0) {
        $columnsToAdd[] = "email VARCHAR(255) NOT NULL";
    }

    // Check if 'phone' column exists
    $columnCheckQuery = "SHOW COLUMNS FROM students LIKE 'phone'";
    $columnCheckResult = $conn->query($columnCheckQuery);
    if ($columnCheckResult->num_rows == 0) {
        $columnsToAdd[] = "phone VARCHAR(15) NOT NULL";
    }

    // Check if 'course' column exists
    $columnCheckQuery = "SHOW COLUMNS FROM students LIKE 'course'";
    $columnCheckResult = $conn->query($columnCheckQuery);
    if ($columnCheckResult->num_rows == 0) {
        $columnsToAdd[] = "course VARCHAR(100) NOT NULL";
    }

    // Check if 'year' column exists
    $columnCheckQuery = "SHOW COLUMNS FROM students LIKE 'year'";
    $columnCheckResult = $conn->query($columnCheckQuery);
    if ($columnCheckResult->num_rows == 0) {
        $columnsToAdd[] = "year INT NOT NULL";
    }

    // Add missing columns if any
    if (!empty($columnsToAdd)) {
        $alterTableQuery = "ALTER TABLE students ADD " . implode(", ADD ", $columnsToAdd);
        if ($conn->query($alterTableQuery) === TRUE) {
            echo "Missing columns added successfully.<br>";
        } else {
            echo "Error adding columns: " . $conn->error;
        }
    }
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "Invalid student ID";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
    $year = $_POST['year'];

    $update = $conn->prepare("UPDATE students SET name=?, email=?, phone=?, course=?, year=? WHERE id=?");
    $update->bind_param("sssssi", $name, $email, $phone, $course, $year, $id);
    $update->execute();

    header("Location: manage_students.php?updated=true");
    exit;
}

$result = $conn->query("SELECT * FROM students WHERE id=$id");
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <style>
        body {
            background: #f2f4f7;
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
        }
        .form-container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
        }
        input[type="text"], input[type="email"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .btn {
            background: #4e73df;
            color: white;
            padding: 10px 20px;
            border: none;
            margin-top: 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2e59d9;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>✏️ Edit Student</h2>
    <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required>

        <label>Course</label>
        <input type="text" name="course" value="<?= htmlspecialchars($student['course']) ?>" required>

        <label>Year</label>
        <input type="number" name="year" value="<?= htmlspecialchars($student['year']) ?>" required>

        <button type="submit" class="btn">Update</button>
    </form>
</div>

</body>
</html>
