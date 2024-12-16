<!-- Users Tab -->
<div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn feature-btn add-btn" data-table="users">
        <i class="fas fa-plus"></i>Add User
        </button>
    </div>
    <div class="table-responsive db-table-container">
        <table class="table table-bordered table-hover table-sm db-table">
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
            <tbody>
            </tbody>
        </table>

        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <!-- Pagination loaded via AJAX -->
            </ul>
        </nav>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
            const loadUsers = (page = 1) => {
                fetch(`includes/tabs/get_table.php?table=users&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                    console.error(data.error);
                    return;
                    }

                    const tbody = document.querySelector('.db-table tbody');
                    tbody.innerHTML = '';
                    data.data.forEach(user => {
                    tbody.innerHTML += `
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

                    const pagination = document.querySelector('.pagination');
                    pagination.innerHTML = '';

                    // Previous Button (Arrow Left)
                    pagination.innerHTML += `
                        <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">
                                &#8249; <!-- Left Arrow -->
                            </a>
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

                    // Next Button (Arrow Right)
                    pagination.innerHTML += `
                        <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">
                                &#8250; <!-- Right Arrow -->
                            </a>
                        </li>
                    `;
                });
            };

            const getUserType = (type) => {
                return type === 0 ? 'Admin' : type === 1 ? 'Student' : type === 2 ? 'Staff' : 'Unknown';
            };

            // Initial load
            loadUsers();

            // Handle pagination clicks
            document.querySelector('.pagination').addEventListener('click', function(e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                const page = parseInt(e.target.getAttribute('data-page'));
                if (!isNaN(page)) {
                    loadUsers(page);
                }
                }
            });
            });
        </script>
    </div>
</div>