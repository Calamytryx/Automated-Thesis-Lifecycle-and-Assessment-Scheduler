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
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// echo '<pre>';
// print_r($_SESSION['team_id'][0]);
// echo '</pre>';
?>

<script>
// Move fetchTeamOverview to global scope
function fetchTeamOverview(teamId = null) {
    <?php if ($_SESSION['usertype'] == 2): ?>
    // Faculty: Show advisee teams and paneling defenses
    fetchFacultyDashboard();
    <?php else: ?>
    // Students: Show team overview
    const url = teamId ? `includes/get_team_overview.php?team_id=${teamId}` : 'includes/get_team_overview.php';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const teamOverviewContent = document.getElementById('teamOverviewContent');

                let content = ''; // Team Selector (if multiple teams available)
                if (data.teams.length > 1) {
                    content += `
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card team-selector-card">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-3 text-muted">Select Team</h6>
                                                <select id="teamSelector" class="form-select">
                                                    ${data.teams.map(team => 
                                                        `<option value="${team.id}" ${team.id == data.selectedTeam.id ? 'selected' : ''}>${team.name}</option>`
                                                    ).join('')}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                }

                // Current Team Info
                content += `
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card team-info-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="card-title mb-1">${data.selectedTeam.name}</h5>
                                                    <p class="text-muted mb-0">Team Overview</p>
                                                </div>
                                                <div class="text-end">
                                                    <div class="d-flex gap-3">
                                                        <div class="text-center">
                                                            <div class="h4 mb-0 text-success">${data.completedCount}</div>
                                                            <small class="text-muted">Completed</small>
                                                        </div>
                                                        <div class="text-center">
                                                            <div class="h4 mb-0 text-warning">${data.pendingCount}</div>
                                                            <small class="text-muted">Pending</small>
                                                        </div>
                                                        <div class="text-center">
                                                            <div class="h4 mb-0">${data.totalRequirements}</div>
                                                            <small class="text-muted">Total</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                // Progress Overview
                const progressPercentage = data.totalRequirements > 0 ? Math.round((data.completedCount / data
                    .totalRequirements) * 100) : 0;
                content += `
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card progress-overview-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-subtitle mb-0">Overall Progress</h6>
                                                <span class="badge bg-dark">${progressPercentage}%</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: ${progressPercentage}%" aria-valuenow="${progressPercentage}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `; // Requirements by Category
                content += `
                            <div class="row">
                                <!-- Completed Requirements -->
                                <div class="col-md-6 mb-4">
                                    <div class="card requirements-completed-card h-100">
                                        <div class="card-header requirements-header bg-transparent pb-3">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Completed</h6>
                                                    <small class="text-muted">${data.completedCount} requirements</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-3">
                                            ${data.requirements.completed.length > 0 
                                                ? data.requirements.completed.map(req => `
                                                    <div class="d-flex align-items-center py-2 border-bottom border-light">
                                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                        <span class="flex-grow-1">${req.name}</span>
                                                    </div>
                                                `).join('')
                                                : '<p class="text-muted mb-0">No completed requirements yet</p>'
                                            }
                                        </div>
                                    </div>
                                </div>

                                <!-- Pending Requirements -->
                                <div class="col-md-6 mb-4">
                                    <div class="card requirements-pending-card h-100">
                                        <div class="card-header requirements-header bg-transparent pb-3">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Pending</h6>
                                                    <small class="text-muted">${data.pendingCount} requirements</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-3">
                                            ${data.requirements.pending.length > 0 
                                                ? data.requirements.pending.map(req => {
                                                    const statusIcon = req.status === 'submitted' ? 'bi-hourglass-split text-info' : 
                                                                      req.status === 'rejected' ? 'bi-x-circle-fill text-danger' : 
                                                                      'bi-circle text-muted';
                                                    const statusText = req.status === 'submitted' ? 'Submitted' : 
                                                                      req.status === 'rejected' ? 'Rejected' : 
                                                                      'Not Started';
                                                    return `
                                                        <div class="d-flex align-items-center py-2 border-bottom border-light">
                                                            <i class="bi ${statusIcon} me-2"></i>
                                                            <div class="flex-grow-1">
                                                                <div>${req.name}</div>
                                                                <small class="text-muted">${statusText}</small>
                                                            </div>
                                                        </div>
                                                    `;
                                                }).join('')
                                                : '<p class="text-muted mb-0">All requirements completed!</p>'
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `; // Defense Schedule
                content += `
                            <div class="row">
                                <div class="col-12">
                                    <div class="card defense-schedule-card">
                                        <div class="card-header defense-schedule-header bg-transparent pb-3">
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-0">Next Defense Schedule</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-3">
                                            ${data.defense 
                                                ? `
                                                    <div class="row g-3 d-flex justify-content-center">
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="text-center p-3 bg-light rounded">
                                                                <i class="bi bi-calendar3 text-muted mb-2 d-block"></i>
                                                                <div class="fw-semibold">${new Date(data.defense.schedule_date).toLocaleDateString()}</div>
                                                                <small class="text-muted">Date</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="text-center p-3 bg-light rounded">
                                                                <i class="bi bi-clock text-muted mb-2 d-block"></i>
                                                                <div class="fw-semibold">${data.defense.start_time} - ${data.defense.end_time}</div>
                                                                <small class="text-muted">Time</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-lg-3">
                                                            <div class="text-center p-3 bg-light rounded">
                                                                <i class="bi bi-geo-alt text-muted mb-2 d-block"></i>
                                                                <div class="fw-semibold">${data.defense.room || 'TBA'}</div>
                                                                <small class="text-muted">Room</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `
                                                : `
                                                    <div class="text-center py-4">
                                                        <i class="bi bi-calendar-x text-muted mb-2" style="font-size: 2rem;"></i>
                                                        <p class="text-muted mb-0">No defense scheduled yet</p>
                                                    </div>
                                                `
                                            }
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                teamOverviewContent.innerHTML = content;

                // Add event listener for team selector after content is inserted
                const teamSelector = document.getElementById('teamSelector');
                if (teamSelector) {
                    teamSelector.addEventListener('change', function() {
                        fetchTeamOverview(this.value);
                    });
                }
            } else {
                console.error(data.message);
                document.getElementById('teamOverviewContent').innerHTML = `
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${data.message}
                        </div>
                    `;
            }
        })
        .catch(error => {
            console.error('Error fetching team overview:', error);
            document.getElementById('teamOverviewContent').innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Failed to load team overview. Please try again.
                    </div>
                `;
        });
    <?php endif; ?>
}

