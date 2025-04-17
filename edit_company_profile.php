<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if company is logged in
if (!isset($_SESSION["company_id"])) {
    die("Session not set. Please log in again.");
}

$company_id = $_SESSION["company_id"];

// Fetch company details
$query = "SELECT name, email, phone, address, website, description, pdf FROM companies WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$stmt->bind_result($company_name, $email, $phone, $address, $website, $description, $pdf);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = $_POST["company_name"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
    $website = $_POST["website"];
    $description = $_POST["description"];

    // Phone validation (server-side)
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        die("❌ Invalid phone number. Must be exactly 10 digits.");
    }

    // Handle PDF upload
    if (!empty($_FILES["pdf"]["name"])) {
        $pdf_dir = "uploads/pdfs/";
        $pdf_ext = pathinfo($_FILES["pdf"]["name"], PATHINFO_EXTENSION);
        $unique_name = uniqid("brochure_", true) . '.' . $pdf_ext;
        $pdf_path = $pdf_dir . $unique_name;

        // Create directory if not exists
        if (!is_dir($pdf_dir)) {
            mkdir($pdf_dir, 0755, true);
        }

        // Check MIME type is PDF
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES["pdf"]["tmp_name"]);
        finfo_close($finfo);

        if ($mime !== 'application/pdf') {
            die("❌ Only PDF files are allowed.");
        }

        // Upload file
        move_uploaded_file($_FILES["pdf"]["tmp_name"], $pdf_path);
    } else {
        $pdf_path = $pdf;
    }

    // Update query
    $update_query = "UPDATE companies SET name = ?, phone = ?, address = ?, website = ?, description = ?, pdf = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssssssi", $company_name, $phone, $address, $website, $description, $pdf_path, $company_id);

    if ($update_stmt->execute()) {
        header("Location: company_dashboard.php?updated=1");
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }

    $update_stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Company Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center text-primary">Edit Company Profile</h2>
        <div class="card shadow-lg p-4">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($company_name); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email (Non-editable)</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" required><?php echo htmlspecialchars($address); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($website); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Company Description</label>
                    <textarea name="description" class="form-control"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Company Brochure (PDF)</label>
                    <input type="file" name="pdf" class="form-control">
                    <?php if ($pdf): ?>
                        <p>Current Brochure: <a href="<?php echo htmlspecialchars($pdf); ?>" target="_blank">View PDF</a></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                <a href="company_dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
