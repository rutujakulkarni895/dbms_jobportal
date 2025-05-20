<?php
session_start();

// MySQL Database Connection
$servername = "localhost";
$username = "root";  // Change if needed
$password = "";      // Change if needed
$dbname = "placmenet_db"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check and create tables if they don't exist
checkAndCreateTables($conn);

// Check and create the uploads folder if it doesn't exist
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

// Assume student is logged in and session contains `student_id`
if (!isset($_SESSION['student_id'])) {
    die("Unauthorized access. Please log in.");
}

$student_id = $_SESSION['student_id'];

// Fetch student details from the database
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $student = $result->fetch_assoc();
} else {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f4f4f4; }
        .sidebar { height: 100vh; background: #007bff; color: white; padding: 20px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; }
        .sidebar a:hover { background: rgba(255, 255, 255, 0.2); }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="index.html"><h1>Student Panel</h1></a>
            <a href="profile.php">Profile</a>
            <a href="applied_jobs.php">Applied Jobs</a>
            <a href="application_status.php">Check Application Status</a> 
            <a href="approved.php">Approved Applications</a>
            <a href="student_interview_schedules.php">Interview Schedules</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        
        <!-- Main Content -->
        <div class="container-fluid p-4">
            <h2 class="text-primary">Student Profile</h2>

            <!-- Profile Card -->
            <div class="card p-3 mb-3">
                <h4><?php echo htmlspecialchars($student['name']); ?></h4>
                <p>Email: <?php echo htmlspecialchars($student['email']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($student['phone']); ?></p>
                <p>Course: <?php echo htmlspecialchars($student['course']); ?></p>
                <p>Year: <?php echo htmlspecialchars($student['year']); ?></p>
                <p>Resume: 
                    <?php if (!empty($student['resume'])): ?>
                        <a href="uploads/<?php echo htmlspecialchars($student['resume']); ?>" download>Download</a>
                    <?php else: ?>
                        No Resume Uploaded
                    <?php endif; ?>
                </p>
                <button class="btn btn-warning" onclick="toggleEditForm()">Edit Profile</button>
            </div>

            <!-- Edit Profile Form -->
            <div id="editProfileForm" class="card p-3" style="display: none;">
                <h4>Edit Profile</h4>
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <input type="text" name="course" class="form-control" value="<?php echo htmlspecialchars($student['course']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" value="<?php echo htmlspecialchars($student['year']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Resume</label>
                        <input type="file" name="resume" class="form-control">
                    </div>
                    
                    <button type="submit" name="update" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="toggleEditForm()">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function toggleEditForm() {
            let form = document.getElementById("editProfileForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $course = $_POST['course'];
    $year = $_POST['year'];

    // Handle Resume Upload
    $resume = $student['resume']; // Keep old resume if no new file is uploaded
    if (!empty($_FILES["resume"]["name"])) {
        $resume = basename($_FILES["resume"]["name"]);
        $target_file = "uploads/" . $resume;
        move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file);
    }

    // Update Query
    if (!empty($_FILES["resume"]["name"])) {
        $sql = "UPDATE students SET name=?, email=?, phone=?, course=?, year=?, resume=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisi", $name, $email, $phone, $course, $year, $resume, $student_id);
    } else {
        $sql = "UPDATE students SET name=?, email=?, phone=?, course=?, year=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssii", $name, $email, $phone, $course, $year, $student_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();

// Function to check and create tables if they don't exist
function checkAndCreateTables($conn) {
    // Check and create the "students" table
    $create_students_table = "
        CREATE TABLE IF NOT EXISTS students (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            phone VARCHAR(15),
            course VARCHAR(255),
            year INT(11),
            resume VARCHAR(255) DEFAULT NULL
        )";

    if (!$conn->query($create_students_table)) {
        die("Error creating 'students' table: " . $conn->error);
    }
}
?>
