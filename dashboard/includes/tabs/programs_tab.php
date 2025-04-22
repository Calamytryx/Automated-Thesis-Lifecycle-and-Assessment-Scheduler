<!-- Programs Tab -->
<div class="tab-pane fade" id="programs" role="tabpanel" aria-labelledby="programs-tab">
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-3">
      <h3>Programs</h3>
      <button class="btn feature-btn add-btn" data-table="programs">
        <i class="fas fa-plus me-2"></i> Add Program
      </button>
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

        // Populate table rows
        data.data.forEach(program => {
          tableBody.innerHTML += `
            <tr>
              <td>${program.college}</td>
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
            </tr>
          `;
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

  // Event listener for pagination
  document.querySelector('#programs-pagination').addEventListener('click', (event) => {
    if (event.target.tagName === 'A') {
      const page = parseInt(event.target.getAttribute('data-page'));
      if (!isNaN(page)) {
        loadPrograms(page);
      }
    }
  });

  // Initial load
  loadPrograms();
</script>
