<?php

/**
 * Home Page
 * 
 * This file serves as the home page for the COECSAT Thesis Management System.
 * It includes various sections and tools for users to interact with, such as 
 * thesis topic decision, research title acceptance, scheduling system, and 
 * requirement checker.
 * 
 * @file /c:/xampp/htdocs/home/index.php
 * 
 * @constant TITLE The title of the page.
 * 
 * @include ../assets/layouts/header.php
 * @include ../assets/layouts/footer.php
 * 
 * @function check_verified Verifies if the user is authenticated and verified.
 * 
 * @section Main Content
 * The main content of the page is divided into several tabs:
 * 
 * - Thesis Topic Decision: Allows users to select a field and get the latest thesis topics.
 * - Research Title Acceptance: Provides a form for users to check the uniqueness of their proposed research title.
 * - Scheduling System: Displays the user's schedule and defense schedule.
 * - Requirement Checker: Provides a checklist for users to check their document requirements.
 * 
 * @section Scripts
 * The following scripts are included for functionality:
 * 
 * - mainModule.js: Main module JavaScript file.
 * - app.js: Application-specific JavaScript file.
 * - marked.min.js: Library for parsing Markdown.
 */
define('TITLE', "Home");
include '../assets/layouts/header.php';
check_verified();
include '../assets/setup/db.inc.php';
require_once '../dashboard/includes/section_access.php';

$isSectionProfessor = ((int)$_SESSION['usertype'] === 2)
    && professorHasSectionAssignment($pdo, (int)$_SESSION['id']);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// echo '<pre>';
// print_r($_SESSION['team_id'][0]);
// echo '</pre>';
?>

<script>
const USER_TYPE = <?php echo (int)$_SESSION['usertype']; ?>;

// Move fetchTeamOverview to global scope
function fetchTeamOverview(teamId = null) {
    // All user types use the unified dashboard
    fetchFacultyDashboard();
}

// Unified Dashboard: Show teams and defense schedules for all user types
function fetchFacultyDashboard() {
    const dashboardContent = document.getElementById('teamOverviewContent');
    
    dashboardContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading dashboard...</p>
        </div>
    `;
    
    fetch('includes/get_faculty_dashboard.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let content = '';
                
                // Section header - changes per role
                const teamsHeader = USER_TYPE === 1 ? 'My Teams' : 'Advisee Teams';
                const noTeamsMsg  = USER_TYPE === 1
                    ? 'You are not currently a member of any team.'
                    : 'You are not currently advising any teams.';
                
                // Advisee / My Teams Section
                content += `
                    <div class="row mb-4 align-items-center">
                        <div class="col-auto">
                            <h4 class="mb-0">
                                ${teamsHeader}
                            </h4>
                        </div>
                `;
                
                // Add dropdown beside header if multiple teams
                if (data.advisee_teams.length > 1) {
                    content += `
                        <div class="col-auto">
                            <select id="adviseeTeamSelector" class="form-select advisee-team-select">
                                ${data.advisee_teams.map((team, index) => 
                                    `<option value="${index}" ${index === 0 ? 'selected' : ''}>${team.name}</option>`
                                ).join('')}
                            </select>
                        </div>
                    `;
                }
                
                content += `
                    </div>
                `;
                
                if (data.advisee_teams.length === 0) {
                    content += `
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            ${noTeamsMsg}
                        </div>
                    `;
                } else {
                    // Store teams data for dynamic switching
                    window.adviseeTeamsData = data.advisee_teams;
                    
                    // Render first team by default
                    content += `<div id="adviseeTeamContent"></div>`;
                }
                
                // Defense Schedule Section
                const defenseHeader = USER_TYPE === 1 ? 'Your Defense Schedules' : 'Panel Defense Schedule';
                const noDefenseMsg  = USER_TYPE === 1
                    ? 'No defense scheduled yet.'
                    : 'No defense schedules assigned to you as panelist.';
                
                content += `
                    <div class="row mb-4 mt-5">
                        <div class="col-12">
                            <h4 class="mb-3">
                                ${defenseHeader}
                                <span class="defense-schedule-count">${data.paneling_defenses.length}</span>
                            </h4>
                        </div>
                    </div>
                `;
                
                if (data.paneling_defenses.length === 0) {
                    content += `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            ${noDefenseMsg}
                        </div>
                    `;
                } else if (USER_TYPE === 1) {
                    // ── Student view: read-only table with panelist names ──
                    content += `
                        <div class="table-responsive">
                            <table class="db-table defense-schedule-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Defense Type</th>
                                        <th>Room</th>
                                        <th>Panelists</th>
                                        <th class="text-center">Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.paneling_defenses.forEach(defense => {
                        const defenseDate = new Date(defense.schedule_date);
                        const isUpcoming = defenseDate >= new Date();
                        
                        // Defense type pill
                        const dtText = (defense.defense_type || 'general').replace(/_/g, ' ');
                        let dtClass = 'type-general';
                        if (dtText.includes('title proposal')) dtClass = 'type-proposal';
                        else if (dtText.includes('title defense')) dtClass = 'type-title';
                        else if (dtText.includes('final')) dtClass = 'type-final';
                        else if (dtText.includes('re defense') || dtText.includes('re-defense')) dtClass = 'type-redefense';
                        
                        // Result badge
                        let resultBadge = '';
                        if (defense.defense_status === 'passed') {
                            resultBadge = '<span class="defense-status-pill status-evaluated">Passed</span>';
                        } else if (defense.defense_status === 'failed') {
                            resultBadge = '<span class="defense-status-pill status-pending">Failed</span>';
                        } else if (isUpcoming) {
                            resultBadge = '<span class="defense-status-pill status-scheduled">Upcoming</span>';
                        } else {
                            resultBadge = '<span class="defense-status-pill status-pending">Pending</span>';
                        }
                        
                        const panelists = defense.panelist_names || 'TBA';
                        
                        content += `
                            <tr>
                                <td>
                                    <strong>${defenseDate.toLocaleDateString()}</strong><br>
                                    <small class="text-muted">${defense.start_time} - ${defense.end_time}</small>
                                </td>
                                <td><span class="defense-type-pill ${dtClass}">${dtText}</span></td>
                                <td>${defense.room || 'TBA'}</td>
                                <td><small>${panelists}</small></td>
                                <td class="text-center">${resultBadge}</td>
                            </tr>
                        `;
                    });
                    
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    content += `
                        <div class="table-responsive">
                            <table class="db-table defense-schedule-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Team</th>
                                        <th>Research Title</th>
                                        <th>Defense Type</th>
                                        <th>Room</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.paneling_defenses.forEach(defense => {
                        const defenseDate = new Date(defense.schedule_date);
                        const isUpcoming = defenseDate >= new Date();
                        const hasEvaluated = parseInt(defense.has_evaluated) > 0;
                        
                        let actionButton = '';
                        if (hasEvaluated) {
                            actionButton = `
                                <button class="btn btn-sm btn-outline-primary defense-action-btn" onclick="redirectToDecisionSupport(${defense.schedule_id}, true)">
                                    View
                                </button>
                            `;
                        } else if (!isUpcoming) {
                            actionButton = `
                                <button class="btn btn-sm btn-primary defense-action-btn" onclick="redirectToDecisionSupport(${defense.schedule_id})">
                                    Evaluate
                                </button>
                            `;
                        } else {
                            actionButton = `<span class="text-muted small">Upcoming</span>`;
                        }
                        
                        const statusBadge = hasEvaluated ? 
                            '<span class="defense-status-pill status-evaluated">Evaluated</span>' :
                            isUpcoming ? '<span class="defense-status-pill status-scheduled">Scheduled</span>' :
                            '<span class="defense-status-pill status-pending">Pending</span>';
                        
                        let defenseTypePill = '';
                        const defenseTypeText = defense.defense_type || 'N/A';
                        let typeClass = 'type-general';
                        if (defenseTypeText.toLowerCase().includes('title proposal')) {
                            typeClass = 'type-proposal';
                        } else if (defenseTypeText.toLowerCase().includes('title defense')) {
                            typeClass = 'type-title';
                        } else if (defenseTypeText.toLowerCase().includes('final')) {
                            typeClass = 'type-final';
                        } else if (defenseTypeText.toLowerCase().includes('re-defense')) {
                            typeClass = 'type-redefense';
                        }
                        defenseTypePill = `<span class="defense-type-pill ${typeClass}">${defenseTypeText}</span>`;
                        
                        content += `
                            <tr>
                                <td>
                                    <strong>${defenseDate.toLocaleDateString()}</strong><br>
                                    <small class="text-muted">${defense.start_time} - ${defense.end_time}</small>
                                </td>
                                <td>
                                    <strong>${defense.team_name}</strong>
                                </td>
                                <td><small>${defense.research_title || 'No title'}</small></td>
                                <td>${defenseTypePill}</td>
                                <td>${defense.room}</td>
                                <td class="text-center">${statusBadge}</td>
                                <td class="text-center">${actionButton}</td>
                            </tr>
                        `;
                    });
                    
                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                dashboardContent.innerHTML = content;
                
                // Render first advisee team card if any
                if (window.adviseeTeamsData && window.adviseeTeamsData.length > 0) {
                    renderAdviseeTeamCard(0);
                    
                    // Add team selector event listener
                    const teamSelector = document.getElementById('adviseeTeamSelector');
                    if (teamSelector) {
                        teamSelector.addEventListener('change', function() {
                            renderAdviseeTeamCard(parseInt(this.value));
                        });
                    }
                }
                
                // Add event listeners for defense stage dropdown
                document.querySelectorAll('.defense-stage-select').forEach(select => {
                    select.addEventListener('change', function() {
                        const teamId = this.dataset.teamId;
                        const newStage = this.value;
                        updateTeamDefenseStage(teamId, newStage, this);
                    });
                });
                
                // Add event listeners for view team summary button
                document.querySelectorAll('.view-team-summary').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const teamId = this.dataset.teamId;
                        const teamName = this.dataset.teamName;
                        showTeamSummaryModal(teamId, teamName);
                    });
                });
            } else {
                dashboardContent.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading faculty dashboard:', error);
            dashboardContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Failed to load dashboard. Please try again.
                </div>
            `;
        });
}

// Render advisee team card (card-based layout)
function renderAdviseeTeamCard(teamIndex) {
    const team = window.adviseeTeamsData[teamIndex];
    if (!team) return;
    const safeTeamName = escapeHtml(team.name || 'N/A');
    
    const container = document.getElementById('adviseeTeamContent');
    if (!container) return;
    
    const progress = team.total_requirements > 0 ? 
        Math.round((team.completed_count / team.total_requirements) * 100) : 0;
    const progressClass = progress >= 75 ? 'bg-success' : progress >= 50 ? 'bg-warning' : 'bg-danger';
    
    // Determine current defense stage
    const defenseType = team.override_defense_type || team.latest_defense_type || 'title_proposal';
    const defenseStageLabels = {
        'title_proposal': { label: 'Title Proposal', class: 'defense-stage-title-proposal' },
        'title_defense': { label: 'Title Defense', class: 'defense-stage-title-defense' },
        'final_defense': { label: 'Final Defense', class: 'defense-stage-final-defense' },
        're-defense': { label: 'Re-Defense', class: 'defense-stage-re-defense' }
    };
    const stageInfo = defenseStageLabels[defenseType] || { label: 'Title Proposal', class: 'defense-stage-title-proposal' };
    
    // Score display with dynamic thresholds
    const avgScore = team.avg_score ? parseFloat(team.avg_score).toFixed(2) : null;
    const teamPassThreshold = team.pass_threshold_3 || 75;
    const teamWarningThreshold = Math.max(teamPassThreshold - 15, 60);
    const scoreClass = avgScore >= teamPassThreshold ? 'text-success' : avgScore >= teamWarningThreshold ? 'text-warning' : avgScore ? 'text-danger' : 'text-muted';
    
    // Title status
    const titleApproved = team.title_approved_at ? true : false;
    const titleBadge = titleApproved ? 
        '<span class="badge bg-success ms-2"><i class="bi bi-check-circle me-1"></i>Approved</span>' : 
        '<span class="badge bg-secondary ms-2"><i class="bi bi-clock me-1"></i>Pending</span>';
    
    // Parse student members
    let membersList = '';
    if (team.student_names) {
        const members = team.student_names.split(', ');
        membersList = members.map(m => `<li class="team-info-member-li"><i class="bi bi-person me-2"></i>${m}</li>`).join('');
    } else {
        membersList = '<li class="list-group-item text-muted py-2">No members found</li>';
    }
    
    // Build requirements list placeholder
    let requirementsList = '<li class="text-muted small py-2">Loading requirements...</li>';
    
    let html = `
        <div class="row">
            <!-- Team Info Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 team-info-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Team Information</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title team-info-title-ellipsis" title="${safeTeamName}">${safeTeamName}</h3>
                        <p class="card-text mb-2">
                            <span class="program-pill" title="${team.program || 'N/A'}">${team.program || 'N/A'}</span>
                        </p>
                        <p class="card-text mb-0">
                            <strong>Members:</strong>
                        </p>
                        <ul class="list-group mt-2">
                            ${membersList}
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Defense Stage & Progress Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 progress-overview-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Progress Overview</h5>
                        ${team.total_evaluations && team.total_evaluations > 0 ? 
                            `<a href="#" class="view-team-summary-link" data-team-id="${team.id}" data-team-name="${team.name}">View Evaluation Details</a>` : 
                            `<span class="text-muted small">No evaluations available</span>`
                        }
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="form-label fw-bold">Defense Stage:</label>
                            <p class="mb-0">
                                <span class="defense-stage-badge ${stageInfo.class}">
                                    ${stageInfo.label}
                                </span>
                            </p>
                        </div>
                        
                        <div class="mb-0">
                            <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
                                <label class="form-label fw-bold mb-0">Requirements:</label>
                                <div class="req-filter-legend">
                                    <button class="req-filter-btn" data-filter="completed" data-team-id="${team.id}">
                                        <i class="bi bi-check-circle-fill text-success"></i> <span>Submitted</span>
                                    </button>
                                    <button class="req-filter-btn" data-filter="pending" data-team-id="${team.id}">
                                        <i class="bi bi-clock-fill text-warning"></i> <span>No Submission</span>
                                    </button>
                                    <button class="req-filter-btn" data-filter="overdue" data-team-id="${team.id}">
                                        <i class="bi bi-exclamation-circle-fill text-danger"></i> <span>Overdue</span>
                                    </button>
                                </div>
                            </div>
                            <div class="requirements-scroll-container">
                                <ul class="list-group mt-2" id="teamRequirementsList-${team.id}">
                                    ${requirementsList}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Fetch and render requirements for the team
    fetchTeamRequirements(team.id);
    
    // Add event listeners for requirement filters
    container.querySelectorAll('.req-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const teamId = this.dataset.teamId;
            const isActive = this.classList.contains('active');
            
            // Deactivate all filters for this team
            container.querySelectorAll(`.req-filter-btn[data-team-id="${teamId}"]`).forEach(b => {
                b.classList.remove('active');
            });
            
            // If clicked button was not active, activate it
            if (!isActive) {
                this.classList.add('active');
            }
            
            renderTeamRequirements(teamId);
        });
    });
    
    // Re-attach event listeners for this card
    container.querySelectorAll('.defense-stage-select').forEach(select => {
        select.addEventListener('change', function() {
            const teamId = this.dataset.teamId;
            const newStage = this.value;
            updateTeamDefenseStage(teamId, newStage, this);
        });
    });
    
    container.querySelectorAll('.view-team-summary').forEach(btn => {
        btn.addEventListener('click', function() {
            const teamId = this.dataset.teamId;
            const teamName = this.dataset.teamName;
            showTeamSummaryModal(teamId, teamName);
        });
    });
    
    container.querySelectorAll('.view-team-summary-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const teamId = this.dataset.teamId;
            const teamName = this.dataset.teamName;
            showTeamSummaryModal(teamId, teamName);
        });
    });
}

// Fetch and render team requirements
function fetchTeamRequirements(teamId) {
    const listElement = document.getElementById(`teamRequirementsList-${teamId}`);
    if (!listElement) return;
    
    fetch(`includes/get_team_requirements.php?team_id=${teamId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.requirements) {
                if (data.requirements.length === 0) {
                    listElement.innerHTML = '<li class="text-muted small py-2">No requirements found</li>';
                    return;
                }
                
                // Store requirements data globally for filtering
                window[`teamRequirements_${teamId}`] = data.requirements;
                
                // Render requirements with default filters (all active)
                renderTeamRequirements(teamId);
            } else {
                listElement.innerHTML = '<li class="text-muted small py-2">Failed to load requirements</li>';
            }
        })
        .catch(error => {
            console.error('Error fetching requirements:', error);
            listElement.innerHTML = '<li class="text-muted small py-2">Error loading requirements</li>';
        });
}

