<!-- Users Tab -->
<div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
    <div class="container-fluid py-4">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">User Management</h3>
                <p class="text-muted">Manage system users, including admins, students, and staff members</p>
            </div>
        </div>

        <!-- User Management Navigation Tabs -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs border-bottom border-dark" id="userManagementTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-users-tab" data-bs-toggle="tab" 
                                data-bs-target="#all-users" type="button" role="tab" 
                                aria-controls="all-users" aria-selected="true">
                            <i class="fas fa-th-list me-2"></i>All Users
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="admin-users-tab" data-bs-toggle="tab" 
                                data-bs-target="#admin-users" type="button" role="tab" 
                                aria-controls="admin-users" aria-selected="false">
                            <i class="fas fa-user-shield me-2"></i>Admins
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="student-users-tab" data-bs-toggle="tab" 
                                data-bs-target="#student-users" type="button" role="tab" 
                                aria-controls="student-users" aria-selected="false">
                            <i class="fas fa-user-graduate me-2"></i>Students
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="staff-users-tab" data-bs-toggle="tab" 
                                data-bs-target="#staff-users" type="button" role="tab" 
                                aria-controls="staff-users" aria-selected="false">
                            <i class="fas fa-user-tie me-2"></i>Staff
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- User Management Tab Content -->
        <div class="tab-content pt-4" id="userManagementTabsContent">
            <!-- All Users Tab -->
            <div class="tab-pane fade show active" id="all-users" role="tabpanel" aria-labelledby="all-users-tab">
                <div class="d-flex justify-content-end align-items-center mb-4 flex-wrap">
                    <div class="d-flex align-items-center justify-content-end flex-wrap gap-2">
                        <div class="input-group mb-2 mb-md-0" style="width: 250px;">
                            <input type="text" class="form-control" id="userSearchInput" placeholder="Search users...">
                            <button class="btn btn-outline-secondary" type="button" id="userSearchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
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
                        <button class="btn feature-btn add-btn" data-table="users">
                            <i class="fas fa-plus me-2"></i>Add User
                        </button>
                        <!-- NEW Bulk Add Users button -->
                        <button class="btn feature-btn bulk-add-btn" data-table="users">
                            <i class="fas fa-users me-2"></i>Bulk Add Students
                        </button>
                    </div>
                </div>
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
                    <ul class="pagination pagination-all justify-content-center"><!-- All users pagination --></ul>
                </nav>
            </div>

            <!-- Admin Users Tab -->
            <div class="tab-pane fade" id="admin-users" role="tabpanel" aria-labelledby="admin-users-tab">
                <div class="d-flex justify-content-end align-items-center mb-4 flex-wrap">
                    <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                        <div class="input-group mb-2 mb-md-0" style="width: 250px;">
                            <input type="text" class="form-control" id="adminSearchInput" placeholder="Search admins...">
                            <button class="btn btn-outline-secondary" type="button" id="adminSearchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <select class="form-select mb-2 mb-md-0" id="adminSortSelect" style="width: 180px;">
                            <option value="id:desc">Default (Newest First)</option>
                            <option value="id:asc">Default (Oldest First)</option>
                            <option value="username:asc">Username (A-Z)</option>
                            <option value="username:desc">Username (Z-A)</option>
                            <option value="first_name:asc">First Name (A-Z)</option>
                            <option value="first_name:desc">First Name (Z-A)</option>
                            <option value="last_name:asc">Last Name (A-Z)</option>
                            <option value="last_name:desc">Last Name (Z-A)</option>
                        </select>
                        <button class="btn feature-btn add-btn" data-table="users" data-usertype="0">
                            <i class="fas fa-plus me-2"></i>Add Admin
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table" data-usertype="0">
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
                    <ul class="pagination pagination-admin justify-content-center"><!-- Admin pagination --></ul>
                </nav>
            </div>

            <!-- Student Users Tab -->
            <div class="tab-pane fade" id="student-users" role="tabpanel" aria-labelledby="student-users-tab">
                <div class="d-flex justify-content-end align-items-center mb-4 flex-wrap">
                    <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                        <div class="input-group mb-2 mb-md-0" style="width: 250px;">
                            <input type="text" class="form-control" id="studentSearchInput" placeholder="Search students...">
                            <button class="btn btn-outline-secondary" type="button" id="studentSearchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <select class="form-select mb-2 mb-md-0" id="studentSortSelect" style="width: 180px;">
                            <option value="id:desc">Default (Newest First)</option>
                            <option value="id:asc">Default (Oldest First)</option>
                            <option value="username:asc">Username (A-Z)</option>
                            <option value="username:desc">Username (Z-A)</option>
                            <option value="first_name:asc">First Name (A-Z)</option>
                            <option value="first_name:desc">First Name (Z-A)</option>
                            <option value="last_name:asc">Last Name (A-Z)</option>
                            <option value="last_name:desc">Last Name (Z-A)</option>
                        </select>
                        <button class="btn feature-btn add-btn" data-table="users" data-usertype="1">
                            <i class="fas fa-plus me-2"></i>Add Student
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table" data-usertype="1">
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
                    <ul class="pagination pagination-student justify-content-center"><!-- Student pagination --></ul>
                </nav>
            </div>

            <!-- Staff Users Tab -->
            <div class="tab-pane fade" id="staff-users" role="tabpanel" aria-labelledby="staff-users-tab">
                <div class="d-flex justify-content-end align-items-center mb-4 flex-wrap">
                    <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                        <div class="input-group mb-2 mb-md-0" style="width: 250px;">
                            <input type="text" class="form-control" id="staffSearchInput" placeholder="Search staff...">
                            <button class="btn btn-outline-secondary" type="button" id="staffSearchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <select class="form-select mb-2 mb-md-0" id="staffSortSelect" style="width: 180px;">
                            <option value="id:desc">Default (Newest First)</option>
                            <option value="id:asc">Default (Oldest First)</option>
                            <option value="username:asc">Username (A-Z)</option>
                            <option value="username:desc">Username (Z-A)</option>
                            <option value="first_name:asc">First Name (A-Z)</option>
                            <option value="first_name:desc">First Name (Z-A)</option>
                            <option value="last_name:asc">Last Name (A-Z)</option>
                            <option value="last_name:desc">Last Name (Z-A)</option>
                        </select>
                        <button class="btn feature-btn add-btn" data-table="users" data-usertype="2">
                            <i class="fas fa-plus me-2"></i>Add Staff
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table" data-usertype="2">
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
                    <ul class="pagination pagination-staff justify-content-center"><!-- Staff pagination --></ul>
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
                        <hr>
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
                        </div>
                        <!-- Number input to add multiple rows -->
                        <div class="mb-3">
                            <label for="rowCountInput" class="form-label">Add Rows: </label>
                            <input type="number" id="rowCountInput" class="form-control" style="width:100px; display:inline-block" min="1" value="1">
                            <button type="button" class="btn btn-secondary" id="addBulkRow">Add Rows</button>
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

            // Function to load all users with search and sorting
            const loadAllUsers = (page = 1, search = '', sort = 'id:desc') => {
                let url = `includes/tabs/get_table.php?table=users&page=${page}`;
                
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
                            console.error('Could not find table body for all users');
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
                        const pagination = document.querySelector('.pagination-all');
                        if (!pagination) {
                            console.error('Could not find pagination for all users');
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
                        console.error('Error loading all users:', error);
                    });
            };

            // Function to load users by type with search and sorting
            const loadUsersByType = (type, page = 1, search = '', sort = 'id:desc') => {
                let url = `includes/tabs/get_table.php?table=users&usertype=${type}&page=${page}`;
                
                // Add search parameter if provided
                if (search) {
                    url += `&search=${encodeURIComponent(search)}`;
                }
                
                // Add sorting parameters
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

                        // Clear the correct table body
                        const tableBody = document.querySelector('.db-table[data-usertype="' + type + '"] tbody');
                        if (!tableBody) {
                            console.error(`Could not find table body for usertype ${type}`);
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

                        // Build pagination for this type
                        const pagination = document.querySelector('.pagination-' + (type === 0 ? 'admin' : type === 1 ? 'student' : 'staff'));
                        if (!pagination) {
                            console.error(`Could not find pagination for usertype ${type}`);
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
                        console.error(`Error loading users of type ${type}:`, error);
                    });
            };

            // Function to initialize data loading when the users tab becomes visible
            const initializeUsersTab = () => {
                console.log('Initializing users tab...');
                // Check if the users tab is currently visible
                const usersTab = document.getElementById('users');
                if (usersTab && (usersTab.classList.contains('active') || usersTab.classList.contains('show'))) {
                    console.log('Users tab is visible, loading data...');
                    // Load data for all tabs to ensure they're ready
                    loadAllUsers();
                    loadUsersByType(0);
                    loadUsersByType(1);
                    loadUsersByType(2);
                } else {
                    console.log('Users tab is not visible yet');
                }
            };

            // Load data immediately on page load
            console.log('DOM loaded, loading user data...');
            loadAllUsers(1, '', 'id:desc');

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
            // This ensures data is loaded if the users tab is visible by default
            setTimeout(initializeUsersTab, 100);
            setTimeout(initializeUsersTab, 500);
            setTimeout(initializeUsersTab, 1000);

            // Handle search button click
            document.getElementById('userSearchButton').addEventListener('click', function() {
                const searchTerm = document.getElementById('userSearchInput').value;
                const sortValue = document.getElementById('userSortSelect').value;
                loadAllUsers(1, searchTerm, sortValue);
            });

            // Handle search on enter key
            document.getElementById('userSearchInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = document.getElementById('userSearchInput').value;
                    const sortValue = document.getElementById('userSortSelect').value;
                    loadAllUsers(1, searchTerm, sortValue);
                }
            });

            // Handle sort dropdown change
            document.getElementById('userSortSelect').addEventListener('change', function() {
                const searchTerm = document.getElementById('userSearchInput').value;
                const sortValue = this.value;
                loadAllUsers(1, searchTerm, sortValue);
            });

            // Handle pagination clicks for all users
            document.querySelector('.pagination-all').addEventListener('click', function(e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        const searchTerm = document.getElementById('userSearchInput').value;
                        const sortValue = document.getElementById('userSortSelect').value;
                        loadAllUsers(page, searchTerm, sortValue);
                    }
                }
            });

            // Handle pagination clicks for each user type
            document.querySelectorAll('.pagination-admin, .pagination-student, .pagination-staff').forEach(pg => {
                pg.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (e.target.tagName === 'A') {
                        const page = parseInt(e.target.getAttribute('data-page'));
                        if (!isNaN(page)) {
                            // Determine user type from class and get the appropriate search and sort terms
                            if (pg.classList.contains('pagination-admin')) {
                                const searchTerm = document.getElementById('adminSearchInput').value;
                                const sortValue = document.getElementById('adminSortSelect').value;
                                loadUsersByType(0, page, searchTerm, sortValue);
                            } else if (pg.classList.contains('pagination-student')) {
                                const searchTerm = document.getElementById('studentSearchInput').value;
                                const sortValue = document.getElementById('studentSortSelect').value;
                                loadUsersByType(1, page, searchTerm, sortValue);
                            } else if (pg.classList.contains('pagination-staff')) {
                                const searchTerm = document.getElementById('staffSearchInput').value;
                                const sortValue = document.getElementById('staffSortSelect').value;
                                loadUsersByType(2, page, searchTerm, sortValue);
                            }
                        }
                    }
                });
            });

            // Handle tab changes to refresh data
            document.querySelectorAll('#userManagementTabs .nav-link').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    const targetId = e.target.getAttribute('data-bs-target');
                    console.log('User management tab changed to:', targetId);
                    if (targetId === '#all-users') {
                        const searchTerm = document.getElementById('userSearchInput').value;
                        const sortValue = document.getElementById('userSortSelect').value;
                        loadAllUsers(1, searchTerm, sortValue);
                    } else if (targetId === '#admin-users') {
                        const searchTerm = document.getElementById('adminSearchInput').value;
                        const sortValue = document.getElementById('adminSortSelect').value;
                        loadUsersByType(0, 1, searchTerm, sortValue);
                    } else if (targetId === '#student-users') {
                        const searchTerm = document.getElementById('studentSearchInput').value;
                        const sortValue = document.getElementById('studentSortSelect').value;
                        loadUsersByType(1, 1, searchTerm, sortValue);
                    } else if (targetId === '#staff-users') {
                        const searchTerm = document.getElementById('staffSearchInput').value;
                        const sortValue = document.getElementById('staffSortSelect').value;
                        loadUsersByType(2, 1, searchTerm, sortValue);
                    }
                });
            });

            // Force Bootstrap to properly initialize the tabs
            // This ensures the active tab is properly displayed
            const userManagementTabs = document.getElementById('userManagementTabs');
            if (userManagementTabs) {
                const allUsersTab = document.getElementById('all-users-tab');
                if (allUsersTab) {
                    // Create and dispatch a click event to ensure the tab is properly initialized
                    setTimeout(() => {
                        console.log('Forcing all-users-tab initialization');
                        const clickEvent = new MouseEvent('click', {
                            bubbles: true,
                            cancelable: true,
                            view: window
                        });
                        allUsersTab.dispatchEvent(clickEvent);
                    }, 200);
                }
            }

            // Add event listeners for admin search
            document.getElementById('adminSearchButton').addEventListener('click', function() {
                const searchTerm = document.getElementById('adminSearchInput').value;
                const sortValue = document.getElementById('adminSortSelect').value;
                loadUsersByType(0, 1, searchTerm, sortValue);
            });

            document.getElementById('adminSearchInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value;
                    const sortValue = document.getElementById('adminSortSelect').value;
                    loadUsersByType(0, 1, searchTerm, sortValue);
                }
            });

            document.getElementById('adminSortSelect').addEventListener('change', function() {
                const searchTerm = document.getElementById('adminSearchInput').value;
                const sortValue = this.value;
                loadUsersByType(0, 1, searchTerm, sortValue);
            });

            // Add event listeners for student search
            document.getElementById('studentSearchButton').addEventListener('click', function() {
                const searchTerm = document.getElementById('studentSearchInput').value;
                const sortValue = document.getElementById('studentSortSelect').value;
                loadUsersByType(1, 1, searchTerm, sortValue);
            });

            document.getElementById('studentSearchInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value;
                    const sortValue = document.getElementById('studentSortSelect').value;
                    loadUsersByType(1, 1, searchTerm, sortValue);
                }
            });

            document.getElementById('studentSortSelect').addEventListener('change', function() {
                const searchTerm = document.getElementById('studentSearchInput').value;
                const sortValue = this.value;
                loadUsersByType(1, 1, searchTerm, sortValue);
            });

            // Add event listeners for staff search
            document.getElementById('staffSearchButton').addEventListener('click', function() {
                const searchTerm = document.getElementById('staffSearchInput').value;
                const sortValue = document.getElementById('staffSortSelect').value;
                loadUsersByType(2, 1, searchTerm, sortValue);
            });

            document.getElementById('staffSearchInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value;
                    const sortValue = document.getElementById('staffSortSelect').value;
                    loadUsersByType(2, 1, searchTerm, sortValue);
                }
            });

            document.getElementById('staffSortSelect').addEventListener('change', function() {
                const searchTerm = document.getElementById('staffSearchInput').value;
                const sortValue = this.value;
                loadUsersByType(2, 1, searchTerm, sortValue);
            });
        });
    </script>
</div>