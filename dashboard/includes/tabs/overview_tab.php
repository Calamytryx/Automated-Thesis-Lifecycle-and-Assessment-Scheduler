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
        <!-- Requirements Completion Graph -->
        <div class="content-container mb-4">
            <div class="row">
                <!-- Two-container layout: Title on left, count+search on right -->
                <div class="col-12 header-container">
                    <div class="row">
                        <!-- Left container with title -->
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <h2 class="requirements-title fw-medium">
                                requirements<br>progress
                            </h2>
                        </div>
                        
                        <!-- Right container with team count and search -->
                        <div class="col-lg-6">
                            <div class="box-container">
                                <div class="d-flex justify-content-end">
                                    <span class="badge bg-light text-dark rounded-pill px-3 py-2">Total Teams: <?php echo $totalTeams; ?></span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text border-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-0" id="searchTeams" placeholder="Search Teams">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 mt-4">
                    <div class="row req-prog-row-con">
                        <!-- Completed - with bold title -->
                        <div class="col-md-4 mb-4">
                            <h3 class="title-bold">Completed</h3>
                            <div class="d-flex align-items-center mt-3 completion-status-item" data-status="completed" style="cursor: pointer;">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; min-width: 50px;">
                                    <i class="bi bi-check-lg text-white fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h2 class="mb-0 display-4"><?php echo $fullCompletionCount; ?></h2>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Partial - regular title -->
                        <div class="col-md-4 mb-4">
                            <h3>Partial</h3>
                            <div class="d-flex align-items-center mt-3 completion-status-item" data-status="partial" style="cursor: pointer;">
                                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; min-width: 50px;">
                                    <i class="bi bi-circle-half text-white fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h2 class="mb-0 display-4"><?php echo $partialCompletionCount; ?></h2>
                                </div>
                            </div>
                        </div>
                        
                        <!-- None - regular title -->
                        <div class="col-md-4 mb-4">
                            <h3>None</h3>
                            <div class="d-flex align-items-center mt-3 completion-status-item" data-status="none" style="cursor: pointer;">
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; min-width: 50px;">
                                    <i class="bi bi-x-lg text-white fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h2 class="mb-0 display-4"><?php echo $noCompletionCount; ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 d-flex justify-content-end mt-3">
                    <a href="#" class="text-decoration-none d-flex align-items-center" id="viewRequirementsLink">
                        view per requirement 
                        <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Defense Schedules Section -->
        <div class="content-container">
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
    // Handle team search functionality
    const searchInput = document.getElementById('searchTeams');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // You can implement the search functionality here
            // For example, filter teams based on the search input
            const searchText = this.value.toLowerCase();
            // Add search implementation based on your requirements
        });
    }
    
    // Make "view per requirement" link work
    const viewRequirementsLink = document.getElementById('viewRequirementsLink');
    if (viewRequirementsLink) {
        viewRequirementsLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Navigate to the requirements page or show the requirements modal
            window.location.href = 'requirements.php';
        });
    }
    
    // Store requirement details for modal
    const requirementDetails = <?php echo $teamRequirementJson; ?>;
    
    // Overall team completion details
    const teamCompletionDetails = <?php echo json_encode($teamCompletion); ?>;
    
    // Add click event handlers for completion status items
    const completionStatusItems = document.querySelectorAll('.completion-status-item');
    completionStatusItems.forEach(item => {
        item.addEventListener('click', function() {
            const status = this.getAttribute('data-status');
            showTeamsWithStatus(status);
        });
    });
    
    // Function to show teams based on completion status
    function showTeamsWithStatus(status) {
        const teamsModal = new bootstrap.Modal(document.getElementById('teamsModal'));
        const modalTitle = document.getElementById('teamsModalLabel');
        const modalContent = document.getElementById('modalContent');
        
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
        
        // Prepare modal content based on status
        if (status === 'completed') {
            modalTitle.textContent = 'Teams with All Requirements Completed';
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-success"><i class="bi bi-check-circle-fill me-2"></i>Completed Teams (${fullCompletionTeams.length})</h5>
                        ${generateTeamList(fullCompletionTeams, true)}
                    </div>
                </div>
            `;
        } else if (status === 'partial') {
            modalTitle.textContent = 'Teams with Partial Completion';
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-warning"><i class="bi bi-clock-fill me-2"></i>Partial Completion Teams (${partialCompletionTeams.length})</h5>
                        ${generateTeamList(partialCompletionTeams, false)}
                    </div>
                </div>
            `;
        } else if (status === 'none') {
            modalTitle.textContent = 'Teams with No Requirements Completed';
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <h5 class="text-danger"><i class="bi bi-x-circle-fill me-2"></i>Teams with No Completion (${noCompletionTeams.length})</h5>
                        ${generateTeamList(noCompletionTeams, false)}
                    </div>
                </div>
            `;
        }
        
        teamsModal.show();
    }
    
    // Handle opening modal with team details
    const teamsModal = document.getElementById('teamsModal');
    teamsModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        // Only process if this is triggered by a button with data-requirement-id
        if (button && button.hasAttribute('data-requirement-id')) {
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