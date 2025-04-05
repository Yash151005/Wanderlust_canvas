<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db_connection.php';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = $_GET['delete'];
    $deleteQuery = "DELETE FROM users WHERE id = $userId AND role != 'admin'";
    mysqli_query($conn, $deleteQuery);
    header("Location: users.php");
    exit();
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) as total FROM users";
$countResult = mysqli_query($conn, $countQuery);
$total = mysqli_fetch_assoc($countResult)['total'];
$pages = ceil($total / $limit);

// Get users with pagination
$usersQuery = "SELECT * FROM users ORDER BY id DESC LIMIT $start, $limit";
$usersResult = mysqli_query($conn, $usersQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Wanderlust Canvas</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <h1>Manage Users</h1>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined Date</th>
                            <th>Subscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php
                                $subQuery = "SELECT plan_name FROM subscriptions WHERE user_id = {$user['id']} AND status = 'active'";
                                $subResult = mysqli_query($conn, $subQuery);
                                if (mysqli_num_rows($subResult) > 0) {
                                    $plan = mysqli_fetch_assoc($subResult)['plan_name'];
                                    echo $plan;
                                } else {
                                    echo "No active plan";
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($user['role'] != 'admin'): ?>
                                <a href="?delete=<?php echo $user['id']; ?>" class="btn-small delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                <?php else: ?>
                                <span class="btn-small disabled">Admin</span>
                                <?php endif; ?>
                            </td>
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