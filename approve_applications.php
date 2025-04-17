<?php
require('fpdf/fpdf.php');  // Include FPDF Library
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Create applied_jobs table if it doesn't exist
$createTableSQL = "
CREATE TABLE IF NOT EXISTS applied_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    job_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    offer_letter_generated BOOLEAN DEFAULT 0,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id)
)";
if (!$conn->query($createTableSQL)) {
    die("Error creating 'applied_jobs' table: " . $conn->error);
}

$application_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($application_id == 0) {
    die("Invalid Request.");
}

// Approve the application
$update_sql = "UPDATE applied_jobs SET status = 'approved', offer_letter_generated = 1 WHERE id = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("i", $application_id);
if (!$stmt->execute()) {
    die("Failed to update status: " . $conn->error);
}

// Fetch application details
$sql = "SELECT s.name AS student_name, j.title AS job_title, c.name AS company_name, j.salary 
        FROM applied_jobs aj
        JOIN students s ON aj.student_id = s.id
        JOIN jobs j ON aj.job_id = j.id
        JOIN companies c ON j.company_id = c.id
        WHERE aj.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("Application details not found.");
}

// Generate Offer Letter PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Job Offer Letter', 1, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, "Dear " . $row['student_name'] . ",\n\n");
$pdf->MultiCell(0, 10, "We are pleased to offer you the position of " . $row['job_title'] . " at " . $row['company_name'] . ".\n");
$pdf->MultiCell(0, 10, "Your salary package will be " . $row['salary'] . ".\n\n");
$pdf->MultiCell(0, 10, "Congratulations and welcome to the team!\n\nBest Regards,\n" . $row['company_name']);

$pdf_file = "offer_letters/offer_letter_" . $application_id . ".pdf";
$pdf->Output('F', $pdf_file); // Save PDF to file

// Redirect back to manage applications
header("Location: manage_applications.php?success=Offer Letter Generated");
exit;
?>
