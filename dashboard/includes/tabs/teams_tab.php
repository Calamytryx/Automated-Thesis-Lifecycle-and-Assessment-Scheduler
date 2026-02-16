<!-- Teams Tab -->
<div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-8 col-md-9">
                <h3 class="mb-2">Team Management</h3>
                <p class="text-muted">Manage research teams, advisers, and team members</p>
                <?php if ($_SESSION['usertype'] == 0): ?>
                <div class="mt-2">
                    <a href="#rubrics" class="tab-redirect-link" onclick="document.getElementById('rubrics-tab').click(); return false;">
                        <i class="bi bi-list-check"></i>
                        <span>Manage Program Rubrics</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="col-4 col-md-3 text-end">
                <button type="button" class="btn btn-warning btn-sm position-relative" id="warningTeamsBtn" style="display: none;" title="Teams without research titles" data-bs-toggle="tooltip" data-bs-placement="left">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="warningTeamsCount">0</span>
                </button>
            </div>
        </div>
        
        <!-- Team Management Controls -->
        <div class="row">
            <div class="col-12">
                <!-- Mobile-first responsive layout -->
                <div class="user-controls-container p-0 mt-3">
                    <!-- Search and Filter Row -->
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col-12 col-md-4 col-lg-4">
                            <!-- Search container -->
                            <div class="users-search-container">
                                <div class="input-group user-control-height m-0">
                                    <span class="input-group-text border-0"> 
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-0" id="teamSearchInput" placeholder="Search teams...">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-3 col-lg-3">
                            <!-- Sort Dropdown -->
                            <select class="form-select user-control-height" id="teamSortSelect">
                                <option value="id:desc">Default (Newest First)</option>
                                <option value="id:asc">Default (Oldest First)</option>
                                <option value="name:asc">Team Name (A-Z)</option>
                                <option value="name:desc">Team Name (Z-A)</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-5 col-lg-5">
                            <!-- Action buttons container -->
                            <div class="d-flex gap-2">
                                <button class="btn feature-btn bulk-add-btn user-control-height flex-fill" data-table="teams" id="bulkAddTeamsBtn" style="display:none;">
                                    <i class="fas fa-upload me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Bulk Add Teams</span>
                                    <span class="d-lg-none">Bulk Add</span>
                                </button> 
                                <button class="btn feature-btn add-btn user-control-height flex-fill" data-table="teams" id="addTeamBtn" onclick="addNewTeamMember()">
                                    <i class="fas fa-plus me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Add Team</span>
                                    <span class="d-lg-none">Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Team Management Content -->
        <div class="row">
            <div class="col-12">
                <!-- Teams table that displays all teams with search and sorting -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table" id="teams-table" data-table="teams">
                        <thead>
                            <tr>
                                <th class="d-none d-md-table-cell">Team Name</th>
                                <th class="d-table-cell d-md-none">Team</th>
                                <th class="d-none d-lg-table-cell">Research Title</th>
                                <th class="d-none d-sm-table-cell">Program</th>
                                <th class="d-none d-md-table-cell">Adviser</th>
                                <th class="d-none d-lg-table-cell">Leader</th>
                                <th class="d-none d-xl-table-cell">Members</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center flex-wrap mt-2" id="teamsPagination"><!-- Teams pagination --></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Add Teams Modal -->
