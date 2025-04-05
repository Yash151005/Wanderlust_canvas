<?php
session_start();
require_once('../includes/db_connect.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if destination ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: destinations.php");
    exit();
}

$destinationId = $_GET['id'];

// Get destination data
$stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
$stmt->bind_param("i", $destinationId);
$stmt->execute();
$destination = $stmt->get_result()->fetch_assoc();

if(!$destination) {
    header("Location: destinations.php");
    exit();
}

// Get traveler types
$travelerTypes = $conn->query("SELECT * FROM traveler_types ORDER BY type_name");

// Get selected traveler types
$selectedTypes = [];
$typeStmt = $conn->prepare("SELECT type_id FROM destination_traveler_types WHERE destination_id = ?");
$typeStmt->bind_param("i", $destinationId);
$typeStmt->execute();
$result = $typeStmt->get_result();
while($row = $result->fetch_assoc()) {
    $selectedTypes[] = $row['type_id'];
}

// Get attractions
$attractions = $conn->prepare("SELECT * FROM attractions WHERE destination_id = ?");
$attractions->bind_param("i", $destinationId);
$attractions->execute();
$attractionsResult = $attractions->get_result();

// Get food recommendations
$foods = $conn->prepare("SELECT * FROM food_recommendations WHERE destination_id = ?");
$foods->bind_param("i", $destinationId);
$foods->execute();
$foodsResult = $foods->get_result();

// Get accommodations
$accommodations = $conn->prepare("SELECT * FROM accommodations WHERE destination_id = ?");
$accommodations->bind_param("i", $destinationId);
$accommodations->execute();
$accommodationsResult = $accommodations->get_result();

