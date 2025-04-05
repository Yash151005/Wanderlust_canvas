<?php
include 'navbar.php';
require_once 'db_connection.php';
require_once 'functions.php';

session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='container'><p>Please <a href='login.php'>login</a> to view your subscriptions.</p></div>";
    include 'footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's active subscription
$stmt = $conn->prepare("
    SELECT sp.plan_name, sp.description, sp.price, s.start_date, s.end_date, s.status
    FROM subscriptions s
    JOIN subscription_plans sp ON s.plan_id = sp.plan_id
    WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= NOW()
    ORDER BY s.start_date DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<section class="my-subscription-section">
    <div class="container">
        <h1>My Subscription</h1>

        <?php if ($result->num_rows > 0): 
            $subscription = $result->fetch_assoc(); ?>
            <div class="subscription-details">
                <h2><?php echo htmlspecialchars($subscription['plan_name']); ?> Plan</h2>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($subscription['description']); ?></p>
                <p><strong>Price:</strong> $<?php echo $subscription['price']; ?>/month</p>
                <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($subscription['start_date'])); ?></p>
                <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($subscription['end_date'])); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($subscription['status']); ?></p>
            </div>
        <?php else: ?>
            <p>You do not have an active subscription. <a href="subscriptions.php" class="btn">Subscribe Now</a></p>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
