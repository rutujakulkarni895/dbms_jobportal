<?php
session_start();

// Manual PDO connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=placmenet_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['id'] = $user['id']; // ✅ Set the id here
            header("Location: admin_dashboard.php");
            exit;
        } else {
            echo "Invalid email or password!";
        }
    } else {
        echo "Please fill in both fields!";
    }
} else {
    echo "Invalid request.";
}
?>
