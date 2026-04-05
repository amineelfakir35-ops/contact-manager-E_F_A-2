<?php
// Registration handler: register.php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 4) {
        $error = 'Password must be at least 4 characters';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        
        if ($stmt->execute()) {
            $success = 'Registration successful! You can now login.';
        } else {
            if ($conn->errno == 1062) {
                $error = 'Username already exists';
            } else {
                $error = 'Registration failed: ' . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Contact Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        .container { background: white; border-radius: 10px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); width: 400px; max-width: 90%; padding: 40px; }
        h2 { text-align: center; margin-bottom: 30px; color: #333; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #5a67d8; }
        .error { background: #fed7d7; color: #c53030; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .success { background: #c6f6d5; color: #22543d; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .link { text-align: center; margin-top: 20px; }
        .link a { color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Account</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password (min 4 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <div class="link">
            <a href="login.php">Already have an account? Login here</a>
        </div>
    </div>
</body>
</html>