<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the ID safely
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: manage_students.php?deleted=true");
    } else {
        header("Location: manage_students.php?error=deletion_failed");
    }
} else {
    header("Location: manage_students.php?error=invalid_id");
}
exit;
?>