// Get practical info
$practicalInfo = $conn->prepare("SELECT * FROM practical_info WHERE destination_id = ?");
$practicalInfo->bind_param("i", $destinationId);
$practicalInfo->execute();
$practicalInfoResult = $practicalInfo->get_result();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $travelerTypeIds = isset($_POST['traveler_types']) ? $_POST['traveler_types'] : [];
    
    // Image upload
    $imagePath = $destination['image_path'];
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = '../uploads/destinations/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Allow certain file formats
        $allowTypes = array('jpg', 'jpeg', 'png', 'gif');
        if(in_array(strtolower($fileType), $allowTypes)) {
            // Upload file to the server
            if(move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
                $imagePath = 'uploads/destinations/' . $fileName;
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        } else {
            $error = "Sorry, only JPG, JPEG, PNG, & GIF files are allowed.";
        }
    }
    
    if(empty($error)) {
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update destination
            $stmt = $conn->prepare("UPDATE destinations SET name = ?, description = ?, image_path = ?, is_popular = ? WHERE destination_id = ?");
            $stmt->bind_param("sssii", $name, $description, $imagePath, $isPopular, $destinationId);
            $stmt->execute();
            
            // Delete existing traveler types
            $conn->query("DELETE FROM destination_traveler_types WHERE destination_id = $destinationId");
            
            // Insert traveler types
            if(!empty($travelerTypeIds)) {
                $typeStmt = $conn->prepare("INSERT INTO destination_traveler_types (destination_id, type_id) VALUES (?, ?)");
                
                foreach($travelerTypeIds as $typeId) {
                    $typeStmt->bind_param("ii", $destinationId, $typeId);
                    $typeStmt->execute();
                }
                
                $typeStmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            $message = "Destination updated successfully!";
            
            // Refresh destination data
            $stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
            $stmt->bind_param("i", $destinationId);
            $stmt->execute();
            $destination = $stmt->get_result()->fetch_assoc();
            
            // Refresh selected types
            $selectedTypes = [];
            $typeStmt = $conn->prepare("SELECT type_id FROM destination_traveler_types WHERE destination_id = ?");
            $typeStmt->bind_param("i", $destinationId);
            $typeStmt->execute();
            $result = $typeStmt->get_result();
            while($row = $result->fetch_assoc()) {
                $selectedTypes[] = $row['type_id'];
            }
            
        } catch (Exception $e) {
            // Rollback changes if something went wrong
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Destination - Wanderlust Canvas</title>
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
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
        }
        .checkbox-item input {
            margin-right: 10px;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .tab.active {
            border: 1px solid #ddd;
            border-bottom-color: white;
            margin-bottom: -1px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .current-image {
            max-width: 300px;
            margin-top: 10px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .item-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="logo">Wanderlust Canvas</div>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="destinations.php" class="active">Destinations</a></li>
                <li><a href="blogs.php">Blog Posts</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h1>Edit Destination: <?php echo $destination['name']; ?></h1>
            
            <?php if(!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="tabs">
                <div class="tab active" data-tab="basic">Basic Info</div>
                <div class="tab" data-tab="attractions">Attractions</div>
                <div class="tab" data-tab="food">Food</div>
                <div class="tab" data-tab="accommodations">Accommodations</div>
                <div class="tab" data-tab="practical">Practical Info</div>
            </div>
            
            <div id="basic" class="tab-content active">
                <div class="form-container">
                    <form action="edit_destination.php?id=<?php echo $destinationId; ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="name">Destination Name*</label>
                            <input type="text" id="name" name="name" value="<?php echo $destination['name']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description*</label>
                            <textarea id="description" name="description" rows="6" required><?php echo $destination['description']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Featured Image</label>
                            <input type="file" id="image" name="image">
                            <?php if(!empty($destination['image_path'])): ?>
                                <p>Current image:</p>
                                <img src="../<?php echo $destination['image_path']; ?>" class="current-image">
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Traveler Types</label>
                            <div class="checkbox-group">
                                <?php 
                                $travelerTypes->data_seek(0);
                                while($type = $travelerTypes->fetch_assoc()): 
                                ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="type_<?php echo $type['type_id']; ?>" name="traveler_types[]" 
                                            value="<?php echo $type['type_id']; ?>" 
                                            <?php echo in_array($type['type_id'], $selectedTypes) ? 'checked' : ''; ?>>
                                        <label for="type_<?php echo $type['type_id']; ?>"><?php echo $type['type_name']; ?></label>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-item">
                                <input type="checkbox" id="is_popular" name="is_popular" <?php echo $destination['is_popular'] ? 'checked' : ''; ?>>
                                <span>Mark as Popular Destination</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Destination</button>
                            <a href="destinations.php" class="btn">Back to List</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div id="attractions" class="tab-content">
                <div class="form-container">
                    <div class="section-header">
                        <h2>Attractions</h2>
                        <a href="add_attraction.php?destination_id=<?php echo $destinationId; ?>" class="btn">Add Attraction</a>
                    </div>
                    
                    <?php if($attractionsResult->num_rows == 0): ?>
                        <p>No attractions added yet.</p>
                    <?php else: ?>
                        <?php while($attraction = $attractionsResult->fetch_assoc()): ?>
                            <div class="item-card">
                                <div class="item-header">
                                    <h3><?php echo $attraction['name']; ?></h3>
                                    <div>
                                        <a href="edit_attraction.php?id=<?php echo $attraction['attraction_id']; ?>" class="btn btn-small">Edit</a>
                                        <a href="delete_attraction.php?id=<?php echo $attraction['attraction_id']; ?>" class="btn btn-small" onclick="return confirm('Are you sure you want to delete this attraction?')">Delete</a>
                                    </div>
                                </div>
                                <p><strong>Cost:</strong> $<?php echo number_format($attraction['cost'], 2); ?></p>
                                <p><?php echo substr($attraction['description'], 0, 150); ?>...</p>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="food" class="tab-content">
                <div class="form-container">
                    <div class="section-header">
                        <h2>Food Recommendations</h2>
                        <a href="add_food.php?destination_id=<?php echo $destinationId; ?>" class="btn">Add Food</a>
                    </div>
                    
                    <?php if($foodsResult->num_rows == 0): ?>
                        <p>No food recommendations added yet.</p>
                    <?php else: ?>
                        <?php while($food = $foodsResult->fetch_assoc()): ?>
                            <div class="item-card">
                                <div class="item-header">
                                    <h3><?php echo $food['name']; ?></h3>
                                    <div>
                                        <a href="edit_food.php?id=<?php echo $food['food_id']; ?>" class="btn btn-small">Edit</a>
                                        <a href="delete_food.php?id=<?php echo $food['food_id']; ?>" class="btn btn-small" onclick="return confirm('Are you sure you want to delete this food recommendation?')">Delete</a>
                                    </div>
                                </div>
                                <p><strong>Price Range:</strong> <?php echo $food['price_range']; ?></p>
                                <p><?php echo substr($food['description'], 0, 150); ?>...</p>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="accommodations" class="tab-content">
                <div class="form-container">
                    <div class="section-header">
                        <h2>Accommodations</h2>
                        <a href="add_accommodation.php?destination_id=<?php echo $destinationId; ?>" class="btn">Add Accommodation</a>
                    </div>
                    
                    <?php if($accommodationsResult->num_rows == 0): ?>
                        <p>No accommodations added yet.</p>
                    <?php else: ?>
                        <?php while($accommodation = $accommodationsResult->fetch_assoc()): ?>
                            <div class="item-card">
                                <div class="item-header">
                                    <h3><?php echo $accommodation['name']; ?></h3>
                                    <div>
                                        <a href="edit_accommodation.php?id=<?php echo $accommodation['accommodation_id']; ?>" class="btn btn-small">Edit</a>
                                        <a href="delete_accommodation.php?id=<?php echo $accommodation['accommodation_id']; ?>" class="btn btn-small" onclick="return confirm('Are you sure you want to delete this accommodation?')">Delete</a>
                                    </div>
                                </div>
                                <p><strong>Price Range:</strong> <?php echo $accommodation['price_range']; ?></p>
                                <p><?php echo substr($accommodation['description'], 0, 150); ?>...</p>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="practical" class="tab-content">
                <div class="form-container">
                    <div class="section-header">
                        <h2>Practical Information</h2>
                        <a href="add_practical_info.php?destination_id=<?php echo $destinationId; ?>" class="btn">Add Practical Info</a>
                    </div>
                    
                    <?php if($practicalInfoResult->num_rows == 0): ?>
                        <p>No practical information added yet.</p>
                    <?php else: ?>
                        <?php while($info = $practicalInfoResult->fetch_assoc()): ?>
                            <div class="item-card">
                                <div class="item-header">
                                    <h3><?php echo $info['title']; ?></h3>
                                    <div>
                                        <a href="edit_practical_info.php?id=<?php echo $info['info_id']; ?>" class="btn btn-small">Edit</a>
                                        <a href="delete_practical_info.php?id=<?php echo $info['info_id']; ?>" class="btn btn-small" onclick="return confirm('Are you sure you want to delete this information?')">Delete</a>
                                    </div>
                                </div>
                                <p><?php echo substr($info['content'], 0, 150); ?>...</p>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs and content
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to current tab and content
                tab.classList.add('active');
                document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
            });
        });
    </script>
</body>
</html>