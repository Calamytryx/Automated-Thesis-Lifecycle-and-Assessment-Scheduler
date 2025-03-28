<!-- Teams Tab -->
<div class="tab-pane fade" id="teams" role="tabpanel" aria-labelledby="teams-tab">
    <div class="container-fluid py-4">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Team Management</h3>
                <p class="text-muted">Manage research teams, advisers, and team members</p>
            </div>
        </div>
        
        <div class="alert alert-warning mb-4 warning-table" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Teams Without Research Titles</h5>
            <p>The following teams do not have assigned research titles. Please add titles for these teams in <span class="text-danger">Research Titles Tab</span>.</p>
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
            <div id="no-title-teams-message" class="text-center py-2">Loading...</div>
        </div>
        
        <div class="d-flex justify-content-end align-items-center mb-4 flex-wrap">
            <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                <div class="input-group mb-2 mb-md-0" style="width: 250px;">
                    <input type="text" class="form-control" id="teamSearchInput" placeholder="Search teams...">
                    <button class="btn btn-outline-secondary" type="button" id="teamSearchButton">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select class="form-select mb-2 mb-md-0" id="teamSortSelect" style="width: 180px;">
                    <option value="id:desc">Default (Newest First)</option>
                    <option value="id:asc">Default (Oldest First)</option>
                    <option value="name:asc">Team Name (A-Z)</option>
                    <option value="name:desc">Team Name (Z-A)</option>
                </select>
                <button class="btn feature-btn add-btn" data-table="teams">
                    <i class="fas fa-plus me-2"></i>Add Team
                </button>
            </div>
        </div>
        
        <div class="table-responsive db-table-container">
            <table class="table table-bordered table-hover table-sm db-table" id="teams-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Research Title</th>
                        <th>Adviser</th>
                        <th>Members</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically populated by AJAX -->
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation" id="pagination">
            <ul class="pagination justify-content-center">
                <!-- Pagination links will be dynamically populated by AJAX -->
            </ul>
        </nav>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to load teams without research titles
        const loadTeamsWithoutTitles = () => {
            fetch('includes/tabs/get_teams_without_titles.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('teams-no-title-body');
                    const messageDiv = document.getElementById('no-title-teams-message');
                    
                    tbody.innerHTML = '';
                    
                    if (data.length === 0) {
                        messageDiv.textContent = 'No teams without research titles found.';
                        messageDiv.style.display = 'block';
                    } else {
                        data.forEach(team => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${team.name}</td>
                                    <td>${team.program}</td>
                                </tr>
                            `;
                        });
                        messageDiv.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching teams without titles:', error);
                    document.getElementById('no-title-teams-message').textContent = 
                        'Error loading data. Please try again later.';
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
                                <td colspan="5" class="text-center">No matching teams found</td>
                            </tr>
                        `;
                        return;
                    }
                    
                    data.data.forEach(team => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${team.name}</td>
                                <td>${team.research_title}</td>
                                <td>${team.adviser}</td>
                                <td>${team.team_members}</td>
                                <td class="action-buttons">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button class="btn btn-sm edit-btn" data-table="teams" data-id="${team.id}">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                        <button class="btn btn-sm delete-btn" data-table="teams" data-id="${team.id}">
                                            <i class="fas fa-trash-alt me-1"></i>Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });

                    // Update Pagination
                    const pagination = document.querySelector('#teams .pagination');
                    pagination.innerHTML = '';

                    // Previous Button
                    pagination.innerHTML += `
                        <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">&#8249;</a>
                        </li>
                    `;

                    // Page Numbers
                    for (let i = 1; i <= data.total_pages; i++) {
                        pagination.innerHTML += `
                            <li class="page-item ${page === i ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>
                        `;
                    }

                    // Next Button
                    pagination.innerHTML += `
                        <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">&#8250;</a>
                        </li>
                    `;
                })
                .catch(error => {
                    console.error('Error loading teams:', error);
                });
        };

        // Initial Load
        loadTeamsWithoutTitles();
        loadTeams(1, '', 'id:desc');

        // Handle Search Button Click
        document.getElementById('teamSearchButton').addEventListener('click', function() {
            const searchTerm = document.getElementById('teamSearchInput').value;
            const sortValue = document.getElementById('teamSortSelect').value;
            loadTeams(1, searchTerm, sortValue);
        });

        // Handle Search on Enter Key
        document.getElementById('teamSearchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value;
                const sortValue = document.getElementById('teamSortSelect').value;
                loadTeams(1, searchTerm, sortValue);
            }
        });

        // Handle Sort Dropdown Change
        document.getElementById('teamSortSelect').addEventListener('change', function() {
            const searchTerm = document.getElementById('teamSearchInput').value;
            const sortValue = this.value;
            loadTeams(1, searchTerm, sortValue);
        });

        // Handle Pagination Clicks
        document.querySelector('#teams .pagination').addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.tagName === 'A') {
                const page = parseInt(e.target.getAttribute('data-page'));
                if (!isNaN(page)) {
                    const searchTerm = document.getElementById('teamSearchInput').value;
                    const sortValue = document.getElementById('teamSortSelect').value;
                    loadTeams(page, searchTerm, sortValue);
                }
            }
        });

        // Initialize when the teams tab becomes visible
        document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                if (e.target.id === 'teams-tab') {
                    loadTeamsWithoutTitles();
                    const searchTerm = document.getElementById('teamSearchInput').value;
                    const sortValue = document.getElementById('teamSortSelect').value;
                    loadTeams(1, searchTerm, sortValue);
                }
            });
        });
    });
</script>
