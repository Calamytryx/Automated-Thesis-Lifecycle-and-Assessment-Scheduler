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
                                    <input type="text" class="form-control border-0" id="userSearchInput" placeholder="Search users...">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-3 col-lg-2">
                            <!-- User Type Dropdown -->
                            <div class="users-tab-controls">
                                <select class="form-select user-control-height" id="userTypeSelect">
                                    <option value="all">All Users</option>
                                    <option value="0">Admins</option>
                                    <option value="1">Students</option>
                                    <option value="2">Staff</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-3 col-lg-3">
                            <!-- Sort Dropdown -->
                            <select class="form-select user-control-height" id="userSortSelect">
                                <option value="id:desc">Default (Newest First)</option>
                                <option value="id:asc">Default (Oldest First)</option>
                                <option value="username:asc">Username (A-Z)</option>
                                <option value="username:desc">Username (Z-A)</option>
                                <option value="first_name:asc">First Name (A-Z)</option>
                                <option value="first_name:desc">First Name (Z-A)</option>
                                <option value="last_name:asc">Last Name (A-Z)</option>
                                <option value="last_name:desc">Last Name (Z-A)</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-2 col-lg-3">
                            <!-- Action buttons container -->
                            <div class="d-flex gap-2">
                                <button class="btn feature-btn add-btn user-control-height flex-fill" data-table="users" id="addUserBtn">
                                    <i class="fas fa-plus me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Add User</span>
                                    <span class="d-lg-none">Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bulk Add Button Row (conditional) -->
                    <div class="row">
                        <div class="col-12">
                            <button class="btn feature-btn bulk-add-btn user-control-height w-100 w-md-auto" data-table="users" id="bulkAddBtn" style="display: none;">
                                <i class="fas fa-users me-2"></i>Bulk Add Students
                            </button>
                        </div>
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
                                <th class="d-none d-md-table-cell">Username</th>
                                <th class="d-table-cell d-md-none">User</th>
                                <th class="d-none d-lg-table-cell">Email</th>
                                <th class="d-none d-sm-table-cell">First Name</th>
                                <th class="d-none d-sm-table-cell">Last Name</th>
                                <th class="d-none d-md-table-cell">User Type</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center flex-wrap mt-2" id="usersPagination"><!-- Users pagination --></ul>
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

            const getUserTypeBadge = (type) => {
                const userType = getUserType(type);
                const badgeClass = userType.toLowerCase();
                return `<span class="user-type-badge ${badgeClass}">${userType}</span>`;
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
                            const isSmallScreen = window.innerWidth < 768;
                            const colspan = isSmallScreen ? "3" : "6";
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="${colspan}" class="text-center">No matching users found</td>
                                </tr>
                            `;
                            return;
                        }

                        // Clear any existing dropdowns
                        document.querySelectorAll('.meatball-dropdown-portal').forEach(portal => portal.remove());
                        
                        // Populate rows
                        data.data.forEach(user => {
                            tableBody.innerHTML += `
                                <tr>
                                    <td class="d-none d-md-table-cell">${user.username}</td>
                                    <td class="d-table-cell d-md-none">
                                        <div class="fw-bold">${user.username}</div>
                                        <div class="text-muted small d-sm-none">${user.email}</div>
                                        <div class="text-muted small d-sm-none">${user.first_name} ${user.last_name}</div>
                                        <div class="d-sm-none mt-1">${getUserTypeBadge(user.usertype)}</div>
                                    </td>
                                    <td class="d-none d-lg-table-cell">${user.email}</td>
                                    <td class="d-none d-sm-table-cell">${user.first_name}</td>
                                    <td class="d-none d-sm-table-cell">${user.last_name}</td>
                                    <td class="d-none d-md-table-cell">${getUserTypeBadge(user.usertype)}</td>
                                    <td class="action-buttons text-center"> 
                                        <button class="meatball-btn" data-user-id="${user.id}" aria-label="Actions">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                            
                            // Create dropdown portal outside table
                            const dropdownPortal = document.createElement('div');
                            dropdownPortal.className = 'meatball-dropdown-portal';
                            dropdownPortal.id = `dropdown-${user.id}`;
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
                            dropdownPortal.innerHTML = `
                                <button class="meatball-dropdown-item edit-item edit-btn" data-table="users" data-id="${user.id}">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </button>
                                <button class="meatball-dropdown-item delete-item delete-btn" data-table="users" data-id="${user.id}">
                                    <i class="fas fa-trash-alt"></i>
                                    Delete
                                </button>
                            `;
                            document.body.appendChild(dropdownPortal);
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
                    if (addBtn) {
                        addBtn.innerHTML = `
                            <i class="fas fa-plus me-1 d-none d-sm-inline"></i>
                            <span class="d-none d-sm-inline">Add Student</span>
                            <span class="d-sm-none">Add</span>
                        `;
                    }
                } else if (userType === '0') { // Admins
                    bulkAddBtn.style.display = 'none';
                    if (addBtn) {
                        addBtn.innerHTML = `
                            <i class="fas fa-plus me-1 d-none d-sm-inline"></i>
                            <span class="d-none d-sm-inline">Add Admin</span>
                            <span class="d-sm-none">Add</span>
                        `;
                    }
                } else if (userType === '2') { // Staff
                    bulkAddBtn.style.display = 'none';
                    if (addBtn) {
                        addBtn.innerHTML = `
                            <i class="fas fa-plus me-1 d-none d-sm-inline"></i>
                            <span class="d-none d-sm-inline">Add Staff</span>
                            <span class="d-sm-none">Add</span>
                        `;
                    }
                } else { // All users
                    bulkAddBtn.style.display = 'none';
                    if (addBtn) {
                        addBtn.innerHTML = `
                            <i class="fas fa-plus me-1 d-none d-sm-inline"></i>
                            <span class="d-none d-sm-inline">Add User</span>
                            <span class="d-sm-none">Add</span>
                        `;
                    }
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

            // Meatball menu functionality
            document.addEventListener('click', function(e) {
                // Only handle meatball clicks if we're in the users tab
                const usersTab = document.getElementById('users');
                if (!usersTab || (!usersTab.classList.contains('active') && !usersTab.classList.contains('show'))) {
                    return;
                }
                
                // Handle meatball button clicks
                if (e.target.closest('.meatball-btn') && e.target.closest('#users')) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const btn = e.target.closest('.meatball-btn');
                    const userId = btn.getAttribute('data-user-id');
                    const dropdown = document.getElementById(`dropdown-${userId}`);
                    
                    if (!dropdown) {
                        console.error('Dropdown not found for user:', userId);
                        return;
                    }
                    
                    const isCurrentlyOpen = dropdown.style.display === 'block';
                    
                    // Close all other dropdowns first
                    document.querySelectorAll('.meatball-dropdown-portal').forEach(dd => {
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
                    document.querySelectorAll('.meatball-dropdown-portal').forEach(dd => {
                        dd.style.display = 'none';
                    });
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
                    
                    // The existing edit-btn and delete-btn event handlers will handle the action
                    // since we've preserved the same classes on the dropdown items
                }
            });
        });
    </script>
</div>