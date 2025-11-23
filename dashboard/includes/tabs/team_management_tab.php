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

<!-- Override Modal -->
<div class="modal fade" id="overrideModal" tabindex="-1" aria-labelledby="overrideModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="overrideModalLabel">Set Defense Type Override</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="overrideForm">
                    <input type="hidden" id="overrideTeamId" name="team_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Team Name</label>
                        <input type="text" id="overrideTeamName" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="overrideType" class="form-label">Override Defense Type</label>
                        <select class="form-select" id="overrideType" name="override_type" required>
                            <option value="">Select Defense Type</option>
                            <option value="title_proposal">Title Proposal</option>
                            <option value="title_defense">Title Defense</option>
                            <option value="final_defense">Final Defense</option>
                            <option value="re-defense">Re-Defense</option>
                        </select>
                        <small class="form-text text-muted">
                            Force this team to be treated as this defense stage, regardless of other criteria.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="overrideReason" class="form-label">Reason for Override</label>
                        <textarea class="form-control" id="overrideReason" name="reason" rows="3" placeholder="E.g., Medical leave, schedule conflict, special approval..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="overrideExpires" class="form-label">Expiration Date (Optional)</label>
                        <input type="date" class="form-control" id="overrideExpires" name="expires_at">
                        <small class="form-text text-muted">Leave blank for permanent override</small>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <strong>Note:</strong> This override will take precedence over automatic defense type detection.
                        It will remain active until removed or the expiration date is reached.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveOverrideBtn">Save Override</button>
                <button type="button" class="btn btn-danger" id="removeOverrideBtn" style="display:none;">Remove Override</button>
            </div>
        </div>
    </div>
</div>

