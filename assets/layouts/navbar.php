<nav class="navbar navbar-expand-md bg-main py-2">

    <div class="container-fluid px-4">
        <a class="navbar-brand" href="../home">

            <img src="../assets/images/<?php echo APP_LOGO_NAVBAR; ?>" alt="" width="88" height="10%" class="10%">

            <!-- <?php echo APP_NAME; ?> -->

        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>        
        <!-- User role pill moved to sidebar header -->

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <!-- All navbar items aligned to the right -->
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="../home">Home</a>
                </li>

                <?php if ($_SESSION['usertype'] == 0): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard">Dashboard</a>
                    </li>
                <?php endif; ?>
                <!-- <?php //if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0): ?>
                    <li class="nav-item"> 
                    <a class="nav-link" href="../decision-support">Defense</a>
                </li>
                <?php //endif; ?> -->
            
                <!-- <li class="nav-item">
                    <a class="nav-link" href="../contact">Contact Us</a>
                </li> -->

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Pages
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="pagesDropdown">
                        <?php
                        // Get all published pages for the menu
                        $pages = getPublishedPages($pdo);
                        if (!empty($pages)) {
                            foreach ($pages as $page) {
                                echo '<li><a class="dropdown-item" href="../page/?slug=' . $page['slug'] . '">' . htmlspecialchars($page['title']) . '</a></li>';
                            }
                        } else {
                            echo '<li><a class="dropdown-item disabled">No pages available</a></li>';
                        }
                        ?>
                    </ul>                </li>
                    <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none; font-size: 0.6rem;">
                            0
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationsDropdown" style="min-width: 320px; max-width: 400px;">
                        <div class="dropdown-header d-flex justify-content-between align-items-center border-bottom px-3 py-2">
                            <h6 class="mb-0">Notifications</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="markAllReadBtn" style="display: none;">
                                Mark all read
                            </button>
                        </div>
                        <div id="notificationsList" class="notification-dropdown-body" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mb-0 mt-2 text-muted">Loading notifications...</p>
                            </div>
                        </div>
                        <div class="dropdown-footer border-top px-3 py-2">
                            <a href="../notifications/" class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-list me-1"></i>View All Notifications
                            </a>
                        </div>
                    </div>
                </li>

                <!-- User role pill removed from navbar, now in sidebar header -->
                <!-- <?php if ($_SESSION['usertype'] == 0): ?>
                    <li class="nav-item">
                    </li>
                <?php endif; ?> -->
                <!-- <?php //if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0): ?>
                    <li class="nav-item"> 
                    <a class="nav-link" href="../decision-support">Defense</a>
                </li>
                <?php //endif; ?> -->
            
                <!-- <li class="nav-item">
                    <a class="nav-link" href="../contact">Contact Us</a> 
                </li> -->
                <script>
                // Load notification count on page load (basic functionality only)
                // Full notification functionality is handled by notifications.js
                document.addEventListener('DOMContentLoaded', function() {
                    loadNotificationCount();
                    
                    // Refresh notification count every 30 seconds
                    setInterval(function() {
                        loadNotificationCount();
                    }, 30000);
                });

                function loadNotificationCount() {
                    fetch('../assets/includes/get_notification_count.php')
                        .then(response => response.json())
                        .then(data => {
                            const badge = document.getElementById('notificationBadge');
                            if (data.success && data.count > 0) {
                                badge.textContent = data.count;
                                badge.style.display = 'block';
                            } else {
                                badge.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Error loading notification count:', error);
                        });
                }

                </script>
                <!-- Profile and logout moved to sidebar -->
            </ul>
        </div>
    </div>
</nav>