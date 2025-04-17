<?php
session_start();
$servername = "localhost";  
$username = "root";  
$password = "";  
$dbname = "placmenet_db";  

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = $_POST['company_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $website = $_POST['website'];
    $description = $_POST['description'];

    // File upload handling
    if ($_FILES['pdf']['name']) {
        $pdf = $_FILES['pdf']['name'];
        $pdf_tmp = $_FILES['pdf']['tmp_name'];
        $pdf_folder = "uploads/" . basename($pdf);

        if (move_uploaded_file($pdf_tmp, $pdf_folder)) {
            // Update query with file
            $sql = "UPDATE companies SET company_name=?, email=?, phone=?, address=?, website=?, description=?, pdf=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $company_name, $email, $phone, $address, $website, $description, $pdf_folder, $company_id);
        } else {
            echo "Failed to upload PDF.";
            exit();
        }
    } else {
        // Update query without file
        $sql = "UPDATE companies SET company_name=?, email=?, phone=?, address=?, website=?, description=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $company_name, $email, $phone, $address, $website, $description, $company_id);
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
        header("Location: company_dashboard.php");
        exit();
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
