<?php

// Get all destinations
function getAllDestinations($conn) {
    $sql = "SELECT * FROM destinations";
    return $conn->query($sql);
}

// Get destination by ID
function getDestinationById($conn, $destination_id) {
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE destination_id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Get all traveler types
function getAllTravelerTypes($conn) {
    $sql = "SELECT * FROM traveler_types";
    return $conn->query($sql);
}

// Get all subscription plans
function getSubscriptionPlans($conn) {
    $sql = "SELECT * FROM subscription_plans";
    return $conn->query($sql);
}

// Get subscription plan by ID
function getSubscriptionPlanById($conn, $plan_id) {
    $stmt = $conn->prepare("SELECT * FROM subscription_plans WHERE plan_id = ?");
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Example utility: check if user is logged in (can be used in future)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
