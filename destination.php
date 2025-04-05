<?php
session_start();
require_once 'config.php';
error_reporting(0);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: destinations.php");
    exit();
}

$destination_id = $_GET['id'];

// Fetch destination details
$query = "SELECT * FROM destination_details WHERE destination_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $destination_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: destinations.php");
    exit();
}

$destination = mysqli_fetch_assoc($result);

// Fetch traveler types
$traveler_query = "SELECT tt.type_name FROM traveler_types tt
                   JOIN destination_traveler_types dtt ON tt.type_id = dtt.type_id
                   WHERE dtt.destination_id = ?";
$traveler_stmt = mysqli_prepare($conn, $traveler_query);
mysqli_stmt_bind_param($traveler_stmt, "i", $destination_id);
mysqli_stmt_execute($traveler_stmt);
$traveler_result = mysqli_stmt_get_result($traveler_stmt);
$traveler_types = [];
while ($row = mysqli_fetch_assoc($traveler_result)) {
    $traveler_types[] = $row['type_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($destination['name']); ?> - Wanderlust Canvas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .destination-header {
            background-image: url('<?php echo htmlspecialchars($destination['image_path']); ?>');
            background-size: cover;
            background-position: center;
            padding: 100px 20px;
            color: white;
            text-align: center;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
        }
        .tag {
            background-color: #eee;
            padding: 5px 10px;
            margin: 3px;
            border-radius: 15px;
            display: inline-block;
        }
        .destination-tabs {
            text-align: center;
            margin: 30px 0;
        }
        .tab-btn {
            margin: 0 10px;
            padding: 8px 15px;
            cursor: pointer;
            border: none;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .tab-btn.active {
            background-color: #007BFF;
            color: white;
        }
        .tab-pane {
            display: none;
            padding: 20px;
        }
        .tab-pane.active {
            display: block;
        }
        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="destination-header">
    <h1><?php echo htmlspecialchars($destination['name']); ?></h1>
    <p><?php echo htmlspecialchars($destination['country']); ?></p>
    <?php if (!empty($traveler_types)): ?>
        <div class="traveler-tags">
            <?php foreach ($traveler_types as $type): ?>
                <span class="tag"><?php echo htmlspecialchars($type); ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="destination-tabs">
    <button class="tab-btn active" data-tab="overview">Overview</button>
    <button class="tab-btn" data-tab="attractions">Attractions</button>
    <button class="tab-btn" data-tab="food">Food</button>
    <button class="tab-btn" data-tab="accommodation">Accommodation</button>
    <button class="tab-btn" data-tab="practical">Practical Info</button>
    <button class="tab-btn" data-tab="cost">Cost Info</button>
</div>

<div class="container">
    <div class="tab-content">
        <div class="tab-pane active" id="overview">
            <h2>About <?php echo htmlspecialchars($destination['name']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>
            <ul>
                <?php if (!empty($destination['best_time'])): ?>
                    <li><strong>Best Time:</strong> <?php echo htmlspecialchars($destination['best_time']); ?></li>
                <?php endif; ?>
                <?php if (!empty($destination['transportation'])): ?>
                    <li><strong>Transportation:</strong> <?php echo htmlspecialchars($destination['transportation']); ?></li>
                <?php endif; ?>
                <?php if (!empty($destination['currency'])): ?>
                    <li><strong>Currency:</strong> <?php echo htmlspecialchars($destination['currency']); ?></li>
                <?php endif; ?>
                <?php if (!empty($destination['language'])): ?>
                    <li><strong>Language:</strong> <?php echo htmlspecialchars($destination['language']); ?></li>
                <?php endif; ?>
                <?php if (!empty($destination['travel_tips'])): ?>
                    <li><strong>Travel Tips:</strong> <?php echo nl2br(htmlspecialchars($destination['travel_tips'])); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="tab-pane" id="attractions">
            <h2>Attractions</h2>
            <p><?php echo nl2br(htmlspecialchars($destination['attractions'])); ?></p>
        </div>

        <div class="tab-pane" id="food">
            <h2>Food</h2>
            <p><?php echo nl2br(htmlspecialchars($destination['food'])); ?></p>
        </div>

        <div class="tab-pane" id="accommodation">
            <h2>Accommodation</h2>
            <p><?php echo nl2br(htmlspecialchars($destination['accommodation'])); ?></p>
        </div>

        <div class="tab-pane" id="practical">
            <h2>Practical Information</h2>
            <p><?php echo nl2br(htmlspecialchars($destination['practical_info'])); ?></p>
        </div>

        <div class="tab-pane" id="cost">
            <h2>Cost Information</h2>
            <p><?php echo nl2br(htmlspecialchars($destination['cost_info'])); ?></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const tab = button.getAttribute('data-tab');
            tabPanes.forEach(pane => {
                pane.classList.remove('active');
                if (pane.id === tab) {
                    pane.classList.add('active');
                }
            });
        });
    });
</script>

</body>
</html>