// Render team requirements based on active filters
function renderTeamRequirements(teamId) {
    const listElement = document.getElementById(`teamRequirementsList-${teamId}`);
    const requirements = window[`teamRequirements_${teamId}`];
    
    if (!listElement || !requirements) return;
    
    // Get active filter (only one or none)
    const activeFilterBtn = document.querySelector(`.req-filter-btn[data-team-id="${teamId}"].active`);
    const activeFilter = activeFilterBtn ? activeFilterBtn.dataset.filter : null;
    
    const now = new Date();
    
    // Filter requirements based on active filter
    let filteredRequirements;
    
    if (!activeFilter) {
        // No filter selected - show all requirements
        filteredRequirements = requirements;
    } else {
        // Filter by selected status
        filteredRequirements = requirements.filter(req => {
            const dueDate = req.due_date ? new Date(req.due_date) : null;
            const isOverdue = dueDate && now > dueDate && req.status !== 'completed';
            
            if (activeFilter === 'completed' && req.status === 'completed') return true;
            if (activeFilter === 'overdue' && isOverdue) return true;
            if (activeFilter === 'pending' && req.status !== 'completed' && !isOverdue) return true;
            
            return false;
        });
    }
    
    // Sort by due date, newest first
    filteredRequirements.sort((a, b) => {
        const dateA = a.due_date ? new Date(a.due_date) : new Date(0);
        const dateB = b.due_date ? new Date(b.due_date) : new Date(0);
        return dateB - dateA;
    });
    
    if (filteredRequirements.length === 0) {
        listElement.innerHTML = '<li class="text-muted small py-2">No requirements found</li>';
        return;
    }
    
    const requirementItems = filteredRequirements.map(req => {
        const dueDate = req.due_date ? new Date(req.due_date) : null;
        const isOverdue = dueDate && now > dueDate && req.status !== 'completed';
        
        let icon = '';
        let statusClass = '';
        
        if (req.status === 'completed') {
            icon = '<i class="bi bi-check-circle-fill text-success me-2"></i>';
            statusClass = 'req-completed';
        } else if (isOverdue) {
            icon = '<i class="bi bi-exclamation-circle-fill text-danger me-2"></i>';
            statusClass = 'req-overdue';
        } else {
            icon = '<i class="bi bi-clock-fill text-warning me-2"></i>';
            statusClass = 'req-pending';
        }
        
        const deadline = dueDate ? dueDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'No deadline';
        
        return `
            <li class="requirement-list-item ${statusClass}">
                <div class="req-content">
                    ${icon}
                    <span class="req-name">${req.name}</span>
                </div>
                <span class="req-deadline">${deadline}</span>
            </li>
        `;
    }).join('');
    
    listElement.innerHTML = requirementItems;
}

// Update team defense stage
function updateTeamDefenseStage(teamId, newStage, selectElement) {
    const originalValue = selectElement.dataset.originalValue || selectElement.value;
    selectElement.disabled = true;
    
    fetch('includes/update_team_defense_stage.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ team_id: teamId, defense_type: newStage })
    })
    .then(response => response.json())
    .then(data => {
        selectElement.disabled = false;
        if (data.success) {
            selectElement.dataset.originalValue = newStage;
            showToast('Success', `Defense stage updated to ${newStage.replace('_', ' ').replace('-', '')}`, 'success');
        } else {
            selectElement.value = originalValue;
            showToast('Error', data.message || 'Failed to update defense stage', 'error');
        }
    })
    .catch(error => {
        selectElement.disabled = false;
        selectElement.value = originalValue;
        console.error('Error updating defense stage:', error);
        showToast('Error', 'Failed to update defense stage', 'error');
    });
}

// Show team summary modal
function showTeamSummaryModal(teamId, teamName) {
    // Remove any existing modal backdrops first
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
    
    // Create modal if not exists
    let modal = document.getElementById('teamSummaryModal');
    if (!modal) {
        document.body.insertAdjacentHTML('beforeend', `
            <div class="modal fade" id="teamSummaryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Team Summary</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="teamSummaryBody">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted">Loading team details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
        modal = document.getElementById('teamSummaryModal');
        
        // Add cleanup on modal hidden
        modal.addEventListener('hidden.bs.modal', function() {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        });
    }
    
    // Reset modal body content
    document.getElementById('teamSummaryBody').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading team details...</p>
        </div>
    `;
    
    const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
    bsModal.show();
    
    // Load team summary
    fetch(`includes/tabs/get_evaluation_details.php?team_id=${teamId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('teamSummaryBody').innerHTML = `
                    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>${data.error}</div>
                `;
                return;
            }
            
            const team = data.team_info;
            const panelists = data.panelists || [];
            const students = data.students || [];
            const evaluationsByStudent = data.evaluations_by_student || {};
            const passThreshold = data.pass_threshold_3 || 75;
            const warningThreshold = Math.max(passThreshold - 15, 60);
            const members = data.members || [];
            
            let html = `
                <div class="mb-4">
                    <h3 class="border-bottom pb-2">${teamName}</h3>
                    <p class="mb-1"><strong>Research Title:</strong> ${team.research_title || '<em class="text-muted">No title yet</em>'}</p>
                    <p class="mb-1"><strong>Program:</strong> ${team.program || 'N/A'}</p>
                    <p class="mb-0"><strong>Adviser:</strong> ${team.adviser || 'N/A'}</p>
                </div>
                
                <!-- Team Members section removed -->
                
                <h6 class="text-dark mb-3">Evaluation Summary:</h6>
            `;
            
            if (panelists.length === 0 || Object.keys(evaluationsByStudent).length === 0) {
                html += `<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No evaluations recorded yet.</div>`;
            } else {
                html += `
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered team-eval-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    ${panelists.map(p => `<th class="text-center"><small>${p.evaluator_name}</small></th>`).join('')}
                                    <th class="text-center table-primary"><strong>Average</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                let allAverages = [];
                students.forEach(student => {
                    const studentData = evaluationsByStudent[student.student_id];
                    let scores = [];
                    
                    let cells = panelists.map(panelist => {
                        const scoreData = studentData?.panelist_scores?.[panelist.evaluator_id];
                        if (scoreData && scoreData.total_score !== null) {
                            const score = parseFloat(scoreData.total_score);
                            scores.push(score);
                            return `<td class="text-center text-dark">${score.toFixed(2)}</td>`;
                        }
                        return '<td class="text-center text-muted">-</td>';
                    }).join('');
                    
                    let avgDisplay = '<span class="text-muted">-</span>';
                    if (scores.length > 0) {
                        const avg = scores.reduce((a, b) => a + b, 0) / scores.length;
                        allAverages.push(avg);
                        const avgClass = avg >= passThreshold ? 'text-success' : avg >= warningThreshold ? 'text-warning' : 'text-danger';
                        avgDisplay = `<strong class="${avgClass}">${avg.toFixed(2)}</strong>`;
                    }
                    
                    html += `<tr><td>${student.student_name}</td>${cells}<td class="text-center table-primary">${avgDisplay}</td></tr>`;
                });
                
                // Team average
                let teamAvgDisplay = '<span class="text-muted">-</span>';
                if (allAverages.length > 0) {
                    const teamAvg = allAverages.reduce((a, b) => a + b, 0) / allAverages.length;
                    const teamAvgClass = teamAvg >= passThreshold ? 'text-success' : teamAvg >= warningThreshold ? 'text-warning' : 'text-danger';
                    teamAvgDisplay = `<strong class="${teamAvgClass}">${teamAvg.toFixed(2)}</strong>`;
                }
                
                html += `
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr><th>Team Average</th>${panelists.map(() => '<td></td>').join('')}<th class="text-center">${teamAvgDisplay}</th></tr>
                            </tfoot>
                        </table>
                    </div>
                `;
            }
            
            document.getElementById('teamSummaryBody').innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading team summary:', error);
            document.getElementById('teamSummaryBody').innerHTML = `
                <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Failed to load team details.</div>
            `;
        });
}

<?php if ($isSectionProfessor): ?>
// --- Academic Year Feature ---
// Update the academic year label in the class record header
function updateAcademicYearLabel(academicYear) {
    const ayLabel = document.getElementById('ayLabel');
    if (!ayLabel) return;
    if (academicYear) {
        ayLabel.textContent = 'A.Y. ' + academicYear;
        ayLabel.title = 'Click to edit academic year';
    } else {
        ayLabel.textContent = 'Academic year not set';
        ayLabel.title = 'Click to set academic year';
    }
}

// Populate start year dropdown: previous year and current year only
function populateAyYears() {
    const select = document.getElementById('ayStartYear');
    if (!select) return;
    select.innerHTML = '';
    const now = new Date().getFullYear();
    for (let y = now - 1; y <= now; y++) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        if (y === now) opt.selected = true;
        select.appendChild(opt);
    }
    // Set end year on change
    select.addEventListener('change', function() {
        document.getElementById('ayEndYear').value = parseInt(this.value) + 1;
    });
    document.getElementById('ayEndYear').value = parseInt(select.value) + 1;
}

// Open the academic year modal (set or edit)
function openAcademicYearModal() {
    populateAyYears();
    const ayLabel = document.getElementById('ayLabel');
    const modalTitle = document.getElementById('ayModalTitle');
    const currentText = ayLabel?.textContent || '';

    // If there's already an AY set, parse and prefill
    if (currentText.startsWith('A.Y. ')) {
        modalTitle.textContent = 'Edit Academic Year';
        const parts = currentText.replace('A.Y. ', '').split(', ');
        const years = (parts[0] || '').split('-');
        const semester = parts[1] || '1st Semester';
        const startYear = parseInt(years[0]);
        if (!isNaN(startYear)) {
            const select = document.getElementById('ayStartYear');
            // Add option if not in range
            if (!select.querySelector(`option[value="${startYear}"]`)) {
                const opt = document.createElement('option');
                opt.value = startYear;
                opt.textContent = startYear;
                select.insertBefore(opt, select.firstChild);
            }
            select.value = startYear;
            document.getElementById('ayEndYear').value = startYear + 1;
        }
        document.getElementById('aySemester').value = semester;
    } else {
        modalTitle.textContent = 'Set Academic Year';
    }

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('academicYearModal'));
    modal.show();
}

// Save academic year via AJAX
function saveAcademicYear() {
    const startYear = document.getElementById('ayStartYear').value;
    const endYear = document.getElementById('ayEndYear').value;
    const semester = document.getElementById('aySemester').value;
    const academicYear = startYear + '-' + endYear + ', ' + semester;

    const saveBtn = document.getElementById('saveAcademicYearBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    $.ajax({
        url: 'includes/save_academic_year.php',
        method: 'POST',
        data: { academic_year: academicYear },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateAcademicYearLabel(response.academic_year);
                bootstrap.Modal.getInstance(document.getElementById('academicYearModal'))?.hide();
                showToast('Success', 'Academic year updated successfully', 'success');
            } else {
                showToast('Error', response.message || 'Failed to save academic year', 'error');
            }
        },
        error: function() {
            showToast('Error', 'Failed to save academic year. Please try again.', 'error');
        },
        complete: function() {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
        }
    });
}

// Load class record for faculty
function loadClassRecord(page = 1) {
    const classRecordContent = document.getElementById('classRecordContent');
    const viewType = document.getElementById('classRecordViewSelect')?.value || 'class';
    const sortContainer = document.getElementById('classRecordSortContainer');
    
    // Hide sort dropdown - class view now always sorts alphabetically by section
    if (sortContainer) {
        sortContainer.style.display = 'none';
    }
    
    // Show loading state
    classRecordContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading class records...</p>
        </div>
    `;
    
    fetch('includes/get_class_record.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update academic year display in header
                updateAcademicYearLabel(data.academic_year);
                
                let content = '';
                
                if (Object.keys(data.sections).length === 0) {
                    content = `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No students found in your assigned sections.
                        </div>
                    `;
                } else if (viewType === 'team') {
                    // TEAM VIEW - Students grouped by teams with pagination
                    const itemsPerPage = 5; // Teams per page
                    
                    for (const [section, teams] of Object.entries(data.sections)) {
                        const teamsArray = Object.entries(teams);
                        const totalTeams = teamsArray.length;
                        const totalPages = Math.ceil(totalTeams / itemsPerPage);
                        const currentPage = window.classRecordCurrentPage || 1;
                        const startIndex = (currentPage - 1) * itemsPerPage;
                        const endIndex = startIndex + itemsPerPage;
                        const paginatedTeams = teamsArray.slice(startIndex, endIndex);
                        
                        content += `
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <h5 class="mb-0">
                                        Section: ${section}
                                    </h5>
                                </div>
                        `;
                        
                        // Loop through paginated teams
                        for (const [teamId, teamData] of paginatedTeams) {
                            const students = teamData.students;
                            
                            // Get all unique panelists from all students in this team
                            const panelistsMap = new Map();
                            students.forEach(student => {
                                if (student.panelist_grades) {
                                    student.panelist_grades.forEach(grade => {
                                        if (!panelistsMap.has(grade.evaluator_id)) {
                                            panelistsMap.set(grade.evaluator_id, grade.panelist_name);
                                        }
                                    });
                                }
                            });
                            const panelists = Array.from(panelistsMap.entries());
                            
                            content += `
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 text-dark">
                                            ${teamData.team_name}
                                            <span class="badge bg-secondary ms-2">${students.length} ${students.length === 1 ? 'member' : 'members'}</span>
                                        </h6>
                                        <strong class="text-muted d-block mt-1">
                                            ${teamData.research_title}
                                        </strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="db-table">
                                                <thead>
                                                    <tr>
                                                        <th>Student Name</th>
                                                        ${panelists.map(([id, name]) => `<th class="text-center">${name}</th>`).join('')}
                                                        <th class="text-center">Average</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                            `;
                            
                            students.forEach(student => {
                                // Use avg_total_score (from latest defense panelist grades) for consistency
                                const avgScore = student.avg_total_score ? parseFloat(student.avg_total_score).toFixed(2) : (student.avg_score ? parseFloat(student.avg_score).toFixed(2) : null);
                                
                                // Get dynamic pass threshold from rubric (default to 75 if not set)
                                const passThreshold = student.pass_threshold_3 || 75;
                                const warningThreshold = Math.max(passThreshold - 15, 60);
                                
                                // Determine status based on average score
                                let status = 'Pending';
                                let statusClass = 'bg-secondary';
                                
                                if (avgScore !== null) {
                                    if (avgScore >= passThreshold) {
                                        status = 'Passed';
                                        statusClass = 'bg-success';
                                    } else if (avgScore < passThreshold) {
                                        status = 'Failed';
                                        statusClass = 'bg-danger';
                                    }
                                }
                                
                                // Create grade columns
                                let gradeColumns = '';
                                panelists.forEach(([panelistId]) => {
                                    const grade = student.panelist_grades?.find(g => g.evaluator_id == panelistId);
                                    if (grade && grade.total_score !== null) {
                                        const gradeValue = parseFloat(grade.total_score).toFixed(2);
                                        gradeColumns += `<td class="text-center text-dark">${gradeValue}</td>`;
                                    } else {
                                        gradeColumns += `<td class="text-center text-muted">-</td>`;
                                    }
                                });
                                
                                const avgScoreClass = avgScore >= passThreshold ? 'text-success' : avgScore >= warningThreshold ? 'text-warning' : avgScore ? 'text-danger' : 'text-muted';
                                const avgScoreDisplay = avgScore !== null ? `<strong>${avgScore}</strong>` : '-';
                                
                                content += `
                                    <tr>
                                        <td>${student.last_name}, ${student.first_name}</td>
                                        ${gradeColumns}
                                        <td class="text-center ${avgScoreClass}">${avgScoreDisplay}</td>
                                        <td class="text-center">
                                            <span class="badge ${statusClass}">${status}</span>
                                        </td>
                                    </tr>
                                `;
                            });
                            
                            content += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        // Add pagination controls
                        if (totalPages > 1) {
                            content += `
                                <nav aria-label="Teams pagination" class="mt-4">
                                    <ul class="pagination justify-content-center" id="team-view-pagination">
                            `;
                            
                            // Previous button
                            const prevDisabled = currentPage <= 1 ? 'disabled' : '';
                            content += `
                                <li class="page-item ${prevDisabled}">
                                    <a class="page-link" href="#" data-page="${currentPage - 1}">&#8249;</a>
                                </li>
                            `;
                            
                            // Page numbers
                            const maxPages = 5;
                            let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
                            let endPage = Math.min(totalPages, startPage + maxPages - 1);
                            
                            if (endPage === totalPages) {
                                startPage = Math.max(1, endPage - maxPages + 1);
                            }
                            
                            if (startPage > 1) {
                                content += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                                if (startPage > 2) {
                                    content += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                                }
                            }
                            
                            for (let i = startPage; i <= endPage; i++) {
                                const active = i === currentPage ? 'active' : '';
                                content += `
                                    <li class="page-item ${active}">
                                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                                    </li>
                                `;
                            }
                            
                            if (endPage < totalPages) {
                                if (endPage < totalPages - 1) {
                                    content += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                                }
                                content += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
                            }
                            
                            // Next button
                            const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
                            content += `
                                <li class="page-item ${nextDisabled}">
                                    <a class="page-link" href="#" data-page="${currentPage + 1}">&#8250;</a>
                                </li>
                            `;
                            
                            content += `
                                    </ul>
                                </nav>
                            `;
                        }
                        
                        content += `</div>`;
                    }
                } else {
                    // CLASS VIEW - Students grouped by section with tabs, alphabetical order
                    // Organize students by section
                    const sectionData = {};
                    const sectionNames = Object.keys(data.sections).sort(); // Sort sections alphabetically
                    
                    sectionNames.forEach(section => {
                        const teams = data.sections[section];
                        sectionData[section] = [];
                        for (const [teamId, teamData] of Object.entries(teams)) {
                            teamData.students.forEach(student => {
                                sectionData[section].push({
                                    ...student,
                                    section: section,
                                    team_name: teamData.team_name,
                                    research_title: teamData.research_title
                                });
                            });
                        }
                        // Sort students alphabetically by last name, then first name
                        sectionData[section].sort((a, b) => {
                            const lastNameCmp = (a.last_name || '').toLowerCase().localeCompare((b.last_name || '').toLowerCase());
                            if (lastNameCmp !== 0) return lastNameCmp;
                            return (a.first_name || '').toLowerCase().localeCompare((b.first_name || '').toLowerCase());
                        });
                    });
                    
                    // Build section tabs
                    content += `
                        <ul class="nav nav-tabs mb-3" id="classRecordSectionTabs" role="tablist">
                            ${sectionNames.map((section, idx) => `
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link ${idx === 0 ? 'active' : ''}" 
                                            id="section-tab-${idx}" 
                                            data-bs-toggle="tab" 
                                            data-bs-target="#section-pane-${idx}" 
                                            type="button" 
                                            role="tab">
                                        ${section}
                                        <span class="badge bg-secondary ms-1">${sectionData[section].length}</span>
                                    </button>
                                </li>
                            `).join('')}
                        </ul>
                        <div class="tab-content" id="classRecordSectionContent">
                    `;
                    
                    // Build content for each section tab
                    sectionNames.forEach((section, idx) => {
                        const students = sectionData[section];
                    
                        content += `
                            <div class="tab-pane fade ${idx === 0 ? 'show active' : ''}" 
                                 id="section-pane-${idx}" 
                                 role="tabpanel">
                                <div class="card">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="db-table" id="classRecordTable-${idx}">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px;">#</th>
                                                        <th class="d-none d-md-table-cell">Student No.</th>
                                                        <th>Student Name</th>
                                                        <th class="d-none d-lg-table-cell">Team</th>
                                                        <th class="text-center">Group</th>
                                                        <th class="text-center">Individual</th>
                                                        <th class="text-center">Total</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                        `;
                        
                        students.forEach((student, studentIdx) => {
                        // Use avg_total_score instead of avg_score for consistency
                        const avgTotalScore = student.avg_total_score ? parseFloat(student.avg_total_score).toFixed(2) : null;
                        const avgGroupScore = student.avg_group_score ? parseFloat(student.avg_group_score).toFixed(2) : null;
                        const avgSoloScore = student.avg_solo_score ? parseFloat(student.avg_solo_score).toFixed(2) : null;
                        
                        // Get dynamic pass threshold from rubric (default to 75 if not set)
                        const passThreshold = student.pass_threshold_3 || 75;
                        const warningThreshold = Math.max(passThreshold - 15, 60);
                        
                        let status = 'Pending';
                        let statusClass = 'bg-secondary';
                        let statusValue = 2; // For sorting
                        
                        if (avgTotalScore !== null) {
                            if (avgTotalScore >= passThreshold) {
                                status = 'Passed';
                                statusClass = 'bg-success';
                                statusValue = 0;
                            } else if (avgTotalScore < passThreshold) {
                                status = 'Failed';
                                statusClass = 'bg-danger';
                                statusValue = 1;
                            }
                        }
                        
                        // Score display helpers (using dynamic thresholds)
                        const getScoreClass = (score) => {
                            if (!score) return 'text-muted';
                            const val = parseFloat(score);
                            return val >= passThreshold ? 'text-success' : val >= warningThreshold ? 'text-warning' : 'text-danger';
                        };
                        
                        const groupScoreClass = getScoreClass(avgGroupScore);
                        const soloScoreClass = getScoreClass(avgSoloScore);
                        const totalScoreClass = getScoreClass(avgTotalScore);
                        
                        const groupScoreDisplay = avgGroupScore !== null ? `${avgGroupScore}` : '-';
                        const soloScoreDisplay = avgSoloScore !== null ? `${avgSoloScore}` : '-';
                        const totalScoreDisplay = avgTotalScore !== null ? `<strong>${avgTotalScore}</strong>` : '-';
                        
                        // Build panelist details for modal (keep for detailed view)
                        let panelistDetailsHTML = '';
                        if (student.panelist_grades && student.panelist_grades.length > 0) {
                            student.panelist_grades.forEach(grade => {
                                if (grade.total_score !== null) {
                                    const gradeValue = parseFloat(grade.total_score).toFixed(2);
                                    const gradeClass = gradeValue >= passThreshold ? 'text-success' : gradeValue >= warningThreshold ? 'text-warning' : 'text-danger';
                                    panelistDetailsHTML += `${grade.panelist_name}: <span class="${gradeClass}">${gradeValue}</span> (G:${grade.group_score || 'N/A'} I:${grade.solo_score || 'N/A'}); `;
                                } else {
                                    panelistDetailsHTML += `${grade.panelist_name}: <span class="text-muted">No grade</span>; `;
                                }
                            });
                        }
                        
                            content += `
                                <tr class="class-record-row" 
                                    data-student-number="${student.student_number || ''}" 
                                    data-last-name="${student.last_name}" 
                                    data-first-name="${student.first_name}"
                                    data-team-name="${student.team_name}" 
                                    data-research-title="${student.research_title || 'No title'}"
                                    data-group-score="${avgGroupScore || 0}"
                                    data-solo-score="${avgSoloScore || 0}"
                                    data-total-score="${avgTotalScore || 0}" 
                                    data-status="${statusValue}"
                                    data-status-text="${status}"
                                    data-status-class="${statusClass}"
                                    data-panelist-details="${panelistDetailsHTML.replace(/"/g, '&quot;')}"
                                    data-pass-threshold="${passThreshold}"
                                    style="cursor: pointer;">
                                    <td class="text-center text-muted">${studentIdx + 1}</td>
                                    <td class="d-none d-md-table-cell"><small class="text-muted">${student.student_number || 'N/A'}</small></td>
                                    <td>${student.last_name}, ${student.first_name}</td>
                                    <td class="d-none d-lg-table-cell"><small class="text-muted">${student.team_name}</small></td>
                                    <td class="text-center text-dark">${groupScoreDisplay}</td>
                                    <td class="text-center text-dark">${soloScoreDisplay}</td>
                                    <td class="text-center ${totalScoreClass}">${totalScoreDisplay}</td>
                                    <td class="text-center">
                                        <span class="badge ${statusClass}">${status}</span>
                                    </td>
                                </tr>
                            `;
                        });
                        
                        content += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    // Close tab-content div
                    content += `</div>`;
                }
                
                classRecordContent.innerHTML = content;
            } else {
                classRecordContent.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading class record:', error);
            classRecordContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Failed to load class records. Please try again.
                </div>
            `;
        });
}

