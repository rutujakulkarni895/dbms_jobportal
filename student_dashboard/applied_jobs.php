<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "placmenet_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create 'jobs' table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        description TEXT,
        requirements TEXT,
        salary VARCHAR(100),
        location VARCHAR(255),
        company_name VARCHAR(255),
        company_id INT,
        posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Create 'applied_jobs' table if not exists
$conn->query("
    CREATE TABLE IF NOT EXISTS applied_jobs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        job_id INT,
        company_id INT,
        status ENUM('Pending', 'Shortlisted', 'Hired', 'Rejected') DEFAULT 'Pending',
        offer_letter_generated BOOLEAN DEFAULT 0,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
    )
");

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Handle Job Application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = $_POST['job_id'];

    // Check if already applied
    $check = $conn->prepare("SELECT * FROM applied_jobs WHERE student_id = ? AND job_id = ?");
    
    if ($check === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $check->bind_param("ii", $student_id, $job_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        // Get company_id from jobs table
        $getCompany = $conn->prepare("SELECT company_id FROM jobs WHERE id = ?");
        
        if ($getCompany === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $getCompany->bind_param("i", $job_id);
        $getCompany->execute();
        $res = $getCompany->get_result();
        $company_id = ($row = $res->fetch_assoc()) ? $row['company_id'] : null;

        if ($company_id) {
            $apply = $conn->prepare("INSERT INTO applied_jobs (student_id, job_id, company_id) VALUES (?, ?, ?)");
            
            if ($apply === false) {
                die("Error preparing statement: " . $conn->error);
            }

            $apply->bind_param("iii", $student_id, $job_id, $company_id);
            $apply->execute();
        }
    }
}

// Fetch jobs NOT yet applied to
$available_stmt = $conn->prepare("
    SELECT id, title,company_name 
    FROM jobs 
    WHERE id NOT IN (
        SELECT job_id FROM applied_jobs WHERE student_id = ?
    )
");

if ($available_stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$available_stmt->bind_param("i", $student_id);
$available_stmt->execute();
$available_jobs = $available_stmt->get_result();

// Fetch applied jobs
$applied_stmt = $conn->prepare("
    SELECT j.id AS job_id, j.title, j.company_name, j.description, j.location, j.salary, aj.status
    FROM applied_jobs aj
    JOIN jobs j ON aj.job_id = j.id
    WHERE aj.student_id = ?
");

if ($applied_stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$applied_stmt->bind_param("i", $student_id);
$applied_stmt->execute();
$applied_jobs = $applied_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Applied Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f4f8; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">📄 Applied Jobs</h2>

    <!-- Job Application Form -->
    <form method="POST" class="mb-4 d-flex align-items-center gap-3">
        <select name="job_id" class="form-select w-50" required>
            <option value="">🔍 Select a Job to Apply</option>
            <?php while ($job = $available_jobs->fetch_assoc()) { ?>
                <option value="<?= $job['id'] ?>">
                    <?= htmlspecialchars($job['title']) ?> (<?= htmlspecialchars($job['company_name']) ?>)
                </option>
            <?php } ?>
        </select>
        <button type="submit" class="btn btn-success">Apply</button>
    </form>

    <!-- Applied Jobs -->
    <div class="row">
        <?php while ($job = $applied_jobs->fetch_assoc()) { ?>
            <div class="col-md-6">
                <div class="card p-3 shadow-sm">
                    <h5><?= htmlspecialchars($job['title']) ?></h5>
                    <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></p>
                    <p><strong>Salary:</strong> ₹<?= htmlspecialchars($job['salary']) ?></p>
                    <p><strong>Status:</strong>
                        <?php
                        $status = $job['status'];
                        $badge = match ($status) {
                            'Hired' => 'success',
                            'Rejected' => 'danger',
                            'Shortlisted' => 'warning text-dark',
                            default => 'secondary',
                        };
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= $status ?></span>
                    </p>
                    <button class="btn btn-primary mt-2" onclick="showJobDetails(
                        '<?= htmlspecialchars($job['title'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($job['company_name'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($job['location'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($job['salary'], ENT_QUOTES) ?>',
                        '<?= htmlspecialchars($job['description'], ENT_QUOTES) ?>'
                    )">View Details</button>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="jobModal" tabindex="-1" aria-labelledby="jobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Job Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="jobModalBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showJobDetails(title, company, location, salary, description) {
        const html = `
            <p><strong>Title:</strong> ${title}</p>
            <p><strong>Company:</strong> ${company}</p>
            <p><strong>Location:</strong> ${location}</p>
            <p><strong>Salary:</strong> ₹${salary}</p>
            <p><strong>Description:</strong><br>${description}</p>
        `;
        document.getElementById('jobModalBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('jobModal')).show();
    }
</script>
</body>
</html>
