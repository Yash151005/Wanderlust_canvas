<?php
include 'db_connection.php'; // This includes your connection and functions
include 'navbar.php';
?>

<section class="explore-section">
    <div class="container">
        <h1>Explore Amazing Destinations</h1>

        <div class="filter-section">
            <h3>Filter by Traveler Type</h3>
            <form action="explore.php" method="get" id="filter-form">
                <select name="traveler_type" id="traveler-type-filter">
                    <option value="">All Types</option>
                    <?php
                    $traveler_types = getAllTravelerTypes($conn);
                    while ($type = $traveler_types->fetch_assoc()) {
                        $selected = (isset($_GET['traveler_type']) && $_GET['traveler_type'] == $type['type_id']) ? 'selected' : '';
                        echo "<option value='{$type['type_id']}' $selected>{$type['type_name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn">Apply Filter</button>
            </form>
        </div>

        <div class="destinations-grid">
            <?php
            if (isset($_GET['traveler_type']) && !empty($_GET['traveler_type'])) {
                $traveler_type = $_GET['traveler_type'];
                $sql = "SELECT DISTINCT d.* FROM destination_details d
                        JOIN destination_traveler_types dtt ON d.destination_id = dtt.destination_id
                        WHERE dtt.type_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $traveler_type);
                $stmt->execute();
                $destinations = $stmt->get_result();
            } else {
                $destinations = getAllDestinations($conn);
            }

            if ($destinations && $destinations->num_rows > 0) {
                while ($destination = $destinations->fetch_assoc()) {
                    ?>
                    <div class="destination-card">
                        <div class="destination-image">
                            <img src="<?php echo $destination['image_path']; ?>" alt="<?php echo $destination['name']; ?>">
                        </div>
                        <div class="destination-info">
                            <h3><?php echo $destination['name']; ?></h3>
                            <p><?php echo substr($destination['description'], 0, 100) . '...'; ?></p>
                            <a href="destination.php?id=<?php echo $destination['destination_id']; ?>" class="btn">Explore More</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No destinations found matching your criteria.</p>";
            }
            ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