<?php endif; ?>

document.addEventListener("DOMContentLoaded", function() {
    // Use a home-specific localStorage key to avoid conflicts with the dashboard
    const activeTab = localStorage.getItem("homeActiveTab") || "overview";
    const savedOverviewViewMode = sessionStorage.getItem('homeOverviewViewMode') || 'dashboard';

    // Deactivate all tab-panes and nav-links
    const allTabPanes = document.querySelectorAll('.tab-pane');
    const allNavLinks = document.querySelectorAll('.nav-link');

    allTabPanes.forEach(pane => {
        pane.classList.remove("show", "active");
    });

    allNavLinks.forEach(link => {
        link.classList.remove("active");
    });

    // Activate the tab and its content (fall back to overview if stored tab doesn't exist)
    const activeTabPane = document.getElementById(activeTab);
    const activeNavLink = document.querySelector(`.nav-link[href="#${activeTab}"]`);
    const isOverview = !activeTabPane || activeTab === "overview";

    if (activeTabPane) {
        activeTabPane.classList.add("show", "active");
    } else {
        document.getElementById('overview').classList.add("show", "active");
    }
    if (activeNavLink) {
        activeNavLink.classList.add("active");
    } else {
        document.getElementById('overview-link').classList.add("active");
    }

    // Add event listener to tabs to update localStorage when clicked
    const tabs = document.querySelectorAll('#v-pills-tab .nav-link');
    tabs.forEach(tab => {
        tab.addEventListener('click', function(event) {
            // Store the ID of the clicked tab-pane (use currentTarget to get the link itself, not child elements)
            const href = event.currentTarget.getAttribute('href');
            if (href) {
                const clickedTabId = href.substring(1);
                localStorage.setItem('homeActiveTab', clickedTabId);
            }
        });
    });

    // Load team overview content when the overview tab is clicked
    document.getElementById('overview-link').addEventListener('click', function() {
        fetchTeamOverview();
    });

    // Load class record when the class record tab is clicked (faculty only)
    <?php if ($isSectionProfessor): ?>
    const classRecordLink = document.getElementById('class-record-link');
    if (classRecordLink) {
        classRecordLink.addEventListener('click', function() {
            window.classRecordCurrentPage = 1; // Initialize page 1
            loadClassRecord(1);
        });
    }
    
    // Academic year label click → open modal
    const ayLabel = document.getElementById('ayLabel');
    if (ayLabel) {
        ayLabel.addEventListener('click', openAcademicYearModal);
    }
    // Save academic year button
    const saveAyBtn = document.getElementById('saveAcademicYearBtn');
    if (saveAyBtn) {
        saveAyBtn.addEventListener('click', saveAcademicYear);
    }
    
    // Add event listener for view toggle
    const classRecordViewSelect = document.getElementById('classRecordViewSelect');
    if (classRecordViewSelect) {
        classRecordViewSelect.addEventListener('change', function() {
            window.classRecordCurrentPage = 1; // Reset to page 1 when changing view
            loadClassRecord(1);
        });
    }
    
    // Add event listener for team view pagination
    document.addEventListener('click', function(e) {
        if (e.target.closest('#team-view-pagination a.page-link')) {
            e.preventDefault();
            const link = e.target.closest('a.page-link');
            const page = parseInt(link.getAttribute('data-page'));
            if (page && page > 0) {
                window.classRecordCurrentPage = page;
                loadClassRecord(page);
            }
        }
    });
    
    // Add event listener for row clicks in class record
    document.addEventListener('click', function(e) {
        // Handle student row click in Class View
        if (e.target.closest('.class-record-row')) {
            const row = e.target.closest('.class-record-row');
            showStudentDetailsModal(row);
        }
    });
    
    // Function to show student details modal
    function showStudentDetailsModal(row) {
        const studentNumber = row.getAttribute('data-student-number');
        const lastName = row.getAttribute('data-last-name');
        const firstName = row.getAttribute('data-first-name');
        const teamName = row.getAttribute('data-team-name');
        const researchTitle = row.getAttribute('data-research-title');
        const groupScore = row.getAttribute('data-group-score');
        const soloScore = row.getAttribute('data-solo-score');
        const totalScore = row.getAttribute('data-total-score');
        const statusText = row.getAttribute('data-status-text');
        const statusClass = row.getAttribute('data-status-class');
        const panelistDetails = row.getAttribute('data-panelist-details');
        
        // Get dynamic thresholds from the row data
        const passThreshold = parseFloat(row.getAttribute('data-pass-threshold')) || 75;
        const warningThreshold = Math.max(passThreshold - 15, 60);
        
        // Score display helpers (using dynamic thresholds)
        const getScoreDisplay = (score, label) => {
            const scoreFloat = parseFloat(score);
            if (scoreFloat > 0) {
                const scoreClass = scoreFloat >= passThreshold ? 'text-success' : scoreFloat >= warningThreshold ? 'text-warning' : 'text-danger';
                return `<strong class="${scoreClass}">${scoreFloat.toFixed(2)}</strong>`;
            }
            return '<span class="text-muted">No grade yet</span>';
        };
        
        const modalHTML = `
            <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="studentDetailsModalLabel">
                                <i class="bi bi-person-circle me-2"></i>Student Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="student-details-card">
                                <div class="row mb-3">
                                    <div class="col-4 fw-semibold text-muted">Student No.:</div>
                                    <div class="col-8">${studentNumber || 'N/A'}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4 fw-semibold text-muted">Name:</div>
                                    <div class="col-8"><strong>${lastName}, ${firstName}</strong></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4 fw-semibold text-muted">Team:</div>
                                    <div class="col-8">${teamName}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4 fw-semibold text-muted">Research Title:</div>
                                    <div class="col-8"><em>${researchTitle}</em></div>
                                </div>
                                <hr>
                                <h6 class="mb-3"><i class="bi bi-clipboard-data me-2"></i>Evaluation Scores</h6>
                                <div class="row mb-3">
                                    <div class="col-4 fw-semibold text-muted">Group Score:</div>
                                    <div class="col-8">${getScoreDisplay(groupScore, 'Group')}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4 fw-semibold text-muted">Individual Score:</div>
                                    <div class="col-8">${getScoreDisplay(soloScore, 'Individual')}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-4 fw-semibold text-muted">Total Score:</div>
                                    <div class="col-8">${getScoreDisplay(totalScore, 'Total')}</div>
                                </div>
                                <div class="row mb-0">
                                    <div class="col-4 fw-semibold text-muted">Status:</div>
                                    <div class="col-8"><span class="badge ${statusClass}">${statusText}</span></div>
                                </div>
                                ${panelistDetails ? `
                                    <hr>
                                    <h6 class="mb-3"><i class="bi bi-people me-2"></i>Panelist Breakdown</h6>
                                    <div class="row mb-0">
                                        <div class="col-12"><small>${panelistDetails}</small></div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('studentDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Append new modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
        modal.show();
        
        // Remove modal from DOM after it's hidden
        document.getElementById('studentDetailsModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    <?php endif; ?>

    // Fetch team overview content on page load if the overview tab is active
    if (isOverview) {
        setTimeout(() => {
            fetchTeamOverview();
        }, 150);
    }

    // Load class record on page load if that tab is active
    <?php if ($isSectionProfessor): ?>
    if (activeTab === "class-record") {
        // Use setTimeout to ensure tab transition completes before loading content
        setTimeout(() => {
            loadClassRecord(1);
        }, 150);
    }
    <?php endif; ?>

    const applyOverviewViewMode = (mode, initializeCalendar = true) => {
        const isCalendarMode = mode === 'calendar';
        document.getElementById('dashboardView').style.display = isCalendarMode ? 'none' : 'block';
        document.getElementById('calendarView').style.display = isCalendarMode ? 'block' : 'none';

        const dashboardRadio = document.getElementById('dashboard-view');
        const calendarRadio = document.getElementById('calendar-view');
        if (dashboardRadio && calendarRadio) {
            dashboardRadio.checked = !isCalendarMode;
            calendarRadio.checked = isCalendarMode;
        }

        if (isCalendarMode && initializeCalendar) {
            setTimeout(() => {
                initializeOverviewCalendar();
            }, 100);
        }
    };

    // Restore Overview view mode on refresh (within current tab session)
    if (isOverview) {
        applyOverviewViewMode(savedOverviewViewMode, savedOverviewViewMode === 'calendar');
    }

    // Handle view mode switching between Dashboard and Calendar
    document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedMode = this.id === 'calendar-view' ? 'calendar' : 'dashboard';
            sessionStorage.setItem('homeOverviewViewMode', selectedMode);
            applyOverviewViewMode(selectedMode, selectedMode === 'calendar');
        });
    });
});

// Function to initialize calendar in the overview tab
function initializeOverviewCalendar() {
    const calendarEl = document.getElementById('calendar2');
    if (!calendarEl) return;

    // Clear any existing calendar
    calendarEl.innerHTML = '';

    // Initialize FullCalendar for the overview tab
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        events: [],
        eventClick: function(info) {
            const isDefenseEvent = info.event.extendedProps.eventType === 'defense';
            const scheduleId = parseInt(info.event.extendedProps.scheduleId, 10);

            if (!isDefenseEvent || Number.isNaN(scheduleId)) {
                return;
            }

            if (USER_TYPE === 2 || USER_TYPE === 0) {
                redirectToDecisionSupport(scheduleId);
            }
        },
        eventContent: function(info) {
            const isDefenseEvent = info.event.extendedProps.eventType === 'defense';
            if (!isDefenseEvent) {
                return true;
            }

            const room = info.event.extendedProps.location || 'TBA';
            const compactText = info.timeText ? `${info.timeText} • ${room}` : room;

            return {
                html: `<div class="calendar-defense-compact-text">${compactText}</div>`
            };
        },
        eventDidMount: function(info) {
            const isDefenseEvent = info.event.extendedProps.eventType === 'defense';
            if (!isDefenseEvent) {
                return;
            }

            info.el.classList.add('calendar-defense-compact-event');
            const teamName = info.event.extendedProps.teamName || info.event.title || 'Defense Team';
            info.el.setAttribute('title', teamName);
        },
        dateClick: function(info) {
            if (calendar.view.type === 'dayGridMonth') {
                calendar.changeView('timeGridDay');
            }
            calendar.gotoDate(info.dateStr);
        }
    });

    // Fetch and display events
    $.ajax({
        url: 'includes/get_user_schedule.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const events = [];

                response.defense_schedules.forEach(defense => {
                    events.push({
                        title: defense.description,
                        start: `${defense.date}T${defense.start_time}`,
                        end: `${defense.date}T${defense.end_time}`,
                        location: defense.room,
                        eventType: 'defense',
                        scheduleId: defense.defense_schedule_id,
                        teamName: defense.team_name
                    });
                });

                response.user_schedules.forEach(schedule => {
                    events.push({
                        title: schedule.description,
                        start: `${getNextDateForDay(schedule.date)}T${schedule.start_time}`,
                        end: `${getNextDateForDay(schedule.date)}T${schedule.end_time}`,
                        location: schedule.room,
                        eventType: 'user'
                    });
                });

                calendar.removeAllEvents();
                calendar.addEventSource(events);
            }
        }
    });

    calendar.render();
}

// Handle requirements team selector change
function handleRequirementsTeamChange() {
    const teamSelector = document.getElementById('requirementsTeamSelector');
    if (teamSelector) {
        teamSelector.addEventListener('change', function() {
            const teamId = this.value;
            updateRequirementsList(teamId);
        });
    }
}

