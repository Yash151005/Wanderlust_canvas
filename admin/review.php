<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db_connection.php';

$destId = isset($_GET['id']) ? $_GET['id'] : 0;

if ($destId) {
    $query = "SELECT * FROM destinations WHERE id = $destId";
    $result = mysqli_query($conn, $query);
    $destination = mysqli_fetch_assoc($result);
    
    if (!$destination) {
        header("Location: destinations.php");
        exit();
    }
    
    // Get images
    $imgQuery = "SELECT * FROM destination_images WHERE destination_id = $destId";
    $imgResult = mysqli_query($conn, $imgQuery);
    
    // Get attractions
    $attrQuery = "SELECT * FROM destination_attractions WHERE destination_id = $destId";
    $attrResult = mysqli_query($conn, $attrQuery);
    
    // Get food recommendations
    $foodQuery = "SELECT * FROM destination_food WHERE destination_id = $destId";
    $foodResult = mysqli_query($conn, $foodQuery);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $destId ? $destination['name'] . ' - Preview' : 'Site Preview'; ?> - Wanderlust Canvas</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin_style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin_sidebar.php'; ?>
        
        <div class="admin-content preview-content">
            <div class="preview-header">
                <h1><?php echo $destId ? 'Destination Preview: ' . $destination['name'] : 'Site Preview'; ?></h1>
                <?php if ($destId): ?>
                <a href="edit_destination.php?id=<?php echo $destId; ?>" class="btn">Edit Destination</a>
                <?php endif; ?>
            </div>
            
            <?php if ($destId): ?>
            <div class="destination-preview">
                <div class="destination-images">
                    <?php 
                    if (mysqli_num_rows($imgResult) > 0):
                        while ($image = mysqli_fetch_assoc($imgResult)):
                    ?>
                    <div class="preview-image">
                        <img src="../<?php echo $image['image_path']; ?>" alt="<?php echo $destination['name']; ?>">
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="preview-image">
                        <img src="../images/placeholder.jpg" alt="No image available">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="destination-info">
                    <h2><?php echo $destination['name']; ?></h2>
                    
                    <div class="info-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo $destination['location']; ?></span>
                        <span><i class="fas fa-tag"></i> <?php echo $destination['traveler_type']; ?></span>
                        <span><i class="fas fa-money-bill-wave"></i> Approx. Cost: $<?php echo $destination['cost']; ?></span>
                    </div>
                    
                    <div class="description">
                        <h3>Description</h3>
                        <p><?php echo nl2br($destination['description']); ?></p>
                    </div>
                    
                    <div class="tabs">
                        <button class="tab-btn active" data-tab="attractions">Attractions</button>
                        <button class="tab-btn" data-tab="food">Food</button>
                        <button class="tab-btn" data-tab="practical">Practical Info</button>
                    </div>
                    
                    <div class="tab-content active" id="attractions">
                        <h3>Top Attractions</h3>
                        <div class="attraction-list">
                            <?php 
                            if (mysqli_num_rows($attrResult) > 0):
                                while ($attr = mysqli_fetch_assoc($attrResult)):
                            ?>
                            <div class="attraction-item">
                                <h4><?php echo $attr['name']; ?></h4>
                                <p><?php echo $attr['description']; ?></p>
                            </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <p>No attractions listed yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="food">
                        <h3>Food Recommendations</h3>
                        <div class="food-list">
                            <?php 
                            if (mysqli_num_rows($foodResult) > 0):
                                while ($food = mysqli_fetch_assoc($foodResult)):
                            ?>
                            <div class="food-item">
                                <h4><?php echo $food['name']; ?></h4>
                                <p><?php echo $food['description']; ?></p>
                            </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <p>No food recommendations listed yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-content" id="practical">
                        <h3>Practical Information</h3>
                        <div class="practical-info">
                            <div class="info-item">
                                <h4>Best Time to Visit</h4>
                                <p><?php echo $destination['best_time']; ?></p>
                            </div>
                            <div class="info-item">
                                <h4>Accommodation</h4>
                                <p><?php echo nl2br($destination['accommodation']); ?></p>
                            </div>
                            <div class="info-item">
                                <h4>Transportation</h4>
                                <p><?php echo nl2br($destination['transportation']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="site-preview">
                <iframe src="../index.php" width="100%" height="800"></iframe>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and content
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });
    </script>
    <script src="../js/admin.js"></script>
</body>
</html>