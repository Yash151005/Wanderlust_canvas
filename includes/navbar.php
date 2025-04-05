<nav class="navbar">
    <div class="container">
        <div class="logo">
            <a href="index.php">Wonderlust Canvas</a>
        </div>
        <div class="nav-links" id="navLinks">
            <ul>
                <li><a href="explore.php">Explore</a></li>
                <li><a href="search.php">Search</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">My Account <i class="fas fa-caret-down"></i></a>
                        <div class="dropdown-menu">
                            <a href="profile.php">Profile</a>
                            <a href="my-subscription.php">My Subscription</a>
                            <?php if ($_SESSION['is_admin'] ?? false): ?>
                                <a href="admin/index.php">Admin Panel</a>
                            <?php endif; ?>
                            <a href="logout.php">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php" class="btn-nav">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>