// Update requirements list based on selected team
function updateRequirementsList(teamId) {
    const requirementsList = document.getElementById('requirementsList');
    if (!requirementsList) return;

    // Show loading state
    requirementsList.innerHTML = '<li class="list-group-item text-center"><em>Loading...</em></li>';

    // Fetch requirements for the selected team
    fetch(`includes/get_team_requirements.php?team_id=${teamId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let listHtml = '';
                if (data.requirements && data.requirements.length > 0) {
                    data.requirements.forEach(requirement => {
                        let statusClass = 'status-pending';
                        let statusText = 'Pending';

                        if (requirement.status === 'approved') {
                            statusClass = 'status-evaluated';
                            statusText = 'Approved';
                        } else if (requirement.status === 'submitted') {
                            statusClass = 'status-evaluated';
                            statusText = 'Submitted';
                        } else if (requirement.status === 'rejected') {
                            statusClass = 'status-rejected';
                            statusText = 'Rejected';
                        }

                        const dueDate = new Date(requirement.due_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });

                        listHtml += `
                                <li class="list-group-item overview-requirement-item">
                                    <div class="overview-requirement-content">
                                        <div class="overview-requirement-title">${requirement.name}</div>
                                        <span class="defense-status-pill overview-status-pill ${statusClass}">${statusText}</span>
                                        <small class="overview-requirement-due-date">Due: ${dueDate}</small>
                                    </div>
                                </li>
                            `;
                    });
                } else {
                    listHtml =
                        '<li class="list-group-item text-center text-muted"><em>No requirements found for this team</em></li>';
                }
                requirementsList.innerHTML = listHtml;
            } else {
                requirementsList.innerHTML =
                    '<li class="list-group-item text-center text-danger"><em>Error loading requirements</em></li>';
            }
        })
        .catch(error => {
            console.error('Error fetching requirements:', error);
            requirementsList.innerHTML =
                '<li class="list-group-item text-center text-danger"><em>Error loading requirements</em></li>';
        });
}

function initializeDefenseScheduleFilter() {
    const filterSelect = document.getElementById('defenseScheduleFilter');
    const defenseList = document.getElementById('defenseSchedulesList');
    if (!filterSelect || !defenseList) return;

    const defenseItems = Array.from(defenseList.querySelectorAll('.defense-item'));
    if (defenseItems.length === 0) return;

    const getDateValue = (item) => {
        const dateValue = item.dataset.defenseDate || '';
        const timestamp = Date.parse(`${dateValue}T00:00:00`);
        return Number.isNaN(timestamp) ? 0 : timestamp;
    };

    const getStatusPriority = (item) => {
        const status = item.dataset.defenseStatus || '';
        return status === 'status-evaluated' ? 1 : 0;
    };

    const sortItems = (items, oldestFirst = false) => {
        return [...items].sort((leftItem, rightItem) => {
            const leftDate = getDateValue(leftItem);
            const rightDate = getDateValue(rightItem);

            if (leftDate !== rightDate) {
                return oldestFirst ? leftDate - rightDate : rightDate - leftDate;
            }

            // Keep evaluated below pending/scheduled on same date
            return getStatusPriority(leftItem) - getStatusPriority(rightItem);
        });
    };

    const applyDefenseFilter = () => {
        const selectedFilter = filterSelect.value;
        let filteredItems = defenseItems;
        let oldestFirst = false;

        if (selectedFilter === 'past_recent') {
            filteredItems = defenseItems.filter(item => item.dataset.defenseIsPast === '1');
        } else if (selectedFilter === 'past_oldest') {
            filteredItems = defenseItems.filter(item => item.dataset.defenseIsPast === '1');
            oldestFirst = true;
        } else if (selectedFilter === 'status_evaluated') {
            filteredItems = defenseItems.filter(item => item.dataset.defenseStatus === 'status-evaluated');
        } else if (selectedFilter === 'status_pending') {
            filteredItems = defenseItems.filter(item => {
                const status = item.dataset.defenseStatus;
                return status === 'status-pending' || status === 'status-scheduled';
            });
        }

        const sortedItems = sortItems(filteredItems, oldestFirst);
        defenseList.innerHTML = '';

        if (sortedItems.length === 0) {
            defenseList.innerHTML = '<li class="list-group-item text-center text-muted"><em>No defense schedules found for this filter</em></li>';
            return;
        }

        sortedItems.forEach(item => defenseList.appendChild(item));
    };

    filterSelect.addEventListener('change', applyDefenseFilter);
    applyDefenseFilter();
}

// Initialize requirements team selector when document is ready
document.addEventListener("DOMContentLoaded", function() {
    handleRequirementsTeamChange();
    initializeDefenseScheduleFilter();
});
</script>
<main role="main" class="container-fluid p-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="row g-0" style="height: 100vh; overflow: hidden;">
                <div id="homeSidebarContainer">
                    <!-- User Profile Section moved to top -->
                    <div class="home-profile-header d-flex justify-content-between align-items-center">
                        <div class="home-profile-dropdown-container" id="homeProfileDropdownToggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="profile-container">
                                <?php if(isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                                    <img src="../assets/uploads/users/<?php echo $_SESSION['profile_image']; ?>" alt="<?php echo $_SESSION['username']; ?>">
                                <?php else: ?>
                                    <img src="../assets/images/sample-pic.png" alt="<?php echo $_SESSION['username']; ?>">
                                <?php endif; ?>
                                <div class="user-info">
                                    <p class="user-name"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></p>
                                    <p class="user-role"><?php 
                                        if ($_SESSION['usertype'] == 0) {
                                            if (isset($_SESSION['program_chair']) && (int)$_SESSION['program_chair'] === 1) {
                                                echo "Program Chair";
                                            } else {
                                                echo "Administrator";
                                            }
                                        } elseif ($_SESSION['usertype'] == 1) {
                                            echo "Student";
                                        } elseif ($_SESSION['usertype'] == 2) {
                                            echo "Faculty";
                                        } else {
                                            echo "User";
                                        }
                                    ?></p>
                                </div>
                            </div>
                            <!-- Profile Dropdown Menu -->
                            <ul class="dropdown-menu home-profile-dropdown-menu" aria-labelledby="homeProfileDropdownToggle">
                                <li><a class="dropdown-item" href="../profile"><i class="bi bi-person-circle me-2"></i>View Profile</a></li>
                                <li><a class="dropdown-item" href="../profile-edit"><i class="bi bi-pencil-square me-2"></i>Edit Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" id="homeLogoutBtn"><i class="bi bi-power me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                        <div class="home-toggle-button-container">
                            <button id="toggleHomeSidebar" class="btn btn-link">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Sidebar -->
                    <div class="home-sidebar">
                        <div class="nav flex-column nav-pills home-sidebar-nav" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">
                            <!-- Dashboard Overview -->
                            <div class="home-sidebar-section">
                                <div class="home-sidebar-category">
                                    Overview
                                </div>
                                <div class="home-sidebar-items">
                                    <a class="nav-link active mt-1" id="overview-link" data-bs-toggle="pill"
                                        href="#overview" role="tab" aria-controls="overview" aria-selected="true">
                                        <i class="bi bi-house me-2 hollow"></i>
                                        <i class="bi bi-house-fill me-2 filled"></i>
                                        <span class="nav-text">Overview</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Research Management -->
                            <div class="home-sidebar-section">
                                <div class="home-sidebar-category">
                                    Research Management
                                </div>
                                <div class="home-sidebar-items">
                                    <!-- <a class="nav-link" id="thesis-topic-link" data-bs-toggle="pill" 
                                        href="#thesis-topic" role="tab" aria-controls="thesis-topic"
                                        aria-selected="true" disabled>
                                        <i class="bi bi-lightbulb me-2 hollow"></i>
                                        <i class="bi bi-lightbulb-fill me-2 filled"></i>
                                        <span class="nav-text">Thesis Topic Decision (in conflict with panel suggestion)</span>
                                    </a> -->
                                    <a class="nav-link" id="research-title-link" data-bs-toggle="pill"
                                        href="#research-title" role="tab" aria-controls="research-title"
                                        aria-selected="false">
                                        <i class="bi bi-check-circle me-2 hollow"></i>
                                        <i class="bi bi-check-circle-fill me-2 filled"></i>
                                        <span class="nav-text">Research Title Acceptance</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Progress Tracking -->
                            <div class="home-sidebar-section">
                                <div class="home-sidebar-category">
                                    Progress Tracking
                                </div>
                                <div class="home-sidebar-items">
                                    <a class="nav-link" id="requirement-checker-link" data-bs-toggle="pill"
                                        href="#requirement-checker" role="tab" aria-controls="requirement-checker"
                                        aria-selected="false">
                                        <i class="bi bi-list-check me-2 hollow"></i>
                                        <i class="bi bi-list-check me-2 filled"></i>
                                        <span class="nav-text">Requirement Checker</span>
                                    </a>
                                    <?php if (in_array((int)$_SESSION['usertype'], [0, 1, 2], true)): ?>
                                    <a class="nav-link" id="research-evaluation-link" data-bs-toggle="pill"
                                        href="#research-evaluation" role="tab" aria-controls="research-evaluation"
                                        aria-selected="false">
                                        <i class="bi bi-chat-dots me-2 hollow"></i>
                                        <i class="bi bi-chat-dots-fill me-2 filled"></i>
                                        <span class="nav-text">Team Evaluations</span>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($isSectionProfessor): ?>
                                    <a class="nav-link" id="class-record-link" data-bs-toggle="pill"
                                        href="#class-record" role="tab" aria-controls="class-record"
                                        aria-selected="false">
                                        <i class="bi bi-table me-2 hollow"></i>
                                        <i class="bi bi-table me-2 filled"></i>
                                        <span class="nav-text">Class Record</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="homeMainContent">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade" id="thesis-topic" role="tabpanel"
                            aria-labelledby="thesis-topic-link">
                            <div class="home-sidebar-box">
                                <div class="d-flex align-items-center mb-4">
                                    <!-- <div class="feature-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-lightbulb text-primary fs-4"></i>
                            </div> -->
                                    <div>
                                        <h4 class="mb-1 feature-title">Thesis Topic Decision Tool</h4>
                                        <p class="text-muted mb-0">Discover trending research topics in your field of
                                            study</p>
                                    </div>
                                </div>

                                <div class="card border-0 mb-4">
                                    <div class="form-group">
                                        <label for="thesisField" class="form-label fw-semibold mb-2">Select your field
                                            of study:</label>
                                        <select id="thesisField" class="form-select form-select-lg shadow-sm">
                                            <option value="">Choose a field</option>
                                            <?php
                                    // Assuming $conn is your database connection object (e.g., PDO or mysqli)
                                    // Include your database connection file if necessary
                                    // require_once '../assets/setup/db.inc.php'; // Already included at the top of the file

                                    try {
                                        // Check if $pdo is initialized
                                        if (!isset($pdo)) {
                                            // Connection is likely handled elsewhere, or throw error
                                            throw new Exception("Database connection not available.");
                                        }

                                        $stmt = $pdo->query("SELECT college, name, specialization FROM programs ORDER BY college, name");
                                        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        $groupedPrograms = [];
                                        foreach ($programs as $program) {
                                            $groupedPrograms[$program['college']][] = $program;
                                        }

                                        foreach ($groupedPrograms as $college => $collegePrograms) {
                                            echo '<optgroup label="' . htmlspecialchars($college) . '">';
                                            foreach ($collegePrograms as $program) {
                                                // Use the program name as the base display text
                                                $displayText = htmlspecialchars($program['name']);
                                                // Use the program name as the default value
                                                $optionValue = htmlspecialchars($program['name']);

                                                // If there is a specialization, append it to the display text
                                                if (!empty($program['specialization'])) {
                                                    $displayText .= ' - ' . htmlspecialchars($program['specialization']) . '';
                                                }

                                                // Output the option tag
                                                echo '<option value="' . $optionValue . '">' . $displayText . '</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                    } catch (Exception $e) {
                                        // Log error or display a user-friendly message
                                        error_log("Error fetching programs: " . $e->getMessage());
                                        echo '<option value="" disabled>Error loading programs</option>';
                                    }
                                    ?>
                                        </select>
                                    </div>
                                </div>
                                <div id="topicsTable">
                                    <!-- The filtered topics table will be loaded here -->
                                </div>
                            </div>
                        </div>

                        <script>
                        document.getElementById('thesisField').addEventListener('change', function() {
                            let selectedField = this.value;

                            // Create an AJAX request
                            let xhr = new XMLHttpRequest();
                            xhr.open('POST', 'includes/get_topics.php', true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    // Update the table with the server response
                                    document.getElementById('topicsTable').innerHTML = xhr.responseText;

                                    // Add event listeners for responsive table interactions
                                    addTopicTableEventListeners();
                                }
                            };

                            // Send the selected field to the server
                            xhr.send('field=' + encodeURIComponent(selectedField));
                        });

                        // Function to add event listeners for table interactions
                        function addTopicTableEventListeners() {
                            // Add click listeners for desktop rows (hidden description column)
                            const topicRows = document.querySelectorAll('.topic-row');
                            topicRows.forEach(row => {
                                // Add mobile-clickable class for touch devices
                                if (window.innerWidth < 768) {
                                    row.classList.add('mobile-clickable');
                                }

                                row.addEventListener('click', function(e) {
                                    // Only trigger on mobile/tablet (when description column is hidden)
                                    if (window.innerWidth < 768) {
                                        e.preventDefault();
                                        const topic = this.getAttribute('data-topic');
                                        const description = this.getAttribute('data-description');
                                        showTopicModal(topic, description);
                                    }
                                });

                                // Add touch feedback for mobile
                                row.addEventListener('touchstart', function(e) {
                                    if (window.innerWidth < 768) {
                                        this.style.backgroundColor = 'var(--primary-100, #cce7ff)';
                                    }
                                });

                                row.addEventListener('touchend', function(e) {
                                    if (window.innerWidth < 768) {
                                        setTimeout(() => {
                                            this.style.backgroundColor = '';
                                        }, 150);
                                    }
                                });
                            });

                            // Add click listeners for view buttons (mobile only)
                            const viewButtons = document.querySelectorAll('.view-topic-btn');
                            viewButtons.forEach(button => {
                                button.addEventListener('click', function(e) {
                                    e.stopPropagation(); // Prevent row click
                                    const topic = this.getAttribute('data-topic');
                                    const description = this.getAttribute('data-description');
                                    showTopicModal(topic, description);
                                });
                            });

                            // Handle window resize to update mobile state
                            window.addEventListener('resize', function() {
                                topicRows.forEach(row => {
                                    if (window.innerWidth < 768) {
                                        row.classList.add('mobile-clickable');
                                    } else {
                                        row.classList.remove('mobile-clickable');
                                        row.style.backgroundColor = '';
                                    }
                                });
                            });
                        }

                        // Function to show topic modal
                        function showTopicModal(topic, description) {
                            document.getElementById('topicModalLabel').textContent = topic;
                            document.getElementById('topicModalDescription').textContent = description;

                            const modal = new bootstrap.Modal(document.getElementById('topicModal'), {
                                backdrop: true,
                                keyboard: true
                            });
                            modal.show();
                        }

                        // Function to show evaluation modal
                        function showEvaluationModal(row) {
                            const teamName = row.getAttribute('data-team');
                            const researchTitle = row.getAttribute('data-title');
                            const studentName = row.getAttribute('data-student');
                            const evaluatorName = row.getAttribute('data-evaluator');
                            const comments = row.getAttribute('data-comments');
                            const score = row.getAttribute('data-score');

                            document.getElementById('evalModalTeam').textContent = teamName;
                            document.getElementById('evalModalTitle').textContent = researchTitle;
                            document.getElementById('evalModalEvaluator').textContent = evaluatorName;
                            document.getElementById('evalModalComments').textContent = comments;
                            document.getElementById('evalModalScore').textContent = score;

                            // Show/hide student section based on user type
                            const studentSection = document.getElementById('evalModalStudentSection');
                            if (studentName && studentName !== 'null') {
                                document.getElementById('evalModalStudent').textContent = studentName;
                                studentSection.style.display = 'block';
                            } else {
                                studentSection.style.display = 'none';
                            }

                            const modal = new bootstrap.Modal(document.getElementById('evaluationModal'), {
                                backdrop: true,
                                keyboard: true
                            });
                            modal.show();
                        }

                        // Add click handlers for evaluation table rows on mobile
                        document.addEventListener('DOMContentLoaded', function() {
                            function addEvaluationTableEventListeners() {
                                const evaluationRows = document.querySelectorAll('.evaluation-row');
                                evaluationRows.forEach(row => {
                                    // Add mobile-clickable class for touch devices
                                    if (window.innerWidth < 992) { // lg breakpoint
                                        row.classList.add('mobile-clickable');
                                    } else {
                                        row.classList.remove('mobile-clickable');
                                    }

                                    // Remove existing listeners
                                    row.removeEventListener('click', handleEvaluationRowClick);

                                    // Add click listener for mobile screens
                                    if (window.innerWidth < 992) {
                                        row.addEventListener('click', handleEvaluationRowClick);
                                    }

                                    // Add touch feedback for mobile
                                    row.addEventListener('touchstart', function(e) {
                                        if (window.innerWidth < 992) {
                                            this.classList.add('mobile-touching');
                                        }
                                    });

                                    row.addEventListener('touchend', function(e) {
                                        if (window.innerWidth < 992) {
                                            this.classList.remove('mobile-touching');
                                        }
                                    });
                                });
                            }

                            function handleEvaluationRowClick(e) {
                                if (window.innerWidth < 992) { // Only on mobile/tablet
                                    e.preventDefault();
                                    showEvaluationModal(this);
                                }
                            }

                            // Initialize table event listeners
                            addEvaluationTableEventListeners();

                            // Handle window resize to update mobile state
                            window.addEventListener('resize', function() {
                                addEvaluationTableEventListeners();
                            });
                        });
                        </script>

                        <!-- Topic Details Modal -->
                        <div class="modal fade" id="topicModal" tabindex="-1" aria-labelledby="topicModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="topicModalLabel">Topic Title</h5>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">Description</h6>
                                            <p id="topicModalDescription" class="mb-0">Topic description will appear
                                                here...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Research Evaluation Details Modal -->
                        <div class="modal fade" id="evaluationModal" tabindex="-1"
                            aria-labelledby="evaluationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="evaluationModalLabel">Evaluation Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Team Name</h6>
                                                <p id="evalModalTeam" class="mb-0 fw-semibold"></p>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Research Title</h6>
                                                <p id="evalModalTitle" class="mb-0"></p>
                                            </div>
                                            <div class="col-12" id="evalModalStudentSection" style="display: none;">
                                                <h6 class="text-muted mb-2">Student Name</h6>
                                                <p id="evalModalStudent" class="mb-0"></p>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Evaluator</h6>
                                                <p id="evalModalEvaluator" class="mb-0"></p>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Total Score</h6>
                                                <p id="evalModalScore" class="mb-0 fw-bold text-primary"></p>
                                            </div>
                                            <div class="col-12">
                                                <h6 class="text-muted mb-2">Comments</h6>
                                                <div id="evalModalComments" class="bg-light p-3 rounded"
                                                    style="min-height: 100px; white-space: pre-line;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="research-title" role="tabpanel"
                            aria-labelledby="research-title-link">
                            <div class="home-sidebar-box">
                                <div class="d-flex align-items-center mb-4">
                                    <!-- <div class="feature-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-check-circle text-primary fs-4"></i>
                            </div> -->
                                    <div>
                                        <h4 class="mb-1 feature-title">Research Title Acceptance Tool</h4>
                                        <p class="text-muted mb-0">Check the uniqueness of your research title and get
                                            AI-powered suggestions</p>
                                    </div>
                                </div>

                                <!-- Research Title Input Form -->
                                <div class="card research-title-form-card mb-4">
                                    <div class="card-body">
                                        <form id="titleSubmissionForm" class="needs-validation">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label for="researchTitle" class="form-label fw-semibold mb-0">Proposed
                                                        Research Title</label>
                                                    <button type="button" id="resetFieldsBtn" class="btn btn-outline-secondary btn-sm rounded-circle" 
                                                            title="Clear all fields" style="width: 32px; height: 32px; padding: 0;">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                </div>
                                                <textarea class="form-control research-title-textarea"
                                                    id="researchTitle" name="researchTitle"
                                                    placeholder="Enter your research title here..." rows="3"
                                                    required></textarea>
                                                <div class="invalid-feedback"></div>
                                                <div class="form-text">Be specific and descriptive about your research
                                                    focus</div>
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <label for="researchField" class="form-label fw-semibold">Research
                                                        Field</label>
                                                    <input type="text" class="form-control research-title-input"
                                                        id="researchField" name="researchField"
                                                        placeholder="e.g., Computer Science" required>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label for="problem" class="form-label fw-semibold">Problem
                                                        Statement</label>
                                                    <input type="text" class="form-control research-title-input"
                                                        id="problem" name="problem"
                                                        placeholder="Brief description of the problem" required>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-4">
                                                <div class="research-title-status">
                                                    <small class="text-muted">Fill in all fields to analyze your
                                                        title</small>
                                                </div>
                                                <button type="button" id="submitTitleBtn"
                                                    class="btn btn-primary research-title-btn">
                                                    <i class="bi bi-search me-2"></i>Analyze Title
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Results Section -->
                                <div class="research-title-results">
                                    <!-- Uniqueness Analysis Card -->
                                    <div class="card research-title-result-card mb-3" id="uniquenessCard"
                                        style="display: none;">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-shield-check text-primary me-2"></i>
                                                <h6 class="mb-0">Uniqueness Analysis</h6>
                                            </div>
                                        </div>
                                        <div class="card-body" id="uniquenessResult">
                                            <!-- Uniqueness results will be inserted here -->
                                        </div>
                                    </div>

                                    <!-- AI Suggestions Card -->
                                    <div class="card research-title-result-card mb-3" id="suggestionsCard"
                                        style="display: none;">
                                        <div class="card-header">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                                <h6 class="mb-0">AI-Powered Suggestions</h6>
                                            </div>
                                        </div>
                                        <div class="card-body" id="aiSuggestions">
                                            <!-- AI suggestions will be inserted here -->
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    <div class="research-title-empty-state" id="emptyState">
                                        <div class="text-center py-5">
                                            <div class="empty-state-icon mb-3">
                                                <i class="bi bi-clipboard2-check"></i>
                                            </div>
                                            <h5 class="text-muted mb-2">Ready to Analyze Your Research Title</h5>
                                            <p class="text-muted mb-0">
                                                Complete the form above to get AI-powered analysis on title uniqueness,
                                                clarity, and receive suggestions for improvement.
                                            </p>
                                            <div class="mt-4">
                                                <div class="row g-3 text-start">
                                                    <div class="col-md-4">
                                                        <div class="d-flex align-items-start">
                                                            <i class="bi bi-1-circle text-primary me-2 mt-1"></i>
                                                            <div>
                                                                <small class="fw-semibold">Uniqueness Check</small>
                                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                                    Compare against existing research titles
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex align-items-start">
                                                            <i class="bi bi-2-circle text-primary me-2 mt-1"></i>
                                                            <div>
                                                                <small class="fw-semibold">Quality Analysis</small>
                                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                                    Evaluate clarity and specificity
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="d-flex align-items-start">
                                                            <i class="bi bi-3-circle text-primary me-2 mt-1"></i>
                                                            <div>
                                                                <small class="fw-semibold">AI Suggestions</small>
                                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                                    Get recommendations for improvement
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="requirement-checker" role="tabpanel"
                            aria-labelledby="requirement-checker-link">
                            <div class="home-sidebar-box">
                                <div class="d-flex align-items-center mb-4">
                                    <!-- <div class="feature-icon bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-tasks text-primary fs-4"></i>
                            </div> -->
                                    <div>
                                        <h4 class="mb-1 feature-title">Requirement Checker Tool</h4>
                                        <p class="text-muted mb-0">Track and manage your thesis requirements and
                                            submissions</p>
                                    </div>
                                </div>

                                <div class="media text-muted pt-3">
                                    <div id="teamSelectorContainer" class="mb-4">
                                        <!-- The dropdown will be dynamically inserted here -->
                                    </div>
                                    <div id="requirementChecklist" class="row g-4 g-lg-5">
                                        <!-- Checklist items will be dynamically added here in a grid -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="research-evaluation" role="tabpanel"
                            aria-labelledby="research-evaluation-link">
                            <div class="home-sidebar-box">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <h4 class="mb-1 feature-title">Team Evaluations</h4>
                                        <p class="text-muted mb-0" id="evaluations-subtitle">
                                            <?php 
                                            if ($_SESSION['usertype'] == 1) {
                                                echo "View your team's evaluation results and feedback";
                                            } elseif ($_SESSION['usertype'] == 0) {
                                                echo "View evaluations for teams you advise";
                                            } elseif ($_SESSION['usertype'] == 2) {
                                                echo "View evaluations for teams you advise";
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Loading State -->
                                <div id="evaluations-loading" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-3 text-muted">Loading evaluations...</p>
                                </div>

                                <!-- Table Container -->
                                <div id="evaluations-content" style="display: none;">
                                    <div class="table-responsive">
                                        <table class="table db-table" id="evaluationsTable">
                                            <thead>
                                                <tr>
                                                    <th>Team Name</th>
                                                    <th class="d-none d-md-table-cell">Research Title</th>
                                                    <th class="d-none d-lg-table-cell">Program</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="evaluations-table-body">
                                                <!-- Populated via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <nav aria-label="Evaluations pagination" id="evaluations-pagination-container">
                                        <ul class="pagination justify-content-center" id="evaluations-pagination">
                                            <!-- Populated via JavaScript -->
                                        </ul>
                                    </nav>
                                </div>

                                <!-- Empty State -->
                                <div id="evaluations-empty" class="text-center py-5" style="display: none;">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No evaluations available yet.</p>
                                </div>
                            </div>
                        </div>

                        <?php if ($isSectionProfessor): ?>
                        <div class="tab-pane fade" id="class-record" role="tabpanel"
                            aria-labelledby="class-record-link">
                            <div class="container-fluid py-4 content-container">
                                <div class="row mb-4">
                                    <div class="col-12 d-flex justify-content-between align-items-start">
                                        <div>
                                            <h3 class="mb-2">Class Record</h3>
                                            <p class="text-muted">Student grades grouped by section, sorted alphabetically</p>
                                        </div>
                                        <div id="academicYearDisplay" class="text-end pt-1">
                                            <button type="button" id="ayLabel" class="btn-ay"
                                                    title="Click to set academic year">
                                                Academic year not set
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Class Record Controls -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="user-controls-container p-0 mt-3 mb-3">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-12 col-md-3 col-lg-2">
                                                    <!-- View Toggle Dropdown -->
                                                    <select class="form-select user-control-height" id="classRecordViewSelect">
                                                        <option value="team">Team View</option>
                                                        <option value="class" selected>Class View</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="col-12 col-md-4 col-lg-3" id="classRecordSortContainer" style="display: none;">
                                                    <!-- Sort Dropdown (only for Class View) -->
                                                    <select class="form-select user-control-height" id="classRecordSortSelect">
                                                        <option value="last_name:asc">Last Name (A-Z)</option>
                                                        <option value="last_name:desc">Last Name (Z-A)</option>
                                                        <option value="team_name:asc">Team (A-Z)</option>
                                                        <option value="team_name:desc">Team (Z-A)</option>
                                                        <option value="total_score:desc">Total Score (High to Low)</option>
                                                        <option value="total_score:asc">Total Score (Low to High)</option>
                                                        <option value="status:asc">Status (Passed First)</option>
                                                        <option value="status:desc">Status (Failed First)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="classRecordContent">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-3 text-muted">Loading class records...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Year Modal -->
                        <div class="modal fade" id="academicYearModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="ayModalTitle">Set Academic Year</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="academicYearForm">
                                            <div class="mb-3">
                                                <label for="ayStartYear" class="form-label">Start Year</label>
                                                <select class="form-select" id="ayStartYear" required>
                                                    <!-- Populated by JS -->
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="ayEndYear" class="form-label">End Year</label>
                                                <input type="text" class="form-control" id="ayEndYear" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label for="aySemester" class="form-label">Semester</label>
                                                <select class="form-select" id="aySemester" required>
                                                    <option value="1st Semester">1st Semester</option>
                                                    <option value="2nd Semester">2nd Semester</option>
                                                    <option value="Summer">Summer</option>
                                                </select>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="saveAcademicYearBtn">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="tab-pane fade show active" id="overview" role="tabpanel"
                            aria-labelledby="overview-link">
                            <div class="container-fluid py-4 content-container team-overview">
                                <!-- Header with title and sub-tabs -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <h3 class="mb-2">Dashboard</h3>
                                        <p class="text-muted">Track your requirement progress and defense schedule</p>
                                        <?php if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0): ?>
                                        <div class="my-2">
                                            <a href="#requirement-checker" class="tab-redirect-link" onclick="document.getElementById('requirement-checker-link').click(); return false;">
                                                <i class="bi bi-list-check"></i>
                                                <span>View Team requirements</span>
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="btn-group z-0" role="group">
                                            <input type="radio" class="btn-check" name="viewMode" id="dashboard-view"
                                                checked>
                                            <label class="btn btn-outline-primary" for="dashboard-view">
                                                Dashboard
                                            </label>

                                            <input type="radio" class="btn-check" name="viewMode" id="calendar-view">
                                            <label class="btn btn-outline-primary" for="calendar-view">
                                                Calendar
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dashboard View (Default) -->
                                <div id="dashboardView">
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="teamOverviewContent">
                                                <!-- Team overview content will be loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Calendar View (Hidden by default) -->
                                <div id="calendarView" style="display: none;">
                                    <div class="row">
                                        <div class="col-12 col-lg-9 mb-3">
                                            <div class="calendar-container p-3">
                                                <!-- Calendar Div -->
                                                <div id="calendar2"></div>
                                            </div>
                                        </div>
                                        <?php if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 1 || $_SESSION['usertype'] == 0): ?>
                                        <?php
                                    // Fetch teams for current user
                                    $userId = $_SESSION['id'];
                                    $userType = $_SESSION['usertype'];
                                    
                                    if ($userType == 1) { // Student
                                        $teamsStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ?");
                                        $teamsStmt->execute([$userId]);
                                        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    } else { // Staff/Adviser
                                        $teamsStmt = $pdo->prepare("SELECT t.id, t.name FROM team_members tm JOIN teams t ON tm.team_id = t.id WHERE tm.user_id = ? AND tm.role IN ('adviser', 'panelist')");
                                        $teamsStmt->execute([$userId]);
                                        $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    }
                                    
                                    // Use first team as default if available
                                    $selectedTeamId = !empty($teams) ? $teams[0]['id'] : null;
                                    
                                    // Fetch requirements for selected team
                                    $requirements = [];
                                    if ($selectedTeamId) {
                                        $requirementsStmt = $pdo->prepare("
                                            SELECT r.id, r.name, r.description, r.due_date,
                                                   COALESCE(tr.status, 'pending') as status,
                                                   tr.submitted_at, tr.feedback
                                            FROM requirements r 
                                            LEFT JOIN team_requirements tr ON r.id = tr.requirement_id AND tr.team_id = ?
                                            ORDER BY r.due_date ASC, r.name ASC
                                        ");
                                        $requirementsStmt->execute([$selectedTeamId]);
                                        $requirements = $requirementsStmt->fetchAll(PDO::FETCH_ASSOC);
                                    }
                                    ?>
                                        <div class="requirements-list col-12 col-lg-3">
                                            <div class="accordion custom-accordion" id="requirementsAccordion2">
                                                <!-- Requirements Section -->
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="headingRequirements2">
                                                        <button class="accordion-button custom-accordion-btn"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapseRequirements2" aria-expanded="true"
                                                            aria-controls="collapseRequirements2">
                                                            Requirements
                                                        </button>
                                                    </h2>
                                                    <div id="collapseRequirements2"
                                                        class="accordion-collapse collapse show"
                                                        aria-labelledby="headingRequirements2"
                                                        data-bs-parent="#requirementsAccordion2">
                                                        <div class="accordion-body custom-scrollbar">
                                                            <!-- Team Selector (only for professors) -->
                                                            <?php if (($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0) && count($teams) > 1): ?>
                                                            <div class="mb-3 requirements-team-selector-wrap">
                                                                <label for="requirementsTeamSelector"
                                                                    class="form-label small text-muted">Select
                                                                    Team:</label>
                                                                <select id="requirementsTeamSelector"
                                                                    class="form-select form-select-sm">
                                                                    <?php foreach ($teams as $team): ?>
                                                                    <option value="<?php echo $team['id']; ?>"
                                                                        <?php echo ($team['id'] == $selectedTeamId) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($team['name']); ?>
                                                                    </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <?php elseif (($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0) && count($teams) == 1): ?>
                                                            <div class="mb-3 requirements-team-selector-wrap">
                                                                <p class="small text-muted mb-2">Team:
                                                                    <strong><?php echo htmlspecialchars($teams[0]['name']); ?></strong>
                                                                </p>
                                                            </div>
                                                            <?php endif; ?>

                                                            <!-- Requirements List -->
                                                            <ul class="list-group" id="requirementsList">
                                                                <?php if (empty($teams)): ?>
                                                                <li class="list-group-item text-center text-muted">
                                                                    <em>No teams assigned to you</em>
                                                                </li>
                                                                <?php elseif (!empty($requirements)): ?>
                                                                <?php foreach ($requirements as $requirement): ?>
                                                                <li
                                                                    class="list-group-item overview-requirement-item">
                                                                    <div class="overview-requirement-content">
                                                                        <div class="overview-requirement-title"><?php echo htmlspecialchars($requirement['name']); ?></div>
                                                                    <?php
                                                                        $statusClass = 'status-pending';
                                                                        $statusText = 'Pending';
                                                                        if ($requirement['status'] === 'approved') {
                                                                            $statusClass = 'status-evaluated';
                                                                            $statusText = 'Approved';
                                                                        } elseif ($requirement['status'] === 'submitted') {
                                                                            $statusClass = 'status-evaluated';
                                                                            $statusText = 'Submitted';
                                                                        } elseif ($requirement['status'] === 'rejected') {
                                                                            $statusClass = 'status-rejected';
                                                                            $statusText = 'Rejected';
                                                                        }
                                                                        ?>
                                                                        <span class="defense-status-pill overview-status-pill <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                                        <small class="overview-requirement-due-date">Due:
                                                                            <?php echo date('M d, Y', strtotime($requirement['due_date'])); ?></small>
                                                                    </div>
                                                                </li>
                                                                <?php endforeach; ?>
                                                                <?php else: ?>
                                                                <li class="list-group-item text-center text-muted">
                                                                    <em>No requirements found for this team</em>
                                                                </li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                            // Defense schedules for both faculty and students
                                            $userId = $_SESSION['id'];
                                            $userType = $_SESSION['usertype'];
                                            
                                            if ($userType == 1) { // Student
                                                $query = "SELECT
                                                            ds.id AS schedule_id,
                                                            ds.schedule_date,
                                                            ds.start_time,
                                                            ds.end_time,
                                                            ds.room,
                                                            t.name AS team_name,
                                                            t.program AS team_program
                                                        FROM defense_schedules ds
                                                        JOIN teams t ON ds.team_id = t.id
                                                        JOIN team_members tm ON t.id = tm.team_id
                                                        WHERE tm.user_id = :user_id
                                                        AND ds.approval_status = 'approved'
                                                        ORDER BY ds.schedule_date, ds.start_time";
                                                $stmt = $pdo->prepare($query);
                                                $stmt->execute(['user_id' => $userId]);
                                            } else { // Faculty
                                                $query = "SELECT
                                                            ds.id AS schedule_id,
                                                            ds.schedule_date,
                                                            ds.start_time,
                                                            ds.end_time,
                                                            ds.room,
                                                            ds.defense_type,
                                                            t.name AS team_name,
                                                            t.program AS team_program,
                                                            (SELECT COUNT(*) FROM evaluation_per_panel epp WHERE epp.defense_schedule_id = ds.id AND epp.evaluator_id = :eval_check_id) as has_evaluated,
                                                            CASE 
                                                                WHEN CONCAT(ds.schedule_date, ' ', ds.end_time) < NOW() THEN 'past'
                                                                WHEN CONCAT(ds.schedule_date, ' ', ds.start_time) <= NOW() AND CONCAT(ds.schedule_date, ' ', ds.end_time) >= NOW() THEN 'ongoing'
                                                                ELSE 'upcoming'
                                                            END as defense_status
                                                        FROM defense_schedules ds
                                                        JOIN teams t ON ds.team_id = t.id
                                                        WHERE
                                                            (ds.panelist_id = :user_id1
                                                            OR ds.panelist_id2 = :user_id2
                                                            OR ds.panelist_id3 = :user_id3)
                                                            AND ds.approval_status = 'approved'
                                                        ORDER BY
                                                            CASE 
                                                                WHEN CONCAT(ds.schedule_date, ' ', ds.end_time) < NOW() THEN 3
                                                                WHEN CONCAT(ds.schedule_date, ' ', ds.start_time) <= NOW() AND CONCAT(ds.schedule_date, ' ', ds.end_time) >= NOW() THEN 1
                                                                ELSE 2
                                                            END,
                                                            ds.schedule_date, ds.start_time";
                                                $stmt = $pdo->prepare($query);
                                                $stmt->execute([
                                                    'user_id1' => $userId,
                                                    'user_id2' => $userId,
                                                    'user_id3' => $userId,
                                                    'eval_check_id' => $userId
                                                ]);
                                            }
                                            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                                <div class="accordion-item mt-2">
                                                    <h2 class="accordion-header" id="headingDefenses2">
                                                        <button class="accordion-button custom-accordion-btn collapsed"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#collapseDefenses2" aria-expanded="false"
                                                            aria-controls="collapseDefenses2">
                                                            <?php echo ($_SESSION['usertype'] == 1) ? 'Your Defense Schedules' : 'Defense Schedules'; ?>
                                                        </button>
                                                    </h2>
                                                    <div id="collapseDefenses2" class="accordion-collapse collapse"
                                                        aria-labelledby="headingDefenses2"
                                                        data-bs-parent="#requirementsAccordion2">
                                                        <div class="accordion-body custom-scrollbar">
                                                            <div class="mb-3 defense-filter-wrap">
                                                                <label for="defenseScheduleFilter" class="form-label small text-muted">Filter / Sort:</label>
                                                                <select id="defenseScheduleFilter" class="form-select form-select-sm">
                                                                    <option value="all_recent" selected>All Defenses (Most Recent)</option>
                                                                    <option value="past_recent">Past Defenses (Most Recent)</option>
                                                                    <option value="past_oldest">Past Defenses (Oldest)</option>
                                                                    <option value="status_evaluated">Status: Evaluated</option>
                                                                    <option value="status_pending">Status: Pending</option>
                                                                </select>
                                                            </div>
                                                            <ul class="list-group" id="defenseSchedulesList">
                                                                <?php 
                                                                foreach ($schedules as $schedule):
                                                                $formatted_date = date('F j, Y', strtotime($schedule['schedule_date']));
                                                                $formatted_start_time = date('g:i a', strtotime($schedule['start_time']));
                                                                $formatted_end_time = date('g:i a', strtotime($schedule['end_time']));
                                                                $schedule_id = $schedule['schedule_id'];
                                                                
                                                                $defense_status = $schedule['defense_status'] ?? 'upcoming';
                                                                $has_evaluated = isset($schedule['has_evaluated']) ? $schedule['has_evaluated'] > 0 : false;
                                                                $defense_type = $schedule['defense_type'] ?? 'general';

                                                                $onclick_attr = '';
                                                                $item_class = 'list-group-item defense-item';
                                                                
                                                                // Defense type pill
                                                                $type_badge = '';
                                                                $type_class = 'type-general';
                                                                switch ($defense_type) {
                                                                    case 'title_proposal':
                                                                        $type_class = 'type-proposal';
                                                                        $type_badge = 'Title Proposal';
                                                                        break;
                                                                    case 'title_defense':
                                                                        $type_class = 'type-title';
                                                                        $type_badge = 'Title Defense';
                                                                        break;
                                                                    case 'final_defense':
                                                                        $type_class = 'type-final';
                                                                        $type_badge = 'Final Defense';
                                                                        break;
                                                                    case 're-defense':
                                                                        $type_class = 'type-redefense';
                                                                        $type_badge = 'Re-Defense';
                                                                        break;
                                                                    default:
                                                                        $type_badge = ucfirst($defense_type);
                                                                }

                                                                // Defense status pill
                                                                $status_badge = 'Scheduled';
                                                                $status_class = 'status-scheduled';
                                                                if (($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0) && $has_evaluated) {
                                                                    $status_badge = 'Evaluated';
                                                                    $status_class = 'status-evaluated';
                                                                } elseif (($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0) && $defense_status !== 'upcoming') {
                                                                    $status_badge = 'Pending';
                                                                    $status_class = 'status-pending';
                                                                }
                                                                
                                                                // Only allow faculty to access evaluation system
                                                                if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0) { // Faculty or Program Chair
                                                                    // For past defenses, make read-only if already evaluated
                                                                    if ($defense_status === 'past' && $has_evaluated) {
                                                                        $onclick_attr = 'onclick="redirectToDecisionSupport(' . $schedule_id . ', true)"';
                                                                        $item_class .= ' defense-item-completed';
                                                                    } else {
                                                                        $onclick_attr = 'onclick="redirectToDecisionSupport(' . $schedule_id . ')"';
                                                                    }
                                                                } else { // Student
                                                                    // Students can view but not evaluate
                                                                    $item_class .= ' defense-item-student';
                                                                }
                                                            ?>
                                                                <li class="<?php echo $item_class; ?>"
                                                                    data-defense-date="<?php echo htmlspecialchars($schedule['schedule_date']); ?>"
                                                                    data-defense-status="<?php echo htmlspecialchars($status_class); ?>"
                                                                    data-defense-is-past="<?php echo ($defense_status === 'past') ? '1' : '0'; ?>"
                                                                    <?php echo $onclick_attr; ?>>
                                                                    <div class="defense-content overview-defense-content">
                                                                        <h6 class="overview-defense-team-name mb-0">
                                                                            <?php echo htmlspecialchars($schedule['team_name']); ?>
                                                                        </h6>
                                                                        <div class="overview-defense-pill-row">
                                                                            <span class="defense-type-pill <?php echo $type_class; ?>"><?php echo $type_badge; ?></span>
                                                                            <span class="defense-status-pill overview-status-pill <?php echo $status_class; ?>"><?php echo $status_badge; ?></span>
                                                                        </div>
                                                                        <div class="overview-defense-meta">
                                                                            <div><strong>Date:</strong> <?php echo htmlspecialchars($formatted_date); ?></div>
                                                                            <div><strong>Time:</strong> <?php echo htmlspecialchars($formatted_start_time . " - " . $formatted_end_time); ?></div>
                                                                            <div><strong>Room:</strong> <?php echo htmlspecialchars($schedule['room']); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Defense Approval Modal -->
<div class="modal fade" id="defenseApprovalModal" tabindex="-1" aria-labelledby="defenseApprovalModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="defenseApprovalModalLabel">
                    <i class="bi bi-calendar-check me-2"></i>Defense Schedule Approval Required
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="approvalModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading approval request...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 3rem; color: var(--main-black); margin-bottom: 1rem;">
                    <i class="fas fa-door-open"></i>
                </div>
                <h4 class="fw-bold mb-3" id="logoutConfirmModalLabel">Confirm Logout</h4>
                <p>Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../logout/" class="btn btn-danger" id="confirmLogout">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Revert Submission Confirmation Modal -->
<div class="modal fade" id="revertConfirmModal" tabindex="-1" aria-labelledby="revertConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 3rem; color: var(--bs-warning); margin-bottom: 1rem;">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>
                <h4 class="fw-bold mb-3" id="revertConfirmModalLabel">Confirm Revert Submission</h4>
                <p>Are you sure you want to revert the submission for <span id="revertRequirementName" class="fw-bold"></span>?</p>
                <p class="text-muted small">This will allow the team to resubmit their work.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmRevert">
                    Revert Submission
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete File Confirmation Modal -->
<div class="modal fade" id="deleteFileConfirmModal" tabindex="-1" aria-labelledby="deleteFileConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 3rem; color: var(--bs-danger); margin-bottom: 1rem;">
                    <i class="bi bi-trash"></i>
                </div>
                <h4 class="fw-bold mb-3" id="deleteFileConfirmModalLabel">Confirm File Deletion</h4>
                <p>Are you sure you want to remove the current file for <span id="deleteRequirementName" class="fw-bold"></span>?</p>
                <p class="text-muted small">This action cannot be undone and the file will be permanently removed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFile">
                    Delete File
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Feedback Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="feedbackModalContent" style="white-space: pre-wrap; line-height: 1.6; color: var(--neutral-800);"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<?php
include '../assets/layouts/footer.php'
?>

<!-- Home Page Sidebar Toggle Script -->
<script>
// ==========================================
// COLLAPSIBLE SIDEBAR FUNCTIONALITY - HOME PAGE (Fixed)
// ==========================================
$(document).ready(function() {
    console.log('Home page JavaScript initializing...');
    
    // IMPORTANT FIX: Replicate exact dashboard functionality
    // Remove all click handlers from the toggle button first
    $('#toggleHomeSidebar').off('click');
    
    // Add a single click handler matching dashboard pattern exactly
    $('#toggleHomeSidebar').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent event bubbling
        
        console.log('Home sidebar toggle clicked!');
        
        $('#homeSidebarContainer').toggleClass('collapsed');
        $('#homeMainContent').toggleClass('expanded');
        $(this).toggleClass('collapsed');
        
        // Update icon rotation
        if ($('#homeSidebarContainer').hasClass('collapsed')) {
            $(this).find('i').css('transform', 'rotate(180deg)');
            $('body').addClass('has-collapsed-home-sidebar');
        } else {
            $(this).find('i').css('transform', 'rotate(0deg)');
            $('body').removeClass('has-collapsed-home-sidebar');
        }
        
        // Save state to localStorage
        localStorage.setItem('homeSidebarCollapsed', $('#homeSidebarContainer').hasClass('collapsed'));
    });
    
    // Check localStorage for saved sidebar state on page load
    const sidebarCollapsed = localStorage.getItem('homeSidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        $('#homeSidebarContainer').addClass('collapsed');
        $('#homeMainContent').addClass('expanded');
        $('#toggleHomeSidebar').addClass('collapsed');
        $('#toggleHomeSidebar').find('i').css('transform', 'rotate(180deg)');
    }
    
    // Handle window resize for responsive behavior
    $(window).on('resize', function() {
        if (window.innerWidth <= 768) {
            $('#homeMainContent').addClass('expanded');
        } else {
            if (!$('#homeSidebarContainer').hasClass('collapsed')) {
                $('#homeMainContent').removeClass('expanded');
            }
        }
    });
    
    console.log('Home page sidebar toggle functionality initialized');
    
    // Home sidebar logout functionality
    $(document).on('click', '#homeLogoutBtn', function(e) {
        e.preventDefault();
        $('#logoutConfirmModal').modal('show');
    });
    
    // Home profile dropdown functionality
    $(document).on('click', '.home-profile-dropdown-menu .dropdown-item[href="../profile"]', function(e) {
        e.preventDefault();
        window.location.href = '../profile';
    });
    
    $(document).on('click', '.home-profile-dropdown-menu .dropdown-item[href="../profile-edit"]', function(e) {
        e.preventDefault();
        window.location.href = '../profile-edit';
    });
    
    // Ensure dropdown closes properly after clicking links
    $(document).on('click', '.home-profile-dropdown-menu .dropdown-item', function() {
        $('.home-profile-dropdown-container').removeClass('show');
        $('.home-profile-dropdown-menu').removeClass('show');
    });
});
</script>
<!-- AI GEMINI MODULE -->
<!-- Main Module JS -->
<script type="module" src="../assets/js/mainModule.js"></script>
<!-- app.js -->
<script type="module" src="../assets/js/app.js"></script>
<!-- Include shared validation utilities for consistent validation -->
<script src="../assets/js/validation-utils.js"></script>
<?php
// Local: research_titles | Deployed: icei_38697196_coecsathesis.research_titles
$stmt = $pdo->query("SELECT title FROM research_titles;");
$titles = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<script>
// ==========================================
// COLLAPSIBLE SIDEBAR FUNCTIONALITY - HOME PAGE
// ==========================================
// Move sidebar toggle functionality to after jQuery is loaded

var existingTitles = "<?php echo implode(', ', $titles); ?>";
</script>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<script>
// Modern toast function similar to app.js.php - Moved to top for global scope
function showToast(title, message, type = 'success') {
    // Generate unique ID for the toast
    const toastId = 'toast-' + Date.now();

    // Modern universal toast styling and structure
    const icon = type === 'success' ?
        `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#eaf0fe;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#1304ee"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>` :
        type === 'error' ?
        `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#fbeaea;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#dc3545"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>` :
        `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#fffbe6;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#ffc107"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg></span>`;

    const bgColor = type === 'success' ? '#f6fffa' : (type === 'error' ? '#fff6f6' : '#fffbe6');
    const borderColor = type === 'success' ? '#1304ee' : (type === 'error' ? '#dc3545' : '#ffc107');
    const textColor = '#222';
    const toast = `
        <div id="${toastId}" class="toast align-items-center border-0 shadow-lg"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
            style="min-width:320px;max-width:400px;opacity:1;background:${bgColor};border-left:5px solid ${borderColor};border-radius:12px;margin-bottom:1rem;box-shadow:0 4px 24px 0 rgba(0,0,0,0.10);">
            <div class="d-flex align-items-center" style="padding:1rem 1.25rem;">
                ${icon}
                <div class="toast-body p-0" style="font-size:1rem;color:${textColor};line-height:1.5;">
                    <div style="font-weight:600;font-size:1.08rem;margin-bottom:2px;">${title}</div>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close" style="margin-left:1.5rem;"></button>
            </div>
        </div>
    `;

    // Ensure toast container exists
    if ($('#toastContainer').length === 0) {
        $('body').append('<div id="toastContainer" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>');
    }

    // Add toast to container
    $('#toastContainer').append(toast);

    // Initialize and show the toast with modified options
    const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
        autohide: true,
        delay: 3000,
        animation: true
    });

    toastElement.show();

    // Auto-remove from DOM after hiding
    document.getElementById(toastId).addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

