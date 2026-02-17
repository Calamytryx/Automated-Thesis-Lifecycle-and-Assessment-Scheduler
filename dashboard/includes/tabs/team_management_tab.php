<!-- Team Management Tab - Defense Type Overrides & Panelist Management -->
<div class="tab-pane fade" id="team_management" role="tabpanel" aria-labelledby="team_management-tab">
    <div class="container-fluid py-4 content-container" id="team_management-container">
        <!-- Header Row -->
        <div class="row mb-4">
            <div class="col">
                <h3 class="mb-2">Team Management</h3>
                <p class="text-muted">Manage defense types, panelist assignments, and admin overrides for special cases.</p>
            </div>
        </div>

        <!-- Search/Filter Section -->
        <div class="row g-2 mb-3 align-items-end">
            <div class="col-12 col-md-6 col-lg-8">
                <!-- Search container -->
                <div class="users-search-container">
                    <div class="input-group user-control-height m-0">
                        <span class="input-group-text border-0"> 
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-0" id="teamSearchInput" placeholder="Search team name...">
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <button class="btn btn-outline-dark user-control-height w-100" id="refreshTeamsBtn" style="border: 1px solid var(--neutral-300); background-color: var(--neutral-100); color: var(--neutral-800);">
                    <i class="fas fa-sync me-2"></i>Refresh Teams
                </button>
            </div>
        </div>

        <!-- Teams List with Override Controls -->
        <div class="table-responsive">
            <table class="table table-hover db-table" id="team_management-table">
                <thead>
                    <tr>
                        <th>Team Name</th>
                        <th>Current Defense Type</th>
                        <th>Override Status</th>
                        <th>Panelists (Locked)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically populated by AJAX -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" id="pagination">
            <ul class="pagination justify-content-center">
                <!-- Pagination links will be dynamically populated by AJAX -->
            </ul>
        </nav>
    </div>
</div>

