<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db_connection.php';

// Handle destination deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $destId = $_GET['delete'];
    
    // Delete associated images first
    $imageQuery = "SELECT image_path FROM destination_images WHERE destination_id = $destId";
    $imageResult = mysqli_query($conn, $imageQuery);
    
    while ($image = mysqli_fetch_assoc($imageResult)) {
        if (file_exists('../' . $image['image_path'])) {
            unlink('../' . $image['image_path']);
        }
    }
    
    // Delete related records
    mysqli_query($conn, "DELETE FROM destination_images WHERE destination_id = $destId");
    mysqli_query($conn, "DELETE FROM destination_attractions WHERE destination_id = $destId");
    mysqli_query($conn, "DELETE FROM destination_food WHERE destination_id = $destId");
    
    // Delete destination
    $deleteQuery = "DELETE FROM destinations WHERE id = $destId";
    mysqli_query($conn, $deleteQuery);
    
    header("Location: destinations.php");
    exit();
}

// Pagination and filtering
$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$whereClause = "1=1";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $whereClause .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

$countQuery = "SELECT COUNT(*) as total FROM destinations WHERE $whereClause";
$countResult = mysqli_query($conn, $countQuery);
$total = mysqli_fetch_assoc($countResult)['total'];
$pages = ceil($total / $limit);

// Get destinations with pagination and filtering
$destQuery = "SELECT * FROM destinations WHERE $whereClause ORDER BY created_at DESC LIMIT $start, $limit";
$destResult = mysqli_query($conn, $destQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Destinations - Wanderlust Canvas</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content">
            <h1>Manage Destinations</h1>
            
            <div class="admin-actions">
                <a href="add_destination.php" class="btn">Add New Destination</a>
                
                <form class="search-form" action="" method="GET">
                    <input type="text" name="search" placeholder="Search destinations..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Traveler Type</th>
                            <th>Created</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($dest = mysqli_fetch_assoc($destResult)): ?>
                        <tr>
                            <td><?php echo $dest['id']; ?></td>
                            <td>
                                <?php
                                $imgQuery = "SELECT image_path FROM destination_images WHERE destination_id = {$dest['id']} LIMIT 1";
                                $imgResult = mysqli_query($conn, $imgQuery);
                                if (mysqli_num_rows($imgResult) > 0) {
                                    $imgPath = mysqli_fetch_assoc($imgResult)['image_path'];
                                    echo "<img src='../{$imgPath}' alt='{$dest['name']}' class='thumbnail'>";
                                } else {
                                    echo "<img src='../images/placeholder.jpg' alt='No image' class='thumbnail'>";
                                }
                                ?>
                            </td>
                            <td><?php echo $dest['name']; ?></td>
                            <td><?php echo $dest['traveler_type']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($dest['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($dest['status']); ?>">
                                    <?php echo ucfirst($dest['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_destination.php?id=<?php echo $dest['id']; ?>" class="btn-small">Edit</a>
                                <a href="preview.php?id=<?php echo $dest['id']; ?>" class="btn-small">Preview</a>
                                <a href="?delete=<?php echo $dest['id']; ?>" class="btn-small delete" onclick="return confirm('Are you sure you want to delete this destination?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($destResult) == 0): ?>
                        <tr>
                            <td colspan="7" class="no-results">No destinations found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo ($page - 1); ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="prev">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $pages): ?>
                <a href="?page=<?php echo ($page + 1); ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" class="next">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../js/admin.js"></script>
</body>
</html>