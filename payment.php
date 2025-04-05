<?php
include 'navbar.php';
require_once 'db_connection.php';
require_once 'functions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['plan_id'])) {
    echo "<p class='container'>Invalid plan selected.</p>";
    include 'footer.php';
    exit;
}

$plan_id = intval($_GET['plan_id']);
$plan = getSubscriptionPlanById($conn, $plan_id);

if (!$plan) {
    echo "<p class='container'>Plan not found.</p>";
    include 'footer.php';
    exit;
}
?>

<section class="payment-section">
    <div class="container">
        <h1>Payment Details for <?php echo htmlspecialchars($plan['plan_name']); ?> Plan</h1>
        <p>Price: $<?php echo number_format($plan['price'], 2); ?>/month</p>

        <form action="process_payment.php" method="post">
            <input type="hidden" name="plan_id" value="<?php echo $plan_id; ?>">

            <label>Cardholder Name</label>
            <input type="text" name="card_name" required>

            <label>Card Number</label>
            <input type="text" name="card_number" required maxlength="16">

            <label>Expiry Date</label>
            <input type="month" name="expiry_date" required>

            <label>CVV</label>
            <input type="text" name="cvv" required maxlength="4">

            <button type="submit" class="btn">Complete Payment</button>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>
