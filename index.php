<?php
error_reporting(0);
session_start();
require_once 'config.php';

// Fetch all popular destinations (removed LIMIT 4)
$query = "SELECT * FROM destinations WHERE popular = TRUE";
$result = mysqli_query($conn, $query);
$popular_destinations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $popular_destinations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wanderlust Canvas - Discover Extraordinary Travel Stories</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <header class="hero">
        <div class="hero-content">
            <h1>Discover Extraordinary Travel Stories</h1>
            <p>Explore destinations through the eyes of real travelers</p>
            
            <div class="search-container">
                <form action="search.php" method="GET">
                    <input type="text" name="query" placeholder="Where would you like to go?" required>
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </header>

    <section class="why-section">
        <div class="container">
            <h2 class="section-title">Why Wanderlust Canvas?</h2>
            <div class="features">
                <div class="feature">
                    <div class="feature-icon"><i class="fas fa-map-marked-alt"></i></div>
                    <h3>Authentic Experiences</h3>
                    <p>Real stories from real travelers who've been there and done that.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><i class="fas fa-compass"></i></div>
                    <h3>Personalized Recommendations</h3>
                    <p>Find destinations that match your traveler type and preferences.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><i class="fas fa-utensils"></i></div>
                    <h3>Local Cuisine</h3>
                    <p>Discover the best local food recommendations at each destination.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><i class="fas fa-info-circle"></i></div>
                    <h3>Practical Information</h3>
                    <p>Get all the details you need for a smooth and enjoyable trip.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="destinations-section">
        <div class="container">
            <h2 class="section-title">Popular Destinations</h2>
            <div class="destinations-grid">
                <?php foreach ($popular_destinations as $destination): ?>
                <div class="destination-card">
                    <a href="destination.php?id=<?php echo $destination['destination_id']; ?>">
                        <div class="destination-img" style="background-image: url('<?php echo $destination['image_path']; ?>')">
                            <div class="destination-overlay">
                                <h3><?php echo $destination['name']; ?></h3>
                                <p><?php echo $destination['country']; ?></p>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="center-btn">
                <a href="destinations.php" class="btn">View All Destinations</a>
            </div>
        </div>
    </section>

    <section class="traveler-type-section">
        <div class="container">
            <h2 class="section-title">What Type of Traveler Are You?</h2>
            <p class="section-desc">Find destinations that match your travel style</p>
            
            <form action="destinations.php" method="GET" class="traveler-form">
                <div class="traveler-select">
                    <select name="traveler_type" id="traveler-type">
                        <option value="">Select your traveler type</option>
                        <?php
                        $query = "SELECT * FROM traveler_types ORDER BY type_name";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="' . $row['type_id'] . '">' . $row['type_name'] . '</option>';
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn">Find Destinations</button>
                </div>
            </form>
        </div>
    </section>

    <section class="subscription-section">
        <div class="container">
            <h2 class="section-title">Subscription Plans</h2>
            <p class="section-desc">Unlock premium features with our subscription plans</p>
            
            <div class="subscription-plans">
                <?php
                $query = "SELECT * FROM subscription_plans ORDER BY price";
                $result = mysqli_query($conn, $query);
                while ($plan = mysqli_fetch_assoc($result)):
                ?>
                <div class="plan-card">
                    <h3><?php echo $plan['plan_name']; ?></h3>
                    <div class="plan-price">
                        <span class="price">$<?php echo number_format($plan['price'], 2); ?></span>
                        <?php if ($plan['duration_months'] > 0): ?>
                        <span class="duration">/ <?php echo $plan['duration_months']; ?> month<?php echo $plan['duration_months'] > 1 ? 's' : ''; ?></span>
                        <?php else: ?>
                        <span class="duration">Forever</span>
                        <?php endif; ?>
                    </div>
                    <div class="plan-features">
                        <p><?php echo $plan['description']; ?></p>
                    </div>
                    <a href="<?php echo isLoggedIn() ? 'subscribe.php?plan=' . $plan['plan_id'] : 'login.php'; ?>" class="btn">
                        <?php echo $plan['plan_name'] == 'Free' ? 'Current Plan' : 'Subscribe Now'; ?>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="js/script.js"></script>
</body>
</html>
