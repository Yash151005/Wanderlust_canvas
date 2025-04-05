<?php
session_start();
require_once('../includes/db_connect.php');

// Check if already logged in
if(isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "All fields are required";
    } else {
        // Check admin credentials
        $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ? AND is_admin = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['user_id'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid credentials";
            }
        } else {
            $error = "Invalid credentials";
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
    <title>Admin Login - Wanderlust Canvas</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .admin-login-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <h1>Admin Login</h1>
        
        <?php if(!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="../index.php">Return to Website</a>
        </div>
    </div>
</body>
</html>