<!-- Panelist Lock Modal -->
<div class="modal fade" id="panelistLockModal" tabindex="-1" aria-labelledby="panelistLockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="panelistLockModalLabel">Manage Panelist Locks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="panelistLockForm">
                    <input type="hidden" id="lockTeamId" name="team_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Team Name</label>
                        <input type="text" id="lockTeamName" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="lockDefenseType" class="form-label">Defense Type</label>
                        <select class="form-select" id="lockDefenseType" name="defense_type" required>
                            <option value="">Select Defense Type</option>
                            <option value="title_proposal">Title Proposal</option>
                            <option value="title_defense">Title Defense</option>
                            <option value="final_defense">Final Defense</option>
                            <option value="re-defense">Re-Defense</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Panelists for This Stage</label>
                        <div id="panelistsList" class="list-group">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <div class="alert alert-warning" role="alert">
                        <strong>Lock Panelists:</strong> Once locked, these panelists cannot be changed by the scheduler
                        algorithm for this defense stage. Use this to prevent unwanted changes after panelists are assigned.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="unlockPanelistsBtn">Unlock Panelists</button>
                <button type="button" class="btn btn-primary" id="lockPanelistsBtn">Lock Panelists</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 1;

        // Load teams
        const loadTeams = (page = 1) => {
            const search = document.getElementById('teamSearchInput').value;
            
            console.log('Loading teams for page:', page, 'search:', search);
            
            fetch(`/dashboard/includes/tabs/get_table.php?table=teams&page=${page}&search=${encodeURIComponent(search)}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    
                    if (data.error) {
                        console.error('API Error:', data.error);
                        alert('Error loading teams: ' + data.error);
                        return;
                    }

                    if (!data.data || !Array.isArray(data.data)) {
                        console.error('Invalid data format:', data);
                        alert('Invalid response format from server');
                        return;
                    }

                    const tbody = document.querySelector('#team_management-table tbody');
                    if (!tbody) {
                        console.error('Table tbody not found');
                        return;
                    }
                    tbody.innerHTML = '';

                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No teams found</td></tr>';
                    } else {
                        data.data.forEach(team => {
                            fetch(`/api/admin_overrides.php?action=get_team_defense_info&team_id=${team.id}`)
                                .then(r => r.json())
                                .then(defenseInfo => {
                                    console.log('Defense info for team', team.id, ':', defenseInfo);
                                    
                                    const defenseType = defenseInfo.defense_type || 'Not set';
                                    const override = defenseInfo.override || null;
                                    const panelists = defenseInfo.panelists || [];

                                    const overrideStatus = override && override.active 
                                        ? `<span class="badge bg-danger">${override.override_type}</span> (Active)`
                                        : '<span class="badge bg-secondary">None</span>';

                                    const lockedCount = panelists.filter(p => p.locked).length;
                                    const panelistsInfo = lockedCount > 0
                                        ? `<span class="badge bg-success">${lockedCount} Locked</span>`
                                        : '<span class="badge bg-light text-dark">Not locked</span>';

                                    const typeColor = {
                                        'title_proposal': 'info',
                                        'title_defense': 'primary',
                                        'final_defense': 'success',
                                        're-defense': 'warning'
                                    };

                                    tbody.innerHTML += `
                                        <tr>
                                            <td>${team.name}</td>
                                            <td><span class="badge bg-${typeColor[defenseType] || 'secondary'}">${defenseType}</span></td>
                                            <td>${overrideStatus}</td>
                                            <td>${panelistsInfo}</td>
                                            <td class="action-buttons text-center">
                                                <button class="btn btn-sm btn-warning override-btn" data-team-id="${team.id}" data-team-name="${team.name}">
                                                    <i class="fas fa-edit"></i> Override
                                                </button>
                                                <button class="btn btn-sm btn-info panelist-btn" data-team-id="${team.id}" data-team-name="${team.name}">
                                                    <i class="fas fa-lock"></i> Panelists
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
                    if (!pagination) {
                        console.error('Pagination container not found');
                        return;
                    }
                    const paginationList = pagination.querySelector('.pagination');
                    paginationList.innerHTML = '';

                    // Previous
                    paginationList.innerHTML += `
                        <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page - 1}">Previous</a>
                        </li>
                    `;

                    // Page numbers
                    for (let i = 1; i <= data.total_pages; i++) {
                        paginationList.innerHTML += `
                            <li class="page-item ${page === i ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>
                        `;
                    }

                    // Next
                    paginationList.innerHTML += `
                        <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page + 1}">Next</a>
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

        // Override button click
        document.addEventListener('click', function(e) {
            if (e.target.closest('.override-btn')) {
                const btn = e.target.closest('.override-btn');
                const teamId = btn.getAttribute('data-team-id');
                const teamName = btn.getAttribute('data-team-name');

                document.getElementById('overrideTeamId').value = teamId;
                document.getElementById('overrideTeamName').value = teamName;

                // Check if override exists
                fetch(`/api/admin_overrides.php?action=get_team_defense_info&team_id=${teamId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.override && data.override.active) {
                            document.getElementById('overrideType').value = data.override.override_type;
                            document.getElementById('overrideReason').value = data.override.reason;
                            document.getElementById('removeOverrideBtn').style.display = 'block';
                        } else {
                            document.getElementById('overrideType').value = '';
                            document.getElementById('overrideReason').value = '';
                            document.getElementById('removeOverrideBtn').style.display = 'none';
                        }
                        new bootstrap.Modal(document.getElementById('overrideModal')).show();
                    });
            }
        });

        // Save override
        document.getElementById('saveOverrideBtn').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('overrideForm'));
            
            fetch('/api/admin_overrides.php?action=set_defense_type_override', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Override saved successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('overrideModal')).hide();
                    loadTeams(currentPage);
                } else {
                    alert('Error: ' + data.error);
                }
            });
        });

        // Remove override
        document.getElementById('removeOverrideBtn').addEventListener('click', function() {
            if (confirm('Remove this override? The team will revert to automatic defense type detection.')) {
                const teamId = document.getElementById('overrideTeamId').value;
                const formData = new FormData();
                formData.append('team_id', teamId);

                fetch('/api/admin_overrides.php?action=remove_defense_type_override', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Override removed!');
                        bootstrap.Modal.getInstance(document.getElementById('overrideModal')).hide();
                        loadTeams(currentPage);
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            }
        });

        // Panelist lock button
        document.addEventListener('click', function(e) {
            if (e.target.closest('.panelist-btn')) {
                const btn = e.target.closest('.panelist-btn');
                const teamId = btn.getAttribute('data-team-id');
                const teamName = btn.getAttribute('data-team-name');

                document.getElementById('lockTeamId').value = teamId;
                document.getElementById('lockTeamName').value = teamName;

                new bootstrap.Modal(document.getElementById('panelistLockModal')).show();
            }
        });
    });
</script>
