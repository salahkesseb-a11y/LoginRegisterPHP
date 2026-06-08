<?php
// register.php
require 'connection.php';
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Default new registrations to 'user' type
    $type = 'user'; 

    try {
        // Prepare statement to prevent SQL injection
        $stmt = $pdo->prepare("INSERT INTO users (username, password, type) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $type]);
        $message = "<div class='success'>Registration successful! <a href='login.php'>Login here</a></div>";
    } catch (PDOException $e) {
        
        if ($e->getCode() == 23000) {
            $message = "<div class='error'>Error: Username already exists.</div>";
        } else {
            $message = "<div class='error'>Error: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .text-center { text-align: center; margin-top: 15px; }
        .success { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 10px; text-align: center; }
        .error { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Create an Account</h2>
        <?php echo $message; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Choose a Username" required>
            <input type="password" name="password" placeholder="Create a Password" required>
            <button type="submit">Register</button>
        </form>
        <div class="text-center">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>