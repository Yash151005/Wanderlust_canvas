<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db_connection.php';

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) as total FROM subscriptions";
$countResult = mysqli_query($conn, $countQuery);
$total = mysqli_fetch_assoc($countResult)['total'];
$pages = ceil($total / $limit);

// Get subscriptions with pagination
$subsQuery = "SELECT s.*, u.name, u.email FROM subscriptions s 
              JOIN users u ON s.user_id = u.id 
              ORDER BY s.created_at DESC LIMIT $start, $limit";
$subsResult = mysqli_query($conn, $subsQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subscriptions - Wanderlust Canvas</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <h1>Manage Subscriptions</h1>
            
            <div class="stats-summary">
                <?php
                $activeQuery = "SELECT COUNT(*) as count FROM subscriptions WHERE status = 'active'";
                $activeResult = mysqli_query($conn, $activeQuery);
                $activeCount = mysqli_fetch_assoc($activeResult)['count'];
                
                $basicQuery = "SELECT COUNT(*) as count FROM subscriptions WHERE plan_name = 'Basic' AND status = 'active'";
                $basicResult = mysqli_query($conn, $basicQuery);
                $basicCount = mysqli_fetch_assoc($basicResult)['count'];
                
                $standardQuery = "SELECT COUNT(*) as count FROM subscriptions WHERE plan_name = 'Standard' AND status = 'active'";
                $standardResult = mysqli_query($conn, $standardQuery);
                $standardCount = mysqli_fetch_assoc($standardResult)['count'];
                
                $premiumQuery = "SELECT COUNT(*) as count FROM subscriptions WHERE plan_name = 'Premium' AND status = 'active'";
                $premiumResult = mysqli_query($conn, $premiumQuery);
                $premiumCount = mysqli_fetch_assoc($premiumResult)['count'];
                ?>
                
                <div class="stat-card">
                    <h3>Active Subscriptions</h3>
                    <p><?php echo $activeCount; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Basic Plan</h3>
                    <p><?php echo $basicCount; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Standard Plan</h3>
                    <p><?php echo $standardCount; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Premium Plan</h3>
                    <p><?php echo $premiumCount; ?></p>
                </div>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sub = mysqli_fetch_assoc($subsResult)): ?>
                        <tr>
                            <td><?php echo $sub['id']; ?></td>
                            <td><?php echo $sub['name']; ?></td>
                            <td><?php echo $sub['email']; ?></td>
                            <td><?php echo $sub['plan_name']; ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($sub['status']); ?>">
                                    <?php echo ucfirst($sub['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($sub['start_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($sub['end_date'])); ?></td>
                            <td>$<?php echo number_format($sub['amount'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo ($page - 1); ?>" class="prev">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $pages): ?>
                <a href="?page=<?php echo ($page + 1); ?>" class="next">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../js/admin.js"></script>
</body>
</html>