<!-- Unified Team Management Modal -->
<div class="modal fade" id="teamManageModal" tabindex="-1" aria-labelledby="teamManageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="teamManageModalLabel">Manage Team Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="teamManageForm">
                    <input type="hidden" id="manageTeamId" name="team_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Team Name</label>
                        <input type="text" id="manageTeamName" class="form-control" readonly>
                    </div>

                    <!-- Defense Type Override Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning bg-opacity-25">
                            <i class="fas fa-edit me-2"></i>Defense Type Override
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="overrideType" class="form-label">Override Defense Type</label>
                                <select class="form-select" id="overrideType" name="override_type">
                                    <option value="">No Override (Use Automatic Detection)</option>
                                    <option value="title_proposal">Title Proposal</option>
                                    <option value="title_defense">Title Defense</option>
                                    <option value="final_defense">Final Defense</option>
                                    <option value="re-defense">Re-Defense</option>
                                </select>
                                <small class="form-text text-muted">
                                    Force this team to be treated as this defense stage.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="overrideReason" class="form-label">Reason for Override</label>
                                <textarea class="form-control" id="overrideReason" name="reason" rows="2" placeholder="E.g., Medical leave, schedule conflict, special approval..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="overrideExpires" class="form-label">Expiration Date (Optional)</label>
                                <input type="date" class="form-control" id="overrideExpires" name="expires_at">
                                <small class="form-text text-muted">Leave blank for permanent override</small>
                            </div>
                        </div>
                    </div>

                    <!-- Locked Panelists Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-info bg-opacity-25">
                            <i class="fas fa-lock me-2"></i>Locked Panelists
                        </div>
                        <div class="card-body">
                            <!-- Current Panelists Display -->
                            <div id="currentPanelistsSection" class="mb-3" style="display:none;">
                                <label class="form-label fw-bold">Current Scheduled Panelists</label>
                                <div id="currentPanelistsList" class="d-flex gap-2 flex-wrap">
                                    <!-- Populated dynamically -->
                                </div>
                                <hr>
                            </div>
                            
                            <p class="text-muted small mb-3">Pre-assign panelists that the scheduler must use for this team. Leave empty for automatic assignment.</p>
                            
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Lock Panelist 1</label>
                                    <select class="form-select" id="lockedPanelist1" name="locked_panelist1">
                                        <option value="">Not Locked</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Lock Panelist 2</label>
                                    <select class="form-select" id="lockedPanelist2" name="locked_panelist2">
                                        <option value="">Not Locked</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="form-label">Lock Panelist 3</label>
                                    <select class="form-select" id="lockedPanelist3" name="locked_panelist3">
                                        <option value="">Not Locked</option>
                                    </select>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-2 mb-0 py-2" role="alert">
                                <small><strong>Note:</strong> Locked panelists will always be assigned to this team's defenses.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="clearAllSettingsBtn">Clear All</button>
                <button type="button" class="btn btn-primary" id="saveTeamSettingsBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 1;
        let availablePanelists = [];

        // Load available panelists (professors/staff)
        const loadPanelists = () => {
            fetch('/dashboard/includes/get_teams_and_staff.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.staff) {
                        // Map to {id, first_name, last_name} format for populatePanelistDropdowns
                        availablePanelists = data.staff.map(s => {
                            const parts = s.name.split(' ');
                            const lastName = parts.pop();
                            const firstName = parts.join(' ');
                            return { id: s.id, first_name: firstName, last_name: lastName };
                        });
                    }
                })
                .catch(err => console.error('Error loading panelists:', err));
        };
        loadPanelists();

        // Populate panelist dropdowns
        const populatePanelistDropdowns = (locked1 = '', locked2 = '', locked3 = '') => {
            const dropdowns = [
                { el: document.getElementById('lockedPanelist1'), val: locked1 },
                { el: document.getElementById('lockedPanelist2'), val: locked2 },
                { el: document.getElementById('lockedPanelist3'), val: locked3 }
            ];

            dropdowns.forEach(dd => {
                dd.el.innerHTML = '<option value="">Not Locked</option>';
                availablePanelists.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user.id;
                    opt.textContent = `${user.first_name} ${user.last_name}`;
                    if (String(dd.val) === String(user.id)) {
                        opt.selected = true;
                    }
                    dd.el.appendChild(opt);
                });
            });
        };

        // Load teams
        const loadTeams = (page = 1) => {
            const search = document.getElementById('teamSearchInput').value;
            
            fetch(`/dashboard/includes/tabs/get_table.php?table=teams&page=${page}&search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('API Error:', data.error);
                        alert('Error loading teams: ' + data.error);
                        return;
                    }

                    if (!data.data || !Array.isArray(data.data)) {
                        console.error('Invalid data format:', data);
                        return;
                    }

                    const tbody = document.querySelector('#team_management-table tbody');
                    if (!tbody) return;
                    tbody.innerHTML = '';

                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No teams found</td></tr>';
                    } else {
                        data.data.forEach(team => {
                            fetch(`/api/admin_overrides.php?action=get_team_defense_info&team_id=${team.id}`)
                                .then(r => r.json())
                                .then(defenseInfo => {
                                    const defenseType = defenseInfo.defense_type || 'Not set';
                                    const override = defenseInfo.override || null;
                                    const panelists = defenseInfo.panelists || [];

                                    const overrideStatus = override && override.active 
                                        ? `<span class="badge override-badge-active">${override.override_type}</span>`
                                        : '<span class="badge override-badge-none">None</span>';

                                    const lockedCount = panelists.filter(p => p.locked).length;
                                    const panelistsInfo = lockedCount > 0
                                        ? `<span class="badge panelist-badge-locked">${lockedCount} Locked</span>`
                                        : '<span class="badge panelist-badge-unlocked">Not locked</span>';

                                    const typeClasses = {
                                        'title_proposal': 'defense-type-title-proposal',
                                        'title_defense': 'defense-type-title-defense',
                                        'final_defense': 'defense-type-final-defense',
                                        're-defense': 'defense-type-re-defense'
                                    };

                                    const defenseTypeLabel = defenseType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                                    tbody.innerHTML += `
                                        <tr>
                                            <td>${team.name}</td>
                                            <td><span class="defense-type-badge ${typeClasses[defenseType] || 'defense-type-default'}">${defenseTypeLabel}</span></td>
                                            <td>${overrideStatus}</td>
                                            <td>${panelistsInfo}</td>
                                            <td class="action-buttons text-center">
                                                <button class="btn btn-sm btn-primary manage-team-btn" 
                                                    data-team-id="${team.id}" 
                                                    data-team-name="${team.name}"
                                                    data-locked1="${team.locked_panelist1 || ''}"
                                                    data-locked2="${team.locked_panelist2 || ''}"
                                                    data-locked3="${team.locked_panelist3 || ''}">
                                                    <i class="fas fa-cog"></i> Manage
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                })
                                .catch(err => console.error('Error fetching defense info for team', team.id, ':', err));
                        });
                    }

                    // Update pagination
                    const pagination = document.querySelector('#team_management-container #pagination');
                    if (!pagination) return;
                    const paginationList = pagination.querySelector('.pagination');
                    paginationList.innerHTML = '';

                    paginationList.innerHTML += `
                        <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page - 1}">&#8249;</a>
                        </li>
                    `;

                    for (let i = 1; i <= data.total_pages; i++) {
                        paginationList.innerHTML += `
                            <li class="page-item ${page === i ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>
                        `;
                    }

                    paginationList.innerHTML += `
                        <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page + 1}">&#8250;</a>
                        </li>
                    `;

                    currentPage = page;
                });
        };

        // Initial load
        loadTeams();

        // Search
        document.getElementById('teamSearchInput').addEventListener('keyup', () => loadTeams(1));

        // Refresh button
        document.getElementById('refreshTeamsBtn').addEventListener('click', () => loadTeams(1));

        // Pagination
        document.addEventListener('click', function(e) {
            if (e.target.closest('.pagination a')) {
                e.preventDefault();
                const page = parseInt(e.target.getAttribute('data-page'));
                loadTeams(page);
            }
        });

        // Manage team button click - opens unified modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.manage-team-btn')) {
                const btn = e.target.closest('.manage-team-btn');
                const teamId = btn.getAttribute('data-team-id');
                const teamName = btn.getAttribute('data-team-name');
                const locked1 = btn.getAttribute('data-locked1');
                const locked2 = btn.getAttribute('data-locked2');
                const locked3 = btn.getAttribute('data-locked3');

                document.getElementById('manageTeamId').value = teamId;
                document.getElementById('manageTeamName').value = teamName;

                // Populate panelist dropdowns with current locked values
                populatePanelistDropdowns(locked1, locked2, locked3);

                // Fetch override info AND current scheduled panelists
                fetch(`/api/admin_overrides.php?action=get_team_defense_info&team_id=${teamId}`)
                    .then(r => r.json())
                    .then(data => {
                        // Set override fields
                        if (data.override && data.override.active) {
                            document.getElementById('overrideType').value = data.override.override_type || '';
                            document.getElementById('overrideReason').value = data.override.reason || '';
                            document.getElementById('overrideExpires').value = data.override.expires_at || '';
                        } else {
                            document.getElementById('overrideType').value = '';
                            document.getElementById('overrideReason').value = '';
                            document.getElementById('overrideExpires').value = '';
                        }

                        // Display current scheduled panelists
                        const currentSection = document.getElementById('currentPanelistsSection');
                        const currentList = document.getElementById('currentPanelistsList');
                        
                        if (data.current_panelists && data.current_panelists.length > 0) {
                            currentSection.style.display = 'block';
                            currentList.innerHTML = data.current_panelists.map(p => 
                                `<span class="badge bg-secondary">${p.name}</span>`
                            ).join('');
                            
                            // Add defense type info if available
                            if (data.defense_type) {
                                const typeLabel = data.defense_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                currentList.innerHTML += `<span class="badge bg-primary ms-2">${typeLabel}</span>`;
                            }
                        } else {
                            currentSection.style.display = 'none';
                            currentList.innerHTML = '';
                        }

                        new bootstrap.Modal(document.getElementById('teamManageModal')).show();
                    });
            }
        });

        // Save all team settings
        document.getElementById('saveTeamSettingsBtn').addEventListener('click', function() {
            const teamId = document.getElementById('manageTeamId').value;
            const overrideType = document.getElementById('overrideType').value;
            const overrideReason = document.getElementById('overrideReason').value;
            const overrideExpires = document.getElementById('overrideExpires').value;
            const locked1 = document.getElementById('lockedPanelist1').value;
            const locked2 = document.getElementById('lockedPanelist2').value;
            const locked3 = document.getElementById('lockedPanelist3').value;

            // Save override if set
            const overridePromise = overrideType ? 
                fetch('/api/admin_overrides.php?action=set_defense_type_override', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `team_id=${teamId}&override_type=${overrideType}&reason=${encodeURIComponent(overrideReason)}&expires_at=${overrideExpires}`
                }).then(r => r.json()) :
                fetch('/api/admin_overrides.php?action=remove_defense_type_override', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `team_id=${teamId}`
                }).then(r => r.json());

            // Save locked panelists
            const panelistPromise = fetch('/api/admin_overrides.php?action=set_locked_panelists', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `team_id=${teamId}&locked_panelist1=${locked1}&locked_panelist2=${locked2}&locked_panelist3=${locked3}`
            }).then(r => r.json());

            Promise.all([overridePromise, panelistPromise])
                .then(([overrideResult, panelistResult]) => {
                    if (overrideResult.success !== false && panelistResult.success !== false) {
                        alert('Team settings saved successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('teamManageModal')).hide();
                        loadTeams(currentPage);
                    } else {
                        alert('Error saving settings: ' + (overrideResult.error || panelistResult.error || 'Unknown error'));
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Error saving settings');
                });
        });

        // Clear all settings
        document.getElementById('clearAllSettingsBtn').addEventListener('click', function() {
            if (confirm('Clear all overrides and locked panelists for this team?')) {
                const teamId = document.getElementById('manageTeamId').value;

                const clearOverride = fetch('/api/admin_overrides.php?action=remove_defense_type_override', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `team_id=${teamId}`
                }).then(r => r.json());

                const clearPanelists = fetch('/api/admin_overrides.php?action=set_locked_panelists', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `team_id=${teamId}&locked_panelist1=&locked_panelist2=&locked_panelist3=`
                }).then(r => r.json());

                Promise.all([clearOverride, clearPanelists])
                    .then(() => {
                        alert('All settings cleared!');
                        bootstrap.Modal.getInstance(document.getElementById('teamManageModal')).hide();
                        loadTeams(currentPage);
                    });
            }
        });
    });
</script>
