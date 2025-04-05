<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Wanderlust Canvas</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <h1>Admin Dashboard</h1>
            
            <div class="dashboard-stats">
                <?php
                // Get destination count
                $destQuery = "SELECT COUNT(*) as total FROM destinations";
                $destResult = mysqli_query($conn, $destQuery);
                $destCount = mysqli_fetch_assoc($destResult)['total'];
                
                // Get user count
                $userQuery = "SELECT COUNT(*) as total FROM users WHERE role = 'user'";
                $userResult = mysqli_query($conn, $userQuery);
                $userCount = mysqli_fetch_assoc($userResult)['total'];
                
                // Get subscription count
                $subQuery = "SELECT COUNT(*) as total FROM subscriptions";
                $subResult = mysqli_query($conn, $subQuery);
                $subCount = mysqli_fetch_assoc($subResult)['total'];
                ?>
                
                <div class="stat-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Total Destinations</h3>
                    <p><?php echo $destCount; ?></p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Registered Users</h3>
                    <p><?php echo $userCount; ?></p>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-credit-card"></i>
                    <h3>Active Subscriptions</h3>
                    <p><?php echo $subCount; ?></p>
                </div>
            </div>
            
            <div class="recent-activity">
                <h2>Recent Destinations Added</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Destination</th>
                            <th>Added On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recentQuery = "SELECT * FROM destinations ORDER BY created_at DESC LIMIT 5";
                        $recentResult = mysqli_query($conn, $recentQuery);
                        
                        while ($destination = mysqli_fetch_assoc($recentResult)) {
                            echo "<tr>";
                            echo "<td>{$destination['name']}</td>";
                            echo "<td>" . date('M d, Y', strtotime($destination['created_at'])) . "</td>";
                            echo "<td><a href='edit_destination.php?id={$destination['id']}' class='btn-small'>Edit</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../js/admin.js"></script>
</body>
</html>