<div class="modal fade" id="bulkAddTeamsModal" tabindex="-1" aria-labelledby="bulkAddTeamsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="bulkAddTeamsForm">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="bulkAddTeamsModalLabel">Bulk Add Teams</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Upload Method Selection -->
                    <div class="mb-3">
                        <label class="form-label">Upload Method</label>
                        <div>
                            <label class="me-3">
                                <input type="radio" name="teams_upload_method" value="file" checked> CSV File
                            </label>
                            <label class="me-3">
                                <input type="radio" name="teams_upload_method" value="paste"> Paste Text
                            </label>
                            <label>
                                <input type="radio" name="teams_upload_method" value="form"> Manual Form
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bulkTeamsFileInput" class="form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" id="bulkTeamsFileInput" name="bulkTeamsFile" accept=".csv, .xls, .xlsx">
                    </div>
                    <div class="mb-3">
                        <label for="bulkTeamsTextInput" class="form-label">Paste Bulk Data</label>
                        <textarea class="form-control" id="bulkTeamsTextInput" name="bulkTeamsTextInput" rows="5" placeholder="Team Name, Research Title, Area of Expertise, Program, Members (optional)"></textarea>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="bulkAddTeamsTable">
                            <thead>
                                <tr>
                                    <th>Team Name</th>
                                    <th>Research Title <small>(optional)</small></th>
                                    <th>Area of Expertise</th>
                                    <th>Program</th>
                                    <th>Members <small>(optional, comma-separated)</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" class="form-control" name="teams[0][name]"></td>
                                    <td><input type="text" class="form-control" name="teams[0][research_title]"></td>
                                    <td><input type="text" class="form-control" name="teams[0][area_of_expertise]"></td>
                                    <td><input type="text" class="form-control" name="teams[0][program]"></td>
                                    <td><input type="text" class="form-control" name="teams[0][members]"></td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- Number input to add multiple rows -->
                        <div class="mb-3">
                            <label for="teamRowCountInput" class="form-label">Add Rows: </label>
                            <input type="number" id="teamRowCountInput" class="form-control" style="width:100px; display:inline-block" min="1" value="1">
                            <button type="button" class="btn btn-secondary" id="addBulkTeamRow">Add Rows</button>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <a href="#" id="downloadTeamsCsvTemplate" class="btn btn-sm btn-secondary">Download CSV Template</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="bulkAddTeamsSubmit" class="btn btn-info">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Warning Modal for Teams Without Titles -->
