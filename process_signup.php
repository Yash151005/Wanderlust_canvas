<?php
session_start();
require_once 'db_connection.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = sanitize_input($_POST["name"]);
    $email = sanitize_input($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    $check_sql = "SELECT * FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors[] = "Email already exists. Please use a different email or login.";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare SQL statement
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        
        // Execute statement
        if ($stmt->execute()) {
            // Set session variables
            $_SESSION["user_id"] = $stmt->insert_id;
            $_SESSION["name"] = $name;
            $_SESSION["email"] = $email;
            $_SESSION["user_type"] = "user";
            
            // Redirect to home page
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again later.";
        }
    }
    
    // If there are errors, store them in session and redirect back to signup page
    if (!empty($errors)) {
        $_SESSION["signup_errors"] = $errors;
        $_SESSION["signup_data"] = ["name" => $name, "email" => $email];
        header("Location: signup.php");
        exit();
    }
}