<?php
include 'navbar.php';
require_once 'db_connection.php';
require_once 'functions.php';
?>

<section class="subscription-section">
    <div class="container">
        <h1>Choose Your Subscription Plan</h1>
        <p class="subscription-intro">Unlock premium features and exclusive content with our subscription plans</p>

        <div class="subscription-plans">
            <?php
            $plans = getSubscriptionPlans($conn);

            if ($plans && $plans->num_rows > 0):
                while ($plan = $plans->fetch_assoc()):
            ?>
                <div class="plan-card <?php echo strtolower($plan['plan_name']); ?>">
                    <h2><?php echo htmlspecialchars($plan['plan_name']); ?></h2>
                    <p class="plan-price">$<?php echo number_format($plan['price'], 2); ?><span>/month</span></p>
                    <p class="plan-description"><?php echo htmlspecialchars($plan['description']); ?></p>

                    <ul class="plan-features">
                        <?php if ($plan['plan_name'] === 'Basic'): ?>
                            <li>Access to basic destination guides</li>
                            <li>Search functionality</li>
                            <li>Basic profile features</li>
                        <?php elseif ($plan['plan_name'] === 'Premium'): ?>
                            <li>All Basic features</li>
                            <li>Exclusive destination guides</li>
                            <li>Personalized recommendations</li>
                            <li>Ad-free experience</li>
                        <?php elseif ($plan['plan_name'] === 'Ultimate'): ?>
                            <li>All Premium features</li>
                            <li>Priority customer support</li>
                            <li>Exclusive travel deals</li>
                            <li>Early access to new features</li>
                            <li>Downloadable travel guides</li>
                        <?php endif; ?>
                    </ul>

                    <!-- Button: Go to payment.php?plan_id=... -->
                    <?php if (isLoggedIn()): ?>
                        <a href="payment.php?plan_id=<?php echo $plan['plan_id']; ?>" class="btn subscribe-btn">
                            Subscribe Now
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn subscribe-btn">Login to Subscribe</a>
                    <?php endif; ?>
                </div>
            <?php
                endwhile;
            else:
                echo "<p>No subscription plans available at the moment.</p>";
            endif;
            ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
