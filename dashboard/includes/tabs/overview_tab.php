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
?>

<!-- Overview Tab -->
<div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
    <h1>Dashboard Overview</h1>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text"><?php echo $totalUsers; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Approved Titles</h5>
                    <p class="card-text"><?php echo $approvedTitles; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Not Approved Titles</h5>
                    <p class="card-text"><?php echo $notApprovedTitles; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Teams</h5>
                    <p class="card-text"><?php echo $totalTeams; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Defenses</h5>
                    <p class="card-text"><?php echo $upcomingDefenses; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Defenses Today</h5>
                    <p class="card-text"><?php echo $defensesToday; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Past Defenses</h5>
                    <p class="card-text"><?php echo $pastDefenses; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>