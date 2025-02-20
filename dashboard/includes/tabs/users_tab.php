<!-- Users Tab -->
<div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn feature-btn add-btn" data-table="users">
            <i class="fas fa-plus"></i>Add User
        </button>
    </div>
    <div class="accordion" id="usersAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingAdmin">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmin">Admins</button>
            </h2>
            <div id="collapseAdmin" class="accordion-collapse collapse show" aria-labelledby="headingAdmin" data-bs-parent="#usersAccordion">
                <div class="accordion-body">
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
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingStudent">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudent">Students</button>
            </h2>
            <div id="collapseStudent" class="accordion-collapse collapse" aria-labelledby="headingStudent" data-bs-parent="#usersAccordion">
                <div class="accordion-body">
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
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingStaff">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStaff">Staff</button>
            </h2>
            <div id="collapseStaff" class="accordion-collapse collapse" aria-labelledby="headingStaff" data-bs-parent="#usersAccordion">
                <div class="accordion-body">
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
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getUserType = (type) => {
                return type === 0 ? 'Admin' : type === 1 ? 'Student' : type === 2 ? 'Staff' : 'Unknown';
            };

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
                    });
            };

            // Load each table on initial page load
            loadUsersByType(0);
            loadUsersByType(1);
            loadUsersByType(2);

            // Handle pagination clicks for each panel
            document.querySelectorAll('.pagination').forEach(pg => {
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
        });
    </script>
</div>