<?php if ($_SESSION['usertype'] == 2): ?>
// Faculty Dashboard: Show advisee teams and paneling defenses
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
                
                // Advisee Teams Section - Card-based layout like student overview
                content += `
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="mb-3">
                                <i class="bi bi-people-fill me-2"></i>
                                Advisee Teams
                                <span class="badge bg-primary ms-2">${data.advisee_teams.length}</span>
                            </h4>
                        </div>
                    </div>
                `;
                
                if (data.advisee_teams.length === 0) {
                    content += `
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            You are not currently advising any teams.
                        </div>
                    `;
                } else {
                    // Team selector if multiple teams
                    if (data.advisee_teams.length > 1) {
                        content += `
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card team-selector-card">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-3 text-muted">Select Team to View</h6>
                                            <select id="adviseeTeamSelector" class="form-select">
                                                ${data.advisee_teams.map((team, index) => 
                                                    `<option value="${index}" ${index === 0 ? 'selected' : ''}>${team.name}</option>`
                                                ).join('')}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Store teams data for dynamic switching
                    window.adviseeTeamsData = data.advisee_teams;
                    
                    // Render first team by default
                    content += `<div id="adviseeTeamContent"></div>`;
                }
                
                // Paneling Defenses Section
                content += `
                    <div class="row mb-4 mt-5">
                        <div class="col-12">
                            <h4 class="mb-3">
                                <i class="bi bi-calendar-check me-2"></i>
                                Defense Schedules (Panelist)
                                <span class="badge bg-success ms-2">${data.paneling_defenses.length}</span>
                            </h4>
                        </div>
                    </div>
                `;
                
                if (data.paneling_defenses.length === 0) {
                    content += `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No defense schedules assigned to you as panelist.
                        </div>
                    `;
                } else {
                    content += `
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
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
                                <button class="btn btn-sm btn-outline-primary" onclick="redirectToDecisionSupport(${defense.schedule_id}, true)">
                                    <i class="bi bi-eye me-1"></i>View
                                </button>
                            `;
                        } else if (!isUpcoming) {
                            actionButton = `
                                <button class="btn btn-sm btn-primary" onclick="redirectToDecisionSupport(${defense.schedule_id})">
                                    <i class="bi bi-clipboard-check me-1"></i>Evaluate
                                </button>
                            `;
                        } else {
                            actionButton = `<span class="text-muted small">Upcoming</span>`;
                        }
                        
                        const statusBadge = hasEvaluated ? 
                            '<span class="badge bg-success">Evaluated</span>' :
                            isUpcoming ? '<span class="badge bg-info">Scheduled</span>' :
                            '<span class="badge bg-warning">Pending</span>';
                        
                        content += `
                            <tr class="${!isUpcoming ? 'table-active' : ''}">
                                <td>
                                    <strong>${defenseDate.toLocaleDateString()}</strong><br>
                                    <small class="text-muted">${defense.start_time} - ${defense.end_time}</small>
                                </td>
                                <td>
                                    <strong>${defense.team_name}</strong><br>
                                    <small class="text-muted">${defense.team_members}</small>
                                </td>
                                <td><small>${defense.research_title || 'No title'}</small></td>
                                <td><span class="badge bg-secondary">${defense.defense_type || 'N/A'}</span></td>
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
    
    const container = document.getElementById('adviseeTeamContent');
    if (!container) return;
    
    const progress = team.total_requirements > 0 ? 
        Math.round((team.completed_count / team.total_requirements) * 100) : 0;
    const progressClass = progress >= 75 ? 'bg-success' : progress >= 50 ? 'bg-warning' : 'bg-danger';
    
    // Determine current defense stage
    const defenseType = team.override_defense_type || team.latest_defense_type || 'title_proposal';
    const defenseStageLabels = {
        'title_proposal': { label: 'Title Proposal', class: 'bg-info', icon: 'bi-file-earmark-text' },
        'title_defense': { label: 'Title Defense', class: 'bg-primary', icon: 'bi-shield-check' },
        'final_defense': { label: 'Final Defense', class: 'bg-success', icon: 'bi-trophy' },
        're-defense': { label: 'Re-Defense', class: 'bg-warning', icon: 'bi-arrow-repeat' }
    };
    const stageInfo = defenseStageLabels[defenseType] || { label: 'Title Proposal', class: 'bg-secondary', icon: 'bi-file-earmark-text' };
    
    // Score display
    const avgScore = team.avg_score ? parseFloat(team.avg_score).toFixed(2) : null;
    const scoreClass = avgScore >= 75 ? 'text-success' : avgScore >= 60 ? 'text-warning' : avgScore ? 'text-danger' : 'text-muted';
    
    // Title status
    const titleApproved = team.title_approved_at ? true : false;
    const titleBadge = titleApproved ? 
        '<span class="badge bg-success ms-2"><i class="bi bi-check-circle me-1"></i>Approved</span>' : 
        '<span class="badge bg-secondary ms-2"><i class="bi bi-clock me-1"></i>Pending</span>';
    
    // Parse student members
    let membersList = '';
    if (team.student_names) {
        const members = team.student_names.split(', ');
        membersList = members.map(m => `<li class="list-group-item py-2"><i class="bi bi-person me-2"></i>${m}</li>`).join('');
    } else {
        membersList = '<li class="list-group-item text-muted py-2">No members found</li>';
    }
    
    let html = `
        <div class="row">
            <!-- Team Info Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 team-info-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Team Information</h6>
                        ${titleBadge}
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-primary">${team.name}</h5>
                        <p class="card-text mb-2">
                            <strong>Research Title:</strong><br>
                            <span class="${team.research_title ? '' : 'text-muted fst-italic'}">
                                ${team.research_title || 'No title yet'}
                            </span>
                        </p>
                        <p class="card-text mb-2">
                            <strong>Program:</strong> ${team.program || 'N/A'}
                        </p>
                        <p class="card-text mb-0">
                            <strong>Members:</strong>
                        </p>
                        <ul class="list-group list-group-flush mt-2">
                            ${membersList}
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Defense Stage & Progress Card -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 progress-overview-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Progress Overview</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Current Defense Stage</label>
                            <p class="mb-0">
                                <span class="badge <?php /* stageInfo is from JS template, use template variable */ ?> ${stageInfo.class}">
                                    <i class="bi ${stageInfo.icon} me-1"></i> ${stageInfo.label}
                                </span>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Requirements Progress</label>
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${progress}%;" 
                                     aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                                    ${progress}%
                                </div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><i class="bi bi-check-circle text-success me-1"></i>${team.completed_count} Completed</span>
                                <span><i class="bi bi-clock text-warning me-1"></i>${team.pending_requirements || 0} Pending</span>
                                <span><i class="bi bi-list-check me-1"></i>${team.total_requirements} Total</span>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h4 class="${scoreClass}">${avgScore || '-'}</h4>
                                    <small class="text-muted">Average Score</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h4 class="text-primary">${team.total_evaluations || 0}</h4>
                                    <small class="text-muted">Evaluations</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions Row -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <button class="btn btn-outline-primary view-team-summary" data-team-id="${team.id}" data-team-name="${team.name}">
                                <i class="bi bi-clipboard-data me-2"></i>View Evaluation Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
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
                            <h5 class="modal-title"><i class="bi bi-people me-2"></i>Team Summary</h5>
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
            const members = data.members || [];
            
            let html = `
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">${teamName}</h5>
                    <p class="mb-1"><strong>Research Title:</strong> ${team.research_title || '<em class="text-muted">No title yet</em>'}</p>
                    <p class="mb-1"><strong>Program:</strong> ${team.program || 'N/A'}</p>
                    <p class="mb-0"><strong>Adviser:</strong> ${team.adviser || 'N/A'}</p>
                </div>
                
                <div class="mb-4">
                    <h6 class="text-primary"><i class="bi bi-people me-2"></i>Team Members</h6>
                    <div class="row">
            `;
            
            members.forEach(member => {
                const badgeClass = member.role === 'Adviser' ? 'bg-success' : member.role === 'Leader' ? 'bg-primary' : 'bg-secondary';
                html += `<div class="col-md-4 mb-2"><span class="badge ${badgeClass} me-2">${member.role}</span>${member.name}</div>`;
            });
            
            html += `</div></div>`;
            
            // Evaluation summary
            html += `<h6 class="text-primary mb-3"><i class="bi bi-clipboard-data me-2"></i>Evaluation Summary</h6>`;
            
            if (panelists.length === 0 || Object.keys(evaluationsByStudent).length === 0) {
                html += `<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No evaluations recorded yet.</div>`;
            } else {
                html += `
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
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
                            const scoreClass = score >= 75 ? 'text-success' : score >= 60 ? 'text-warning' : 'text-danger';
                            return `<td class="text-center ${scoreClass}"><strong>${score.toFixed(2)}</strong></td>`;
                        }
                        return '<td class="text-center text-muted">-</td>';
                    }).join('');
                    
                    let avgDisplay = '<span class="text-muted">-</span>';
                    if (scores.length > 0) {
                        const avg = scores.reduce((a, b) => a + b, 0) / scores.length;
                        allAverages.push(avg);
                        const avgClass = avg >= 75 ? 'text-success' : avg >= 60 ? 'text-warning' : 'text-danger';
                        avgDisplay = `<strong class="${avgClass}">${avg.toFixed(2)}</strong>`;
                    }
                    
                    html += `<tr><td><strong>${student.student_name}</strong></td>${cells}<td class="text-center table-primary">${avgDisplay}</td></tr>`;
                });
                
                // Team average
                let teamAvgDisplay = '<span class="text-muted">-</span>';
                if (allAverages.length > 0) {
                    const teamAvg = allAverages.reduce((a, b) => a + b, 0) / allAverages.length;
                    const teamAvgClass = teamAvg >= 75 ? 'text-success' : teamAvg >= 60 ? 'text-warning' : 'text-danger';
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

// Load class record for faculty
function loadClassRecord(page = 1) {
    const classRecordContent = document.getElementById('classRecordContent');
    const viewType = document.getElementById('classRecordViewSelect')?.value || 'team';
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
                let content = '';
                
                if (Object.keys(data.sections).length === 0) {
                    content = `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No students found in your assigned sections.
                        </div>
                    `;
                } else if (viewType === 'team') {
                    // TEAM VIEW - Students grouped by teams
                    for (const [section, teams] of Object.entries(data.sections)) {
                        content += `
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="bi bi-people-fill me-2 text-primary"></i>
                                        Section: ${section}
                                    </h5>
                                </div>
                        `;
                        
                        // Loop through each team in the section
                        for (const [teamId, teamData] of Object.entries(teams)) {
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
                                            <i class="bi bi-diagram-3 me-2"></i>
                                            ${teamData.team_name}
                                            <span class="badge bg-secondary ms-2">${students.length} ${students.length === 1 ? 'member' : 'members'}</span>
                                        </h6>
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-book me-1"></i>
                                            ${teamData.research_title}
                                        </small>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="db-table">
                                                <thead>
                                                    <tr>
                                                        <th>Student No.</th>
                                                        <th>Student Name</th>
                                                        ${panelists.map(([id, name]) => `<th class="text-center">${name}</th>`).join('')}
                                                        <th class="text-center">Average</th>
                                                        <th class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                            `;
                            
                            students.forEach(student => {
                                const avgScore = student.avg_score ? parseFloat(student.avg_score).toFixed(2) : null;
                                
                                // Determine status based on average score
                                let status = 'Pending';
                                let statusClass = 'bg-secondary';
                                
                                if (avgScore !== null) {
                                    if (avgScore >= 75) {
                                        status = 'Passed';
                                        statusClass = 'bg-success';
                                    } else if (avgScore < 75) {
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
                                        const gradeClass = gradeValue >= 75 ? 'text-success' : gradeValue >= 60 ? 'text-warning' : 'text-danger';
                                        gradeColumns += `<td class="text-center ${gradeClass}"><strong>${gradeValue}</strong></td>`;
                                    } else {
                                        gradeColumns += `<td class="text-center text-muted">-</td>`;
                                    }
                                });
                                
                                const avgScoreClass = avgScore >= 75 ? 'text-success' : avgScore >= 60 ? 'text-warning' : avgScore ? 'text-danger' : 'text-muted';
                                const avgScoreDisplay = avgScore !== null ? `<strong>${avgScore}</strong>` : '-';
                                
                                content += `
                                    <tr>
                                        <td><small class="text-muted">${student.student_number || 'N/A'}</small></td>
                                        <td><strong>${student.last_name}, ${student.first_name}</strong></td>
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
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-mortarboard me-2"></i>
                                            Section ${section}
                                            <span class="badge bg-primary ms-2">${students.length} students</span>
                                        </h6>
                                    </div>
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
                        
                        let status = 'Pending';
                        let statusClass = 'bg-secondary';
                        let statusValue = 2; // For sorting
                        
                        if (avgTotalScore !== null) {
                            if (avgTotalScore >= 75) {
                                status = 'Passed';
                                statusClass = 'bg-success';
                                statusValue = 0;
                            } else if (avgTotalScore < 75) {
                                status = 'Failed';
                                statusClass = 'bg-danger';
                                statusValue = 1;
                            }
                        }
                        
                        // Score display helpers
                        const getScoreClass = (score) => {
                            if (!score) return 'text-muted';
                            const val = parseFloat(score);
                            return val >= 75 ? 'text-success' : val >= 60 ? 'text-warning' : 'text-danger';
                        };
                        
                        const groupScoreClass = getScoreClass(avgGroupScore);
                        const soloScoreClass = getScoreClass(avgSoloScore);
                        const totalScoreClass = getScoreClass(avgTotalScore);
                        
                        const groupScoreDisplay = avgGroupScore !== null ? `<strong>${avgGroupScore}</strong>` : '-';
                        const soloScoreDisplay = avgSoloScore !== null ? `<strong>${avgSoloScore}</strong>` : '-';
                        const totalScoreDisplay = avgTotalScore !== null ? `<strong>${avgTotalScore}</strong>` : '-';
                        
                        // Build panelist details for modal (keep for detailed view)
                        let panelistDetailsHTML = '';
                        if (student.panelist_grades && student.panelist_grades.length > 0) {
                            student.panelist_grades.forEach(grade => {
                                if (grade.total_score !== null) {
                                    const gradeValue = parseFloat(grade.total_score).toFixed(2);
                                    const gradeClass = gradeValue >= 75 ? 'text-success' : gradeValue >= 60 ? 'text-warning' : 'text-danger';
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
                                    style="cursor: pointer;">
                                    <td class="text-center text-muted">${studentIdx + 1}</td>
                                    <td class="d-none d-md-table-cell"><small class="text-muted">${student.student_number || 'N/A'}</small></td>
                                    <td><strong>${student.last_name}, ${student.first_name}</strong></td>
                                    <td class="d-none d-lg-table-cell"><small class="text-muted">${student.team_name}</small></td>
                                    <td class="text-center ${groupScoreClass}">${groupScoreDisplay}</td>
                                    <td class="text-center ${soloScoreClass}">${soloScoreDisplay}</td>
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
    // Check if there's a previously selected tab stored in localStorage
    const activeTab = localStorage.getItem("activeTab") || "overview";

    // Deactivate all tab-panes and nav-links
    const allTabPanes = document.querySelectorAll('.tab-pane');
    const allNavLinks = document.querySelectorAll('.nav-link');

    allTabPanes.forEach(pane => {
        pane.classList.remove("show", "active");
    });

    allNavLinks.forEach(link => {
        link.classList.remove("active");
    });

    // Activate the tab and its content
    const activeTabPane = document.getElementById(activeTab);
    const activeNavLink = document.querySelector(`.nav-link[href="#${activeTab}"]`);

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
            // Store the ID of the clicked tab-pane
            const clickedTabId = event.target.getAttribute('href').substring(1);
            localStorage.setItem('activeTab', clickedTabId);
        });
    });

    // Load team overview content when the overview tab is clicked
    document.getElementById('overview-link').addEventListener('click', function() {
        fetchTeamOverview();
    });

    // Load class record when the class record tab is clicked (faculty only)
    <?php if ($_SESSION['usertype'] == 2): ?>
    const classRecordLink = document.getElementById('class-record-link');
    if (classRecordLink) {
        classRecordLink.addEventListener('click', function() {
            loadClassRecord(1);
        });
    }
    
    // Add event listener for view toggle
    const classRecordViewSelect = document.getElementById('classRecordViewSelect');
    if (classRecordViewSelect) {
        classRecordViewSelect.addEventListener('change', function() {
            loadClassRecord(1);
        });
    }
    
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
        
        // Score display helpers
        const getScoreDisplay = (score, label) => {
            const scoreFloat = parseFloat(score);
            if (scoreFloat > 0) {
                const scoreClass = scoreFloat >= 75 ? 'text-success' : scoreFloat >= 60 ? 'text-warning' : 'text-danger';
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
    if (activeTab === "overview") {
        fetchTeamOverview();
    }

    // Load class record on page load if that tab is active
    <?php if ($_SESSION['usertype'] == 2): ?>
    if (activeTab === "class-record") {
        loadClassRecord(1);
    }
    <?php endif; ?>

    // Handle view mode switching between Dashboard and Calendar
    document.querySelectorAll('input[name="viewMode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.id === 'calendar-view') {
                document.getElementById('dashboardView').style.display = 'none';
                document.getElementById('calendarView').style.display = 'block';
                // Initialize calendar for the overview tab when calendar view is selected
                setTimeout(() => {
                    initializeOverviewCalendar();
                }, 100);
            } else {
                document.getElementById('dashboardView').style.display = 'block';
                document.getElementById('calendarView').style.display = 'none';
            }
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
            // Handle event click
            console.log('Event clicked:', info.event);
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
                        eventType: 'defense'
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
                        let badgeClass = 'bg-warning';
                        let badgeText = 'Pending';

                        if (requirement.status === 'approved') {
                            badgeClass = 'bg-success';
                            badgeText = 'Approved';
                        } else if (requirement.status === 'submitted') {
                            badgeClass = 'bg-info';
                            badgeText = 'Submitted';
                        } else if (requirement.status === 'rejected') {
                            badgeClass = 'bg-danger';
                            badgeText = 'Rejected';
                        }

                        const dueDate = new Date(requirement.due_date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });

                        listHtml += `
                                <li class="list-group-item d-flex justify-content-between align-items-center req-li">
                                    <div>
                                        <strong>${requirement.name}</strong>
                                        <br>
                                        <small class="text-muted due-date-txt">Due: ${dueDate}</small>
                                    </div>
                                    <span class="badge ${badgeClass} rounded-pill">${badgeText}</span>
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

// Initialize requirements team selector when document is ready
document.addEventListener("DOMContentLoaded", function() {
    handleRequirementsTeamChange();
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
                                            echo "Administrator";
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
                                    <?php if ($_SESSION['usertype'] != 0): // Hide evaluations for admins - they use dashboard ?>
                                    <a class="nav-link" id="research-evaluation-link" data-bs-toggle="pill"
                                        href="#research-evaluation" role="tab" aria-controls="research-evaluation"
                                        aria-selected="false">
                                        <i class="bi bi-chat-dots me-2 hollow"></i>
                                        <i class="bi bi-chat-dots-fill me-2 filled"></i>
                                        <span class="nav-text">Team Evaluations</span>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['usertype'] == 2): ?>
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
                                    <div id="requirementChecklist" class="row g-4">
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
                                            } elseif ($_SESSION['usertype'] == 2) {
                                                echo "View evaluations for teams you advise or evaluated as panelist";
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
                                                    <th class="d-none d-md-table-cell">Adviser</th>
                                                    <th class="text-center">Evaluations</th>
                                                    <th class="text-center d-none d-sm-table-cell">Avg Score</th>
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

                        <?php if ($_SESSION['usertype'] == 2): ?>
                        <div class="tab-pane fade" id="class-record" role="tabpanel"
                            aria-labelledby="class-record-link">
                            <div class="container-fluid py-4 content-container">
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h3 class="mb-2">Class Record</h3>
                                        <p class="text-muted">Student grades grouped by section, sorted alphabetically</p>
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
                                                        <option value="class">Class View</option>
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
                        <?php endif; ?>

                        <div class="tab-pane fade show active" id="overview" role="tabpanel"
                            aria-labelledby="overview-link">
                            <div class="container-fluid py-4 content-container team-overview">
                                <!-- Header with title and sub-tabs -->
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <h3 class="mb-2">Dashboard</h3>
                                        <p class="text-muted">Track your requirement progress and defense schedule</p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="btn-group z-0" role="group">
                                            <input type="radio" class="btn-check" name="viewMode" id="dashboard-view"
                                                checked>
                                            <label class="btn btn-outline-primary" for="dashboard-view">
                                                <i class="bi bi-grid-3x3"></i> Dashboard
                                            </label>

                                            <input type="radio" class="btn-check" name="viewMode" id="calendar-view">
                                            <label class="btn btn-outline-primary" for="calendar-view">
                                                <i class="bi bi-calendar"></i> Calendar
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
                                        <?php if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 1): ?>
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
                                                            <?php if ($_SESSION['usertype'] == 2 && count($teams) > 1): ?>
                                                            <div class="mb-3">
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
                                                            <?php elseif ($_SESSION['usertype'] == 2 && count($teams) == 1): ?>
                                                            <div class="mb-3">
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
                                                                    class="list-group-item d-flex justify-content-between align-items-center req-li">
                                                                    <div>
                                                                        <strong><?php echo htmlspecialchars($requirement['name']); ?></strong>
                                                                        <br>
                                                                        <small class="text-muted due-date-txt">Due:
                                                                            <?php echo date('M d, Y', strtotime($requirement['due_date'])); ?></small>
                                                                    </div>
                                                                    <?php
                                                                        $badgeClass = 'bg-warning';
                                                                        $badgeText = 'Pending';
                                                                        if ($requirement['status'] === 'approved') {
                                                                            $badgeClass = 'bg-success';
                                                                            $badgeText = 'Approved';
                                                                        } elseif ($requirement['status'] === 'submitted') {
                                                                            $badgeClass = 'bg-info';
                                                                            $badgeText = 'Submitted';
                                                                        } elseif ($requirement['status'] === 'rejected') {
                                                                            $badgeClass = 'bg-danger';
                                                                            $badgeText = 'Rejected';
                                                                        }
                                                                        ?>
                                                                    <span
                                                                        class="badge <?php echo $badgeClass; ?> rounded-pill"><?php echo $badgeText; ?></span>
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
                                                            ds.panelist_id = :user_id1
                                                            OR ds.panelist_id2 = :user_id2
                                                            OR ds.panelist_id3 = :user_id3
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
                                                            <ul class="list-group">
                                                                <?php 
                                                                $current_status = null;
                                                                foreach ($schedules as $schedule):
                                                                $formatted_date = date('F j, Y', strtotime($schedule['schedule_date']));
                                                                $formatted_start_time = date('g:i a', strtotime($schedule['start_time']));
                                                                $formatted_end_time = date('g:i a', strtotime($schedule['end_time']));
                                                                $schedule_id = $schedule['schedule_id'];
                                                                
                                                                $defense_status = $schedule['defense_status'] ?? 'upcoming';
                                                                $has_evaluated = isset($schedule['has_evaluated']) ? $schedule['has_evaluated'] > 0 : false;
                                                                $defense_type = $schedule['defense_type'] ?? 'general';
                                                                
                                                                // Add section headers for different statuses
                                                                if ($_SESSION['usertype'] == 2 && $current_status !== $defense_status) {
                                                                    $current_status = $defense_status;
                                                                    $status_icon = $defense_status === 'ongoing' ? 'bi-clock-history text-warning' : 
                                                                                 ($defense_status === 'upcoming' ? 'bi-calendar-event text-primary' : 'bi-calendar-check text-muted');
                                                                    $status_label = $defense_status === 'ongoing' ? 'Ongoing' : 
                                                                                  ($defense_status === 'upcoming' ? 'Upcoming' : 'Past');
                                                                    echo '<li class="list-group-item bg-light"><strong><i class="bi ' . $status_icon . ' me-2"></i>' . $status_label . ' Defenses</strong></li>';
                                                                }

                                                                $onclick_attr = '';
                                                                $item_class = 'list-group-item defense-item';
                                                                $disabled_message = '';
                                                                
                                                                // Badge for defense type
                                                                $type_badge = '';
                                                                $type_color = 'secondary';
                                                                switch ($defense_type) {
                                                                    case 'title_proposal':
                                                                        $type_color = 'info';
                                                                        $type_badge = 'Title Proposal';
                                                                        break;
                                                                    case 'title_defense':
                                                                        $type_color = 'primary';
                                                                        $type_badge = 'Title Defense';
                                                                        break;
                                                                    case 'final_defense':
                                                                        $type_color = 'success';
                                                                        $type_badge = 'Final Defense';
                                                                        break;
                                                                    case 're-defense':
                                                                        $type_color = 'warning';
                                                                        $type_badge = 'Re-Defense';
                                                                        break;
                                                                    default:
                                                                        $type_badge = ucfirst($defense_type);
                                                                }
                                                                
                                                                // Only allow faculty to access evaluation system
                                                                if ($_SESSION['usertype'] == 2) { // Faculty
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
                                                                    <?php echo $onclick_attr; ?>>
                                                                    <div class="defense-content">
                                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                                            <h6 class="team-name mb-0">
                                                                                <?php echo htmlspecialchars($schedule['team_name']); ?>
                                                                            </h6>
                                                                            <div>
                                                                                <span class="badge bg-<?php echo $type_color; ?> me-1"><?php echo $type_badge; ?></span>
                                                                                <?php if ($_SESSION['usertype'] == 2 && $has_evaluated): ?>
                                                                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Evaluated</span>
                                                                                <?php elseif ($_SESSION['usertype'] == 2 && $defense_status !== 'upcoming'): ?>
                                                                                    <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> Pending</span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="defense-details">
                                                                            <div class="detail-item">
                                                                                <i class="far fa-calendar me-2"></i>
                                                                                <?php echo htmlspecialchars($formatted_date); ?>
                                                                            </div>
                                                                            <div class="detail-item">
                                                                                <i class="far fa-clock me-2"></i>
                                                                                <?php echo htmlspecialchars($formatted_start_time . " - " . $formatted_end_time); ?>
                                                                            </div>
                                                                            <div class="detail-item">
                                                                                <i class="fas fa-door-open me-2"></i>
                                                                                <?php echo htmlspecialchars($schedule['room']); ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php if ($_SESSION['usertype'] == 2 && $defense_status === 'past' && $has_evaluated): ?>
                                                                            <small class="text-muted"><i class="bi bi-info-circle"></i> Click to view your evaluation (read-only)</small>
                                                                        <?php endif; ?>
                                                                        <?php echo $disabled_message; ?>
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
        var teamSelectHtml = '<select id="teamSelect" class="form-select mb-3">';
        <?php if (!empty($teams)) { ?>
        <?php foreach ($teams as $team) { ?>
        teamSelectHtml +=
            '<option id="team_id-<?php echo $team['id']; ?>" value="<?php echo $team['id']; ?>"><?php echo htmlspecialchars($team['name']); ?></option>';
        <?php } ?>
        <?php } else { ?>
        teamSelectHtml += '<option value="">No teams available</option>';
        <?php } ?>
        teamSelectHtml += '</select>';
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
                        '<form id="requirementChecklistForm" method="POST" action="includes/update_requirements.php" enctype="multipart/form-data" class="row g-4">';
                    response.requirements.forEach(function(req) {
                        checklistHtml += `
                        <div class="col-12 col-lg-6 requirement-card-wrapper">
                            <div class="card requirement-adviser-card h-100" id="req-card-${req.id}" data-req-id="${req.id}" data-status="${req.status}">
                                <div class="card-body requirement-card-body">
                                    <!-- Header Section with Title, Due Date, and Checkbox -->
                                    <div class="d-flex justify-content-between align-items-start requirement-header-section">
                                        <div class="requirement-content requirement-info-section">
                                            <label class="form-check-label requirement-title-label" for="req${req.id}">
                                                <strong class="requirement-name">${req.name}</strong>
                                                <p class="mb-1 text-muted requirement-description">${req.description || 'No description provided.'}</p>
                                                ${req.template_file 
                                                    ? `<a href="/dashboard/uploads/requirements/${req.template_file}" class="requirement-template-link" download>
                                                        <i class="bi bi-download"></i> Template
                                                    </a>` 
                                                    : ``
                                                }
                                                <small class="text-muted requirement-due-date">Due: ${new Date(req.due_date).toLocaleDateString()}</small>
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
                                        <label for="status${req.id}" class="form-label requirement-status-label">Status:</label>
                                        <select id="status${req.id}" name="status[${req.id}]" class="form-select form-select-sm requirement-status-select">
                                            <option value="pending" ${req.status === 'pending' ? 'selected' : ''}>Pending</option>
                                            <option value="submitted" ${req.status === 'submitted' ? 'selected' : ''}>Submitted</option>
                                            <option value="approved" ${req.status === 'approved' ? 'selected' : ''}>Approved</option>
                                            <option value="rejected" ${req.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Instructor Feedback Section -->
                                    <div class="requirement-feedback-section">
                                        <div class="requirement-feedback-header">
                                            <i class="bi bi-chat-dots"></i>
                                            <label for="feedback${req.id}" class="requirement-feedback-label">Instructor Feedback</label>
                                        </div>
                                        <textarea id="feedback${req.id}" name="feedback[${req.id}]" class="form-control requirement-feedback-textarea" rows="3" placeholder="Enter your feedback here...">${req.feedback}</textarea>
                                        
                                        <!-- Upload Updated Feedback File Section -->
                                        <div class="requirement-upload-header mt-3">
                                            <i class="bi bi-cloud-upload"></i>
                                            <label for="feedbackFile${req.id}" class="requirement-upload-label">Upload Updated Feedback File</label>
                                        </div>
                                        <div class="upload-controls">
                                            <input class="form-control requirement-file-input" type="file" id="feedbackFile${req.id}" name="feedbackFile[${req.id}]">
                                        </div>
                                        
                                        ${req.feedback_file ? `
                                        <div class="requirement-feedback-file-section">
                                            <a href="./feedback/${req.feedback_file}" class="requirement-feedback-download-btn" download>
                                                <i class="bi bi-download"></i> Download Feedback File
                                            </a>
                                        </div>
                                        ` : ''}
                                    </div>
                                    
                                    ${req.file_name && req.status !== 'pending'
                                        ? `
                                            <!-- Submitted File Section -->
                                            <div class="requirement-submitted-file-section">
                                                <div class="submitted-file-header">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    <h6>Submitted File</h6>
                                                </div>
                                                <div class="submitted-file-name">${req.file_name}</div>
                                                <div class="requirement-file-actions">
                                                    <a href="../assets/uploads/submission/${req.file_name}" class="requirement-download-btn" download>
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                    <a href="../assets/uploads/submission/viewer.html#file=${encodeURIComponent(req.file_name)}" class="requirement-view-btn">
                                                        <i class="far fa-eye"></i> View File
                                                    </a>
                                                    <button type="button" class="btn btn-warning btn-sm revert-submission-btn" 
                                                            data-req-id="${req.id}" data-req-name="${req.name}" 
                                                            title="Revert submission to allow resubmission">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Revert Submission
                                                    </button>
                                                </div>
                                            </div>
                                        ` 
                                        : `
                                            <!-- No Submission Section -->
                                            <div class="requirement-no-submission-section">
                                                <div class="no-submission-header">
                                                    <i class="bi bi-file-earmark-x text-muted"></i>
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
                        '<div class="col-12"><button type="submit" class="btn btn-primary mt-3" id="updateReqsBtn">Update Requirements</button></div></form>';
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

    // Call loadRequirements after the function is defined
    loadRequirements();

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
                        loadRequirements();
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
                                <div class="col-md-6 mb-4 requirement-student-wrapper">
                                    <div class="card requirement-student-card h-100 rounded" id="student-req-card-${req.id}" data-req-id="${req.id}" data-status="${req.status}">
                                        <div class="card-body requirement-student-body rct-cbody">
                                            <h5 class="card-title requirement-student-title rct-ctitle">${req.name}</h5>
                                            <p class="card-text requirement-student-description">${req.description}</p>
                                            ${req.template_file 
                                                ? `<a href="../dashboard/uploads/requirements/${req.template_file}" class="requirement-template-btn" download>
                                                    <i class="bi bi-download"></i> Template
                                                </a>` 
                                                : ``
                                            }
                                            <p class="card-text requirement-student-due-date"><strong>Due date:</strong> ${new Date(req.due_date).toLocaleDateString()}</p>
                                            <p class="card-text requirement-student-status"><strong>Status:</strong> ${req.status}</p>
                                            <p class="card-text requirement-student-feedback"><strong>Feedback:</strong> ${req.feedback}</p>
                                        </div>
                                        
                                        <div class="card-footer requirement-student-feedback-footer">
                                            ${req.feedback_file ? 
                                                `<a href="./feedback/${req.feedback_file}" class="btn btn-secondary requirement-feedback-download-btn" download>Download Feedback File</a>` 
                                                : '<span class="requirement-no-feedback-file">No Uploaded Feedback File</span>'}
                                        </div> 
                                        <!-- Current Submission Section (styled like feedback file) -->
                                        <div class="card-footer requirement-student-submission-footer">
                                            ${req.file_name ? 
                                                `<div class="requirement-current-submission-section">
                                                    <strong>Current Submission:</strong>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="../assets/uploads/submission/${req.file_name}" class="requirement-current-file-link" download title="${req.file_name}">
                                                            <span class="filename-text">${req.file_name}</span>
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                        ${req.status === 'pending' ? `
                                                            <button type="button" class="remove-current-file-btn" data-req-id="${req.id}" title="Remove current file">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        ` : ''}
                                                    </div>
                                                </div>` 
                                                : '<span class="requirement-no-submission-file">No Submitted File</span>'}
                                        </div> 
                                        <?php if ($role === 'leader') { ?>
                                        <div class="card-footer requirement-student-upload-footer rct-cfooter">
                                            <!-- Upload File Form - Allow multiple if allow_multiple_submissions is true -->
                                            <div class="upload-file-section ${req.file_name && req.status !== 'pending' && !req.allow_multiple_submissions ? 'upload-disabled' : ''}">
                                                <strong>Upload ${req.file_name ? 'New' : ''} File:</strong>
                                                <form class="upload-form requirement-upload-form mt-2" data-req-id="${req.id}" enctype="multipart/form-data" action="includes/upload_file.php" method="POST">
                                                    <input type="hidden" name="document_name" value="${req.name}">
                                                    <input type="hidden" name="requirement_id" value="${req.id}">
                                                    <div class="mb-3 requirement-file-input-section">
                                                        <input class="form-control requirement-file-input" type="file" id="file-${req.id}" name="file" 
                                                               ${req.file_name && req.status !== 'pending' && !req.allow_multiple_submissions ? 'disabled' : 'required'}>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary feature-btn requirement-submit-btn" 
                                                            ${req.file_name && req.status !== 'pending' && !req.allow_multiple_submissions ? 'disabled' : ''}>
                                                        Submit ${req.file_name ? 'New' : ''} File
                                                    </button>
                                                    <span class="upload-status requirement-upload-status ms-2 small"></span> 
                                                </form>
                                            </div>
                                        </div>
                                        <?php } ?>
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
    // Moved outside the usertype condition, uses delegation
    $(document).on('submit', '#requirementChecklist .upload-form', function(event) {
        console.log('Student upload form submission intercepted.'); // Added log
        event.preventDefault(); // Prevent default form submission

        var form = $(this);
        var uploadSection = form.closest('.upload-file-section');
        
        // Security check: Prevent submission if upload section is disabled
        if (uploadSection.hasClass('upload-disabled')) {
            console.log('Upload blocked: Section is disabled');
            showToast("Upload Blocked", "File upload is not allowed for this requirement.", "error");
            return false;
        }
        
        // Additional security: Check if submit button is disabled
        var submitButton = form.find('button[type="submit"]');
        if (submitButton.prop('disabled') && !submitButton.hasClass('uploading')) {
            console.log('Upload blocked: Submit button is disabled');
            showToast("Upload Blocked", "File upload is not allowed at this time.", "error");
            return false;
        }

        var formData = new FormData(this);
        var statusSpan = form.find('.upload-status');

        statusSpan.text('Uploading...').removeClass('text-danger text-success');
        submitButton.prop('disabled', true).addClass('uploading');
        console.log('Initiating AJAX upload...'); // Added log

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: formData,
            processData: false, // Important for FormData
            contentType: false, // Important for FormData
            dataType: 'json', // Expect JSON response from upload_file.php
            success: function(response) {
                console.log('AJAX upload success response:', response); // Added log
                if (response.success) {
                    statusSpan.text('Upload successful!').addClass('text-success');
                    showToast("Success!", "File uploaded successfully!", "success");
                    // Refresh the requirements list after a short delay
                    setTimeout(loadRequirements, 1500);
                } else {
                    statusSpan.text('Error: ' + (response.error || 'Unknown error')).addClass('text-danger');
                    showToast("Upload Failed", response.error || 'Unknown error', "error");
                    submitButton.prop('disabled', false).removeClass('uploading');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Log the raw response text to see what the server actually sent
                console.log('Raw response:', jqXHR.responseText);
                statusSpan.text('Upload failed. Please try again.').addClass('text-danger');
                showToast("Upload Failed", "Upload failed. Please try again.", "error");
                console.error("AJAX upload error:", textStatus, errorThrown);
                submitButton.prop('disabled', false).removeClass('uploading');
            }
        });
    });
    // --- End AJAX submission handler ---

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
        const evalCount = team.evaluation_count || 0;
        const scoresPending = team.scores_pending == 1;
        const adviserCol = `<td class="d-none d-md-table-cell">${team.adviser || 'No adviser'}</td>`;
        const researchTitle = team.research_title ? escapeHtml(team.research_title) : '<em class="text-muted">No title yet</em>';
        
        // For students: show "Pending" if scores are less than 1 week old
        let avgScoreDisplay;
        if (usertype == 1 && scoresPending) {
            // Student with pending scores - show pending message
            if (team.avg_total_score) {
                avgScoreDisplay = '<span class="badge bg-warning text-dark" title="Scores will be visible 1 week after evaluation"><i class="fas fa-clock me-1"></i>Pending</span>';
            } else {
                avgScoreDisplay = '<em class="text-muted">N/A</em>';
            }
        } else {
            // Faculty or scores are old enough to show
            const avgScore = team.avg_total_score ? parseFloat(team.avg_total_score).toFixed(2) : 'N/A';
            avgScoreDisplay = avgScore !== 'N/A' ? `<strong>${avgScore}</strong>` : '<em class="text-muted">N/A</em>';
        }
        
        const row = `
            <tr class="evaluation-team-row" data-team-id="${team.team_id}">
                <td>
                    <strong>${escapeHtml(team.team_name)}</strong>
                    ${team.role ? `<span class="badge bg-info ms-2">${team.role}</span>` : ''}
                    <small class="d-block d-md-none text-muted">${researchTitle}</small>
                </td>
                <td class="d-none d-md-table-cell">${researchTitle}</td>
                <td class="d-none d-lg-table-cell">
                    <small>${escapeHtml(team.program)}</small>
                </td>
                ${adviserCol}
                <td class="text-center">
                    <span class="badge bg-primary">${evalCount}</span>
                </td>
                <td class="text-center d-none d-sm-table-cell">
                    ${avgScoreDisplay}
                </td>
                <td class="text-center">
                    <button class="btn btn-sm edit-btn view-eval-details" data-team-id="${team.team_id}">
                        <i class="fas fa-eye"></i><span class="d-none d-sm-inline"> View</span>
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
                            <i class="fas fa-chart-bar me-2"></i>Team Evaluation Details
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
    const members = data.members;
    const usertype = data.usertype;
    
    let detailsHtml = `
        <div class="mb-4">
            <h5 class="border-bottom pb-2">${escapeHtml(team.team_name)}</h5>
            <p class="mb-1"><strong>Research Title:</strong> ${team.research_title ? escapeHtml(team.research_title) : '<em class="text-muted">No title yet</em>'}</p>
            <p class="mb-1"><strong>Program:</strong> ${escapeHtml(team.program)}</p>
            <p class="mb-0"><strong>Adviser:</strong> ${team.adviser || '<em class="text-muted">No adviser</em>'}</p>
        </div>

        <div class="mb-4">
            <h6 class="text-primary">Team Members</h6>
            <div class="row">
    `;

    members.forEach(member => {
        const badgeClass = member.role === 'Adviser' ? 'bg-success' : (member.role === 'Leader' ? 'bg-primary' : 'bg-secondary');
        detailsHtml += `
            <div class="col-md-4 mb-2">
                <span class="badge ${badgeClass} me-2">${member.role}</span>
                ${escapeHtml(member.name)}
            </div>
        `;
    });

    detailsHtml += `
            </div>
        </div>

        <h6 class="text-primary mb-3">Evaluation Scores by Student</h6>
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
                <table class="table table-sm table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Student Name</th>
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
                        const scoreClass = score >= 75 ? 'text-success' : score >= 60 ? 'text-warning' : 'text-danger';
                        return `<td class="text-center ${scoreClass}"><strong>${score.toFixed(2)}</strong></td>`;
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
                const avgClass = avg >= 75 ? 'text-success' : avg >= 60 ? 'text-warning' : 'text-danger';
                avgDisplay = `<strong class="${avgClass}">${avg.toFixed(2)}</strong>`;
            } else {
                avgDisplay = '<span class="text-muted">-</span>';
            }
            
            detailsHtml += `
                <tr>
                    <td><strong>${escapeHtml(student.student_name)}</strong></td>
                    ${panelistCells}
                    <td class="text-center table-primary">${avgDisplay}</td>
                </tr>
            `;
        });
        
        // Add overall team average row
        let overallAvgDisplay;
        if (allStudentAverages.length > 0) {
            const overallAvg = allStudentAverages.reduce((a, b) => a + b, 0) / allStudentAverages.length;
            const overallClass = overallAvg >= 75 ? 'text-success' : overallAvg >= 60 ? 'text-warning' : 'text-danger';
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
        
        // Add comments section if there are any
        let hasComments = false;
        let commentsHtml = `
            <h6 class="text-primary mt-4 mb-3">Evaluator Comments</h6>
            <div class="accordion" id="commentsAccordion">
        `;
        
        let accordionIndex = 0;
        Object.entries(evaluationsByStudent).forEach(([studentId, studentData]) => {
            Object.entries(studentData.panelist_scores || {}).forEach(([panelistId, scoreData]) => {
                if (scoreData.comments && scoreData.comments.trim()) {
                    hasComments = true;
                    commentsHtml += `
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#comment${accordionIndex}">
                                    <small><strong>${escapeHtml(scoreData.evaluator_name)}</strong> → ${escapeHtml(studentData.student_name)}</small>
                                </button>
                            </h2>
                            <div id="comment${accordionIndex}" class="accordion-collapse collapse" data-bs-parent="#commentsAccordion">
                                <div class="accordion-body py-2">
                                    <small>${escapeHtml(scoreData.comments)}</small>
                                </div>
                            </div>
                        </div>
                    `;
                    accordionIndex++;
                }
            });
        });
        
        commentsHtml += `</div>`;
        
        if (hasComments) {
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
