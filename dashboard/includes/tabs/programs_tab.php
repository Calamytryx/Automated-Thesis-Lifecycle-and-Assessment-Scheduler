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

    <div id="programs-content">
      <!-- Content will be dynamically populated using JavaScript -->
    </div>
  </div>
</div>

<script>
  const loadPrograms = () => {
    fetch(`includes/tabs/get_table.php?table=programs`)
      .then(response => response.json())
      .then(data => {
        const content = document.querySelector('#programs-content');
        content.innerHTML = '';  // Clear existing content

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

        // Create collapsible college groups
        Object.keys(programsByCollege).sort().forEach((college, index) => {
          // Create a unique ID for this college group
          const collegeId = `college-${index}-${college.replace(/\s+/g, '-').toLowerCase()}`;
          
          const programs = programsByCollege[college];
          
          // Create college card
          const collegeCard = document.createElement('div');
          collegeCard.className = 'card mb-3';
          
          // College header
          const collegeHeader = document.createElement('div');
          collegeHeader.className = 'card-header bg-light';
          collegeHeader.innerHTML = `
            <div class="d-flex align-items-center college-header-content" 
                 data-college-id="${collegeId}" role="button" style="cursor: pointer;">
              <i class="fas fa-caret-right toggle-icon me-2"></i>
              <i class="fas fa-university me-2"></i>
              <strong>${college}</strong>
              <span class="ms-2 badge bg-secondary">${programs.length} program(s)</span>
            </div>
          `;
          
          // Programs list
          const programsList = document.createElement('div');
          programsList.className = 'card-body d-none'; // Initially hidden
          programsList.dataset.collegeGroup = collegeId;
          
          programs.forEach(program => {
            const programDiv = document.createElement('div');
            programDiv.className = 'border-bottom pb-2 mb-2';
            programDiv.innerHTML = `
              <div class="row align-items-center">
                <div class="col-md-3">
                  <strong>Department:</strong> ${program.department || 'N/A'}
                </div>
                <div class="col-md-3">
                  <strong>Program:</strong> ${program.name}
                </div>
                <div class="col-md-3">
                  <strong>Specialization:</strong> ${program.specialization || 'N/A'}
                </div>
                <div class="col-md-3 text-end">
                  <div class="d-flex gap-2 justify-content-end">
                    <button class="btn btn-sm btn-primary edit-btn" data-table="programs" data-id="${program.id}">
                      <i class="fas fa-edit me-1"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-btn" data-table="programs" data-id="${program.id}">
                      <i class="fas fa-trash-alt me-1"></i> Delete
                    </button>
                  </div>
                </div>
              </div>
            `;
            programsList.appendChild(programDiv);
          });
          
          collegeCard.appendChild(collegeHeader);
          collegeCard.appendChild(programsList);
          content.appendChild(collegeCard);
        });
      })
      .catch(error => console.error('Error loading programs:', error));
  };

  // Toggle event listener for collapse functionality
  document.addEventListener('click', function(e) {
    if (e.target.closest('.college-header-content')) {
      const header = e.target.closest('.college-header-content');
      const collegeId = header.dataset.collegeId;
      const icon = header.querySelector('.toggle-icon');
      
      // Get the programs list for this college
      const programsList = document.querySelector(`div[data-college-group="${collegeId}"]`);
      
      // Check if content is currently visible
      const isVisible = !programsList.classList.contains('d-none');
      
      // Toggle visibility
      if (isVisible) {
        programsList.classList.add('d-none');
        icon.classList.remove('fa-caret-down');
        icon.classList.add('fa-caret-right');
      } else {
        programsList.classList.remove('d-none');
        icon.classList.remove('fa-caret-right');
        icon.classList.add('fa-caret-down');
      }
    }
  });

  // Initial load
  loadPrograms();

</script>
