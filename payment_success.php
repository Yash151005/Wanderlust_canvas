<?php
include 'navbar.php';
require_once 'db_connection.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['plan_id'])) {
    $plan_id = intval($_POST['plan_id']);
    $plan = getSubscriptionPlanById($conn, $plan_id);

    if ($plan) {
        // Normally you'd process the payment here using a real payment gateway
        echo "<section class='success-section'><div class='container'>";
        echo "<h2>Payment Successful!</h2>";
        echo "<p>You have subscribed to the <strong>" . htmlspecialchars($plan['plan_name']) . "</strong> plan for $<strong>" . $plan['price'] . "</strong>/month.</p>";
        echo "<a href='index.php' class='btn'>Go to Home</a>";
        echo "</div></section>";
    } else {
        echo "<p>Invalid plan selected.</p>";
    }
} else {
    echo "<p>Something went wrong. Please try again.</p>";
}
include 'footer.php';
?>
