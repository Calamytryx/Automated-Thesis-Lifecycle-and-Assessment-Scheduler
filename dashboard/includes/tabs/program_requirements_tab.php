<!-- Program Requirements Mapping Tab -->
<div class="tab-pane fade" id="program_requirements" role="tabpanel" aria-labelledby="program_requirements-tab">
    <div class="container-fluid py-4 content-container" id="program_requirements-container">
        <!-- Header Row -->
        <div class="row mb-4">
            <div class="col">
                <h3 class="mb-2">Program Requirements by Defense Type</h3>
                <p class="text-muted">Configure which requirements apply to each defense type on a per-program basis.</p>
            </div>
        </div>

        <!-- Program & Defense Type Selector -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="programSelector" class="form-label">Select Program</label>
                <select class="form-select" id="programSelector">
                    <option value="">-- Choose a program --</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="defenseTypeSelector" class="form-label">Select Defense Type</label>
                <select class="form-select" id="defenseTypeSelector">
                    <option value="">-- Choose a defense type --</option>
                </select>
            </div>
        </div>

        <!-- Requirements Mapping Interface -->
        <div class="row" id="requirementsMappingContainer" style="display: none;">
            <div class="col-12">
                <!-- Mapped Requirements -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle me-2"></i>Mapped Requirements 
                            <span class="badge bg-light text-dark" id="mappedCount">0</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="mappedRequirementsContainer">
                            <p class="text-muted">No records found.</p>
                        </div>
                    </div>
                </div>

                <!-- Available Requirements to Add -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Available Requirements 
                            <span class="badge bg-light text-dark" id="unmappedCount">0</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="unmappedRequirementsContainer">
                            <p class="text-muted">All available requirements are already mapped.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyStateContainer" class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Select a program and defense type to manage requirements.
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Requirement Details -->
<div class="modal fade" id="requirementDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Requirement Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="requirementDetailsContent">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Remove Mapping Confirmation Modal -->
<div class="modal fade" id="mappingDeleteConfirmModal" tabindex="-1" aria-labelledby="mappingDeleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="text-danger mb-3" style="font-size: 3rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
                    </svg>
                </div>
                <h4 class="fw-bold mb-3" id="mappingDeleteConfirmModalLabel">Confirm Deletion</h4>
                <p>Are you sure you want to remove <span id="mappingDeleteTargetLabel" class="fw-semibold">this requirement mapping</span>?</p>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmMappingDeleteBtn">Remove</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentProgram = null;
        let currentDefenseType = null;
        const validDefenseTypes = ['title_proposal', 'title_defense', 'final_defense', 're-defense', 'general'];
        const mappingDeleteConfirmModal = new bootstrap.Modal(document.getElementById('mappingDeleteConfirmModal'));
        const confirmMappingDeleteBtn = document.getElementById('confirmMappingDeleteBtn');
        let mappingDeleteResolver = null;

        function confirmMappingDelete(targetLabel) {
            document.getElementById('mappingDeleteTargetLabel').textContent = targetLabel || 'this requirement mapping';
            mappingDeleteConfirmModal.show();

            return new Promise(resolve => {
                mappingDeleteResolver = resolve;
            });
        }

        confirmMappingDeleteBtn.addEventListener('click', function() {
            mappingDeleteConfirmModal.hide();
            if (mappingDeleteResolver) {
                mappingDeleteResolver(true);
                mappingDeleteResolver = null;
            }
        });

        document.getElementById('mappingDeleteConfirmModal').addEventListener('hidden.bs.modal', function() {
            if (mappingDeleteResolver) {
                mappingDeleteResolver(false);
                mappingDeleteResolver = null;
            }
        });
        
        // Load programs on page load
        loadPrograms();
        
        // Event listeners
        document.getElementById('programSelector').addEventListener('change', function() {
            currentProgram = this.value;
            currentDefenseType = null;
            document.getElementById('defenseTypeSelector').value = '';
            if (currentProgram) {
                loadDefenseTypes(currentProgram);
            } else {
                clearDefenseTypes();
                hideRequirementsMapping();
            }
        });
        
        document.getElementById('defenseTypeSelector').addEventListener('change', function() {
            currentDefenseType = this.value;
            if (currentProgram && currentDefenseType) {
                loadRequirementsForDefenseType(currentProgram, currentDefenseType);
            } else {
                hideRequirementsMapping();
            }
        });
        
        // Function: Load programs
        function loadPrograms() {
            console.log('Loading programs...');
            fetch('/api/program_requirements_mapping.php?action=get_programs')
                .then(response => response.json())
                .then(data => {
                    console.log('Programs loaded:', data);
                    if (data.success && data.programs) {
                        const select = document.getElementById('programSelector');
                        select.innerHTML = '<option value="">-- Choose a program --</option>';
                        data.programs.forEach(program => {
                            const option = document.createElement('option');
                            option.value = program.id;
                            option.textContent = `${program.name} (${program.college})`;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading programs:', error);
                    alert('Failed to load programs');
                });
        }
        
        // Function: Load defense types for selected program
        function loadDefenseTypes(programId) {
            console.log('Loading defense types for program:', programId);
            fetch(`/api/program_requirements_mapping.php?action=get_defense_types&program_id=${programId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Defense types loaded:', data);
                    if (data.success && data.defense_types) {
                        const select = document.getElementById('defenseTypeSelector');
                        select.innerHTML = '<option value="">-- Choose a defense type --</option>';
                        data.defense_types.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type.type;
                            option.textContent = `${type.label} (${type.count} requirements)`;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading defense types:', error);
                });
        }
        
        // Function: Clear defense type selector
        function clearDefenseTypes() {
            document.getElementById('defenseTypeSelector').innerHTML = '<option value="">-- Choose a defense type --</option>';
        }
        
        // Function: Load requirements for selected defense type
        function loadRequirementsForDefenseType(programId, defenseType) {
            console.log(`Loading requirements for Program ${programId}, Type ${defenseType}`);
            fetch(`/api/program_requirements_mapping.php?action=get_requirements&program_id=${programId}&defense_type=${defenseType}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Requirements loaded:', data);
                    if (data.success) {
                        displayRequirements(data);
                        showRequirementsMapping();
                    } else {
                        console.error('Error:', data.error);
                        alert('Failed to load requirements: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error loading requirements:', error);
                    alert('Failed to load requirements');
                });
        }
        
        // Function: Display requirements in mapped and unmapped containers
        function displayRequirements(data) {
            const mappedContainer = document.getElementById('mappedRequirementsContainer');
            const unmappedContainer = document.getElementById('unmappedRequirementsContainer');
            
            const mappedReqs = data.requirements.filter(r => r.is_mapped);
            const unmappedReqs = data.requirements.filter(r => !r.is_mapped);
            
            // Display mapped requirements
            if (mappedReqs.length > 0) {
                let html = '<div class="list-group">';
                mappedReqs.forEach((req, index) => {
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${escapeHtml(req.name)}</h6>
                                <small class="text-muted">${escapeHtml(req.description || 'No description')}</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-info" onclick="viewRequirementDetails(${req.id}, '${escapeHtml(req.name)}')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="removeRequirement(${req.requirement_id || req.id}, ${currentProgram}, '${currentDefenseType}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                mappedContainer.innerHTML = html;
            } else {
                mappedContainer.innerHTML = '<p class="text-muted">No records found.</p>';
            }
            
            // Display unmapped requirements
            if (unmappedReqs.length > 0) {
                let html = '<div class="list-group">';
                unmappedReqs.forEach((req) => {
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${escapeHtml(req.name)}</h6>
                                <small class="text-muted">${escapeHtml(req.description || 'No description')}</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-info" onclick="viewRequirementDetails(${req.id}, '${escapeHtml(req.name)}')">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="addRequirement(${req.id}, ${currentProgram}, '${currentDefenseType}')">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                unmappedContainer.innerHTML = html;
            } else {
                unmappedContainer.innerHTML = '<p class="text-muted">All available requirements are already mapped.</p>';
            }
            
            // Update counts
            document.getElementById('mappedCount').textContent = mappedReqs.length;
            document.getElementById('unmappedCount').textContent = unmappedReqs.length;
        }
        
        // Function: Add requirement to defense type
        window.addRequirement = function(requirementId, programId, defenseType) {
            console.log(`Adding requirement ${requirementId} to ${defenseType} for program ${programId}`);
            
            fetch('/api/program_requirements_mapping.php?action=add_mapping', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `program_id=${programId}&defense_type=${defenseType}&requirement_id=${requirementId}&is_mandatory=1`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Add response:', data);
                if (data.success) {
                    loadRequirementsForDefenseType(programId, defenseType);
                    showSuccessMessage('Requirement added successfully');
                } else {
                    alert('Failed to add requirement: ' + (data.error || data.message));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add requirement');
            });
        };
        
        // Function: Remove requirement from defense type
        window.removeRequirement = function(requirementId, programId, defenseType) {
            confirmMappingDelete(`this requirement mapping (ID: ${requirementId})`).then(confirmed => {
                if (!confirmed) return;

                console.log(`Removing requirement ${requirementId} from ${defenseType} for program ${programId}`);

                fetch('/api/program_requirements_mapping.php?action=remove_mapping', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `program_id=${programId}&defense_type=${defenseType}&requirement_id=${requirementId}`
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Remove response:', data);
                    if (data.success) {
                        loadRequirementsForDefenseType(programId, defenseType);
                        showSuccessMessage('Requirement removed successfully');
                    } else {
                        alert('Failed to remove requirement: ' + (data.error || data.message));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to remove requirement');
                });
            });
        };
        
        // Function: View requirement details
        window.viewRequirementDetails = function(requirementId, name) {
            const modal = new bootstrap.Modal(document.getElementById('requirementDetailsModal'));
            const content = document.getElementById('requirementDetailsContent');
            content.innerHTML = `
                <div class="alert alert-info">
                    <strong>${escapeHtml(name)}</strong> (ID: ${requirementId})
                </div>
                <p>Requirement details view can be extended to show more information here.</p>
            `;
            modal.show();
        };
        
        // Function: Show requirements mapping interface
        function showRequirementsMapping() {
            document.getElementById('requirementsMappingContainer').style.display = 'block';
            document.getElementById('emptyStateContainer').style.display = 'none';
        }
        
        // Function: Hide requirements mapping interface
        function hideRequirementsMapping() {
            document.getElementById('requirementsMappingContainer').style.display = 'none';
            document.getElementById('emptyStateContainer').style.display = 'block';
        }
        
        // Function: Show success message
        function showSuccessMessage(message) {
            // You can customize this to show a toast or alert
            console.log('Success:', message);
        }
        
        // Function: Escape HTML
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    });
</script>

<style>
    #program_requirements-container {
        max-width: 100%;
    }
    
    .list-group-item {
        border-left: 4px solid var(--main-primary);
        transition: all 0.3s ease;
    }
    
    .list-group-item:hover {
        background-color: var(--neutral-50);
        border-left-color: var(--primary-500);
    }
    
    .card-header {
        border-bottom: 2px solid rgba(0,0,0,0.1);
    }
    
    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
</style>
