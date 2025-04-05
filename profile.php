<?php 
include 'header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user subscription
$subscription = getUserSubscription($conn, $_SESSION['user_id']);
?>

<section class="profile-section">
    <div class="container">
        <h1>My Account</h1>
        
        <div class="profile-content">
            <div class="profile-info">
                <h2>Profile Information</h2>
                <p><strong>Name:</strong> <?php echo $_SESSION['name']; ?></p>
                <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                <a href="edit_profile.php" class="btn">Edit Profile</a>
            </div>
            
            <div class="subscription-info">
                <h2>Subscription Details</h2>
                <?php if ($subscription): ?>
                    <div class="current-plan">
                        <h3>Current Plan: <?php echo $subscription['plan_name']; ?></h3>
                        <p><?php echo $subscription['description']; ?></p>
                        <p><strong>Price:</strong> $<?php echo $subscription['price']; ?>/month</p>
                        <p><strong>Valid until:</strong> <?php echo $subscription['end_date']; ?></p>
                        <a href="cancel_subscription.php" class="btn btn-danger">Cancel Subscription</a>
                    </div>
                <?php else: ?>
                    <p>You don't have an active subscription.</p>
                    <a href="subscription.php" class="btn">View Subscription Plans</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bookmarked-destinations">
            <h2>My Favorite Destinations</h2>
            <p>Feature coming soon!</p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>