<!-- Programs Tab -->
<div class="tab-pane fade" id="programs" role="tabpanel" aria-labelledby="programs-tab">
  <div class="container-fluid py-4 content-container">
    <!-- Header with title and description -->
    <div class="row mb-4">
      <div class="col-12">
        <h3 class="mb-2">Programs</h3>
        <p class="text-muted">Manage academic programs and their associated colleges</p>
      </div>
    </div>
 
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between mb-3">
          <div></div>
          <button class="btn feature-btn add-btn" data-table="programs">
            <i class="fas fa-plus me-2"></i> Add Program
          </button>
        </div>
      </div>
    </div>

    <div class="table-responsive db-table-container">
      <table class="table table-bordered table-hover table-sm db-table">
        <thead>
          <tr>
            <th>College</th>
            <th>Department</th>
            <th>Program</th>
            <th>Specialization</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="programs-table-body">
          <!-- Table body will be dynamically populated using JavaScript -->
        </tbody>
      </table>
    </div>

    <nav aria-label="Programs Page navigation">
      <ul class="pagination justify-content-center" id="programs-pagination">
        <!-- Pagination will be dynamically populated using JavaScript -->
      </ul>
    </nav>
  </div>
</div>

<script>
  const loadPrograms = (page = 1) => {
    fetch(`includes/tabs/get_table.php?table=programs&page=${page}`)
      .then(response => response.json())
      .then(data => {
        const tableBody = document.querySelector('#programs-table-body');
        tableBody.innerHTML = '';  // Clear existing rows

        if (data.error) {
          console.error(data.error);
          return;
        }

        // Group programs by college
        const programsByCollege = {};
        data.data.forEach(program => {
          const college = program.college || 'Other';
          if (!programsByCollege[college]) {
            programsByCollege[college] = [];
          }
          programsByCollege[college].push(program);
        });

        // Populate table with colleges as collapsible groups
        Object.keys(programsByCollege).sort().forEach((college, index) => {
          // Create a unique ID for this college group
          const collegeId = `college-${index}-${college.replace(/\s+/g, '-').toLowerCase()}`;
          
          // Add college header row with collapse/expand functionality
          const programs = programsByCollege[college];
          const collegeRow = document.createElement('tr');
          collegeRow.className = 'college-header';
          collegeRow.innerHTML = `
            <td colspan="5" class="bg-light">
              <div class="d-flex align-items-center college-header-content" 
                   data-college-id="${collegeId}" role="button" style="cursor: pointer;">
                <i class="fas fa-caret-down toggle-icon me-2"></i>
                <i class="fas fa-university me-2"></i>
                <strong>${college}</strong>
                <span class="ms-2 badge bg-secondary">${programs.length} program(s)</span>
              </div>
            </td>
          `;
          tableBody.appendChild(collegeRow);

          // Add individual program rows under this college
          programs.forEach(program => {
            const programRow = document.createElement('tr');
            programRow.className = `program-row ${collegeId}`;
            programRow.dataset.collegeGroup = collegeId; // Add data attribute for grouping
            programRow.innerHTML = `
              <td class="ps-4">-</td>
              <td>${program.department || ''}</td>
              <td>${program.name}</td>
              <td>${program.specialization || ''}</td>
              <td class="action-buttons text-center">
                <div class="d-flex gap-2 justify-content-center">
                  <button class="btn btn-sm btn-primary edit-btn" data-table="programs" data-id="${program.id}">
                    <i class="fas fa-edit me-1"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-danger delete-btn" data-table="programs" data-id="${program.id}">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                  </button>
                </div>
              </td>
            `;
            tableBody.appendChild(programRow);
          });
        });

        // Build pagination
        const pagination = document.querySelector('#programs-pagination');
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
      .catch(error => console.error('Error loading programs:', error));
  };

  // Replace the toggle event listener with a better version
  document.addEventListener('click', function(e) {
    if (e.target.closest('.college-header-content')) {
      const header = e.target.closest('.college-header-content');
      const collegeId = header.dataset.collegeId;
      const icon = header.querySelector('.toggle-icon');
      
      // Get all program rows for this college
      const programRows = document.querySelectorAll(`tr[data-college-group="${collegeId}"]`);
      
      // Check if rows are currently visible
      const isVisible = !programRows[0]?.classList.contains('d-none');
      
      // Toggle visibility of program rows
      programRows.forEach(row => {
        if (isVisible) {
          row.classList.add('d-none');
          icon.classList.remove('fa-caret-down');
          icon.classList.add('fa-caret-right');
        } else {
          row.classList.remove('d-none');
          icon.classList.remove('fa-caret-right');
          icon.classList.add('fa-caret-down');
        }
      });
    }
  });

  // Event listener for pagination
  document.querySelector('#programs-pagination').addEventListener('click', (event) => {
    if (event.target.tagName === 'A') {
      const page = parseInt(event.target.getAttribute('data-page'));
      if (!isNaN(page)) {
        loadPrograms(page);
      }
    }
  });

  // // Add event listener for delete buttons
  // document.addEventListener('click', function(e) {
  //   if (e.target && e.target.closest('.delete-btn')) {
  //     const button = e.target.closest('.delete-btn');
  //     const table = button.getAttribute('data-table');
  //     const id = button.getAttribute('data-id');
      
  //     if (confirm('Are you sure you want to delete this program?')) {
  //       const formData = new FormData();
  //       formData.append('table', table);
  //       formData.append('id', id);
        
  //       fetch('includes/delete_item.php', {
  //         method: 'POST',
  //         body: formData
  //       })
  //       .then(response => response.json())
  //       .then(data => {
  //         if (data.success) {
  //           // Reload the programs table to reflect the deletion
  //           loadPrograms();
  //         } else {
  //           alert('Error deleting program: ' + (data.message || 'Unknown error'));
  //           console.error('Delete error:', data);
  //         }
  //       })
  //       .catch(error => {
  //         console.error('Error during delete operation:', error);
  //         alert('An error occurred during delete. Check console for details.');
  //       });
  //     }
  //   }
  // });

  // Initial load
  loadPrograms();
</script>
