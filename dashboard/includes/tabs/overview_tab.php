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
        'name' => $requirement['name'],
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
    <div class="container-fluid p-0">
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
                                <div class="d-flex justify-content-end align-items-center mb-2">
                                    <span class="badge bg-light text-dark rounded-pill px-3 py-2">Total Teams: <?php echo $totalTeams; ?></span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text border-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-0" id="searchTeams" placeholder="Search Teams">
                                    <button class="btn btn-outline-primary border-0" type="button" id="searchTeamsBtn">
                                        
                                    </button>
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
        </div>        <!-- Defense Schedules Section -->
        <div class="content-container">
            <div class="row">
                <!-- Two-container layout: Title on left, date pill on right -->
                <div class="col-12 header-container">
                    <div class="row">
                        <!-- Left container with title -->
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <h2 class="requirements-title fw-medium">
                                defense<br>schedules
                            </h2>
                        </div>
                        
                        <!-- Right container with current date -->
                        <div class="col-lg-6">
                            <div class="box-container">
                                <div class="d-flex justify-content-end">
                                    <span class="badge bg-light text-dark rounded-pill px-3 py-2">Today: <?php echo date('F d, Y'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                  <!-- Defense schedules content -->
                <div class="col-12 mt-4">
                    <div class="row">                        <!-- Defense Schedule Table -->                        <div class="col-12 mb-4">
                            <div class="defense-table-container">
                                <!-- No padding or shadow here -->
                                    <?php
                                    // Fetch upcoming and today's defenses with team information and research titles
                                    $defensesListStmt = $pdo->prepare("
                                        SELECT 
                                            ds.id, 
                                            ds.schedule_date, 
                                            ds.start_time, 
                                            ds.end_time, 
                                            t.id as team_id, 
                                            t.name as team_name,
                                            rt.title as research_title,
                                            CASE 
                                                WHEN ds.schedule_date = CURDATE() AND TIME(NOW()) BETWEEN ds.start_time AND ds.end_time THEN 'ongoing'
                                                ELSE 'scheduled'
                                            END as status,
                                            CASE 
                                                WHEN ds.schedule_date = CURDATE() THEN 'today'
                                                WHEN ds.schedule_date > CURDATE() THEN 'upcoming'
                                            END as date_category
                                        FROM defense_schedules ds
                                        JOIN teams t ON ds.team_id = t.id
                                        LEFT JOIN research_titles rt ON t.id = rt.team_id
                                        WHERE ds.schedule_date >= CURDATE()
                                        ORDER BY ds.schedule_date ASC, ds.start_time ASC
                                        LIMIT 15
                                    ");
                                    $defensesListStmt->execute();
                                    $defensesList = $defensesListStmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                      <?php if (count($defensesList) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover defense-schedule-table">
                                            <thead>
                                                <tr>
                                                    <th>Date Category</th>
                                                    <th>Team</th>
                                                    <th>Research Title</th>
                                                    <th>Schedule</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($defensesList as $defense): ?>
                                                <tr class="defense-row" style="cursor: pointer;" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#defenseDetailsModal" 
                                                    data-team-id="<?php echo $defense['team_id']; ?>">
                                                    <td>
                                                        <span class="badge rounded-pill <?php echo $defense['date_category'] === 'today' ? 'bg-danger' : 'bg-primary'; ?>">
                                                            <?php echo ucfirst($defense['date_category']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($defense['team_name']); ?></td>
                                                    <td class="text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($defense['research_title'] ?? 'No title assigned'); ?></td>
                                                    <td>
                                                        <?php echo date('M d, Y', strtotime($defense['schedule_date'])); ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo date('h:i A', strtotime($defense['start_time'])); ?> - 
                                                            <?php echo date('h:i A', strtotime($defense['end_time'])); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge rounded-pill <?php echo $defense['status'] === 'ongoing' ? 'bg-success' : 'bg-secondary'; ?>">
                                                            <?php echo ucfirst($defense['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                        <p class="text-center text-muted">No upcoming defenses scheduled.</p>
                                    <?php endif; ?>
                                      <?php if (count($defensesList) < ($upcomingDefenses + $defensesToday)): ?>
                                        <div class="defense-view-all-btn">
                                            <a href="#defense-schedules" class="btn btn-sm btn-outline-primary" id="viewAllDefensesBtn">View All Defense Schedules</a>
                                        </div>
                                    <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        </div>
    </div>
</div>

<!-- Teams Modal -->
<div class="modal fade" id="teamsModal" tabindex="-1" aria-labelledby="teamsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teamsModalLabel">Team Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
    // Global variable to store current search results
    let currentSearchResults = null;
    
    // Handle team search functionality
    const searchInput = document.getElementById('searchTeams');
    const searchBtn = document.getElementById('searchTeamsBtn');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchText = this.value.trim();
            
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Debounce search to avoid too many requests
            searchTimeout = setTimeout(() => {
                if (searchText.length >= 2) {
                    performTeamSearch(searchText);
                }
            }, 300);
        });
        
        // Handle Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchText = this.value.trim();
                if (searchText.length >= 2) {
                    performTeamSearch(searchText);
                } else if (searchText.length > 0 && searchText.length < 2) {
                    showSearchError('Please enter at least 2 characters to search');
                }
            }
        });
    }
    
    // Handle search button click
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const searchText = searchInput.value.trim();
            if (searchText.length >= 2) {
                performTeamSearch(searchText);
            } else if (searchText.length === 0) {
                showSearchError('Please enter a search term');
            } else {
                showSearchError('Please enter at least 2 characters to search');
            }
        });
    }
    
    // Function to perform AJAX team search
    function performTeamSearch(searchQuery) {
        // Show loading state
        const searchBtn = document.getElementById('searchTeamsBtn');
        if (!searchBtn) {
            console.error('Search button not found');
            return;
        }
        
        const originalBtnContent = searchBtn.innerHTML;
        searchBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        searchBtn.disabled = true;
        
        // Use relative URL to avoid HTTPS/HTTP issues
        const searchUrl = `./includes/overview_search.php?search=${encodeURIComponent(searchQuery)}`;
        console.log('Fetching:', searchUrl);
        
        fetch(searchUrl)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text(); // Get text first to debug
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        showSearchResults(data);
                    } else {
                        showSearchError(data.message || 'Search failed');
                    }
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError);
                    console.error('Response text:', text);
                    showSearchError('Invalid response from server. Check console for details.');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                showSearchError(`An error occurred while searching: ${error.message}`);
            })
            .finally(() => {
                // Restore button state
                if (searchBtn) {
                    searchBtn.innerHTML = originalBtnContent;
                    searchBtn.disabled = false;
                }
            });
    }
    
    // Function to show search results in modal
    function showSearchResults(data) {
        // Store the search results globally for navigation
        currentSearchResults = data;
        
        const teamsModal = new bootstrap.Modal(document.getElementById('teamsModal'));
        const modalTitle = document.getElementById('teamsModalLabel');
        const modalContent = document.getElementById('modalContent');
        
        modalTitle.textContent = `Search Results: "${data.search_query}" (${data.total_found} team${data.total_found !== 1 ? 's' : ''} found)`;
        
        if (data.teams.length === 0) {
            modalContent.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No teams found matching your search criteria.
                </div>
            `;
        } else {
            let content = '<div class="row">';
            
            data.teams.forEach(team => {
                const statusBadge = getStatusBadge(team.status_category, team.completion_percentage);
                
                content += `
                    <div class="col-12 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0 d-flex align-items-center">
                                        ${team.name}
                                        <span class="ms-2">${statusBadge}</span>
                                    </h5>
                                    <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="showTeamRequirements(${team.id}, '${team.name.replace(/'/g, "\\'")}', ${JSON.stringify(team.requirement_details).replace(/"/g, '&quot;')})">
                                            View Requirements
                                        </button>
                                        <button class="btn btn-sm" style="background-color: #1304ee; color: white; border-color: #1304ee;" onclick="showTeamDetails(${team.id}, '${team.name.replace(/'/g, "\\'")}')">
                                            View Info
                                        </button>
                                    </div>
                                </div>
                                
                                <p class="card-text text-muted mb-2">
                                    <strong>Research Title:</strong> ${team.research_title}
                                </p>
                                <p class="card-text text-muted mb-0">
                                    <strong>Adviser:</strong> ${team.adviser}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            content += '</div>';
            modalContent.innerHTML = content;
        }
        
        teamsModal.show();
    }
    
    // Function to show search errors
    function showSearchError(message) {
        const teamsModal = new bootstrap.Modal(document.getElementById('teamsModal'));
        const modalTitle = document.getElementById('teamsModalLabel');
        const modalContent = document.getElementById('modalContent');
        
        modalTitle.textContent = 'Search Error';
        modalContent.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
        
        teamsModal.show();
    }
    
    // Helper function to get status badge
    function getStatusBadge(category, percentage) {
        switch (category) {
            case 'completed':
                return '<span class="badge bg-success">Completed</span>';
            case 'partial':
                return `<span class="badge bg-warning">${percentage}% Complete</span>`;
            case 'none':
                return '<span class="badge bg-secondary">Not Started</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }
    
    // Function to show detailed team information
    window.showTeamDetails = function(teamId, teamName) {
        // Don't create a new modal, use the same teams modal
        const modalTitle = document.getElementById('teamsModalLabel');
        const modalContent = document.getElementById('modalContent');
        
        modalTitle.textContent = `Team: ${teamName}`;
        modalContent.innerHTML = `
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
            
        // Load team details
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
                    
                    // Create HTML for team details with back button
                    let backButton = '';
                    if (currentSearchResults) {
                        backButton = `
                            <div class="mt-3 text-center">
                                <button class="btn btn-secondary" onclick="goBackToSearchResults()">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Search Results
                                </button>
                            </div>
                        `;
                    }
                    
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
                        ${backButton}
                    `;
                } else {
                    modalContent.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load team details'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching team details:', error);
                modalContent.innerHTML = `<div class="alert alert-danger">An error occurred while loading team details.</div>`;
            });
        
        // Modal is already showing, just update content
    };
    
    // Function to show team requirements breakdown
    window.showTeamRequirements = function(teamId, teamName, requirementDetails) {
        // Use the existing modal, don't create a new one
        const modalTitle = document.getElementById('teamsModalLabel');
        const modalContent = document.getElementById('modalContent');
        
        modalTitle.textContent = `Requirements - ${teamName}`;
            
            // Parse requirement details if it's a string
            let requirements = requirementDetails;
            if (typeof requirementDetails === 'string') {
                try {
                    requirements = JSON.parse(requirementDetails);
                } catch (e) {
                    console.error('Error parsing requirement details:', e);
                    requirements = [];
                }
            }
            
            // Categorize requirements
            const completed = requirements.filter(req => req.status === 'approved' || req.status === 'submitted');
            const pending = requirements.filter(req => req.status === 'pending');
            const remaining = requirements.filter(req => req.status === 'missing' || !req.status);
            
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-12 mb-4">
                        <h5 class="text-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Completed Requirements (${completed.length})
                        </h5>
                        ${completed.length > 0 ? `
                            <ul class="list-group">
                                ${completed.map(req => `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ${req.name}
                                        <span class="badge bg-success rounded-pill">${req.status === 'approved' ? 'Approved' : 'Submitted'}</span>
                                    </li>
                                `).join('')}
                            </ul>
                        ` : '<p class="text-muted">No completed requirements</p>'}
                    </div>
                    
                    <div class="col-12 mb-4">
                        <h5 class="text-warning">
                            <i class="bi bi-clock-fill me-2"></i>
                            Pending Requirements (${pending.length})
                        </h5>
                        ${pending.length > 0 ? `
                            <ul class="list-group">
                                ${pending.map(req => `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ${req.name}
                                        <span class="badge bg-warning rounded-pill">Pending</span>
                                    </li>
                                `).join('')}
                            </ul>
                        ` : '<p class="text-muted">No pending requirements</p>'}
                    </div>
                    
                    <div class="col-12">
                        <h5 class="text-danger">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            Remaining/Not Yet Submitted (${remaining.length})
                        </h5>
                        ${remaining.length > 0 ? `
                            <ul class="list-group">
                                ${remaining.map(req => `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ${req.name}
                                        <span class="badge bg-secondary rounded-pill">Not Submitted</span>
                                    </li>
                                `).join('')}
                            </ul>
                        ` : '<p class="text-muted">No remaining requirements</p>'}
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <button class="btn btn-secondary" onclick="goBackToSearchResults()">
                        <i class="bi bi-arrow-left me-1"></i>Back to Search Results
                    </button>
                </div>
            `;
            
            // Modal is already showing, just update content
    };
    
    // Function to go back to search results
    window.goBackToSearchResults = function() {
        if (currentSearchResults) {
            // Simply restore the search results in the same modal
            const modalTitle = document.getElementById('teamsModalLabel');
            const modalContent = document.getElementById('modalContent');
            
            modalTitle.textContent = `Search Results: "${currentSearchResults.search_query}" (${currentSearchResults.total_found} team${currentSearchResults.total_found !== 1 ? 's' : ''} found)`;
            
            if (currentSearchResults.teams.length === 0) {
                modalContent.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No teams found matching your search criteria.
                    </div>
                `;
            } else {
                let content = '<div class="row">';
                
                currentSearchResults.teams.forEach(team => {
                    const statusBadge = getStatusBadge(team.status_category, team.completion_percentage);
                    
                    content += `
                        <div class="col-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0 d-flex align-items-center">
                                            ${team.name}
                                            <span class="ms-2">${statusBadge}</span>
                                        </h5>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm" style="background-color: #1304ee; color: white; border-color: #1304ee;" onclick="showTeamDetails(${team.id}, '${team.name.replace(/'/g, "\\'")}')">
                                                View Info
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="showTeamRequirements(${team.id}, '${team.name.replace(/'/g, "\\'")}', ${JSON.stringify(team.requirement_details).replace(/"/g, '&quot;')})">
                                                View Requirements
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <p class="card-text text-muted mb-2">
                                        <strong>Research Title:</strong> ${team.research_title}
                                    </p>
                                    <p class="card-text text-muted mb-0">
                                        <strong>Adviser:</strong> ${team.adviser}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                content += '</div>';
                modalContent.innerHTML = content;
            }
        } else {
            // If no search results stored, just close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('teamsModal'));
            if (modal) {
                modal.hide();
            }
        }
    };
    
    // Function to show requirements list
    window.showRequirementsList = function() {
        const modalTitle = document.getElementById('teamsModalLabel');
        const modalContent = document.getElementById('modalContent');
        
        modalTitle.textContent = 'Requirements Overview';
        
        // Debug: Check if requirementDetails is available
        console.log('Requirements details:', requirementDetails);
        
        if (!requirementDetails || Object.keys(requirementDetails).length === 0) {
            modalContent.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    No requirements data available.
                </div>
            `;
            return;
        }
        
        let content = '<div class="row">';
        
        // Create requirements list from requirementDetails
        Object.entries(requirementDetails).forEach(([requirementId, details]) => {
            // Add null checks for details and details.name
            if (!details || !details.name) {
                console.warn(`Skipping requirement ${requirementId} - missing name`, details);
                return;
            }
            
            const totalTeams = (details.completed?.length || 0) + (details.pending?.length || 0) + (details.missing?.length || 0);
            const safeName = details.name.replace(/'/g, "\\'");
            
            content += `
                <div class="col-12 mb-3">
                    <div class="card h-100" style="cursor: pointer;" onclick="showRequirementTeams('${requirementId}', '${safeName}')">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">${details.name}</h5>
                                <span class="badge bg-primary rounded-pill">${totalTeams}</span>
                            </div>
                            <div class="mt-2">
                                <small class="text-success me-3">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    ${details.completed?.length || 0} Completed
                                </small>
                                <small class="text-warning me-3">
                                    <i class="bi bi-clock-fill me-1"></i>
                                    ${details.pending?.length || 0} Pending
                                </small>
                                <small class="text-danger">
                                    <i class="bi bi-x-circle-fill me-1"></i>
                                    ${details.missing?.length || 0} Missing
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        content += '</div>';
        modalContent.innerHTML = content;
        
        // Don't create a new modal, just show the existing one if it's not already shown
        const existingModal = bootstrap.Modal.getInstance(document.getElementById('teamsModal'));
        if (existingModal) {
            // Modal is already initialized, just make sure it's shown
            existingModal.show();
        } else {
            // Create and show the modal
            const teamsModal = new bootstrap.Modal(document.getElementById('teamsModal'));
            teamsModal.show();
        }
    };
    
    // Function to show teams for a specific requirement
    window.showRequirementTeams = function(requirementId, requirementName) {
        const modalTitle = document.getElementById('teamsModalLabel');
        const modalContent = document.getElementById('modalContent');
        
        modalTitle.textContent = `Teams Status: ${requirementName}`;
        
        const requirement = requirementDetails[requirementId];
        
        if (!requirement) {
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Requirement details not found.
                </div>
            `;
            return;
        }
        
        const content = `
            <div class="row">
                <div class="col-12 mb-4">
                    <h5 class="text-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Completed Teams (${requirement.completed.length})
                    </h5>
                    ${requirement.completed.length > 0 ? `
                        <ul class="list-group">
                            ${requirement.completed.map(team => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${team.name}
                                    <span class="badge bg-success rounded-pill">Completed</span>
                                </li>
                            `).join('')}
                        </ul>
                    ` : '<p class="text-muted">No teams have completed this requirement</p>'}
                </div>
                
                <div class="col-12 mb-4">
                    <h5 class="text-warning">
                        <i class="bi bi-clock-fill me-2"></i>
                        Pending Teams (${requirement.pending.length})
                    </h5>
                    ${requirement.pending.length > 0 ? `
                        <ul class="list-group">
                            ${requirement.pending.map(team => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${team.name}
                                    <span class="badge bg-warning rounded-pill">Pending</span>
                                </li>
                            `).join('')}
                        </ul>
                    ` : '<p class="text-muted">No teams have pending submissions</p>'}
                </div>
                
                <div class="col-12 mb-4">
                    <h5 class="text-danger">
                        <i class="bi bi-x-circle-fill me-2"></i>
                        Missing Teams (${requirement.missing.length})
                    </h5>
                    ${requirement.missing.length > 0 ? `
                        <ul class="list-group">
                            ${requirement.missing.map(team => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${team.name}
                                    <span class="badge bg-secondary rounded-pill">Missing</span>
                                </li>
                            `).join('')}
                        </ul>
                    ` : '<p class="text-muted">No teams are missing this requirement</p>'}
                </div>
            </div>
            
            <div class="mt-3 text-center">
                <button class="btn btn-secondary" onclick="showRequirementsList()">
                    <i class="bi bi-arrow-left me-1"></i>Back to Requirements List
                </button>
            </div>
        `;
        
        modalContent.innerHTML = content;
    };
    
    // Make "view per requirement" link work
    const viewRequirementsLink = document.getElementById('viewRequirementsLink');
    if (viewRequirementsLink) {
        viewRequirementsLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.showRequirementsList();
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