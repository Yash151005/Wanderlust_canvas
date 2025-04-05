<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wanderlust_canvas";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to display alert messages
function showAlert($message, $type = 'info') {
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
}

// Function to get user details
function getUserDetails($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get all destinations
function getAllDestinations() {
    global $conn;
    $sql = "SELECT * FROM destinations ORDER BY id DESC";
    $result = $conn->query($sql);
    $destinations = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $destinations[] = $row;
        }
    }

    return $destinations;
}

// Function to get destination by ID
function getDestinationById($id) {
    global $conn;
    $sql = "SELECT * FROM destinations WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get subscription plans
function getSubscriptionPlans() {
    global $conn;
    $sql = "SELECT * FROM subscription_plans";
    $result = $conn->query($sql);
    $plans = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
    }

    return $plans;
}

// Function to get user subscription
function getUserSubscription($user_id) {
    global $conn;
    $sql = "SELECT s.*, p.name as plan_name, p.price, p.features 
            FROM subscriptions s 
            JOIN subscription_plans p ON s.plan_id = p.id 
            WHERE s.user_id = ? AND s.end_date >= CURDATE() 
            ORDER BY s.end_date DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get traveler types
function getTravelerTypes() {
    global $conn;
    $sql = "SELECT * FROM traveler_types";
    $result = $conn->query($sql);
    $types = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $types[] = $row;
        }
    }

    return $types;
}
?>
