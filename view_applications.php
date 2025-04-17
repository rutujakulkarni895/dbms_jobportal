<?php
session_start();
$conn = new mysqli("localhost", "root", "", "placmenet_db");

if (!isset($_SESSION['company_id'])) {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Fetch applications for this company
$query = "
    SELECT aj.id, s.name AS student_name, j.title AS job_title, aj.status
    FROM applied_jobs aj
    JOIN students s ON aj.student_id = s.id
    JOIN jobs j ON aj.job_id = j.id
    WHERE aj.company_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>📋 Job Applications</h2>
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Job Title</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['job_title']) ?></td>
                    <td>
                        <?php
                        $status = $row['status'];
                        if ($status === 'Hired') echo '<span class="badge bg-success">🎉 Hired</span>';
                        elseif ($status === 'Rejected') echo '<span class="badge bg-danger">❌ Rejected</span>';
                        elseif ($status === 'Shortlisted') echo '<span class="badge bg-warning text-dark">⚡ Shortlisted</span>';
                        else echo '<span class="badge bg-secondary">⏳ Pending</span>';
                        ?>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="generate_offer_letter.php?application_id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View Details</a>

                            <?php if ($status === 'Pending') { ?>
                                <a href="generate_offer_letter.php?application_id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Approve</a>

                                <form method="post" action="handle_applications.php" onsubmit="return confirm('Are you sure you want to reject this application?');">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="status" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            <?php } else { ?>
                                <span class="text-muted">Action Taken</span>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
