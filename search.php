<?php include 'navbar.php';

?>
<?php error_reporting(0);?>
<link rel="stylesheet" href="css/style.css">
<section class="search-section">
    <div class="container">
        <h1>Find Your Dream Destination</h1>
        
        <div class="search-form-container">
            <form action="search.php" method="get" class="search-form">
                <div class="search-input-container">
                    <input type="text" name="q" placeholder="Where would you like to go?" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <select name="traveler_type">
                        <option value="">All Traveler Types</option>
                        <?php
                        $traveler_types = getAllTravelerTypes($conn);
                        while ($type = $traveler_types->fetch_assoc()) {
                            $selected = (isset($_GET['traveler_type']) && $_GET['traveler_type'] == $type['type_id']) ? 'selected' : '';
                            echo "<option value='{$type['type_id']}' $selected>{$type['type_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>

        <?php
        // Display search results if search was performed
        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $search_term = $_GET['q'];
            $traveler_type = isset($_GET['traveler_type']) && !empty($_GET['traveler_type']) ? $_GET['traveler_type'] : null;
            
            $search_results = searchDestinations($conn, $search_term, $traveler_type);
            
            echo "<h2>Search Results for: " . htmlspecialchars($search_term) . "</h2>";
            
            if ($search_results->num_rows > 0) {
                echo "<div class='destinations-grid'>";
                while ($destination = $search_results->fetch_assoc()) {
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
                echo "</div>";
            } else {
                echo "<p class='no-results'>No destinations found matching your search criteria.</p>";
            }
        }
        ?>
    </div>
</section>

<?php include 'footer.php'; ?>