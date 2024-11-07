<?php
// Include database connection
include '../assets/setup/db.inc.php';

// Fetch counts
$totalUsersStmt = $pdo->prepare("SELECT COUNT(*) AS total_users FROM users");
$totalUsersStmt->execute();
$totalUsers = $totalUsersStmt->fetchColumn();

$approvedTitlesStmt = $pdo->prepare("SELECT COUNT(*) AS approved_titles FROM research_titles WHERE approved_at IS NOT NULL");
$approvedTitlesStmt->execute();
$approvedTitles = $approvedTitlesStmt->fetchColumn();

$notApprovedTitlesStmt = $pdo->prepare("SELECT COUNT(*) AS not_approved_titles FROM research_titles WHERE approved_at IS NULL");
$notApprovedTitlesStmt->execute();
$notApprovedTitles = $notApprovedTitlesStmt->fetchColumn();

$totalTeamsStmt = $pdo->prepare("SELECT COUNT(*) AS total_teams FROM teams");
$totalTeamsStmt->execute();
$totalTeams = $totalTeamsStmt->fetchColumn();

$upcomingDefensesStmt = $pdo->prepare("SELECT COUNT(*) AS upcoming_defenses FROM defense_schedules WHERE schedule_date > CURDATE()");
$upcomingDefensesStmt->execute();
$upcomingDefenses = $upcomingDefensesStmt->fetchColumn();

$defensesTodayStmt = $pdo->prepare("SELECT COUNT(*) AS defenses_today FROM defense_schedules WHERE schedule_date = CURDATE()");
$defensesTodayStmt->execute();
$defensesToday = $defensesTodayStmt->fetchColumn();

$pastDefensesStmt = $pdo->prepare("SELECT COUNT(*) AS past_defenses FROM defense_schedules WHERE schedule_date < CURDATE()");
$pastDefensesStmt->execute();
$pastDefenses = $pastDefensesStmt->fetchColumn();

// Fetch user types
$adminCountStmt = $pdo->prepare("SELECT COUNT(*) AS total_admins FROM users WHERE usertype = 0");
$adminCountStmt->execute();
$totalAdmins = $adminCountStmt->fetchColumn();

$studentCountStmt = $pdo->prepare("SELECT COUNT(*) AS total_students FROM users WHERE usertype = 1");
$studentCountStmt->execute();
$totalStudents = $studentCountStmt->fetchColumn();

$staffCountStmt = $pdo->prepare("SELECT COUNT(*) AS total_staff FROM users WHERE usertype = 2");
$staffCountStmt->execute();
$totalStaff = $staffCountStmt->fetchColumn();
?>

<!-- Overview Tab -->
<div class="tab-pane fade show active my-3" id="overview" role="tabpanel" aria-labelledby="overview-tab">
    <div class="row">
        <!-- Total Users Section -->
        <div class="col-md-12 mb-3">
            <div class="card text-center overview-user-container">
                <div class="card-body"> 
                    <h5 class="card-title">Total Users: <span style="color: var(--main-primary); font-weight: 500;"><?php echo $totalUsers; ?></span></h5>
                    <p class="card-text"></p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-3 mb-3">
                        <div class="card text-center"> 
                            <div class="card-body user-card">
                                <img src="../assets/icons/admin.svg" alt="Admin Icon" class="mb-3 overview-thesis-cards-img" >
                                <h5 class="card-title overview-users">Admins</h5>
                                <p class="card-text overview-users-count" ><?php echo $totalAdmins; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body user-card">
                                <img src="../assets/icons/student.svg" alt="Student Icon" class="mb-3 overview-thesis-cards-img" >
                                <h5 class="card-title overview-users">Students</h5>
                                <p class="card-text overview-users-count"><?php echo $totalStudents; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card text-center">
                            <div class="card-body user-card">
                                <img src="../assets/icons/staff.svg" alt="Staff Icon" class="mb-3 overview-thesis-cards-img" >
                                <h5 class="card-title overview-users">Staff</h5>
                                <p class="card-text overview-users-count"><?php echo $totalStaff; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thesis-related Data Section -->
        <div class="col-md-4 mb-3">
            <div class="card thesis-card p-2 d-flex flex-row align-items-center">
                <!-- Fixed-size left box for image -->
                <div class="image-box d-flex align-items-center justify-content-center">
                    <img src="../assets/icons/approved.svg" alt="Icon" class="img-fluid">
                </div>
                <!-- Flexible right box for text -->
                <div class="text-box flex-grow-1 text-center">
                    <h6 class="mb-0 overview-thesis">Approved Titles</h6>
                    <span>6</span>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card thesis-card p-2 d-flex flex-row align-items-center">
                <div class="image-box d-flex align-items-center justify-content-center">
                    <img src="../assets/icons/rejected.svg" alt="Icon" class="img-fluid">
                </div>
                <div class="text-box flex-grow-1 text-center">
                    <h6 class="mb-0 overview-thesis">Pending Titles</h6>
                    <span>1</span>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card thesis-card p-2 d-flex flex-row align-items-center">
                <div class="image-box d-flex align-items-center justify-content-center">
                    <img src="../assets/icons/group.svg" alt="Icon" class="img-fluid">
                </div>
                <div class="text-box flex-grow-1 text-center">
                    <h6 class="mb-0 overview-thesis">Total Teams</h6>
                    <span>7</span>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card thesis-card p-2 d-flex flex-row align-items-center">
                <div class="image-box d-flex align-items-center justify-content-center">
                    <img src="../assets/icons/upcoming.svg" alt="Icon" class="img-fluid">
                </div>
                <div class="text-box flex-grow-1 text-center">
                    <h6 class="mb-0 overview-thesis">Upcoming Defenses</h6>
                    <span>7</span>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card thesis-card p-2 d-flex flex-row align-items-center">
                <div class="image-box d-flex align-items-center justify-content-center">
                    <img src="../assets/icons/today.svg" alt="Icon" class="img-fluid">
                </div>
                <div class="text-box flex-grow-1 text-center">
                    <h6 class="mb-0 overview-thesis">Defenses Today</h6>
                    <span>0</span>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card thesis-card p-2 d-flex flex-row align-items-center">
                <div class="image-box d-flex align-items-center justify-content-center">
                    <img src="../assets/icons/past.svg" alt="Icon" class="img-fluid">
                </div>
                <div class="text-box flex-grow-1 text-center">
                    <h6 class="mb-0 overview-thesis">Past Defenses</h6>
                    <span>0</span>
                </div>
            </div>
        </div>
    </div>
</div>