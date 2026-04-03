<!-- Programs Tab -->
<div class="tab-pane fade" id="programs" role="tabpanel" aria-labelledby="programs-tab">
  <div class="container-fluid py-4 content-container">
    <!-- Header with title and description -->
    <div class="row mb-4">
      <div class="col-12">
        <h3 class="mb-2">Academic Structure</h3>
        <p class="text-muted">Manage colleges, departments, program names, and specializations</p>
        <?php if ($_SESSION['usertype'] == 0): ?>
        <div class="mt-2">
          <a href="#users" class="tab-redirect-link" onclick="document.getElementById('users-tab').click(); return false;">
            <i class="bi bi-people-fill"></i>
            <span>Create new users and assign created programs</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Program Management Controls -->
    <div class="row">
      <div class="col-12">
        <!-- Mobile-first responsive layout -->
        <div class="program-controls-container p-0 mt-3">
          <!-- Search and Filter Row -->
          <div class="row g-2 mb-3 align-items-end">
            <div class="col-12 col-md-4 col-lg-4">
              <!-- Search container -->
              <div class="programs-search-container">
                <div class="input-group user-control-height m-0">
                  <span class="input-group-text border-0"> 
                    <i class="bi bi-search"></i>
                  </span>
                  <input type="text" class="form-control border-0" id="programSearchInput" placeholder="Search programs...">
                </div>
              </div>
            </div>
            
            <div class="col-12 col-md-3 col-lg-2">
              <!-- College Dropdown -->
              <div class="programs-tab-controls">
                <select class="form-select user-control-height" id="collegeFilterSelect">
                  <option value="all">All Colleges</option>
                  <?php
                  // Get distinct colleges for filter
                  try {
                    $stmt = $pdo->query("SELECT DISTINCT college FROM programs WHERE college IS NOT NULL ORDER BY college");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                      echo '<option value="' . htmlspecialchars($row['college']) . '">' . htmlspecialchars($row['college']) . '</option>';
                    }
                  } catch (PDOException $e) {
                    echo '<option disabled>Error loading colleges</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            
            <div class="col-12 col-md-3 col-lg-3">
              <!-- Sort Dropdown -->
              <select class="form-select user-control-height" id="programSortSelect">
                <option value="id:desc">Default (Newest First)</option>
                <option value="id:asc">Default (Oldest First)</option>
                <option value="name:asc">Name (A-Z)</option>
                <option value="name:desc">Name (Z-A)</option>
                <option value="college:asc">College (A-Z)</option>
                <option value="college:desc">College (Z-A)</option>
                <option value="department:asc">Department (A-Z)</option>
                <option value="department:desc">Department (Z-A)</option>
              </select>
            </div>
            
            <div class="col-12 col-md-2 col-lg-3">
              <!-- Add Button -->
              <div class="d-flex gap-2 justify-content-end">
                <button class="btn feature-btn add-btn user-control-height w-100 w-md-auto" data-table="programs" id="addProgramBtn">
                  <i class="fas fa-plus me-1 d-none d-sm-inline"></i>
                  <span class="d-none d-sm-inline">Add Program</span>
                  <span class="d-sm-none">Add</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Program Management Content -->
    <div class="row">
      <div class="col-12">
        <!-- Programs table with filters -->
        <div class="table-responsive">
          <table class="table table-bordered table-hover table-sm db-table" id="allProgramsTable">
            <thead>
              <tr>
                <th class="d-none d-md-table-cell">College</th>
                <th class="d-table-cell d-md-none">Program</th>
                <th class="d-none d-lg-table-cell">Department</th>
                <th>Program Name</th>
                <th class="d-none d-md-table-cell">Specialization</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody id="programsTableBody">
              <!-- Content will be dynamically populated -->
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Programs pagination">
          <ul class="pagination justify-content-center" id="programsPagination">
            <!-- Pagination will be dynamically populated -->
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Function to load programs based on filters with search and sorting
    const loadPrograms = (collegeFilter = 'all', page = 1, search = '', sort = 'id:desc') => {
      let url = `includes/tabs/get_table.php?table=programs&page=${page}&per_page=10`;
      
      if (collegeFilter !== 'all') {
        url += `&college=${encodeURIComponent(collegeFilter)}`;
      }
      if (search) {
        url += `&search=${encodeURIComponent(search)}`;
      }
      if (sort) {
        url += `&sort=${encodeURIComponent(sort)}`;
      }

      fetch(url)
        .then(response => response.json())
        .then(data => {
          const tableBody = document.querySelector('#programsTableBody');
          if (!tableBody) {
            console.error('Could not find table body');
            return;
          }
          
          tableBody.innerHTML = '';

          if (data.error) {
            console.error(data.error);
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading programs</td></tr>';
            return;
          }

          if (!data.data || data.data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No programs found</td></tr>';
            return;
          }

          // Clear any existing dropdowns
          document.querySelectorAll('.meatball-dropdown-portal').forEach(portal => portal.remove());
          
          // Populate table with programs
          data.data.forEach(program => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td class="d-none d-md-table-cell">${program.college || 'N/A'}</td>
              <td class="d-table-cell d-md-none">
                <div class="fw-bold">${program.name || 'N/A'}</div>
                <small class="text-muted">${program.college || 'N/A'}</small>
                ${program.specialization ? `<br><small class="text-info">${program.specialization}</small>` : ''}
              </td>
              <td class="d-none d-lg-table-cell">${program.department || 'N/A'}</td>
              <td>${program.name || 'N/A'}</td>
              <td class="d-none d-md-table-cell">${program.specialization || 'N/A'}</td>
              <td class="action-buttons text-center">
                <button class="meatball-btn" data-program-id="${program.id}" aria-label="Actions">
                  <i class="fas fa-ellipsis-h"></i>
                </button>
              </td>
            `;
            tableBody.appendChild(row);
            
            // Create dropdown portal outside table
            const dropdownPortal = document.createElement('div');
            dropdownPortal.className = 'meatball-dropdown-portal';
            dropdownPortal.id = `dropdown-${program.id}`;
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
              <button class="meatball-dropdown-item edit-item edit-btn" data-table="programs" data-id="${program.id}">
                <i class="fas fa-edit"></i>
                Edit
              </button>
              <button class="meatball-dropdown-item delete-item delete-btn" data-table="programs" data-id="${program.id}">
                <i class="fas fa-trash-alt"></i>
                Delete
              </button>
            `;
            const deleteBtn = dropdownPortal.querySelector('.delete-btn');
            if (deleteBtn) {
              const programName = program.name || 'Unnamed program';
              const specialization = (program.specialization || '').trim();
              deleteBtn.dataset.deleteLabel = specialization ? `${programName} (${specialization})` : programName;
            }
            document.body.appendChild(dropdownPortal);
          });

          // Build pagination
          const pagination = document.querySelector('#programsPagination');
          if (!pagination) {
            console.error('Could not find pagination');
            return;
          }
          
          pagination.innerHTML = '';

          if (data.total_pages > 1) {
            const totalPages = data.total_pages;

            pagination.innerHTML += `
              <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${page - 1}">&#8249;</a>
              </li>
            `;

            const startPage = Math.max(1, page - 2);
            const endPage = Math.min(totalPages, page + 2);

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

            if (endPage < totalPages) {
              if (endPage < totalPages - 1) {
                pagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
              }
              pagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
            }

            pagination.innerHTML += `
              <li class="page-item ${page >= totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${page + 1}">&#8250;</a>
              </li>
            `;
          } 
        })
        .catch(error => {
          console.error('Error loading programs:', error);
          const tableBody = document.querySelector('#programsTableBody');
          if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading programs</td></tr>';
          }
        });
    };

    // Function to get current filters
    const getCurrentFilters = () => {
      return {
        collegeFilter: document.getElementById('collegeFilterSelect').value,
        search: document.getElementById('programSearchInput').value,
        sort: document.getElementById('programSortSelect').value
      };
    };

    // Function to reload current view
    const reloadCurrentView = (page = 1) => {
      const filters = getCurrentFilters();
      loadPrograms(filters.collegeFilter, page, filters.search, filters.sort);
    };

    // Expose reloadCurrentView to global scope for use by main app.js.php
    window.reloadProgramsView = reloadCurrentView;

    // Initialize on page load
    loadPrograms('all', 1, '', 'id:desc');

    // Handle college filter dropdown change
    document.getElementById('collegeFilterSelect').addEventListener('change', function() {
      reloadCurrentView(1);
    });

    // Handle search input
    document.getElementById('programSearchInput').addEventListener('keyup', function(e) {
      reloadCurrentView(1);
    });

    // Handle sort dropdown change
    document.getElementById('programSortSelect').addEventListener('change', function() {
      reloadCurrentView(1);
    });

    // Handle pagination clicks
    document.querySelector('#programsPagination').addEventListener('click', function(e) {
      e.preventDefault();
      if (e.target.tagName === 'A') {
        const page = parseInt(e.target.getAttribute('data-page'));
        if (!isNaN(page)) {
          reloadCurrentView(page);
        }
      }
    });

    // Function to initialize data loading when the programs tab becomes visible
    const initializeProgramsTab = () => {
      console.log('Initializing programs tab...');
      const programsTab = document.getElementById('programs');
      if (programsTab && (programsTab.classList.contains('active') || programsTab.classList.contains('show'))) {
        console.log('Programs tab is visible, loading data...');
        reloadCurrentView(1);
      }
    };

    // Also initialize when the main dashboard tab for programs becomes visible
    document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
      tab.addEventListener('shown.bs.tab', function(e) {
        if (e.target.id === 'programs-tab') {
          console.log('Programs tab shown event triggered');
          initializeProgramsTab();
        }
      });
    });

    // Initialize if programs tab is already active
    initializeProgramsTab();

    // Handle meatball button clicks for programs
    document.addEventListener('click', function(e) {
      const programsTab = document.getElementById('programs');
      if (!programsTab || (!programsTab.classList.contains('active') && !programsTab.classList.contains('show'))) {
        return;
      }
      
      // Handle meatball button clicks
      if (e.target.closest('.meatball-btn') && e.target.closest('#programs')) {
        e.preventDefault();
        e.stopPropagation();
        
        const btn = e.target.closest('.meatball-btn');
        const programId = btn.getAttribute('data-program-id');
        const dropdown = document.getElementById(`dropdown-${programId}`);
        
        if (!dropdown) {
          console.error('Dropdown not found for program:', programId);
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

    // Handle meatball dropdown item clicks for programs
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
