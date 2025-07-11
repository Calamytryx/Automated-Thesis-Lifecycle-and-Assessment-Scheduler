<!-- Requirements Tab -->
<div class="tab-pane fade" id="requirements" role="tabpanel" aria-labelledby="requirements-tab">
    <div class="container-fluid py-4 content-container" id="requirements-container">
        <!-- Header Row -->
        <div class="row mb-4">
            <div class="col">
                <h3 class="mb-2">Requirements Management</h3>
                <p class="text-muted">Manage thesis and project requirements, including deadlines and submission guidelines for students.</p>
            </div>
        </div>

        <!-- Action Buttons Row -->
        <div class="row mb-3">
            <div class="col">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button class="btn feature-btn add-btn" data-table="requirements" id="addRequirementBtn">
                        <i class="fas fa-plus"></i> Add Requirement
                    </button>
                </div>
            </div>
        </div>

        <!-- Requirements Table -->
        <div class="table-responsive">
            <table class="table table-hover db-table" id="requirements-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Action</th>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadRequirements = (page = 1) => {
            fetch(`includes/tabs/get_table.php?table=requirements&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error, 'SQL Query:', data.sql_query, 'Parameters:', data.parameters, 'Stack Trace:', data.stack_trace);
                        return;
                    }

                    const tbody = document.querySelector('#requirements .db-table tbody');
                    tbody.innerHTML = '';
                    data.data.forEach(requirement => {
                        tbody.innerHTML += `
                                <tr>
                            <td>${requirement.name}</td>
                            <td>${requirement.description}</td>
                            <td>${requirement.due_date}</td>
                            <td class="action-buttons text-center">
                                <button class="meatball-btn" data-requirement-id="${requirement.id}" aria-label="Actions">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </td>
                        </tr>
                            `;
                        
                        // Create dropdown portal outside table
                        const dropdownPortal = document.createElement('div');
                        dropdownPortal.className = 'meatball-dropdown-portal';
                        dropdownPortal.id = `requirement-dropdown-${requirement.id}`;
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
                            <button class="meatball-dropdown-item edit-btn" data-table="requirements" data-id="${requirement.id}">
                                <i class="fas fa-edit"></i>
                                Edit
                            </button>
                            <button class="meatball-dropdown-item delete-btn" data-table="requirements" data-id="${requirement.id}">
                                <i class="fas fa-trash-alt"></i>
                                Delete
                            </button>
                        `;
                        document.body.appendChild(dropdownPortal);
                    });

                    // Update Pagination
                    const pagination = document.querySelector('#requirements .pagination');
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
                    });
            };


        // Initial Load
        loadRequirements();

        // Handle Pagination Clicks
        document.querySelector('#requirements .pagination').addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.tagName === 'A') {
                const page = parseInt(e.target.getAttribute('data-page'));
                if (!isNaN(page)) {
                    loadRequirements(page);
                }
            }
        });

        // --- Meatball Menu Functionality ---
        // Handle meatball button clicks specifically for requirements
        document.addEventListener('click', function(e) {
            const requirementsTab = document.getElementById('requirements');
            
            // Only handle if we're in the requirements tab and it's active/visible
            if (!requirementsTab || (!requirementsTab.classList.contains('active') && !requirementsTab.classList.contains('show'))) {
                return;
            }
            
            // Handle meatball button clicks
            if (e.target.closest('.meatball-btn') && e.target.closest('#requirements')) {
                e.preventDefault();
                e.stopPropagation();
                
                const btn = e.target.closest('.meatball-btn');
                const requirementId = btn.getAttribute('data-requirement-id');
                const dropdown = document.getElementById(`requirement-dropdown-${requirementId}`);
                
                if (!dropdown) {
                    console.error('Dropdown not found for requirement:', requirementId);
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
                
                // Only handle if this is a requirements dropdown item
                if (!item.classList.contains('edit-btn') && !item.classList.contains('delete-btn')) {
                    return;
                }
                
                // Close the dropdown
                const dropdown = item.closest('.meatball-dropdown-portal');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
                
                // The existing edit-btn and delete-btn event handlers will handle the action
                // since we've preserved the same classes and data attributes on the dropdown items
            }
        });
    });
</script>