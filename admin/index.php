<?php
session_start();
require_once('../includes/db_connect.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get stats for dashboard
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch_assoc()['count'];
$totalDestinations = $conn->query("SELECT COUNT(*) as count FROM destinations")->fetch_assoc()['count'];
$totalBlogs = $conn->query("SELECT COUNT(*) as count FROM blog_posts")->fetch_assoc()['count'];

// Get recent destinations
$recentDestinations = $conn->query("SELECT * FROM destinations ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Wanderlust Canvas</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #333;
            color: white;
            padding: 20px;
        }
        .sidebar .logo {
            font-size: 24px;
            margin-bottom: 30px;
            text-align: center;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
        }
        .sidebar ul li a:hover {
            background-color: #555;
        }
        .sidebar ul li a.active {
            background-color: #4CAF50;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .card h3 {
            margin-top: 0;
            color: #333;
        }
        .card .count {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
            color: #4CAF50;
        }
        .recent-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .recent-table th, .recent-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .recent-table th {
            background-color: #f2f2f2;
        }
        .btn-small {
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="logo">Wanderlust Canvas</div>
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="destinations.php">Destinations</a></li>
                <li><a href="blogs.php">Blog Posts</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Admin Dashboard</h1>
                <div>
                    <a href="add_destination.php" class="btn btn-primary">Add Destination</a>
                    <a href="add_blog.php" class="btn btn-primary">Add Blog Post</a>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <div class="count"><?php echo $totalUsers; ?></div>
                </div>
                
                <div class="card">
                    <h3>Destinations</h3>
                    <div class="count"><?php echo $totalDestinations; ?></div>
                </div>
                
                <div class="card">
                    <h3>Blog Posts</h3>
                    <div class="count"><?php echo $totalBlogs; ?></div>
                </div>
            </div>
            
            <div class="card">
                <h2>Recent Destinations</h2>
                <table class="recent-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Popular</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($destination = $recentDestinations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $destination['name']; ?></td>
                                <td><?php echo $destination['is_popular'] ? 'Yes' : 'No'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($destination['created_at'])); ?></td>
                                <td>
                                    <a href="edit_destination.php?id=<?php echo $destination['destination_id']; ?>" class="btn btn-small">Edit</a>
                                    <a href="view_destination.php?id=<?php echo $destination['destination_id']; ?>" class="btn btn-small">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if($recentDestinations->num_rows == 0): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No destinations found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>