$(document).ready(function() {
    <?php
        $role = isset($_SESSION['team_role']) ? $_SESSION['team_role'] : '';
        $teamId = isset($_SESSION['team_id']) ? $_SESSION['team_id'] : [];

        if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0) {
            if ($role === 'adviser') {
                // Retrieve the teams from the session (ensure $teamId is an array)
                $teams = [];
                if (!empty($teamId)) {
                    // Query the team information based on the team_id from session
                    $teamIdStr = implode(',', (array)$teamId);
                    $stmt = $pdo->prepare("SELECT id, name FROM teams WHERE id IN ($teamIdStr)");
                    $stmt->execute();
                    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            ?>

    $(document).ready(function() {
        var teamSelectHtml = '<div class="d-flex align-items-center mb-4">';
        teamSelectHtml += '<label for="teamSelect" class="me-3 mb-0 fw-semibold">Select Team:</label>';
        teamSelectHtml += '<select id="teamSelect" class="form-select advisee-team-select">';
        <?php if (!empty($teams)) { ?>
        <?php foreach ($teams as $team) { ?>
        teamSelectHtml +=
            '<option id="team_id-<?php echo $team['id']; ?>" value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['name']); ?></option>';
        <?php } ?>
        <?php } else { ?>
        teamSelectHtml += '<option value="">No teams available</option>';
        <?php } ?>
        teamSelectHtml += '</select></div>';
        console.log(teamSelectHtml);
        $('#teamSelectorContainer').html(teamSelectHtml);

        // Load initial requirements for the first team
        var initialTeamId = $('#teamSelect').val();
        if (initialTeamId) {
            loadRequirements(initialTeamId);
        }

        // Reload requirements when team changes
        $('#teamSelect').on('change', function() {
            var selectedTeamId = $(this).val();
            loadRequirements(selectedTeamId);
        });
    });

    function loadRequirements(teamId) {
        console.log("Loading requirements for teamId:", teamId);
        $.ajax({
            url: 'includes/get_requirements.php',
            method: 'GET',
            data: {
                team_id: teamId
            },
            dataType: 'json',
            success: function(response) {
                console.log("AJAX request successful. Response:", response);
                if (response.success) {
                    var checklistHtml =
                        '<form id="requirementChecklistForm" method="POST" action="includes/update_requirements.php" enctype="multipart/form-data" class="row g-4 g-lg-5">';
                    response.requirements.forEach(function(req) {
                        checklistHtml += `
                        <div class="col-12 col-md-6 requirement-card-wrapper">
                            <div class="card requirement-adviser-card h-100" id="req-card-${req.id}" data-req-id="${req.id}" data-status="${req.status}">
                                <div class="card-body requirement-card-body">
                                    <!-- Header Section with Title, Due Date, and Checkbox -->
                                    <div class="d-flex justify-content-between align-items-start requirement-header-section">
                                        <div class="requirement-content requirement-info-section">
                                            <label class="form-check-label requirement-title-label" for="req${req.id}">
                                                <h5 class="requirement-name mb-2">${req.name}</h5>
                                                <p class="mb-2 text-muted requirement-description">${req.description || 'No description provided.'}</p>
                                                <div class="mb-2 d-flex align-items-center">
                                                    ${req.template_file 
                                                        ? `<i class="bi bi-download requirement-template-icon"></i>
                                                        <a href="/dashboard/uploads/requirements/${req.template_file}" class="requirement-template-link" download>Template Available</a>` 
                                                        : `<i class="bi bi-file-earmark requirement-template-icon text-muted"></i>
                                                        <span class="requirement-template-placeholder">No template</span>`
                                                    }
                                                </div>
                                                <div class="requirement-due-date">Due Date: <strong>${new Date(req.due_date).toLocaleDateString()}</strong></div>
                                            </label>
                                        </div>
                                        <div class="form-check requirement-checkbox-section">
                                            <input class="form-check-input requirement-checkbox" type="checkbox" 
                                                   value="${req.id}" id="req${req.id}" 
                                                   name="requirements[]" ${req.status !== 'pending' ? 'checked' : ''}>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Section -->
                                    <div class="requirement-status-section">
                                        <label class="requirement-status-label">Status:</label>
                                        <div class="requirement-status-options">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input requirement-status-radio" type="radio" 
                                                       name="status[${req.id}]" id="status-approved-${req.id}" 
                                                       value="approved" ${req.status === 'approved' ? 'checked' : ''}>
                                                <label class="form-check-label requirement-status-option-label" for="status-approved-${req.id}">
                                                    Approved
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input requirement-status-radio" type="radio" 
                                                       name="status[${req.id}]" id="status-submitted-${req.id}" 
                                                       value="submitted" ${req.status === 'submitted' ? 'checked' : ''}>
                                                <label class="form-check-label requirement-status-option-label" for="status-submitted-${req.id}">
                                                    Submitted
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input requirement-status-radio" type="radio" 
                                                       name="status[${req.id}]" id="status-pending-${req.id}" 
                                                       value="pending" ${req.status === 'pending' ? 'checked' : ''}>
                                                <label class="form-check-label requirement-status-option-label" for="status-pending-${req.id}">
                                                    Pending
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input requirement-status-radio" type="radio" 
                                                       name="status[${req.id}]" id="status-rejected-${req.id}" 
                                                       value="rejected" ${req.status === 'rejected' ? 'checked' : ''}>
                                                <label class="form-check-label requirement-status-option-label" for="status-rejected-${req.id}">
                                                    Rejected
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Instructor Feedback Section -->
                                    <div class="requirement-feedback-section">
                                        <!-- Upload Feedback File Section -->
                                        <div class="requirement-upload-section">
                                            <div class="requirement-upload-header">
                                                <label class="requirement-upload-label">Upload Feedback:</label>
                                                <label for="feedbackFile${req.id}" class="requirement-add-file-btn">
                                                    + Add file
                                                </label>
                                                <input class="d-none" type="file" id="feedbackFile${req.id}" name="feedbackFile[${req.id}]" accept=".pdf,.doc,.docx">
                                                <div class="requirement-file-pill-container" id="filePillContainer${req.id}">
                                                    ${req.feedback_file ? `
                                                    <div class="requirement-file-pill">
                                                        <a href="./feedback/${req.feedback_file}" class="requirement-file-name" download>${req.feedback_file}</a>
                                                        <button type="button" class="requirement-remove-file-btn" data-req-id="${req.id}">&times;</button>
                                                    </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Instructor Feedback Textarea -->
                                        <textarea id="feedback${req.id}" name="feedback[${req.id}]" class="form-control requirement-feedback-textarea mt-3" rows="1" placeholder="Enter your feedback here...">${req.feedback}</textarea>
                                    </div>
                                    
                                    ${req.file_name && req.status !== 'pending'
                                        ? `
                                            <!-- Submitted File Section -->
                                            <div class="requirement-submitted-file-section">
                                                <div class="submitted-file-header">
                                                    <h6>Submitted File:</h6>
                                                </div>
                                                <a href="../assets/uploads/submission/${req.file_name}" class="requirement-submitted-file-pill" download>
                                                    ${req.file_name}
                                                </a>
                                                <div class="requirement-file-actions">
                                                    <a href="../assets/uploads/submission/viewer.html#file=${encodeURIComponent(req.file_name)}" class="requirement-view-btn">
                                                        <i class="far fa-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm revert-submission-btn" 
                                                            data-req-id="${req.id}" data-req-name="${req.name}" 
                                                            title="Revert submission to allow resubmission">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Revert
                                                    </button>
                                                </div>
                                            </div>
                                        ` 
                                        : `
                                            <!-- No Submission Section -->
                                            <div class="requirement-no-submission-section">
                                                <div class="no-submission-header">
                                                    <h6 class="text-muted">No Submitted File</h6>
                                                </div>
                                                <p class="text-muted small mb-0">No file has been submitted for this requirement yet.</p>
                                            </div>
                                        `
                                    }
                                </div>
                            </div>
                        </div>`;
                    });
                    checklistHtml +=
                        '<div class="col-12 d-flex justify-content-end"><button type="submit" class="btn btn-primary mt-3" id="updateReqsBtn">Update Requirements</button></div></form>';
                    // Insert the generated HTML into the container
                    $('#requirementChecklist').html(checklistHtml);
                } else {
                    $('#requirementChecklist').html('<p class="text-danger">' + response.error +
                        '</p>');
                    console.error('Error in response:', response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#requirementChecklist').html(
                    '<p class="text-danger">Error loading requirements. Please refresh the page.</p>'
                    );
                console.error("AJAX error:", textStatus, errorThrown);
            }
        });
    }

    $(document).on('submit', '#requirementChecklistForm', function(event) {
            event.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'includes/update_requirements.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    console.log("Update response:", response);
                    if (response.success) {
                        showToast("Success!", "Requirements updated successfully!", "success");
                    } else {
                        showToast("Error", response.error, "error");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX error:", textStatus, errorThrown);
                    showToast("Error", "An error occurred while updating requirements.", "error");
                }
            });
        });

        // Handle revert submission button click
        $(document).on('click', '.revert-submission-btn', function(event) {
            event.preventDefault();
            
            const reqId = $(this).data('req-id');
            const reqName = $(this).data('req-name');
            const button = $(this);
            
            // Set up the modal with requirement name
            $('#revertRequirementName').text(reqName);
            
            // Store data for confirmation
            $('#confirmRevert').data('req-id', reqId);
            $('#confirmRevert').data('req-name', reqName);
            $('#confirmRevert').data('button', button);
            
            // Show confirmation modal
            const revertModal = new bootstrap.Modal(document.getElementById('revertConfirmModal'));
            revertModal.show();
        });

        // Handle revert confirmation
        $('#confirmRevert').on('click', function() {
            const reqId = $(this).data('req-id');
            const reqName = $(this).data('req-name');
            const button = $(this).data('button');
            
            // Hide modal
            const revertModal = bootstrap.Modal.getInstance(document.getElementById('revertConfirmModal'));
            revertModal.hide();
            
            // Get the current team ID from session
            const teamId = <?php echo isset($_SESSION['team_id']) ? $_SESSION['team_id'][0] : 'null'; ?>;
            
            if (!teamId) {
                showToast("Error", "No team selected.", "error");
                return;
            }
            
            // Disable button during request
            button.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Reverting...');
            
            $.ajax({
                url: 'includes/revert_submission.php',
                method: 'POST',
                data: {
                    team_id: teamId,
                    requirement_id: reqId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast("Success!", response.message, "success");
                        // Reload requirements to show updated status
                        var selectedTeamId = $('#teamSelect').val();
                        if (selectedTeamId) {
                            loadRequirements(selectedTeamId);
                        }
                    } else {
                        showToast("Error", response.message, "error");
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Revert error:", textStatus, errorThrown);
                    showToast("Error", "An error occurred while reverting submission.", "error");
                },
                complete: function() {
                    // Re-enable button
                    button.prop('disabled', false).html('<i class="bi bi-arrow-counterclockwise"></i> Revert Submission');
                }
            });
        });

        // Handle feedback file selection and pill display
        $(document).on('change', 'input[type="file"][id^="feedbackFile"]', function() {
            const fileInput = $(this);
            const reqId = fileInput.attr('id').replace('feedbackFile', '');
            const pillContainer = $(`#filePillContainer${reqId}`);
            const file = fileInput[0].files[0];
            
            if (file) {
                // Clear existing pills
                pillContainer.empty();
                
                // Create new pill
                const pill = $(`
                    <div class="requirement-file-pill">
                        <span class="requirement-file-name">${file.name}</span>
                        <button type="button" class="requirement-remove-file-btn" data-req-id="${reqId}">&times;</button>
                    </div>
                `);
                
                pillContainer.append(pill);
            }
        });

        // Handle remove file button click
        $(document).on('click', '.requirement-remove-file-btn', function() {
            const reqId = $(this).data('req-id');
            const fileInput = $(`#feedbackFile${reqId}`);
            const pillContainer = $(`#filePillContainer${reqId}`);
            
            // Clear file input and pill
            fileInput.val('');
            pillContainer.empty();
        });

        // Auto-resize textarea (grow from 1 to 2 lines max)
        $(document).on('input', '.requirement-feedback-textarea', function() {
            this.style.height = 'auto';
            const newHeight = Math.min(this.scrollHeight, 80); // 80px = ~2 lines
            this.style.height = newHeight + 'px';
        });

        // Function to show feedback in modal
        window.showFeedbackModal = function(element) {
            const feedback = $(element).data('feedback');
            if (feedback && feedback !== 'No feedback provided yet.') {
                $('#feedbackModalContent').text(feedback);
                const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
                feedbackModal.show();
            }
        };
    <?php
            }
        } else if ($_SESSION['usertype'] == 1) { ?>
    // console.log("Loading requirements for teamId:", teamId);
    loadRequirements(); // Just call the function here for usertype 1

    function loadRequirements() {

        $.ajax({
            url: 'includes/get_requirements.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var displayHtml = '<div class="row">';
                    response.requirements.forEach(function(req) {
                        <?php if ($role === 'leader' || $role === 'member') { ?>
                            displayHtml += `
                                <div class="col-md-6 mb-4 requirement-card-wrapper">
                                    <div class="card requirement-student-card h-100" id="student-req-card-${req.id}" data-req-id="${req.id}" data-status="${req.status}">
                                        <div class="card-body requirement-card-body">
                                            <!-- Header Section -->
                                            <div class="requirement-header-section">
                                                <div class="requirement-info-section">
                                                    <h5 class="requirement-name">${req.name}</h5>
                                                    <p class="requirement-description">${req.description || 'No description provided.'}</p>
                                                    <div class="mb-2 d-flex align-items-center">
                                                        ${req.template_file 
                                                            ? `<i class="bi bi-download requirement-template-icon"></i>
                                                            <a href="../dashboard/uploads/requirements/${req.template_file}" class="requirement-template-link" download>Template Available</a>` 
                                                            : `<i class="bi bi-file-earmark requirement-template-icon text-muted"></i>
                                                            <span class="requirement-template-placeholder">No template</span>`
                                                        }
                                                    </div>
                                                    <div class="requirement-due-date">Due Date: <strong>${new Date(req.due_date).toLocaleDateString()}</strong></div>
                                                </div>
                                            </div>
                                            
                                            <!-- Status Section -->
                                            <div class="requirement-status-section requirement-status-readonly">
                                                <label class="requirement-status-label">Status:</label>
                                                <div class="requirement-status-display">
                                                    <span class="badge status-badge status-${req.status}">${req.status.charAt(0).toUpperCase() + req.status.slice(1)}</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Feedback Section -->
                                            <div class="requirement-feedback-section requirement-feedback-readonly">
                                                <label class="requirement-upload-label">Feedback:</label>
                                                <div class="requirement-feedback-display">
                                                    <p class="requirement-feedback-text" data-feedback="${(req.feedback || 'No feedback provided yet.').replace(/"/g, '&quot;')}" onclick="showFeedbackModal(this)">${req.feedback || 'No feedback provided yet.'}</p>
                                                    ${req.feedback_file ? 
                                                        `<div class="requirement-file-pill-container">
                                                            <div class="requirement-file-pill">
                                                                <a href="./feedback/${req.feedback_file}" class="requirement-file-name" download>${req.feedback_file}</a>
                                                            </div>
                                                        </div>` 
                                                        : ''
                                                    }
                                                </div>
                                            </div>
                                            
                                            <!-- Current Submission Section -->
                                            ${req.file_name ? 
                                                `<div class="requirement-submitted-file-section">
                                                    <div class="submitted-file-header">
                                                        <h6>Current Submission:</h6>
                                                    </div>
                                                    <a href="../assets/uploads/submission/${req.file_name}" class="requirement-submitted-file-pill" download title="${req.file_name}">
                                                        ${req.file_name}
                                                    </a>
                                                    ${req.status === 'pending' ? `
                                                        <div class="requirement-file-actions">
                                                            <button type="button" class="btn btn-sm remove-current-file-btn" data-req-id="${req.id}" title="Remove current file">
                                                                <i class="bi bi-trash"></i> Remove
                                                            </button>
                                                        </div>
                                                    ` : ''}
                                                </div>` 
                                                : ''
                                            }
                                            
                                            <!-- Upload Section (Leader Only) -->
                                            <?php if ($role === 'leader') { ?>
                                            ${req.file_name 
                                                ? '' 
                                                : `<div class="requirement-upload-section-student">
                                                    <label class="requirement-upload-label">Upload File:</label>
                                                    <form class="upload-form requirement-upload-form" data-req-id="${req.id}" enctype="multipart/form-data" action="includes/upload_file.php" method="POST">
                                                        <input type="hidden" name="document_name" value="${req.name}">
                                                        <input type="hidden" name="requirement_id" value="${req.id}">
                                                        <div class="requirement-file-input-section mb-2">
                                                            <input class="form-control requirement-file-input" type="file" id="file-${req.id}" name="file" required>
                                                        </div>
                                                        <div class="d-flex justify-content-end">
                                                            <button type="submit" class="btn btn-primary requirement-submit-btn">
                                                                Submit File
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>`
                                            }
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            `;
                        <?php } ?>
                    });
                    displayHtml += '</div>';
                    $('#requirementChecklist').html(displayHtml);
                } else {
                    $('#requirementChecklist').html('<p class="text-danger">' + response.error +
                        '</p>');
                    console.error('Error fetching requirements:', response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#requirementChecklist').html(
                    '<p class="text-danger">Error loading requirements. Please refresh the page.</p>'
                    );
                console.error("AJAX error:", textStatus, errorThrown);
                console.error("Response Text:", jqXHR.responseText);
                console.error("Status Code:", jqXHR.status);
            }
        });
    }

    <?php } ?>

    // --- AJAX submission handler for student file uploads ---
    $(document).on('submit', '#requirementChecklist .upload-form', function(event) {
        event.preventDefault();

        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var formData = new FormData(this);
        var originalText = submitButton.html();

        submitButton.prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Uploading...');

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast("Success!", "File uploaded successfully!", "success");
                    setTimeout(loadRequirements, 1000);
                } else {
                    showToast("Upload Failed", response.error || 'Unknown error', "error");
                    submitButton.prop('disabled', false).html(originalText);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                showToast("Upload Failed", "Upload failed. Please try again.", "error");
                console.error("Upload error:", textStatus, errorThrown);
                submitButton.prop('disabled', false).html(originalText);
            }
        });
    });

    // --- Handle remove current file button ---
    $(document).on('click', '.remove-current-file-btn', function(event) {
        event.preventDefault();
        
        const reqId = $(this).data('req-id');
        const button = $(this);
        
        // Get requirement name for the modal
        const requirementRow = button.closest('.requirement-item');
        const requirementName = requirementRow.find('.requirement-title').text() || 'this requirement';
        
        // Set the requirement name in the modal
        $('#deleteRequirementName').text(requirementName);
        
        // Store the requirement ID for later use
        $('#confirmDeleteFile').data('req-id', reqId);
        
        // Show confirmation modal
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteFileConfirmModal'));
        deleteModal.show();
    });

    // Handle confirmation of file deletion
    $(document).on('click', '#confirmDeleteFile', function() {
        const reqId = $(this).data('req-id');
        const originalButton = $(`.remove-current-file-btn[data-req-id="${reqId}"]`);
        
        // Hide the modal
        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteFileConfirmModal'));
        deleteModal.hide();
        
        // Disable button during request (no animation, just text change)
        originalButton.prop('disabled', true).text('Removing...');
        
        $.ajax({
            url: 'includes/remove_file.php',
            method: 'POST',
            data: {
                requirement_id: reqId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast("Success!", response.message, "success");
                    // Reload requirements to show updated interface
                    loadRequirements();
                } else {
                    showToast("Error", response.message, "error");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Remove file error:", textStatus, errorThrown);
                showToast("Error", "An error occurred while removing the file.", "error");
            },
            complete: function() {
                // Re-enable button (simple text, no icon)
                originalButton.prop('disabled', false).text('Remove');
            }
        });
    });
    // --- End remove file handler ---


});
//calendar
console.log('FullCalendar loaded:', typeof FullCalendar !== 'undefined');
$(document).ready(function() {
    // Thesis Topic Decision Tool
    $('#topicSuggestionForm').on('submit', function(e) {
        e.preventDefault();
        var field = $('#field').val();
        // AJAX call to get topic suggestions
        $.ajax({
            url: 'includes/get_topic_suggestions.php',
            method: 'POST',
            data: {
                field: field
            },
            dataType: 'json',
            success: function(response) {
                var suggestionsHtml = '<ul>';
                response.suggestions.forEach(function(suggestion) {
                    suggestionsHtml += '<li>' + suggestion + '</li>';
                });
                suggestionsHtml += '</ul>';
                $('#suggestedTopics').html(suggestionsHtml);
            },
            error: function() {
                $('#suggestedTopics').html(
                    '<p>Error fetching suggestions. Please try again.</p>');
            }
        });
    });

    // Scheduling System
    function loadUserSchedule() {
        $.ajax({
            url: 'includes/get_user_schedule.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("AJAX response:", response);
                if (response.success) {
                    var events = [];
                    // Add user schedules to events
                    response.user_schedules.forEach(function(event) {
                        events.push({
                            title: event.description,
                            start: event.date + 'T' + event.start_time,
                            end: event.date + 'T' + event.end_time,
                        });
                    });
                    // Add defense schedules to events
                    response.defense_schedules.forEach(function(event) {
                        events.push({
                            title: event.description,
                            start: event.date + 'T' + event.start_time,
                            end: event.date + 'T' + event.end_time,
                        });
                    });
                    console.log("Events to be rendered:", events);
                    initializeCalendar(events);
                } else {
                    $('#userSchedule').html('<p>Error loading schedules: ' + response.error +
                        '</p>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", textStatus, errorThrown);
                $('#userSchedule').html('<p>Error loading schedules. Please try again later.</p>');
            }
        });
    }

    function initializeCalendar(events) {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 'auto', // or set a specific height like '600px'
            events: events, // Use the dynamically loaded events
            eventClick: function(info) {
                alert('Event: ' + info.event.title);
            }
        });
        calendar.render();
    }

});



