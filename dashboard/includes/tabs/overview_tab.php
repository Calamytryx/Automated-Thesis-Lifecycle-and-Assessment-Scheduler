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

// Get requirement completion data
// Fetch all requirements
$requirementsStmt = $pdo->prepare("SELECT id, name FROM requirements");
$requirementsStmt->execute();
$requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all teams
$teamsStmt = $pdo->prepare("SELECT id, name FROM teams");
$teamsStmt->execute();
$teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate completion percentages for each requirement
$requirementStats = [];
$teamCompletion = [];
$teamRequirementDetails = [];

// Initialize team completion tracking
foreach ($teams as $team) {
    $teamCompletion[$team['id']] = [
        'name' => $team['name'],
        'completed_requirements' => 0,
        'total_requirements' => count($requirements),
        'requirements' => []
    ];
}

foreach ($requirements as $requirement) {
    // Get status for each team for this requirement
    $teamStatusStmt = $pdo->prepare("
        SELECT tr.team_id, t.name as team_name, tr.status
        FROM teams t
        LEFT JOIN team_requirements tr ON t.id = tr.team_id AND tr.requirement_id = ?
    ");
    $teamStatusStmt->execute([$requirement['id']]);
    $teamStatuses = $teamStatusStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $completedTeams = [];
    $pendingTeams = [];
    $missingTeams = [];
    
    foreach ($teamStatuses as $status) {
        if (isset($status['status']) && in_array($status['status'], ['approved', 'submitted'])) {
            $completedTeams[] = ['id' => $status['team_id'], 'name' => $status['team_name']];
            
            // Track that this team completed this requirement
            if (isset($teamCompletion[$status['team_id']])) {
                $teamCompletion[$status['team_id']]['completed_requirements']++;
                $teamCompletion[$status['team_id']]['requirements'][$requirement['id']] = 'completed';
            }
        } elseif (isset($status['status']) && $status['status'] == 'pending') {
            $pendingTeams[] = ['id' => $status['team_id'], 'name' => $status['team_name']];
            
            if (isset($teamCompletion[$status['team_id']])) {
                $teamCompletion[$status['team_id']]['requirements'][$requirement['id']] = 'pending';
            }
        } else {
            $missingTeams[] = ['id' => $status['team_id'], 'name' => $status['team_name']];
            
            if (isset($teamCompletion[$status['team_id']])) {
                $teamCompletion[$status['team_id']]['requirements'][$requirement['id']] = 'missing';
            }
        }
    }
    
    $requirementStats[$requirement['id']] = [
        'name' => $requirement['name'],
        'completed' => count($completedTeams),
        'pending' => count($pendingTeams),
        'missing' => count($missingTeams),
        'percentage' => ($totalTeams > 0) ? round((count($completedTeams) / $totalTeams) * 100) : 0,
        'completed_teams' => $completedTeams,
        'pending_teams' => $pendingTeams,
        'missing_teams' => $missingTeams
    ];
    
    // Store team requirement details for the popup
    $teamRequirementDetails[$requirement['id']] = [
        'completed' => $completedTeams,
        'pending' => $pendingTeams,
        'missing' => $missingTeams
    ];
}

// Calculate team completion statistics
$fullCompletionCount = 0;
$partialCompletionCount = 0;
$noCompletionCount = 0;

foreach ($teamCompletion as $teamId => $data) {
    $completionPercentage = ($data['total_requirements'] > 0) 
        ? ($data['completed_requirements'] / $data['total_requirements']) * 100 
        : 0;
    
    $teamCompletion[$teamId]['completion_percentage'] = $completionPercentage;
    
    if ($completionPercentage == 100) {
        $fullCompletionCount++;
    } elseif ($completionPercentage > 0) {
        $partialCompletionCount++;
    } else {
        $noCompletionCount++;
    }
}

// Encode team requirement details for JavaScript
$teamRequirementJson = json_encode($teamRequirementDetails);
?>

<div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
    <div class="container-fluid my-3">
        <!-- User Statistics Section -->
        <div class="row">
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
            <hr class="border-dark my-4">
        </div>

        <!-- Requirements Completion Graph -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">Teams Requirements Progress</h4>
            </div>
            
            <!-- Overall Team Completion Status -->
            <div class="col-lg-12 mb-4">
                <div class="card border-0 shadow-sm team-card" data-bs-toggle="modal" data-bs-target="#teamsModal" data-requirement-id="overall">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Team Completion Status</h5>
                        <div class="text-muted">Total Teams: <strong><?php echo $totalTeams; ?></strong></div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="chart-container position-relative" style="height:200px; width:200px; margin:auto;">
                                    <canvas id="teamCompletionChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div><i class="bi bi-check-circle-fill text-success me-2"></i> All Requirements Completed:</div>
                                    <span class="badge bg-success rounded-pill"><?php echo $fullCompletionCount; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div><i class="bi bi-clock-fill text-warning me-2"></i> Partial Completion:</div>
                                    <span class="badge bg-warning rounded-pill"><?php echo $partialCompletionCount; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div><i class="bi bi-x-circle-fill text-danger me-2"></i> No Requirements Completed:</div>
                                    <span class="badge bg-danger rounded-pill"><?php echo $noCompletionCount; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted text-center mt-3">
                            Click to see detailed team completion status
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Collapsible Requirements Section -->
            <div class="col-12">
                <button class="btn btn-link text-decoration-none d-flex align-items-center p-0" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#requirementsCollapse" 
                        aria-expanded="false" 
                        aria-controls="requirementsCollapse"
                        id="toggleRequirementsBtn">
                    <span class="me-2">View All Requirements Progress</span>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
            </div>

            <!-- Individual Requirements Progress (Collapsible) -->
            <div class="collapse" id="requirementsCollapse">
                <div class="row">
                    <?php foreach ($requirementStats as $reqId => $stat): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100 team-card" data-bs-toggle="modal" data-bs-target="#teamsModal" data-requirement-id="<?php echo $reqId; ?>">
                            <div class="card-header bg-white border-0">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($stat['name']); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 18px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                style="width: <?php echo $stat['percentage']; ?>%;" 
                                                aria-valuenow="<?php echo $stat['percentage']; ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                                <?php echo $stat['percentage']; ?>%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <span class="badge bg-success rounded-pill">
                                            <?php echo $stat['completed']; ?>/<?php echo $totalTeams; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between small">
                                    <div>
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        <span>Completed: <strong><?php echo $stat['completed']; ?></strong></span>
                                    </div>
                                    <div>
                                        <i class="bi bi-clock-fill text-warning me-1"></i>
                                        <span>Pending: <strong><?php echo $stat['pending']; ?></strong></span>
                                    </div>
                                    <div>
                                        <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                        <span>Missing: <strong><?php echo $stat['missing']; ?></strong></span>
                                    </div>
                                </div>
                                <div class="small text-muted text-center mt-2">
                                    Click to see teams
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <hr class="border-dark my-4">
        </div>

        <!-- Defense Schedules Section -->
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4">Defense Schedules</h4>
            </div>
            
            <!-- Upcoming Defenses -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Upcoming Defenses</h5>
                        <span class="badge bg-primary rounded-pill"><?php echo $upcomingDefenses; ?></span>
                    </div>
                    <div class="card-body">
                        <?php
                        // Fetch upcoming defenses with team information
                        $upcomingDefensesListStmt = $pdo->prepare("
                            SELECT ds.id, ds.schedule_date, ds.start_time, ds.end_time, 
                                   t.id as team_id, t.name as team_name
                            FROM defense_schedules ds
                            JOIN teams t ON ds.team_id = t.id
                            WHERE ds.schedule_date >= CURDATE()
                            ORDER BY ds.schedule_date ASC, ds.start_time ASC
                            LIMIT 10
                        ");
                        $upcomingDefensesListStmt->execute();
                        $upcomingDefensesList = $upcomingDefensesListStmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <?php if (count($upcomingDefensesList) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Group Name</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcomingDefensesList as $defense): ?>
                                    <tr class="defense-row" style="cursor: pointer;" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#defenseDetailsModal" 
                                        data-team-id="<?php echo $defense['team_id']; ?>">
                                        <td><?php echo htmlspecialchars($defense['team_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($defense['schedule_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($defense['start_time'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-center text-muted">No upcoming defenses scheduled.</p>
                        <?php endif; ?>
                        
                        <?php if (count($upcomingDefensesList) < $upcomingDefenses): ?>
                            <div class="text-center mt-3">
                                <a href="defense_schedules.php" class="btn btn-sm btn-outline-primary">View All Upcoming Defenses</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Past Defenses -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Past Defenses</h5>
                        <span class="badge bg-secondary rounded-pill"><?php echo $pastDefenses; ?></span>
                    </div>
                    <div class="card-body">
                        <?php
                        // Fetch past defenses with team information
                        $pastDefensesListStmt = $pdo->prepare("
                            SELECT ds.id, ds.schedule_date, ds.start_time, ds.end_time, 
                                   t.id as team_id, t.name as team_name
                            FROM defense_schedules ds
                            JOIN teams t ON ds.team_id = t.id
                            WHERE ds.schedule_date < CURDATE()
                            ORDER BY ds.schedule_date DESC, ds.start_time ASC
                            LIMIT 10
                        ");
                        $pastDefensesListStmt->execute();
                        $pastDefensesList = $pastDefensesListStmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <?php if (count($pastDefensesList) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Group Name</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pastDefensesList as $defense): ?>
                                    <tr class="defense-row" style="cursor: pointer;" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#defenseDetailsModal" 
                                        data-team-id="<?php echo $defense['team_id']; ?>">
                                        <td><?php echo htmlspecialchars($defense['team_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($defense['schedule_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($defense['start_time'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-center text-muted">No past defenses found.</p>
                        <?php endif; ?>
                        
                        <?php if (count($pastDefensesList) < $pastDefenses): ?>
                            <div class="text-center mt-3">
                                <a href="defense_schedules.php" class="btn btn-sm btn-outline-secondary">View All Past Defenses</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Teams Modal -->
<div class="modal fade" id="teamsModal" tabindex="-1" aria-labelledby="teamsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teamsModalLabel">Team Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Defense Details Modal -->
<div class="modal fade" id="defenseDetailsModal" tabindex="-1" aria-labelledby="defenseDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="defenseDetailsModalLabel">Team Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="defenseModalContent">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set up chart data for teams' completion status
    const ctx = document.getElementById('teamCompletionChart').getContext('2d');
    const teamCompletionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Completed All', 'Partial Completion', 'No Completion'],
            datasets: [{
                data: [<?php echo $fullCompletionCount; ?>, <?php echo $partialCompletionCount; ?>, <?php echo $noCompletionCount; ?>],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            cutout: '70%'
        }
    });
    
    // Store requirement details for modal
    const requirementDetails = <?php echo $teamRequirementJson; ?>;
    
    // Overall team completion details
    const teamCompletionDetails = <?php echo json_encode($teamCompletion); ?>;
    
    // Handle opening modal with team details
    const teamsModal = document.getElementById('teamsModal');
    teamsModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const requirementId = button.getAttribute('data-requirement-id');
        const modalTitle = teamsModal.querySelector('.modal-title');
        const modalContent = document.getElementById('modalContent');
        
        if (requirementId === 'overall') {
            // Show overall team completion status
            modalTitle.textContent = 'Overall Team Completion Status';
            
            let fullCompletionTeams = [];
            let partialCompletionTeams = [];
            let noCompletionTeams = [];
            
            // Group teams by completion status
            Object.entries(teamCompletionDetails).forEach(([teamId, data]) => {
                if (data.completion_percentage === 100) {
                    fullCompletionTeams.push(data);
                } else if (data.completion_percentage > 0) {
                    partialCompletionTeams.push(data);
                } else {
                    noCompletionTeams.push(data);
                }
            });
            
            // Generate modal content
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-12 mb-4">
                        <h5 class="text-success"><i class="bi bi-check-circle-fill me-2"></i>Teams with All Requirements Completed (${fullCompletionTeams.length})</h5>
                        ${generateTeamList(fullCompletionTeams, true)}
                    </div>
                    
                    <div class="col-12 mb-4">
                        <h5 class="text-warning"><i class="bi bi-clock-fill me-2"></i>Teams with Partial Completion (${partialCompletionTeams.length})</h5>
                        ${generateTeamList(partialCompletionTeams, false)}
                    </div>
                    
                    <div class="col-12">
                        <h5 class="text-danger"><i class="bi bi-x-circle-fill me-2"></i>Teams with No Requirements Completed (${noCompletionTeams.length})</h5>
                        ${generateTeamList(noCompletionTeams, false)}
                    </div>
                </div>
            `;
        } else {
            // Show specific requirement details
            const requirement = document.querySelector(`.card[data-requirement-id="${requirementId}"] .card-title`).textContent;
            modalTitle.textContent = `Teams Status for: ${requirement}`;
            
            const completedTeams = requirementDetails[requirementId].completed;
            const pendingTeams = requirementDetails[requirementId].pending;
            const missingTeams = requirementDetails[requirementId].missing;
            
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-12 mb-4">
                        <h5 class="text-success"><i class="bi bi-check-circle-fill me-2"></i>Completed Teams (${completedTeams.length})</h5>
                        <ul class="list-group">
                            ${completedTeams.length ? completedTeams.map(team => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${team.name}
                                    <span class="badge bg-success rounded-pill">Completed</span>
                                </li>
                            `).join('') : '<li class="list-group-item">No teams have completed this requirement</li>'}
                        </ul>
                    </div>
                    
                    <div class="col-12 mb-4">
                        <h5 class="text-warning"><i class="bi bi-clock-fill me-2"></i>Pending Teams (${pendingTeams.length})</h5>
                        <ul class="list-group">
                            ${pendingTeams.length ? pendingTeams.map(team => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${team.name}
                                    <span class="badge bg-warning rounded-pill">Pending</span>
                                </li>
                            `).join('') : '<li class="list-group-item">No teams have pending submissions</li>'}
                        </ul>
                    </div>
                    
                    <div class="col-12">
                        <h5 class="text-danger"><i class="bi bi-x-circle-fill me-2"></i>Missing Teams (${missingTeams.length})</h5>
                        <ul class="list-group">
                            ${missingTeams.length ? missingTeams.map(team => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${team.name}
                                    <span class="badge bg-danger rounded-pill">Missing</span>
                                </li>
                            `).join('') : '<li class="list-group-item">No teams are missing this requirement</li>'}
                        </ul>
                    </div>
                </div>
            `;
        }
    });
    
    // Helper function to generate team lists
    function generateTeamList(teams, isComplete) {
        if (teams.length === 0) {
            return '<p class="text-muted">No teams in this category</p>';
        }
        
        let html = '<ul class="list-group">';
        
        teams.forEach(team => {
            if (isComplete) {
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${team.name}
                        <span class="badge bg-success rounded-pill">100%</span>
                    </li>
                `;
            } else {
                const percentage = Math.round(team.completion_percentage);
                let badgeClass = percentage > 50 ? 'bg-warning' : 'bg-danger';
                
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        ${team.name}
                        <span class="badge ${badgeClass} rounded-pill">${percentage}%</span>
                    </li>
                `;
            }
        });
        
        html += '</ul>';
        return html;
    }
    
    // Make cards look clickable
    document.querySelectorAll('.team-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('mouseover', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
            this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
        });
        card.addEventListener('mouseout', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 0.125rem 0.25rem rgba(0,0,0,0.075)';
        });
    });
    
    // Toggle icon rotation for requirements collapse
    const toggleBtn = document.getElementById('toggleRequirementsBtn');
    const toggleIcon = toggleBtn.querySelector('.toggle-icon');
    
    document.getElementById('requirementsCollapse').addEventListener('show.bs.collapse', function () {
        toggleIcon.classList.remove('bi-chevron-down');
        toggleIcon.classList.add('bi-chevron-up');
        toggleBtn.querySelector('span').textContent = 'Hide Requirements Progress';
    });
    
    document.getElementById('requirementsCollapse').addEventListener('hide.bs.collapse', function () {
        toggleIcon.classList.remove('bi-chevron-up');
        toggleIcon.classList.add('bi-chevron-down');
        toggleBtn.querySelector('span').textContent = 'View All Requirements Progress';
    });

    // Handle defense details modal
    const defenseDetailsModal = document.getElementById('defenseDetailsModal');
    defenseDetailsModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const teamId = button.getAttribute('data-team-id');
        const modalTitle = defenseDetailsModal.querySelector('.modal-title');
        const modalContent = document.getElementById('defenseModalContent');
        
        // Show loading spinner
        modalContent.innerHTML = `
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        
        // Fix the path to get_team_details.php
        fetch(`includes/get_team_details.php?team_id=${teamId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalTitle.textContent = `Team: ${data.team.name}`;
                    
                    // Format defense schedule if available
                    let defenseSchedule = 'Not scheduled';
                    if (data.defense) {
                        const date = new Date(data.defense.schedule_date);
                        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                        defenseSchedule = `${date.toLocaleDateString('en-US', options)} at ${data.defense.start_time} - ${data.defense.end_time}`;
                        if (data.defense.location) {
                            defenseSchedule += ` (${data.defense.location})`;
                        }
                    }
                    
                    // Find adviser from members list if not in adviser field
                    let adviser = data.adviser;
                    let adviserMember = null;
                    
                    if (!adviser) {
                        // Look for a member with the "adviser" role
                        adviserMember = data.members.find(member => member.role === 'adviser');
                        if (adviserMember) {
                            adviser = {
                                id: adviserMember.user_id,
                                name: adviserMember.name
                            };
                        }
                    }
                    
                    // Filter out the advisor from the members list
                    const filteredMembers = data.members.filter(member => {
                        // Remove member if they're the adviser by ID or role
                        if (adviser && member.user_id === adviser.id) return false;
                        if (member.role === 'adviser') return false;
                        return true;
                    });
                    
                    // Create HTML for team details
                    modalContent.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Research Title</h5>
                                <p>${data.title ? data.title.title : 'No approved title'}</p>
                                
                                <h5>Defense Schedule</h5>
                                <p>${defenseSchedule}</p>
                                
                                <h5>Adviser</h5>
                                <p>${adviser ? adviser.name : 'No adviser assigned'}</p>
                            </div>
                            <div class="col-md-6">
                                <h5>Team Members</h5>
                                <ul class="list-group">
                                    ${filteredMembers.map(member => `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            ${member.name}
                                            <span class="badge bg-info rounded-pill">${member.role || 'Member'}</span>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
                } else {
                    modalContent.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load team details'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching team details:', error);
                modalContent.innerHTML = `<div class="alert alert-danger">An error occurred while loading team details.</div>`;
            });
    });
});
</script>