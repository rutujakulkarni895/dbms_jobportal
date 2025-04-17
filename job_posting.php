<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - Placement.co</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Placement.co</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="company_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="job_listings.php">Job Listings</a></li>
                    <li class="nav-item"><a class="nav-link active" href="job_posting.php">Post a Job</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Job Posting Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg p-4">
                    <h2 class="text-center text-primary mb-4">Post a New Job</h2>
                    <form action="add_job.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" class="form-control" placeholder="Enter job title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Job Description</label>
                            <textarea name="job_description" class="form-control" rows="4" placeholder="Describe the job" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="Enter job location" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Salary</label>
                            <input type="text" name="salary" class="form-control" placeholder="Enter salary details">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Requirements</label>
                            <textarea name="requirements" class="form-control" rows="3" placeholder="List job requirements"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Post Job</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mt-3">&copy; 2025 Placement.co. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>