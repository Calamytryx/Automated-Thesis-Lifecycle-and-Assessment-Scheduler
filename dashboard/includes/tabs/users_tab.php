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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="input-group me-3" style="width: 300px;">
                            <input type="text" class="form-control" id="userSearchInput" placeholder="Search users...">
                            <button class="btn btn-outline-secondary" type="button" id="userSearchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <select class="form-select me-3" id="userTypeFilter" style="width: 150px;">
                            <option value="all">All Types</option>
                            <option value="0">Admin</option>
                            <option value="1">Student</option>
                            <option value="2">Staff</option>
                        </select>
                    </div>
                    <button class="btn feature-btn add-btn" data-table="users">
                        <i class="fas fa-plus me-2"></i>Add User
                    </button>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Admin Users</h4>
                    <button class="btn feature-btn add-btn" data-table="users" data-usertype="0">
                        <i class="fas fa-plus me-2"></i>Add Admin
                    </button>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Student Users</h4>
                    <button class="btn feature-btn add-btn" data-table="users" data-usertype="1">
                        <i class="fas fa-plus me-2"></i>Add Student
                    </button>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Staff Users</h4>
                    <button class="btn feature-btn add-btn" data-table="users" data-usertype="2">
                        <i class="fas fa-plus me-2"></i>Add Staff
                    </button>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getUserType = (type) => {
                return type === 0 ? 'Admin' : type === 1 ? 'Student' : type === 2 ? 'Staff' : 'Unknown';
            };

            // Function to load all users with search and filter
            const loadAllUsers = (page = 1, search = '', typeFilter = 'all') => {
                let url = `includes/tabs/get_table.php?table=users&page=${page}`;
                
                if (search) {
                    url += `&search=${encodeURIComponent(search)}`;
                }
                
                if (typeFilter !== 'all') {
                    url += `&usertype=${typeFilter}`;
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

            // Function to load users by type
            const loadUsersByType = (type, page = 1) => {
                fetch(`includes/tabs/get_table.php?table=users&usertype=${type}&page=${page}`)
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
            loadAllUsers();
            loadUsersByType(0);
            loadUsersByType(1);
            loadUsersByType(2); 

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
                const typeFilter = document.getElementById('userTypeFilter').value;
                loadAllUsers(1, searchTerm, typeFilter);
            });

            // Handle search on enter key
            document.getElementById('userSearchInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = document.getElementById('userSearchInput').value;
                    const typeFilter = document.getElementById('userTypeFilter').value;
                    loadAllUsers(1, searchTerm, typeFilter);
                }
            });

            // Handle type filter change
            document.getElementById('userTypeFilter').addEventListener('change', function() {
                const searchTerm = document.getElementById('userSearchInput').value;
                const typeFilter = this.value;
                loadAllUsers(1, searchTerm, typeFilter);
            });

            // Handle pagination clicks for all users
            document.querySelector('.pagination-all').addEventListener('click', function(e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        const searchTerm = document.getElementById('userSearchInput').value;
                        const typeFilter = document.getElementById('userTypeFilter').value;
                        loadAllUsers(page, searchTerm, typeFilter);
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
                            // Determine user type from class
                            const type = pg.classList.contains('pagination-admin') ? 0 :
                                pg.classList.contains('pagination-student') ? 1 : 2;
                            loadUsersByType(type, page);
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
                        const typeFilter = document.getElementById('userTypeFilter').value;
                        loadAllUsers(1, searchTerm, typeFilter);
                    } else if (targetId === '#admin-users') {
                        loadUsersByType(0);
                    } else if (targetId === '#student-users') {
                        loadUsersByType(1);
                    } else if (targetId === '#staff-users') {
                        loadUsersByType(2);
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
        });
    </script>
</div>