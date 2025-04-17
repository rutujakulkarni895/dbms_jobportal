<?php
$host = 'localhost';
$db = 'placmenet_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ 1. Ensure 'admin' table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY
    )");

    // ✅ 2. Ensure required columns
    $requiredColumns = [
        'name' => 'VARCHAR(100) NOT NULL',
        'email' => 'VARCHAR(100) NOT NULL UNIQUE',
        'password' => 'VARCHAR(255) NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ];

    $existingColsStmt = $pdo->query("SHOW COLUMNS FROM admin");
    $existingCols = $existingColsStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($requiredColumns as $column => $definition) {
        if (!in_array($column, $existingCols)) {
            $pdo->exec("ALTER TABLE admin ADD $column $definition");
        }
    }

    // ✅ 3. Get form data safely
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hashed

    // ✅ 4. Insert into admin table
    $sql = "INSERT INTO admin (name, email, password) VALUES (:username, :email, :password)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $password
    ]);

    echo "<script>alert('Registration successful!'); window.location.href='admin_login.php';</script>";

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "<script>alert('Username or email already exists.'); window.location.href='admin_register.php';</script>";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
