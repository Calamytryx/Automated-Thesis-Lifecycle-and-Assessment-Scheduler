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

            <!-- Navbar user Program and role -->
            <div class="d-flex align-items-center me-3 justify-content-center flex-wrap navbar-user-badge-wrap">
                <?php
                $userType = $_SESSION['usertype'];
                $userId = $_SESSION['id'];
                $programChairFlag = isset($_SESSION['program_chair']) ? (int)$_SESSION['program_chair'] : null;
                $roleLabel = '';
                $roleClass = '';
                $canAccessDashboard = false;

                require_once __DIR__ . '/../../dashboard/includes/section_access.php';
                $canAccessDashboard = userCanAccessDashboard($pdo, (int)$userId, (int)$userType);

                if ($userType == 0) {
                    // Super admin (id=0) keeps Admin label; other type-0 accounts are Program Chair.
                    $isProgramChair = ($userId != 0) || ($programChairFlag === 1);
                    if ($isProgramChair) {
                        $roleLabel = 'Program Chair';
                        $roleClass = 'navbar-role-admin';
                    } else {
                        $roleLabel = 'Admin';
                        $roleClass = 'navbar-role-admin';
                    }
                } elseif ($userType == 1) {
                    $roleLabel = 'Student';
                    $roleClass = 'navbar-role-student';
                } elseif ($userType == 2) {
                    $roleLabel = 'Faculty';
                    $roleClass = 'navbar-role-prof';
                } else {
                    $roleLabel = 'Unknown';
                    $roleClass = 'navbar-role-user';
                }

                $program = trim((string)($_SESSION['program'] ?? ''));
                // Preserve legacy stored values like "Program - Track" by keeping only the base program name.
                if ($program !== '') {
                    $program = preg_replace('/\s-\s.*$/', '', $program);
                }

                if ($_SESSION['id'] == 0) {
                    echo '<span class="badge navbar-user-type-badge ' . $roleClass . '">ITD</span>';
                } else
                if ( $userType == 1) {
                    // Get team information for the student
                    $teamQuery = "SELECT t.*, tm.role 
                                  FROM teams t 
                                  INNER JOIN team_members tm ON t.id = tm.team_id 
                                  WHERE tm.user_id = ?";
                    $teamStmt = $pdo->prepare($teamQuery);
                    $teamStmt->execute([$userId]);
                    $team = $teamStmt->fetch();

                    $researchSubject = 'Research Methods'; // Default

                    if ($team) {
                        // Check if team has an approved research title
                        $titleQuery = "SELECT id, approved_at FROM research_titles WHERE team_id = ?";
                        $titleStmt = $pdo->prepare($titleQuery);
                        $titleStmt->execute([$team['id']]);
                        $title = $titleStmt->fetch();
                        
                        if ($title && $title['approved_at'] !== null) {
                            // Count evaluations for the research title
                            $evaluationQuery = "SELECT COUNT(*) FROM evaluation_per_panel epp
                            WHERE epp.student_id = ?";
                            $evaluationStmt = $pdo->prepare($evaluationQuery);
                            $evaluationStmt->execute([$userId]);
                            $evaluationCount = (int)$evaluationStmt->fetchColumn();
                            
                            if ($evaluationCount === 1) {
                                $researchSubject = 'Research 1';
                            } elseif ($evaluationCount === 2) {
                                $researchSubject = 'Research 2';
                            } else {
                                $researchSubject = 'Research Methods';
                            }
                        }
                    }
                    $studentBadgeText = $roleLabel . ($program !== '' ? ' - ' . $program : '');
                    echo '<span class="badge navbar-user-type-badge ' . $roleClass . ' text-start">' . htmlspecialchars($studentBadgeText) . '<br>' . htmlspecialchars($researchSubject) . '</span>';
                } else if ($userType == 0 || $userType == 2) {
                    $staffBadgeText = $roleLabel . ($program !== '' ? ' - ' . $program : '');
                    echo '<span class="badge navbar-user-type-badge ' . $roleClass . '">' . htmlspecialchars($staffBadgeText) . '</span>';
                }
                else {
                    echo '<span class="badge navbar-user-type-badge ' . $roleClass . '">Unknown Role</span>';
                }
                ?>
                
                
            </div>

            <!-- All navbar items aligned to the right -->
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
    <a class="nav-link" href="../home">
        <?php 
            echo ($_SESSION['usertype'] == 1) ? 'Home' : 'Faculty View';
        ?>
    </a>
</li>


                <?php if ($canAccessDashboard): ?>
                    <li class="nav-item">
    <a class="nav-link" href="../dashboard">
        <?php
        echo ($_SESSION['usertype'] == 2) ? 'Subject Teacher View' :
             (($_SESSION['usertype'] == 0 && $_SESSION['id'] == 0) ? 'Admin View' : 'Program Chair View');
        ?>
    </a>
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
                    <!---<a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Pages
                    </a>-->
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
                            <button type="button" class="btn btn-sm" id="markAllReadBtn" style="display: none; color: #1304ee; border-color: #1304ee; font-size: 0.8rem;">
                                <i class="fas fa-check-double me-1" style="color: #1304ee;"></i>Mark all read
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
                        <div class="dropdown-footer border-top px-3 py-2 text-center">
                            <a href="../notifications/" class="text-decoration-none fw-semibold" style="color: #1304ee; font-size: 0.875rem;">
                                <i class="fas fa-list me-1" style="color: #1304ee;"></i>View All Notifications
                            </a>
                        </div>
                    </div>
                </li>

                <li class="nav-item logout">
                    <a class="nav-link" href="../logout" title="Logout"><i class="fas fa-sign-out"></i></a>
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