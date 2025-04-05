<?php
// Database connection configuration
$servername = "localhost";
$username = "root";  // Change this to your MySQL username
$password = "";      // Change this to your MySQL password
$dbname = "wonderlust_canvas";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize user inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to get all destinations
function getAllDestinations($conn) {
    $sql = "SELECT * FROM destinations ORDER BY name";
    $result = $conn->query($sql);
    return $result;
}

// Function to get popular destinations
function getPopularDestinations($conn, $limit = 4) {
    $sql = "SELECT * FROM destinations WHERE popular = TRUE LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

// Function to get destination details
function getDestinationDetails($conn, $destination_id) {
    $sql = "SELECT d.*, dd.* FROM destinations d 
            LEFT JOIN destination_details dd ON d.destination_id = dd.destination_id 
            WHERE d.destination_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get traveler types for a destination
function getDestinationTravelerTypes($conn, $destination_id) {
    $sql = "SELECT tt.* FROM traveler_types tt
            JOIN destination_traveler_types dtt ON tt.type_id = dtt.type_id
            WHERE dtt.destination_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

// Function to get all traveler types
function getAllTravelerTypes($conn) {
    $sql = "SELECT * FROM traveler_types ORDER BY type_name";
    $result = $conn->query($sql);
    return $result;
}

// Function to get subscription plans
function getSubscriptionPlans($conn) {
    $sql = "SELECT * FROM subscription_plans ORDER BY price";
    $result = $conn->query($sql);
    return $result;
}

// Function to get user subscription
function getUserSubscription($conn, $user_id) {
    $sql = "SELECT us.*, sp.* FROM user_subscriptions us
            JOIN subscription_plans sp ON us.plan_id = sp.plan_id
            WHERE us.user_id = ? AND us.status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to search destinations
function searchDestinations($conn, $search_term, $traveler_type = null) {
    $search_term = "%$search_term%";
    
    if ($traveler_type) {
        $sql = "SELECT DISTINCT d.* FROM destinations d
                JOIN destination_traveler_types dtt ON d.destination_id = dtt.destination_id
                WHERE (d.name LIKE ? OR d.description LIKE ?) AND dtt.type_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $search_term, $search_term, $traveler_type);
    } else {
        $sql = "SELECT * FROM destinations WHERE name LIKE ? OR description LIKE ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $search_term, $search_term);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}