const calendarEl = document.getElementById('calendar');

// Function to get the next date for a given day of the week
function getNextDateForDay(day) {
    const daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const today = new Date();
    const targetDayIndex = daysOfWeek.indexOf(day);
    if (targetDayIndex === -1) {
        return day; // Return original if invalid day
    }
    const resultDate = new Date(today);
    resultDate.setDate(today.getDate() + ((7 + targetDayIndex - today.getDay()) % 7));
    return resultDate.toISOString().split('T')[0];
}


/**
 * Function to redirect to decision-support with the schedule_id.
 * The decision-support page will auto-determine the correct rubric group based on defense_type and program.
 * @param {number} scheduleId - The ID of the defense schedule.
 * @param {boolean} viewOnly - (Optional) If true, indicates the evaluation is completed and should be read-only.
 */
function redirectToDecisionSupport(scheduleId, viewOnly = false) {
    if (!scheduleId) {
        console.error('Missing scheduleId for redirection.');
        alert('Error: Cannot navigate to evaluation page. Missing schedule information.');
        return;
    }
    
    // Construct the URL
    let url = `../decision-support/index.php?schedule_id=${scheduleId}`;
    
    // Add view-only parameter if needed (future enhancement for read-only mode)
    if (viewOnly) {
        url += `&view_only=1`;
    }
    
    console.log(`Redirecting to: ${url}`);
    window.location.href = url;
}

