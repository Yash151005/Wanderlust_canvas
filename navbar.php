<?php
session_start();
require_once 'db_connection.php';
error_reporting(0);
// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
$is_admin = $logged_in && isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wonderlust Canvas - Discover Extraordinary Travel Stories</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php">Wonderlust Canvas</a>
        </div>
        <nav>
            <ul class="navbar">
                <li><a href="index.php">Home</a></li>
                <li><a href="explore.php">Explore</a></li>
                <li><a href="search.php">Search</a></li>
                <?php if ($logged_in): ?>
                    <li><a href="profile.php">My Account</a></li>
                    <?php if ($is_admin): ?>
                        <li><a href="admin/index.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    <main>