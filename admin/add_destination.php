<?php
session_start();
require_once('../includes/db_connect.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get traveler types
$travelerTypes = $conn->query("SELECT * FROM traveler_types ORDER BY type_name");

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $isPopular = isset($_POST['is_popular']) ? 1 : 0;
    $travelerTypeIds = isset($_POST['traveler_types']) ? $_POST['traveler_types'] : [];
    
    // Image upload
    $imagePath = '';
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
            // Insert destination
            $stmt = $conn->prepare("INSERT INTO destinations (name, description, image_path, is_popular) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $description, $imagePath, $isPopular);
            $stmt->execute();
            
            $destinationId = $conn->insert_id;
            
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
            
            $message = "Destination added successfully!";
            
            // Redirect after a short delay
            header("Refresh: 2; URL=edit_destination.php?id=" . $destinationId);
            
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
    <title>Add Destination - Wanderlust Canvas</title>
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
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            max-width: 800px;
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
            <h1>Add New Destination</h1>
            
            <?php if(!empty($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form action="add_destination.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Destination Name*</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description*</label>
                        <textarea id="description" name="description" rows="6" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Featured Image</label>
                        <input type="file" id="image" name="image">
                    </div>
                    
                    <div class="form-group">
                        <label>Traveler Types</label>
                        <div class="checkbox-group">
                            <?php while($type = $travelerTypes->fetch_assoc()): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="type_<?php echo $type['type_id']; ?>" name="traveler_types[]" value="<?php echo $type['type_id']; ?>">
                                    <label for="type_<?php echo $type['type_id']; ?>"><?php echo $type['type_name']; ?></label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-item">
                            <input type="checkbox" id="is_popular" name="is_popular">
                            <span>Mark as Popular Destination</span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Save Destination</button>
                        <a href="destinations.php" class="btn">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>