// ==========================================
// EVALUATIONS TAB FUNCTIONALITY
// ==========================================
let currentEvaluationsPage = 1;

// Load evaluations when tab is clicked
$(document).on('click', '#research-evaluation-link', function() {
    loadEvaluations(1);
});

// Also check if evaluations tab is active on page load (e.g., after refresh)
$(document).ready(function() {
    // Check if evaluations tab is active (either by hash or Bootstrap tab state)
    const hash = window.location.hash;
    const isEvaluationsTabActive = hash === '#research-evaluation' || 
                                   $('#research-evaluation-link').hasClass('active') ||
                                   $('#research-evaluation').hasClass('show active');
    
    if (isEvaluationsTabActive) {
        loadEvaluations(1);
    }
});

function loadEvaluations(page = 1) {
    currentEvaluationsPage = page;
    
    $('#evaluations-loading').show();
    $('#evaluations-content').hide();
    $('#evaluations-empty').hide();

    $.ajax({
        url: 'includes/tabs/get_evaluations.php',
        method: 'GET',
        data: { page: page },
        dataType: 'json',
        success: function(response) {
            $('#evaluations-loading').hide();
            
            if (response.error) {
                showToast('Error', response.error, 'error');
                $('#evaluations-empty').show();
                return;
            }

            if (!response.data || response.data.length === 0) {
                $('#evaluations-empty').show();
                return;
            }

            renderEvaluationsTable(response.data, response.usertype);
            renderEvaluationsPagination(response.page, response.total_pages);
            $('#evaluations-content').show();
        },
        error: function(xhr, status, error) {
            console.error('Error loading evaluations:', error);
            $('#evaluations-loading').hide();
            $('#evaluations-empty').show();
            showToast('Error', 'Failed to load evaluations', 'error');
        }
    });
}

