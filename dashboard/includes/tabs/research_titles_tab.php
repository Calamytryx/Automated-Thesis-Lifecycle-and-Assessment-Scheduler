<!-- Research Titles Tab -->
<div class="tab-pane fade" id="research-titles" role="tabpanel"
    aria-labelledby="research-titles-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Research Titles Management</h3>
                <p class="text-muted">Manage research titles assigned to teams and track their approval status</p>
            </div>
        </div>

        <!-- Research Titles Management Controls -->
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
                                    <input type="text" class="form-control border-0" id="researchTitleSearchInput" placeholder="Search research titles...">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-3 col-lg-3">
                            <!-- Sort Dropdown -->
                            <select class="form-select user-control-height" id="researchTitleSortSelect">
                                <option value="id:desc">Default (Newest First)</option>
                                <option value="id:asc">Default (Oldest First)</option>
                                <option value="title:asc">Title (A-Z)</option>
                                <option value="title:desc">Title (Z-A)</option>
                                <option value="approved_at:desc">Recently Approved</option>
                                <option value="approved_at:asc">Oldest Approved</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-5 col-lg-5">
                            <!-- Action buttons container -->
                            <div class="d-flex gap-2">
                                <button class="btn feature-btn add-btn user-control-height flex-fill" data-table="research_titles" id="addResearchTitleBtn">
                                    <i class="fas fa-plus me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Add Research Title</span>
                                    <span class="d-lg-none">Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Research Titles Content -->
        <div class="row">
            <div class="col-12">
                <!-- Research titles table that displays all titles with search and sorting -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table" id="research-titles-table" data-table="research_titles">
                        <thead>
                            <tr>
                                <th class="d-none d-md-table-cell">Title</th>
                                <th class="d-table-cell d-md-none">Research Title</th>
                                <th class="d-none d-sm-table-cell">Team</th>
                                <th class="d-none d-lg-table-cell">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center flex-wrap mt-2" id="researchTitlesPagination"><!-- Research titles pagination --></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to load research titles with search and sorting
        const loadResearchTitles = (page = 1, search = '', sort = 'id:desc') => {
            let url = `includes/tabs/get_table.php?table=research_titles&page=${page}`;
            
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
                        console.error('Error:', data.error);
                        return;
                    }

                    const tbody = document.querySelector('#research-titles-table tbody');
                    tbody.innerHTML = '';
                    
                    // Show a message if no results
                    if (data.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-search fs-1 d-block mb-2"></i>
                                        <p class="mb-0">No research titles found matching your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        
                        // Clear pagination
                        document.getElementById('researchTitlesPagination').innerHTML = '';
                        return;
                    }

                    // Clear any existing dropdowns
                    document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-research-title-"]').forEach(portal => portal.remove());
                    
                    data.data.forEach(title => {
                        const isApproved = title.approved_at ? true : false;
                        const statusText = isApproved ? 'Approved' : 'Pending';
                        const statusClass = isApproved ? 'approved' : 'pending';
                        const statusDate = title.updated_at || '';
                        const teamName = title.team_name || 'No Team Assigned';
                        
                        tbody.innerHTML += `
                            <tr>
                                <td class="d-none d-md-table-cell">${title.title}</td>
                                <td class="d-table-cell d-md-none">
                                    <div class="fw-semibold">${title.title}</div>
                                    <div class="text-muted small d-sm-none">${teamName}</div>
                                    <div class="d-lg-none mt-1">
                                        <span class="status-badge ${statusClass}" title="${statusText} (${statusDate})">
                                            ${statusText}
                                        </span>
                                    </div>
                                </td>
                                <td class="d-none d-sm-table-cell">${teamName}</td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="status-badge ${statusClass}" title="${statusText} (${statusDate})">
                                        ${statusText}
                                    </span>
                                </td>
                                <td class="action-buttons text-center">
                                    <button class="meatball-btn" data-title-id="${title.id}" aria-label="Actions">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        
                        // Create dropdown portal outside table
                        const dropdownPortal = document.createElement('div');
                        dropdownPortal.className = 'meatball-dropdown-portal';
                        dropdownPortal.id = `dropdown-research-title-${title.id}`;
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
                            <button class="meatball-dropdown-item edit-item edit-btn" data-table="research_titles" data-id="${title.id}">
                                <i class="fas fa-edit"></i>
                                Edit
                            </button>
                            <button class="meatball-dropdown-item delete-item delete-btn" data-table="research_titles" data-id="${title.id}">
                                <i class="fas fa-trash-alt"></i>
                                Delete
                            </button>
                        `;
                        document.body.appendChild(dropdownPortal);
                    });

                    // Update Pagination
                    const pagination = document.getElementById('researchTitlesPagination');
                    pagination.innerHTML = '';

                    if (data.total_pages > 1) {
                        const currentPage = page;
                        const totalPages = data.total_pages;
                        
                        // Previous Button
                        pagination.innerHTML += `
                            <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        `;

                        // Page Numbers with ellipsis
                        const startPage = Math.max(1, currentPage - 2);
                        const endPage = Math.min(totalPages, currentPage + 2);

                        if (startPage > 1) {
                            pagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                            if (startPage > 2) {
                                pagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                            }
                        }

                        for (let i = startPage; i <= endPage; i++) {
                            pagination.innerHTML += `
                                <li class="page-item ${currentPage === i ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                </li>
                            `;
                        }

                        if (endPage < totalPages) {
                            if (endPage < totalPages - 1) {
                                pagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                            }
                            pagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
                        }

                        // Next Button
                        pagination.innerHTML += `
                            <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading research titles:', error);
                });
        };

        // Function to get current filters
        const getCurrentFilters = () => {
            return {
                search: document.getElementById('researchTitleSearchInput').value,
                sort: document.getElementById('researchTitleSortSelect').value
            };
        };

        // Function to reload current view
        const reloadCurrentView = (page = 1) => {
            const filters = getCurrentFilters();
            loadResearchTitles(page, filters.search, filters.sort);
        };

        // Expose reloadCurrentView to global scope for use by main app.js.php
        window.reloadCurrentResearchTitlesView = reloadCurrentView;

        // Initialize on page load
        loadResearchTitles(1, '', 'id:desc');

        // Handle search input with debouncing
        let searchTimeout;
        document.getElementById('researchTitleSearchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                reloadCurrentView(1);
            }, 300);
        });

        // Handle sort dropdown change
        document.getElementById('researchTitleSortSelect').addEventListener('change', function() {
            reloadCurrentView(1);
        });

        // Handle pagination clicks
        document.getElementById('researchTitlesPagination').addEventListener('click', function(e) {
            e.preventDefault();
            if (e.target.tagName === 'A') {
                const page = parseInt(e.target.getAttribute('data-page'));
                if (!isNaN(page)) {
                    reloadCurrentView(page);
                }
            }
        });

        // Handle meatball button clicks specifically for research titles
        document.addEventListener('click', function(e) {
            const researchTitlesTab = document.getElementById('research-titles');
            
            // Only handle if we're in the research titles tab and it's active/visible
            if (!researchTitlesTab || (!researchTitlesTab.classList.contains('active') && !researchTitlesTab.classList.contains('show'))) {
                return;
            }
            
            // Handle meatball button clicks
            if (e.target.closest('.meatball-btn') && e.target.closest('#research-titles')) {
                e.preventDefault();
                e.stopPropagation();
                
                const btn = e.target.closest('.meatball-btn');
                const titleId = btn.getAttribute('data-title-id');
                
                if (!titleId) {
                    console.error('No title ID found');
                    return;
                }
                
                const dropdown = document.getElementById(`dropdown-research-title-${titleId}`);
                
                if (!dropdown) {
                    console.error('Dropdown not found for title:', titleId);
                    return;
                }
                
                const isCurrentlyOpen = dropdown.style.display === 'block';
                
                // Close all research title dropdowns first
                document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-research-title-"]').forEach(dd => {
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
                
                return; // Prevent other handlers
            } 
            // Close dropdown when clicking outside (only for research titles)
            else if (!e.target.closest('.meatball-dropdown-portal') && !e.target.closest('.meatball-btn')) {
                document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-research-title-"]').forEach(dd => {
                    dd.style.display = 'none';
                });
            }
        });

        // Handle meatball dropdown item clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.meatball-dropdown-item')) {
                const item = e.target.closest('.meatball-dropdown-item');
                
                // Only handle if this is a research titles dropdown item
                if (!item.hasAttribute('data-table') || item.getAttribute('data-table') !== 'research_titles') {
                    return;
                }
                
                // Close the dropdown
                const dropdown = item.closest('.meatball-dropdown-portal');
                if (dropdown) {
                    dropdown.style.display = 'none';
                }
                
                // The existing edit-btn and delete-btn event handlers will handle the action
                // since we've preserved the same classes on the dropdown items
            }
        });

        // Function to initialize research titles tab
        const initializeResearchTitlesTab = () => {
            console.log('Initializing research titles tab...');
            const researchTitlesTab = document.getElementById('research-titles');
            if (researchTitlesTab && (researchTitlesTab.classList.contains('active') || researchTitlesTab.classList.contains('show'))) {
                reloadCurrentView(1);
            }
        };

        // Initialize when tab becomes active
        document.addEventListener('shown.bs.tab', function (e) {
            if (e.target.getAttribute('aria-controls') === 'research-titles') {
                initializeResearchTitlesTab();
            }
        });

        // Also initialize if already active on page load
        setTimeout(initializeResearchTitlesTab, 100);
    });
</script>

