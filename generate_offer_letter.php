<?php
session_start();

// ✅ DomPDF (manual import, no Composer)
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

// ✅ DB Connection
$conn = new mysqli("localhost", "root", "", "placmenet_db");
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// ✅ Company session check
if (!isset($_SESSION['company_id'])) {
    die("Unauthorized Access!");
}

// ✅ Get application ID with sanitization
$application_id = isset($_GET['application_id']) ? (int) $_GET['application_id'] : null;
if (!$application_id) {
    die("Invalid request!");
}

// ✅ Check if 'applied_jobs' table exists
$table_check_sql = "SHOW TABLES LIKE 'applied_jobs'";
$table_check_result = $conn->query($table_check_sql);

if ($table_check_result->num_rows == 0) {
    $create_table_sql = "CREATE TABLE applied_jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        job_id INT NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Applied',
        offer_letter_generated BOOLEAN DEFAULT 0,
        offer_letter_path VARCHAR(255) NULL,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (job_id) REFERENCES jobs(id)
    )";

    if ($conn->query($create_table_sql) === TRUE) {
        echo "Table 'applied_jobs' created successfully.";
    } else {
        die("Error creating table: " . $conn->error);
    }
}

// ✅ Fetch application info
$sql = "SELECT aj.id, s.name AS student_name, s.id AS student_id, j.title AS job_title, j.company_name, aj.offer_letter_generated 
        FROM applied_jobs aj
        JOIN students s ON aj.student_id = s.id
        JOIN jobs j ON aj.job_id = j.id
        WHERE aj.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

if (!$application) {
    die("Application not found!");
}

// ✅ Check if offer letter is already generated
if ($application['offer_letter_generated']) {
    die("Offer letter has already been generated for this application.");
}

// ✅ Generate Offer Letter PDF
$dompdf = new Dompdf();
$html = "<h1>Offer Letter</h1>
         <p>Dear {$application['student_name']},</p>
         <p>We are pleased to offer you the position of <strong>{$application['job_title']}</strong> at <strong>{$application['company_name']}</strong>.</p>
         <p>Your dedication and qualifications have impressed us, and we are confident you'll be a valuable asset to our team.</p>
         <p>Please find this letter as a formal confirmation of your offer.</p>
         <br><p>Best regards,<br><strong>{$application['company_name']}</strong></p>";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ✅ Save PDF to offerletters/ folder
$folderPath = 'offerletters';
if (!file_exists($folderPath)) {
    mkdir($folderPath, 0777, true);
}

$pdfFilePath = "$folderPath/offer_letter_{$application['id']}.pdf";
file_put_contents($pdfFilePath, $dompdf->output());

// ✅ Update DB: set status to 'Selected'
$updateQuery = "UPDATE applied_jobs SET status = 'Selected', offer_letter_generated = 1, offer_letter_path = ? WHERE id = ?";
$update_stmt = $conn->prepare($updateQuery);
$update_stmt->bind_param("si", $pdfFilePath, $application_id);
if (!$update_stmt->execute()) {
    die("Error updating application status in the database.");
}

// ✅ Redirect to applications page
header("Location: view_applications.php?success=1");
exit;
?>