function renderEvaluationsTable(data, usertype) {
    const tbody = $('#evaluations-table-body');
    tbody.empty();

    data.forEach(function(team) {
        const researchTitle = team.research_title ? escapeHtml(team.research_title) : '<em class="text-muted">No title yet</em>';
        
        let roleBadge = '';
        if (team.role) {
            const roleText = team.role.toLowerCase();
            if (roleText.includes('panelist')) {
                roleBadge = '<span class="badge bg-secondary text-white eval-role-badge">Panelist</span>';
            } else if (roleText.includes('advisee')) {
                roleBadge = '<span class="badge bg-primary text-white eval-role-badge">Advisee</span>';
            } else {
                roleBadge = `<span class="badge bg-info text-white eval-role-badge">${team.role}</span>`;
            }
        }
        
        const row = `
            <tr class="evaluation-team-row" data-team-id="${team.team_id}">
                <td>
                    <div>${escapeHtml(team.team_name)}</div>
                    ${roleBadge ? `<div class="mt-1">${roleBadge}</div>` : ''}
                    <small class="d-block d-md-none text-muted mt-1">${researchTitle}</small>
                </td>
                <td class="d-none d-md-table-cell">${researchTitle}</td>
                <td class="d-none d-lg-table-cell">${escapeHtml(team.program)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary view-eval-details" data-team-id="${team.team_id}">
                        View
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function renderEvaluationsPagination(currentPage, totalPages) {
    const pagination = $('#evaluations-pagination');
    pagination.empty();

    if (totalPages <= 1) return;

    // Previous button
    const prevDisabled = currentPage <= 1 ? 'disabled' : '';
    pagination.append(`
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">&#8249;</a>
        </li>
    `);

    // Page numbers
    const maxPages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
    let endPage = Math.min(totalPages, startPage + maxPages - 1);

    if (endPage === totalPages) {
        startPage = Math.max(1, endPage - maxPages + 1);
    }

    if (startPage > 1) {
        pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
        if (startPage > 2) {
            pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const active = i === currentPage ? 'active' : '';
        pagination.append(`
            <li class="page-item ${active}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
        pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
    }

    // Next button
    const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
    pagination.append(`
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">&#8250;</a>
        </li>
    `);
}

// Handle pagination clicks
$(document).on('click', '#evaluations-pagination a.page-link', function(e) {
    e.preventDefault();
    const page = parseInt($(this).data('page'));
    if (page && page > 0) {
        loadEvaluations(page);
    }
});

// Handle view details button
$(document).on('click', '.view-eval-details', function() {
    const teamId = $(this).data('team-id');
    showEvaluationDetails(teamId);
});

function showEvaluationDetails(teamId) {
    // Create and show modal
    const modalHtml = `
        <div class="modal fade" id="evaluationDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Team Evaluation Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="evaluationDetailsBody">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-3">Loading evaluation details...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    $('#evaluationDetailsModal').remove();
    $('body').append(modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('evaluationDetailsModal'));
    modal.show();

    // Load details
    $.ajax({
        url: 'includes/tabs/get_evaluation_details.php',
        method: 'GET',
        data: { team_id: teamId },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                $('#evaluationDetailsBody').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>${response.error}
                    </div>
                `);
                return;
            }

            renderEvaluationDetails(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading evaluation details:', error);
            $('#evaluationDetailsBody').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>Failed to load evaluation details
                </div>
            `);
        }
    });
}

function renderEvaluationDetails(data) {
    const team = data.team_info;
    const evaluationsByStudent = data.evaluations_by_student || {};
    const panelists = data.panelists || [];
    const students = data.students || [];
    const usertype = data.usertype;
    const passThreshold = data.pass_threshold_3 || 75;
    const warningThreshold = Math.max(passThreshold - 15, 60);
    
    let detailsHtml = `
        <div class="mb-4">
            <h3 class="border-bottom pb-2">${escapeHtml(team.team_name)}</h3>
            <p class="mb-1"><strong>Research Title:</strong> ${team.research_title ? escapeHtml(team.research_title) : '<em class="text-muted">No title yet</em>'}</p>
            <p class="mb-1"><strong>Program:</strong> ${escapeHtml(team.program)}</p>
            <p class="mb-0"><strong>Adviser:</strong> ${team.adviser || '<em class="text-muted">No adviser</em>'}</p>
        </div>

        <h6 class="text-dark mb-3">Evaluation Summary:</h6>
    `;
    
    // Check if any evaluations have pending scores (for students)
    const hasPendingScores = Object.values(evaluationsByStudent).some(s => s.has_pending);
    if (usertype == 1 && hasPendingScores) {
        detailsHtml += `
            <div class="alert alert-warning mb-3">
                <i class="fas fa-clock me-2"></i>
                <strong>Note:</strong> Some evaluation scores are pending and will be visible 1 week after the evaluation date.
            </div>
        `;
    }

    if (panelists.length === 0 || Object.keys(evaluationsByStudent).length === 0) {
        detailsHtml += `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No evaluations recorded yet for this team.
            </div>
        `;
    } else {
        const pendingBadge = '<span class="badge bg-warning text-dark" title="Score will be visible 1 week after evaluation"><i class="fas fa-clock"></i> Pending</span>';
        
        // Build dynamic table with panelist columns
        detailsHtml += `
            <div class="table-responsive">
                <table class="table table-sm table-bordered team-eval-table">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            ${panelists.map(p => `<th class="text-center"><small>${escapeHtml(p.evaluator_name)}</small></th>`).join('')}
                            <th class="text-center table-primary"><strong>Average</strong></th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        // Track all student averages for overall team average
        let allStudentAverages = [];
        
        // Iterate through students
        students.forEach(student => {
            const studentId = student.student_id;
            const studentData = evaluationsByStudent[studentId];
            
            let studentScores = [];
            let hasPending = false;
            
            // Build panelist score cells
            let panelistCells = panelists.map(panelist => {
                const panelistId = panelist.evaluator_id;
                const scoreData = studentData?.panelist_scores?.[panelistId];
                
                if (scoreData) {
                    if (scoreData.score_pending == 1) {
                        hasPending = true;
                        return `<td class="text-center">${pendingBadge}</td>`;
                    } else if (scoreData.total_score !== null) {
                        const score = parseFloat(scoreData.total_score);
                        studentScores.push(score);
                        return `<td class="text-center text-dark">${score.toFixed(2)}</td>`;
                    }
                }
                return `<td class="text-center text-muted">-</td>`;
            }).join('');
            
            // Calculate average for this student
            let avgDisplay;
            if (hasPending && studentScores.length === 0) {
                avgDisplay = pendingBadge;
            } else if (studentScores.length > 0) {
                const avg = studentScores.reduce((a, b) => a + b, 0) / studentScores.length;
                allStudentAverages.push(avg);
                const avgClass = avg >= passThreshold ? 'text-success' : avg >= warningThreshold ? 'text-warning' : 'text-danger';
                avgDisplay = `<strong class="${avgClass}">${avg.toFixed(2)}</strong>`;
            } else {
                avgDisplay = '<span class="text-muted">-</span>';
            }
            
            detailsHtml += `
                <tr>
                    <td>${escapeHtml(student.student_name)}</td>
                    ${panelistCells}
                    <td class="text-center table-primary">${avgDisplay}</td>
                </tr>
            `;
        });
        
        // Add overall team average row
        let overallAvgDisplay;
        if (allStudentAverages.length > 0) {
            const overallAvg = allStudentAverages.reduce((a, b) => a + b, 0) / allStudentAverages.length;
            const overallClass = overallAvg >= passThreshold ? 'text-success' : overallAvg >= warningThreshold ? 'text-warning' : 'text-danger';
            overallAvgDisplay = `<strong class="${overallClass}">${overallAvg.toFixed(2)}</strong>`;
        } else {
            overallAvgDisplay = '<span class="text-muted">-</span>';
        }
        
        detailsHtml += `
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th>Team Average</th>
                            ${panelists.map(() => '<td></td>').join('')}
                            <th class="text-center">${overallAvgDisplay}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        
        // Add comments section if there are any (one per evaluator to avoid duplication)
        const evaluatorComments = new Map();
        
        Object.entries(evaluationsByStudent).forEach(([studentId, studentData]) => {
            Object.entries(studentData.panelist_scores || {}).forEach(([panelistId, scoreData]) => {
                if (scoreData.comments && scoreData.comments.trim() && !evaluatorComments.has(panelistId)) {
                    evaluatorComments.set(panelistId, {
                        evaluator_name: scoreData.evaluator_name,
                        comments: scoreData.comments
                    });
                }
            });
        });
        
        if (evaluatorComments.size > 0) {
            let commentsHtml = `
                <h6 class="text-dark mt-4 mb-3">Evaluator Comments</h6>
                <div class="accordion accordion-flush" id="commentsAccordion">
            `;
            
            let accordionIndex = 0;
            evaluatorComments.forEach((commentData, panelistId) => {
                commentsHtml += `
                    <div class="accordion-item evaluator-comment-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#comment${accordionIndex}">
                                ${escapeHtml(commentData.evaluator_name)}
                            </button>
                        </h2>
                        <div id="comment${accordionIndex}" class="accordion-collapse collapse" data-bs-parent="#commentsAccordion">
                            <div class="accordion-body">
                                ${escapeHtml(commentData.comments)}
                            </div>
                        </div>
                    </div>
                `;
                accordionIndex++;
            });
            
            commentsHtml += `</div>`;
            detailsHtml += commentsHtml;
        }
    }

    $('#evaluationDetailsBody').html(detailsHtml);
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, m => map[m]);
}

// Defense Approval Modal System
$(document).ready(function() {
    // Check for pending defense approvals on page load
    checkPendingApprovals();

    function checkPendingApprovals() {
        $.ajax({
            url: '../assets/includes/get_pending_approvals.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.pendingApprovals.length > 0) {
                    // Show modal for the first pending approval
                    showApprovalModal(response.pendingApprovals[0]);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking pending approvals:', error);
            }
        });
    }

    function showApprovalModal(approval) {
        const modalBody = `
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <strong>You have been assigned as a panelist for the following defense:</strong>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="bi bi-calendar3 me-2"></i>Schedule Details
                                </h6>
                                <p class="mb-1"><strong>Date:</strong> ${approval.formatted_date}</p>
                                <p class="mb-1"><strong>Time:</strong> ${approval.formatted_time}</p>
                                <p class="mb-0"><strong>Room:</strong> ${approval.room}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="bi bi-people me-2"></i>Team Information
                                </h6>
                                <p class="mb-1"><strong>Team:</strong> ${approval.team_name}</p>
                                <p class="mb-1"><strong>Program:</strong> ${approval.program || 'N/A'}</p>
                                <p class="mb-0"><strong>Research:</strong> ${approval.research_title || 'N/A'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6 class="text-primary">
                        <i class="bi bi-person-badge me-2"></i>Other Panelists
                    </h6>
                    <p class="mb-0">${approval.other_panelists}</p>
                </div>
                
                <div class="mb-4">
                    <label for="rejectionReason" class="form-label">
                        <i class="bi bi-chat-text me-2"></i>Reason for declining (optional):
                    </label>
                    <textarea class="form-control" id="rejectionReason" rows="3" 
                              placeholder="Please provide a reason if you cannot attend this defense session..."></textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-3">
                    <button type="button" class="btn btn-outline-danger" onclick="respondToApproval('reject', ${approval.schedule_id})">
                        <i class="bi bi-x-circle me-2"></i>Decline
                    </button>
                    <button type="button" class="btn btn-success" onclick="respondToApproval('approve', ${approval.schedule_id})">
                        <i class="bi bi-check-circle me-2"></i>Accept
                    </button>
                </div>
            `;

        $('#approvalModalBody').html(modalBody);
        $('#defenseApprovalModal').modal('show');
    }

    // Global function for handling approval responses
    window.respondToApproval = function(action, scheduleId) {
        const reason = $('#rejectionReason').val().trim();
        const actionText = action === 'approve' ? 'accepting' : 'declining';

        // Show loading state
        const modalBody = $('#approvalModalBody');
        const originalContent = modalBody.html();
        modalBody.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Processing...</span>
                    </div>
                    <p>Processing your response...</p>
                </div>
            `);

        $.ajax({
            url: '../assets/includes/handle_defense_approval.php',
            method: 'POST',
            data: {
                action: action,
                schedule_id: scheduleId,
                rejection_reason: reason
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    modalBody.html(`
                            <div class="text-center py-4">
                                <div class="text-success mb-3">
                                    <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-success">Response Recorded!</h5>
                                <p class="mb-0">${response.message}</p>
                            </div>
                        `);

                    // Close modal after 3 seconds and check for more approvals
                    setTimeout(function() {
                        $('#defenseApprovalModal').modal('hide');
                        checkPendingApprovals(); // Check for more pending approvals
                    }, 3000);

                } else {
                    // Show error message with retry option
                    modalBody.html(`
                            <div class="text-center py-4">
                                <div class="text-danger mb-3">
                                    <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-danger">Error</h5>
                                <p class="mb-3">${response.message}</p>
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                                </button>
                            </div>
                        `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error processing approval:', error);
                modalBody.html(`
                        <div class="text-center py-4">
                            <div class="text-danger mb-3">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-danger">Connection Error</h5>
                            <p class="mb-3">Failed to process your response. Please try again.</p>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-2"></i>Reload Page
                            </button>
                        </div>
                    `);
            }
        });
    };
});
</script>
