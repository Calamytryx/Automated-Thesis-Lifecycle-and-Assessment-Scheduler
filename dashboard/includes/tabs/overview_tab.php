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

<div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
    <div class="container-fluid my-3">
        <!-- User Statistics Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-4 ">User Statistics</h4>
            </div>
            <div class="col-sm-6 col-xl-3 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-normal mb-2">Total Users</h6>
                                <h2 class="mb-0"><?php echo $totalUsers; ?></h2>
                            </div>
                            <div class="fs-1 opacity-75">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3 mb-3"> 
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-normal mb-2">Admins</h6>
                                <h2 class="mb-0"><?php echo $totalAdmins; ?></h2>
                            </div>
                            <div class="fs-1 opacity-75">
                                <i class="bi bi-person-badge"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-normal mb-2">Students</h6>
                                <h2 class="mb-0"><?php echo $totalStudents; ?></h2>
                            </div>
                            <div class="fs-1 opacity-75">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-normal mb-2">Staff</h6>
                                <h2 class="mb-0"><?php echo $totalStaff; ?></h2>
                            </div>
                            <div class="fs-1 opacity-75">
                                <i class="bi bi-person-workspace"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thesis Statistics -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">Thesis Management</h4>
            </div>
            
            <!-- Thesis Status -->
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Thesis Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-2 rounded me-2">
                                    <i class="bi bi-check-circle text-success"></i>
                                </div>
                                <span>Approved Titles</span>
                            </div>
                            <span class="badge bg-success rounded-pill">6</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-2 rounded me-2">
                                    <i class="bi bi-clock text-warning"></i>
                                </div>
                                <span>Pending Titles</span>
                            </div>
                            <span class="badge bg-warning rounded-pill">1</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-2">
                                    <i class="bi bi-people text-primary"></i>
                                </div>
                                <span>Total Teams</span>
                            </div>
                            <span class="badge bg-primary rounded-pill">7</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Defense Schedule -->
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Defense Schedule</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-10 p-2 rounded me-2">
                                    <i class="bi bi-calendar-event text-info"></i>
                                </div>
                                <span>Upcoming Defenses</span>
                            </div>
                            <span class="badge bg-info rounded-pill">7</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-2 rounded me-2">
                                    <i class="bi bi-calendar-check text-success"></i>
                                </div>
                                <span>Defenses Today</span>
                            </div>
                            <span class="badge bg-success rounded-pill">0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-10 p-2 rounded me-2">
                                    <i class="bi bi-calendar-x text-secondary"></i>
                                </div>
                                <span>Past Defenses</span>
                            </div>
                            <span class="badge bg-secondary rounded-pill">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions na di gumagana, suggestion lang kase wala pa ako idea sa pede ipalit-->
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Users
                            </button>
                            <button class="btn btn-info text-white">
                                <i class="bi bi-calendar-plus me-2"></i>Thesis Topics
                            </button>
                            <button class="btn btn-success">
                                <i class="bi bi-file-earmark-text me-2"></i>Research Titles
                            </button>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</div>