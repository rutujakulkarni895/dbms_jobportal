<?php
$conn = new mysqli("localhost", "root", "", "placmenet_db");

// Handle deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM companies WHERE id = $id");
    header("Location: admin_manage_companies.php");
    exit();
}

// Handle add/edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $contact = $_POST['phone'];
    $address = $_POST['address'];

    if (isset($_POST['company_id']) && $_POST['company_id'] !== '') {
        // Edit
        $id = $_POST['company_id'];
        $stmt = $conn->prepare("UPDATE companies SET name=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $contact, $address, $id);
    } else {
        // Add
        $stmt = $conn->prepare("INSERT INTO companies (name, email, phone, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $contact, $address);
    }
    $stmt->execute();
    header("Location: admin_manage_companies.php");
    exit();
}

// Get company list
$companies = $conn->query("SELECT * FROM companies ORDER BY id DESC");

// If editing, fetch data
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_res = $conn->query("SELECT * FROM companies WHERE id = $edit_id");
    $edit_data = $edit_res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Companies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>🏢 Manage Companies</h2>

    <!-- Add/Edit Form -->
    <form method="POST" class="card p-4 mt-4 shadow-sm">
        <input type="hidden" name="company_id" value="<?= $edit_data['id'] ?? '' ?>">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Company Name</label>
                <input type="text" name="name" class="form-control" required value="<?= $edit_data['name'] ?? '' ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required value="<?= $edit_data['email'] ?? '' ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Contact</label>
                <input type="text" name="contact" class="form-control" value="<?= $edit_data['phone'] ?? '' ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="<?= $edit_data['address'] ?? '' ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><?= $edit_data ? "Update" : "Add" ?> Company</button>
        <?php if ($edit_data): ?>
            <a href="admin_manage_companies.php" class="btn btn-secondary ms-2">Cancel</a>
        <?php endif; ?>
    </form>

    <!-- Company Table -->
    <table class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Company</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $companies->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['phone'] ?></td>
                    <td><?= $row['address'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this company?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('a.btn-danger').on('click', function(e) {
            e.preventDefault();
            const link = $(this).attr('href');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link;
                }
            });
        });
    });
</script>
