<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = $_GET['search'] ?? '';

// Use a prepared statement to avoid SQL injection
$query = "SELECT * FROM students WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC";
$stmt = $conn->prepare($query);
$searchTerm = "%" . $search . "%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #34495e;
        }

        .search-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        input[type="text"] {
            width: 300px;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .btn {
            background-color: #4e73df;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #2e59d9;
        }

        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .card {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .card h3 {
            margin: 0;
            color: #2c3e50;
        }

        .card p {
            margin: 6px 0;
            font-size: 14px;
            color: #555;
        }

        .card .actions {
            margin-top: 12px;
        }

        .card .actions a {
            text-decoration: none;
            margin-right: 10px;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 13px;
            color: white;
        }

        .edit-btn { background-color: #36b9cc; }
        .delete-btn { background-color: #e74a3b; }
        .attendance-btn { background-color: #1cc88a; }
        .resume-btn { background-color: #858796; }

        .export-btn {
            background-color: #28a745;
        }
    </style>
</head>
<body>

<h2>📋 Manage Students (Card View)</h2>

<div class="search-container">
    <form method="GET">
        <input type="text" name="search" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn">Search</button>
        <a href="upload_students_csv.php" class="btn">📂 Upload CSV</a>
        <a href="add_student.php" class="btn">➕ Add Student</a>
        <a href="export_students_csv.php" class="btn export-btn">⬇ Export CSV</a>
    </form>
</div>

<div class="cards-container">
<?php while ($row = $result->fetch_assoc()): ?>
    <div class="card">
        <h3><?= htmlspecialchars($row['name']) ?></h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($row['course']) ?> - Year <?= $row['year'] ?></p>
        <p><strong>Status:</strong> <?= $row['status'] ?? 'Active' ?></p>
        <div class="actions">
            <a href="edit_student.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
            <a href="delete_student.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Delete student?')">Delete</a>
            <a href="view_attendance.php?id=<?= $row['id'] ?>" class="attendance-btn">Attendance</a>
            <a href="uploads/resumes/<?= $row['resume'] ?>" class="resume-btn" download>Resume</a>
            <a href="student_activity.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info mt-2">
    View Activity
</a>
        </div>
    </div>
<?php endwhile; ?>
</div>

</body>
</html>