<div class="modal fade" id="teamsWarningModal" tabindex="-1" aria-labelledby="teamsWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="teamsWarningModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Teams Without Research Titles
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">The following teams do not have assigned research titles. Please add titles for these teams in the <span class="text-danger fw-bold">Research Titles Tab</span>.</p>
                <div class="table-responsive">
                    <table class="table table-sm table-warning table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Team Name</th>
                                <th>Program</th>
                            </tr>
                        </thead>
                        <tbody id="teams-no-title-body">
                            <!-- Teams with no title will be loaded via JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div id="no-title-teams-message" class="text-center py-2" style="display: none;">All teams have research titles assigned.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to load teams without research titles
        const loadTeamsWithoutTitles = () => {
            fetch('includes/tabs/get_teams_without_titles.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('teams-no-title-body');
                    const messageDiv = document.getElementById('no-title-teams-message');
                    const warningBtn = document.getElementById('warningTeamsBtn');
                    const warningCount = document.getElementById('warningTeamsCount');
                    
                    tbody.innerHTML = '';
                    
                    if (data.length === 0) {
                        // Hide warning button when no teams without titles
                        warningBtn.style.display = 'none';
                        messageDiv.style.display = 'block';
                        messageDiv.textContent = 'All teams have research titles assigned.';
                    } else {
                        // Show warning button with count
                        warningBtn.style.display = 'inline-flex';
                        warningCount.textContent = data.length;
                        
                        // Populate table in modal
                        data.forEach(team => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${team.name}</td>
                                    <td>${team.program || 'N/A'}</td>
                                </tr>
                            `;
                        });
                        messageDiv.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching teams without titles:', error);
                    // Hide warning button on error
                    document.getElementById('warningTeamsBtn').style.display = 'none';
                });
        };

        // Function to load teams with search and sorting
        const loadTeams = (page = 1, search = '', sort = 'id:desc') => {
            let url = `includes/tabs/get_table.php?table=teams&page=${page}`;
            
            if (search) {
                url += `&search=${encodeURIComponent(search)}`;
            }
            
            if (sort) {
                const [sortField, sortOrder] = sort.split(':');
                url += `&sort_by=${encodeURIComponent(sortField)}&sort_dir=${encodeURIComponent(sortOrder)}`;
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    const tbody = document.querySelector('#teams-table tbody');
                    tbody.innerHTML = '';
                    
                    // Show a message if no results
                    if (data.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-search fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No teams found matching your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        
                        // Clear pagination
                        document.getElementById('teamsPagination').innerHTML = '';
                        return;
                    }

                    // Clear any existing dropdowns
                    document.querySelectorAll('.meatball-dropdown-portal').forEach(portal => portal.remove());
                    
                    // Clear any existing dropdowns
                    document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-team-"]').forEach(portal => portal.remove());
                    
                    data.data.forEach(team => {
                        // Process team members
                        let adviser = team.adviser || ''; 
                        let leader = '';
                        let members = [];

                        if (team.team_members && typeof team.team_members === 'string') {
                            const memberParts = team.team_members.split(', ');
                            memberParts.forEach(part => {
                                const matchWithRole = part.match(/^(.*?)\s\((.*?)\)$/i);
                                let memberName = part.trim();
                                let role = '';

                                if (matchWithRole) { 
                                    memberName = matchWithRole[1].trim();
                                    role = matchWithRole[2].trim().toLowerCase();
                                }

                                if (role === 'adviser') {
                                    adviser = memberName;
                                }

                                if (role === 'leader') {
                                    leader = memberName;
                                } else if (role === 'member'){
                                    const nameParts = memberName.split(' ');
                                    const surname = nameParts.length > 1 ? nameParts[nameParts.length - 1] : memberName;
                                    members.push({
                                        name: memberName,
                                        surname: surname
                                    });
                                }
                            });
                        }
                        
                        members.sort((a, b) => a.surname.localeCompare(b.surname));
                        
                        const membersHtml = members.length > 0 
                            ? '<ul class="mb-0 ps-3 small">' + 
                              members.map(m => `<li>${m.name}</li>`).join('') +
                              '</ul>'
                            : '<span class="text-muted">No members</span>';
                        
                        tbody.innerHTML += `
                            <tr>
                                <td class="d-none d-md-table-cell">${team.name}</td>
                                <td class="d-table-cell d-md-none">
                                    <div class="fw-semibold">${team.name}</div>
                                    <div class="text-muted small">${team.program || 'N/A'}</div>
                                </td>
                                <td class="d-none d-lg-table-cell text-truncate" style="max-width: 200px;" title="${team.research_title || 'No title assigned'}">${team.research_title || '<span class="text-muted">No title</span>'}</td>
                                <td class="d-none d-sm-table-cell">${team.program || 'N/A'}</td>
                                <td class="d-none d-md-table-cell">${adviser || '<span class="text-muted">No adviser</span>'}</td>
                                <td class="d-none d-lg-table-cell">${leader || '<span class="text-muted">No leader</span>'}</td>
                                <td class="d-none d-xl-table-cell">${membersHtml}</td>
                                <td class="action-buttons text-center">
                                    <button class="meatball-btn" data-team-id="${team.id}" aria-label="Actions">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        
                        // Create dropdown portal outside table
                        const dropdownPortal = document.createElement('div');
                        dropdownPortal.className = 'meatball-dropdown-portal';
                        dropdownPortal.id = `dropdown-team-${team.id}`; // Use team- prefix to avoid conflicts
                        console.log('Creating dropdown portal for team:', team.id);
                        dropdownPortal.style.cssText = `
                            position: fixed;
                            background: white;
                            border: 1px solid #dee2e6;
                            border-radius: 6px;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                            z-index: 9999;
                            min-width: 120px;
                            padding: 4px 0;
                            display: none;
                        `;
                        
                        // Build dropdown items based on user type and team state
                        let dropdownHTML = `
                            <button class="meatball-dropdown-item edit-item edit-btn" data-table="teams" data-id="${team.id}">
                                <i class="fas fa-edit"></i>
                                Edit
                            </button>
                            <button class="meatball-dropdown-item delete-item delete-btn" data-table="teams" data-id="${team.id}">
                                <i class="fas fa-trash-alt"></i>
                                Delete
                            </button>
                        `;
                        
                        // 🎓 ADD ADVISER OPTION: For professors on non-title-proposal teams
                        // Check if: user is professor (usertype 2), professor is not already adviser, and title is not "title proposal"
                        if (currentUserType === 2 && team.adviser !== '<span class="text-muted">No adviser</span>' && team.adviser && !team.adviser.toLowerCase().includes(currentUserId)) {
                            // Professor is not yet adviser and someone is already adviser (so we can't become adviser)
                            // Skip adding button
                        } else if (currentUserType === 2 && (!team.adviser || team.adviser === '<span class="text-muted">No adviser</span>') && team.research_title && !team.research_title.toLowerCase().includes('title proposal')) {
                            // Professor can become adviser if: no adviser exists and title is not a title proposal
                            dropdownHTML += `
                                <button class="meatball-dropdown-item become-adviser-btn" data-team-id="${team.id}">
                                    <i class="fas fa-user-tie"></i>
                                    Become Adviser
                                </button>
                            `;
                        }
                        
                        dropdownPortal.innerHTML = dropdownHTML;
                        document.body.appendChild(dropdownPortal);
                    });

                    // Update Pagination
                    const pagination = document.getElementById('teamsPagination');
                    pagination.innerHTML = '';

                    if (data.total_pages > 1) {
                        // Previous Button
                        pagination.innerHTML += `
                            <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        `;

                        // Page Numbers with ellipsis
                        const startPage = Math.max(1, page - 2);
                        const endPage = Math.min(data.total_pages, page + 2);

                        if (startPage > 1) {
                            pagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                            if (startPage > 2) {
                                pagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                            }
                        }

                        for (let i = startPage; i <= endPage; i++) {
                            pagination.innerHTML += `
                                <li class="page-item ${page === i ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
                            `;
                        }

                        if (endPage < data.total_pages) {
                            if (endPage < data.total_pages - 1) {
                                pagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                            }
                            pagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${data.total_pages}">${data.total_pages}</a></li>`;
                        }

                        // Next Button
                        pagination.innerHTML += `
                            <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading teams:', error);
                });
        };

        // Function to get current filters
        const getCurrentFilters = () => {
            return {
                search: document.getElementById('teamSearchInput').value,
                sort: document.getElementById('teamSortSelect').value
            };
        };

        // Function to reload current view
        const reloadCurrentView = (page = 1) => {
            const filters = getCurrentFilters();
            loadTeams(page, filters.search, filters.sort);
        };

        // Expose reloadCurrentView to global scope for use by main app.js.php
        window.reloadCurrentTeamsView = reloadCurrentView;

        // Initialize on page load
        loadTeamsWithoutTitles();
        loadTeams(1, '', 'id:desc');

        // Initialize Bootstrap tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Handle search input with debouncing
        let searchTimeout;
        document.getElementById('teamSearchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                reloadCurrentView(1);
            }, 300);
        });

        // Handle sort dropdown change
        document.getElementById('teamSortSelect').addEventListener('change', function() {
            reloadCurrentView(1);
        });

        // Handle pagination clicks
        document.getElementById('teamsPagination').addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.tagName === 'A') {
                const page = parseInt(e.target.getAttribute('data-page'));
                if (!isNaN(page)) {
                    reloadCurrentView(page);
                }
            }
        });

        // Function to initialize teams tab
        const initializeTeamsTab = () => {
            console.log('Initializing teams tab...');
            const teamsTab = document.getElementById('teams');
            if (teamsTab && (teamsTab.classList.contains('active') || teamsTab.classList.contains('show'))) {
                loadTeamsWithoutTitles();
                reloadCurrentView(1);
            }
        };

        // Initialize when the teams tab becomes visible
        document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                if (e.target.id === 'teams-tab') {
                    initializeTeamsTab();
                }
            });
            
            // Clean up teams dropdowns when switching away from teams tab
            tab.addEventListener('hide.bs.tab', function(e) {
                if (e.target.id === 'teams-tab') {
                    document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-team-"]').forEach(portal => {
                        portal.style.display = 'none';
                    });
                }
            });
        });

        // Call initialization function on page load with multiple attempts
        setTimeout(initializeTeamsTab, 100);
        setTimeout(initializeTeamsTab, 500);
        setTimeout(initializeTeamsTab, 1000);

        // Add event listeners for bulk add teams button
        document.getElementById('bulkAddTeamsBtn').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Bulk Add Teams button clicked');
            const modal = new bootstrap.Modal(document.getElementById('bulkAddTeamsModal'));
            modal.show();
        });

        // Add event listener for warning teams button
        document.getElementById('warningTeamsBtn').addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Warning Teams button clicked');
            const modal = new bootstrap.Modal(document.getElementById('teamsWarningModal'));
            modal.show();
        });

        // Add event listeners for upload method radio buttons
        document.querySelectorAll('input[name="teams_upload_method"]').forEach(radio => {
            radio.addEventListener('change', updateTeamsUploadMethodVisibility);
        });

        function updateTeamsUploadMethodVisibility() {
            const selected = document.querySelector('input[name="teams_upload_method"]:checked').value;
            const fileInputDiv = document.getElementById('bulkTeamsFileInput').closest('.mb-3');
            const textInputDiv = document.getElementById('bulkTeamsTextInput').closest('.mb-3');
            const manualFormDiv = document.getElementById('bulkAddTeamsTable').closest('.table-responsive');
            
            // Hide all sections first
            fileInputDiv.style.display = 'none';
            textInputDiv.style.display = 'none';
            manualFormDiv.style.display = 'none';
            
            if (selected === 'file') {
                fileInputDiv.style.display = 'block';
            } else if (selected === 'paste') {
                textInputDiv.style.display = 'block';
            } else { // selected === 'form'
                manualFormDiv.style.display = 'block';
            }
        }

        // Run on DOM load
        updateTeamsUploadMethodVisibility();

        // Add bulk teams row functionality
        document.getElementById('addBulkTeamRow').addEventListener('click', function() {
            const count = parseInt(document.getElementById('teamRowCountInput').value) || 1;
            const tbody = document.querySelector('#bulkAddTeamsTable tbody');
            const currentRowCount = tbody.children.length;
            
            for (let i = 0; i < count; i++) {
                const newRowIndex = currentRowCount + i;
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td><input type="text" class="form-control" name="teams[${newRowIndex}][name]"></td>
                    <td><input type="text" class="form-control" name="teams[${newRowIndex}][research_title]"></td>
                    <td><input type="text" class="form-control" name="teams[${newRowIndex}][area_of_expertise]"></td>
                    <td><input type="text" class="form-control" name="teams[${newRowIndex}][program]"></td>
                    <td><input type="text" class="form-control" name="teams[${newRowIndex}][members]"></td>
                `;
                tbody.appendChild(newRow);
            }
        });

        // CSV template download for teams
        document.getElementById('downloadTeamsCsvTemplate').addEventListener('click', function(e) {
            e.preventDefault();
            const csvContent = 'Team Name,Research Title,Area of Expertise,Program,Members\n';
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'teams_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

        // Bulk add teams form submission
        document.getElementById('bulkAddTeamsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Bulk add teams form submitted');
            
            const formData = new FormData(this);
            
            // If pasted bulk text is provided, append it
            const bulkText = document.getElementById('bulkTeamsTextInput').value.trim();
            if (bulkText !== "") {
                formData.append('bulk_teams', bulkText);
            }
            
            fetch('includes/bulk_add_teams.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message (you can implement showToast function)
                    alert('Teams added successfully');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkAddTeamsModal'));
                    modal.hide();
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + (data.message || 'Failed to add teams'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding teams');
            });
        });

        // Meatball menu functionality
        document.addEventListener('click', function(e) {
            // Only handle meatball clicks if we're in the teams tab
            const teamsTab = document.getElementById('teams');
            if (!teamsTab || (!teamsTab.classList.contains('active') && !teamsTab.classList.contains('show'))) {
                return;
            }
            
            // Handle meatball button clicks
            if (e.target.closest('.meatball-btn') && e.target.closest('#teams')) {
                e.preventDefault();
                e.stopPropagation();
                
                const btn = e.target.closest('.meatball-btn');
                const teamId = btn.getAttribute('data-team-id');
                const dropdown = document.getElementById(`dropdown-team-${teamId}`);
                
                if (!dropdown) {
                    console.error('Dropdown not found for team:', teamId);
                    return;
                }
                
                const isCurrentlyOpen = dropdown.style.display === 'block';
                
                // Close all other teams dropdowns first
                document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-team-"]').forEach(dd => {
                    dd.style.display = 'none';
                });
                
                // Toggle current dropdown
                if (!isCurrentlyOpen) {
                    // Position the dropdown relative to the button
                    const btnRect = btn.getBoundingClientRect();
                    const viewportWidth = window.innerWidth;
                    const dropdownWidth = 120;
                    
                    // Calculate position
                    let left = btnRect.right - dropdownWidth;
                    let top = btnRect.bottom + 5;
                    
                    // Adjust for mobile screens
                    if (viewportWidth < 768) {
                        // On mobile, center the dropdown below the button
                        left = btnRect.left + (btnRect.width / 2) - (dropdownWidth / 2);
                    }
                    
                    // Ensure dropdown doesn't go off-screen
                    if (left < 10) left = 10;
                    if (left + dropdownWidth > viewportWidth - 10) {
                        left = viewportWidth - dropdownWidth - 10;
                    }
                    
                    dropdown.style.position = 'fixed';
                    dropdown.style.top = `${top}px`;
                    dropdown.style.left = `${left}px`;
                    dropdown.style.display = 'block';
                }
            } 
            // Close dropdown when clicking outside
            else if (!e.target.closest('.meatball-dropdown-portal') && !e.target.closest('.meatball-btn')) {
                // Only close teams-specific dropdowns when in teams tab
                const teamsTab = document.getElementById('teams');
                if (teamsTab && (teamsTab.classList.contains('active') || teamsTab.classList.contains('show'))) {
                    document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-team-"]').forEach(dd => {
                        dd.style.display = 'none';
                    });
                }
            }
        });

        // Handle meatball dropdown item clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.meatball-dropdown-item')) {
                const item = e.target.closest('.meatball-dropdown-item');
                
                // Close the dropdown
                const dropdown = item.closest('.meatball-dropdown-portal');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
                
                // Handle "Become Adviser" action
                if (item.classList.contains('become-adviser-btn')) {
                    e.preventDefault();
                    const teamId = item.getAttribute('data-team-id');
                    
                    // Add professor as adviser to team
                    fetch('includes/add_items.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            table: 'team_members',
                            team_id: teamId,
                            user_id: currentUserId,
                            role: 'adviser'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Success', 'You are now the adviser for this team!', 'success');
                            // Reload teams table
                            window.reloadCurrentTeamsView();
                        } else {
                            showToast('Error', data.message || 'Failed to become adviser', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error', 'An error occurred while trying to become adviser', 'error');
                    });
                    
                    return; // Stop event propagation
                }
                
                // The existing edit-btn and delete-btn event handlers will handle the action
                // since we've preserved the same classes on the dropdown items
            }
        });
    });
</script>
