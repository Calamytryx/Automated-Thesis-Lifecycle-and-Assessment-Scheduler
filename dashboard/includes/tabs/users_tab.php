<!-- Users Tab -->
<div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">User Management</h3>
                <p class="text-muted">Manage system users, including admins, students, and staff members</p>
            </div>
        </div>

        <!-- User Management Controls -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <!-- Left side: Search and User Type Filter -->
                    <div class="d-flex align-items-center gap-3">
                        <!-- Search container -->
                        <div class="users-search-container" style="width: 280px;">
                            <div class="input-group mt-0">
                                <span class="input-group-text border-0"> 
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control border-0" id="userSearchInput" placeholder="Search users...">
                            </div>
                        </div>
                        
                        <!-- User Type Dropdown -->
                        <div class="users-tab-controls">
                            <select class="form-select" id="userTypeSelect" style="width: 200px;">
                                <option value="all" data-icon="fas fa-th-list"><i class="fas fa-th-list me-2"></i>All Users</option>
                                <option value="0" data-icon="fas fa-user-shield"><i class="fas fa-user-shield me-2"></i>Admins</option>
                                <option value="1" data-icon="fas fa-user-graduate"><i class="fas fa-user-graduate me-2"></i>Students</option>
                                <option value="2" data-icon="fas fa-user-tie"><i class="fas fa-user-tie me-2"></i>Staff</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Right side: Sort and Action buttons -->
                    <div class="d-flex align-items-center flex-wrap gap-2 users-tab-controls">
                        <select class="form-select mb-2 mb-md-0" id="userSortSelect" style="width: 180px;">
                            <option value="id:desc">Default (Newest First)</option>
                            <option value="id:asc">Default (Oldest First)</option>
                            <option value="username:asc">Username (A-Z)</option>
                            <option value="username:desc">Username (Z-A)</option>
                            <option value="first_name:asc">First Name (A-Z)</option>
                            <option value="first_name:desc">First Name (Z-A)</option>
                            <option value="last_name:asc">Last Name (A-Z)</option>
                            <option value="last_name:desc">Last Name (Z-A)</option>
                        </select>
                        <button class="btn feature-btn add-btn" data-table="users" id="addUserBtn">
                            <i class="fas fa-plus me-2"></i>Add User
                        </button>
                        <!-- Bulk Add Users button (only show for students) -->
                        <button class="btn feature-btn bulk-add-btn" data-table="users" id="bulkAddBtn" style="display: none;">
                            <i class="fas fa-users me-2"></i>Bulk Add Students
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Management Content -->
        <div class="row">
            <div class="col-12">
                <!-- Single table that changes content based on selected user type -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table" id="allUsersTable" data-usertype="all">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>User Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="usersPagination"><!-- Users pagination --></ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bulk Add Users Modal -->
    <div class="modal fade" id="bulkAddModal" tabindex="-1" aria-labelledby="bulkAddModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="bulkAddForm">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="bulkAddModalLabel">Bulk Add Students</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Force bulk added users to be type 1 (Student) -->
                        <input type="hidden" name="usertype" value="1">
                        <!-- NEW: Let user select the upload method -->
                        <div class="mb-3">
                            <label class="form-label">Upload Method</label>
                            <div>
                                <label class="me-3">
                                    <input type="radio" name="upload_method" value="file" checked> CSV File
                                </label>
                                <label class="me-3">
                                    <input type="radio" name="upload_method" value="paste"> Paste Text
                                </label>
                                <label>
                                    <input type="radio" name="upload_method" value="form"> Manual Form
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="bulkFileInput" class="form-label">Upload Excel/CSV File</label>
                            <input type="file" class="form-control" id="bulkFileInput" name="bulkFile" accept=".csv, .xls, .xlsx">
                        </div>
                        <div class="mb-3">
                            <label for="bulkTextInput" class="form-label">Paste Bulk Data</label>
                            <textarea class="form-control" id="bulkTextInput" name="bulkTextInput" rows="5" placeholder="Paste CSV data here"></textarea>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="bulkAddTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name <small>(format: Lastname, Firstname [no middle])</small></th>
                                        <th>Program</th>
                                        <th>No Username <br><small>(if checked, a username will be auto-generated)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control" name="users[0][id]"></td>
                                        <td><input type="text" class="form-control" name="users[0][name]"></td>
                                        <td><input type="text" class="form-control" name="users[0][program]"></td>
                                        <td class="text-center">
                                            <input type="checkbox" name="users[0][no_username]">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Number input to add multiple rows -->
                        <div class="mb-3">
                            <label for="rowCountInput" class="form-label">Add Rows: </label>
                            <input type="number" id="rowCountInput" class="form-control" style="width:100px; display:inline-block" min="1" value="1">
                            <button type="button" class="btn btn-secondary" id="addBulkRow">Add Rows</button>
                        </div>
                        </div>
                        <hr>
                    <div class="mb-3">
                        <a href="#" id="downloadCsvTemplate" class="btn btn-sm btn-secondary">Download CSV Template</a>
                    </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="bulkAddSubmit" class="btn btn-info">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getUserType = (type) => {
                return type === 0 ? 'Admin' : type === 1 ? 'Student' : type === 2 ? 'Staff' : 'Unknown';
            };

            // Function to load users based on type with search and sorting
            const loadUsers = (userType = 'all', page = 1, search = '', sort = 'id:desc') => {
                let url = `includes/tabs/get_table.php?table=users&page=${page}`;
                
                // Add usertype filter if not 'all'
                if (userType !== 'all') {
                    url += `&usertype=${userType}`;
                }
                
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

                        // Clear the table body
                        const tableBody = document.querySelector('#allUsersTable tbody');
                        if (!tableBody) {
                            console.error('Could not find table body');
                            return;
                        }
                        
                        tableBody.innerHTML = '';

                        // Show a message if no results
                        if (data.data.length === 0) {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="text-center">No matching users found</td>
                                </tr>
                            `;
                            return;
                        }

                        // Populate rows
                        data.data.forEach(user => {
                            tableBody.innerHTML += `
                                <tr>
                                    <td>${user.username}</td>
                                    <td>${user.email}</td>
                                    <td>${user.first_name}</td>
                                    <td>${user.last_name}</td>
                                    <td>${getUserType(user.usertype)}</td>
                                    <td class="action-buttons"> 
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm edit-btn" data-table="users" data-id="${user.id}">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm delete-btn" data-table="users" data-id="${user.id}">
                                                <i class="fas fa-trash-alt me-1"></i>Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        // Build pagination
                        const pagination = document.querySelector('#usersPagination');
                        if (!pagination) {
                            console.error('Could not find pagination');
                            return;
                        }
                        
                        pagination.innerHTML = '';
                        pagination.innerHTML += `
                            <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page - 1}">&#8249;</a>
                            </li>
                        `;
                        for (let i = 1; i <= data.total_pages; i++) {
                            pagination.innerHTML += `
                                <li class="page-item ${page === i ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
                            `;
                        }
                        pagination.innerHTML += `
                            <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page + 1}">&#8250;</a>
                            </li>
                        `; 
                    })
                    .catch(error => {
                        console.error('Error loading users:', error);
                    });
            };

            // Function to update button visibility based on user type
            const updateButtonVisibility = (userType) => {
                const addBtn = document.getElementById('addUserBtn');
                const bulkAddBtn = document.getElementById('bulkAddBtn');
                
                if (userType === '1') { // Students
                    bulkAddBtn.style.display = 'inline-flex';
                    if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add Student';
                } else if (userType === '0') { // Admins
                    bulkAddBtn.style.display = 'none';
                    if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add Admin';
                } else if (userType === '2') { // Staff
                    bulkAddBtn.style.display = 'none';
                    if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add Staff';
                } else { // All users
                    bulkAddBtn.style.display = 'none';
                    if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add User';
                }
                
                // Update data-usertype attribute for add button
                if (addBtn && userType !== 'all') {
                    addBtn.setAttribute('data-usertype', userType);
                } else if (addBtn) {
                    addBtn.removeAttribute('data-usertype');
                }
            };

            // Function to get current filters
            const getCurrentFilters = () => {
                return {
                    userType: document.getElementById('userTypeSelect').value,
                    search: document.getElementById('userSearchInput').value,
                    sort: document.getElementById('userSortSelect').value
                };
            };

            // Function to reload current view
            const reloadCurrentView = (page = 1) => {
                const filters = getCurrentFilters();
                loadUsers(filters.userType, page, filters.search, filters.sort);
            };

            // Initialize on page load
            loadUsers('all', 1, '', 'id:desc');
            updateButtonVisibility('all');

            // Handle user type dropdown change
            document.getElementById('userTypeSelect').addEventListener('change', function() {
                const userType = this.value;
                updateButtonVisibility(userType);
                reloadCurrentView(1);
            });

            // Handle search input
            document.getElementById('userSearchInput').addEventListener('keyup', function(e) {
                reloadCurrentView(1);
            });

            // Handle sort dropdown change
            document.getElementById('userSortSelect').addEventListener('change', function() {
                reloadCurrentView(1);
            });

            // Handle pagination clicks
            document.querySelector('#usersPagination').addEventListener('click', function(e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        reloadCurrentView(page);
                    }
                }
            });

            // Function to initialize data loading when the users tab becomes visible
            const initializeUsersTab = () => {
                console.log('Initializing users tab...');
                const usersTab = document.getElementById('users');
                if (usersTab && (usersTab.classList.contains('active') || usersTab.classList.contains('show'))) {
                    console.log('Users tab is visible, loading data...');
                    reloadCurrentView(1);
                }
            };

            // Also initialize when the main dashboard tab for users becomes visible
            document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    if (e.target.id === 'users-tab') {
                        console.log('Users tab shown event triggered');
                        initializeUsersTab();
                    }
                });
            });

            // Call initialization function on page load with multiple attempts
            setTimeout(initializeUsersTab, 100);
            setTimeout(initializeUsersTab, 500);
            setTimeout(initializeUsersTab, 1000);

            // Add event listeners for upload method radio buttons
            document.querySelectorAll('input[name="upload_method"]').forEach(radio => {
                radio.addEventListener('change', updateUploadMethodVisibility);
            });

            function updateUploadMethodVisibility() {
                const selected = document.querySelector('input[name="upload_method"]:checked').value;
                const fileInputDiv = document.getElementById('bulkFileInput').closest('.mb-3');
                const textInputDiv = document.getElementById('bulkTextInput').closest('.mb-3');
                const manualFormDiv = document.getElementById('bulkAddTable').closest('.table-responsive');
                if (selected === 'file') {
                    fileInputDiv.style.display = '';
                    textInputDiv.style.display = 'none';
                    manualFormDiv.style.display = 'none';
                } else if (selected === 'paste') {
                    fileInputDiv.style.display = 'none';
                    textInputDiv.style.display = '';
                    manualFormDiv.style.display = 'none';
                } else { // selected === 'form'
                    fileInputDiv.style.display = 'none';
                    textInputDiv.style.display = 'none';
                    manualFormDiv.style.display = '';
                }
            }

            // Run on DOM load
            updateUploadMethodVisibility();
        });
    </script>
</div>