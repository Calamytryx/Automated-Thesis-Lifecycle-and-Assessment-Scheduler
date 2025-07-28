<!-- Environment Variables Tab -->
<div class="tab-pane fade" id="env-variables" role="tabpanel"
    aria-labelledby="env-variables-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Content Management System</h3>
                <p class="text-muted">Manage website content, system settings, and display options</p>
            </div>
        </div>

        <!-- Content Management Controls -->
        <div class="row">
            <div class="col-12">
                <div class="user-controls-container p-0">
                    <!-- Search and Filter Row -->
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col-12 col-md-4 col-lg-3">
                            <!-- CMS Content Type Filter -->
                            <label class="form-label text-muted small">Content Type</label>
                            <select class="form-select user-control-height" id="cmsContentTypeSelect">
                                <option value="all-content">All Content</option>
                                <option value="system-settings">System Settings</option>
                                <option value="page-content">Page Content</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-8 col-lg-9">
                            <!-- Action buttons container -->
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                <button class="btn feature-btn add-btn user-control-height" data-table="env_variables" id="addSettingBtn" style="display: none;">
                                    <i class="fas fa-plus me-2"></i>Add Setting
                                </button>
                                <button class="btn feature-btn page-content-add-btn user-control-height" id="addPageContentBtn" style="display: none;">
                                    <i class="fas fa-plus me-2"></i>Add Page Content
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Management Content -->
        <div class="pt-4" id="cmsContentContainer">
            <!-- All Content View (Default) -->
            <div id="all-content-view">
                <!-- System Settings Section -->
                <div class="mb-5" id="system-settings-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">System Settings</h4>
                    </div>
                    <div class="table-responsive">
                        <?php 
                        $groupedVariables = [];
                        foreach ($envVariables as $variable) {
                            $prefix = explode('_', $variable['key'])[0];
                            $groupedVariables[$prefix][] = $variable;
                        }
                        
                        foreach ($groupedVariables as $prefix => $variables): ?>
                            <div class="settings-group mb-4">
                                <div class="settings-group-header px-4 py-3">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($prefix == 'ALLOWED' ? $prefix . ' Inactivity Time' : $prefix . ' Settings'); ?></h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm db-table m-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-4">Name</th>
                                                <th>Value</th>
                                                <th>Description</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($variables as $variable): ?>
                                                <tr>
                                                    <td class="ps-4 fw-medium">
                                                        <?php echo htmlspecialchars($variable['key']); ?>
                                                        <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top" 
                                                           title="<?php echo htmlspecialchars('ID: ' . $variable['id'] . ' | Key: ' . $variable['key'] . ' | Description: ' . ($variable['description'] ?? 'No description')); ?>"></i>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        if (strpos($variable['key'], 'PASSWORD') !== false) {
                                                            echo '<span class="text-muted">••••••••</span>';
                                                        } elseif ($variable['key'] == 'ALLOWED_INACTIVITY_TIME') {
                                                            $hours = floor($variable['value'] / 3600);
                                                            $minutes = floor(($variable['value'] % 3600) / 60);
                                                            $seconds = $variable['value'] % 60;
                                                            echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                                        } else {
                                                            echo htmlspecialchars($variable['value']);
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-muted">
                                                        <?php echo !empty($variable['description']) ? htmlspecialchars($variable['description']) : '<em>No description</em>'; ?>
                                                    </td>                                                <td class="text-center">
                                                    <button class="meatball-btn" data-env-id="<?php echo $variable['id']; ?>">
                                                        <i class="fas fa-ellipsis-h"></i> 
                                                    </button>
                                                </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr class="border-dark my-5">
                </div>

                <!-- Page Content Section -->
                <div class="mb-5" id="page-content-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Page Content</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm db-table m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Page Title</th>
                                    <th>Slug</th>
                                    <th>Last Updated</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pageContentTableBody">
                                <?php
                                // Get all page content from database
                                $page_content_query = "SELECT * FROM page_content ORDER BY updated_at DESC";
                                try {
                                    $page_content_stmt = $pdo->prepare($page_content_query);
                                    $page_content_stmt->execute();
                                    $pages = $page_content_stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (count($pages) > 0) {
                                        foreach ($pages as $page) {
                                            $status_badge = $page['status'] === 'published' ? 'bg-success' : 'bg-warning';
                                            echo '<tr>
                                                <td class="ps-4 fw-medium">
                                                    ' . htmlspecialchars($page['title']) . '
                                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="top" 
                                                       title="Page ID: ' . $page['id'] . ' | URL: /' . $page['slug'] . ' | Created: ' . $page['created_at'] . '"></i>
                                                </td>
                                                <td>' . htmlspecialchars($page['slug']) . '</td>
                                                <td>' . date('Y-m-d', strtotime($page['updated_at'])) . '</td>
                                                <td><span class="badge ' . $status_badge . '">' . ucfirst($page['status']) . '</span></td>                                        <td class="text-center">
                                            <button class="meatball-btn" data-page-id="' . $page['id'] . '">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                        </td>
                                            </tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="5" class="text-center">No pages found</td></tr>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<tr><td colspan="5" class="text-center text-danger">Error loading pages: ' . $e->getMessage() . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Settings Only View -->
            <div id="system-settings-view" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">System Settings</h4>
                </div>

                <div class="table-responsive">
                    <!-- Same grouped variables content as above -->
                    <?php foreach ($groupedVariables as $prefix => $variables): ?>
                        <div class="settings-group mb-4">
                            <div class="settings-group-header px-4 py-3">
                                <h5 class="mb-0"><?php echo htmlspecialchars($prefix == 'ALLOWED' ? $prefix . ' Inactivity Time' : $prefix . ' Settings'); ?></h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm db-table m-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Name</th>
                                            <th>Value</th>
                                            <th>Description</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($variables as $variable): ?>
                                            <tr>
                                                <td class="ps-4 fw-medium">
                                                    <?php echo htmlspecialchars($variable['key']); ?>
                                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                                       data-bs-toggle="tooltip" 
                                                       data-bs-placement="top" 
                                                       title="<?php echo htmlspecialchars('ID: ' . $variable['id'] . ' | Key: ' . $variable['key'] . ' | Description: ' . ($variable['description'] ?? 'No description')); ?>"></i>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (strpos($variable['key'], 'PASSWORD') !== false) {
                                                        echo '<span class="text-muted">••••••••</span>';
                                                    } elseif ($variable['key'] == 'ALLOWED_INACTIVITY_TIME') {
                                                        $hours = floor($variable['value'] / 3600);
                                                        $minutes = floor(($variable['value'] % 3600) / 60);
                                                        $seconds = $variable['value'] % 60;
                                                        echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                                    } else {
                                                        echo htmlspecialchars($variable['value']);
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-muted">
                                                    <?php echo !empty($variable['description']) ? htmlspecialchars($variable['description']) : '<em>No description</em>'; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button class="meatball-btn" data-env-id="<?php echo $variable['id']; ?>">
                                                        <i class="fas fa-ellipsis-h"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Page Content Only View -->
            <div id="page-content-view" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Page Content</h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table m-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Page Title</th>
                                <th>Slug</th>
                                <th>Last Updated</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Same page content as above -->
                            <?php
                            if (count($pages) > 0) {
                                foreach ($pages as $page) {
                                    $status_badge = $page['status'] === 'published' ? 'bg-success' : 'bg-warning';
                                    echo '<tr>
                                        <td class="ps-4 fw-medium">
                                            ' . htmlspecialchars($page['title']) . '
                                            <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Page ID: ' . $page['id'] . ' | URL: /' . $page['slug'] . ' | Created: ' . $page['created_at'] . '"></i>
                                        </td>
                                        <td>' . htmlspecialchars($page['slug']) . '</td> 
                                        <td>' . date('Y-m-d', strtotime($page['updated_at'])) . '</td>
                                        <td><span class="badge ' . $status_badge . '">' . ucfirst($page['status']) . '</span></td>
                                        <td class="text-center">
                                            <button class="meatball-btn" data-page-id="' . $page['id'] . '">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center">No pages found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal for CMS Content -->
<div class="modal fade" id="cmsContentModal" tabindex="-1" aria-labelledby="cmsContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cmsContentModalLabel">Edit Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form will be dynamically inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCmsContent">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal for CMS Content -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Content Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Details will be dynamically inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Page Content Modal -->
<div class="modal fade" id="pageContentModal" tabindex="-1" aria-labelledby="pageContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageContentModalLabel">Add Page Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pageContentForm">
                    <input type="hidden" id="page_id" name="page_id" value="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="page_title" class="form-label">Page Title</label>
                            <input type="text" class="form-control" id="page_title" name="title" required>
                        </div>
                        <div class="col-md-6">
                            <label for="page_slug" class="form-label">Page Slug</label>
                            <div class="input-group">
                                <span class="input-group-text">/</span>
                                <input type="text" class="form-control" id="page_slug" name="slug" required>
                            </div>
                            <small class="text-muted">URL-friendly version of the title (e.g., about-us)</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="page_content" class="form-label">Page Content</label>
                        <textarea id="page_content" name="content" class="form-control summernote"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="page_status" class="form-label">Status</label>
                            <select class="form-select" id="page_status" name="status">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="savePageContent">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePageModal" tabindex="-1" aria-labelledby="deletePageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg>
                </div>
                <h4 class="fw-bold mb-3" id="deletePageModalLabel">Confirm Delete</h4>
                <p>Are you sure you want to delete this page? This action cannot be undone.</p>
                <input type="hidden" id="delete_page_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePage">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize tooltips and content
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true,
                container: 'body'
            }); 
        });
        
        // Function to initialize content management tab when it becomes visible
        const initializeContentManagementTab = () => {
            // Check if the env-variables tab is currently visible
            if (document.getElementById('env-variables').classList.contains('active')) {
                // Ensure the "All Content" tab is active and visible
                const allContentTab = document.getElementById('all-content');
                const allContentTabButton = document.getElementById('all-content-tab');
                
                if (allContentTab && !allContentTab.classList.contains('show')) {
                    // Manually activate the "All Content" tab if it's not already active
                    document.querySelectorAll('#cmsContentTabs .nav-link').forEach(tab => {
                        tab.classList.remove('active');
                        tab.setAttribute('aria-selected', 'false');
                    });
                    
                    document.querySelectorAll('#cmsContentTabsContent .tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });
                    
                    if (allContentTabButton) {
                        allContentTabButton.classList.add('active');
                        allContentTabButton.setAttribute('aria-selected', 'true');
                    }
                    
                    allContentTab.classList.add('show', 'active');
                }
            }
        };
        
        // Initialize when the main dashboard tab for content management becomes visible
        document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(e) {
                if (e.target.id === 'env-variables-tab') {
                    initializeContentManagementTab();
                }
            });
        });
        
        // Call initialization function on page load
        // This ensures content is visible if the content management tab is visible by default
        setTimeout(initializeContentManagementTab, 100);
        
        // Add click event for info icons to show modal with full details
        document.querySelectorAll('.info-icon').forEach(function(icon) {
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Get the tooltip content
                var content = this.getAttribute('data-bs-original-title') || this.getAttribute('title');
                
                // Get the item name for the modal title
                var itemName = this.parentElement.textContent.trim().split('\n')[0].trim();
                document.getElementById('detailModalLabel').innerHTML = '<i class="fas fa-info-circle text-primary me-2"></i>' + itemName;
                
                // Format the content for the modal
                var formattedContent = '';
                content.split('|').forEach(function(item) {
                    var parts = item.split(':');
                    if (parts.length > 1) {
                        formattedContent += '<div class="detail-item">';
                        formattedContent += '<div class="detail-label">' + parts[0].trim() + '</div>';
                        formattedContent += '<div class="detail-value">' + parts.slice(1).join(':').trim() + '</div>';
                        formattedContent += '</div>';
                    }
                });
                
                // Set the modal content and show it
                document.getElementById('detailModalBody').innerHTML = formattedContent;
                var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                detailModal.show();
            });
        });

        // Handle password field display in the edit modal
        const cmsModalElement = document.getElementById('editModal');
        if (cmsModalElement) {
            cmsModalElement.addEventListener('shown.bs.modal', function () {
                const keyInput = cmsModalElement.querySelector('input[name="key"]');
                const valueInput = cmsModalElement.querySelector('input[name="value"]');
                const tableInput = cmsModalElement.querySelector('input[name="table"]');

                // Check if it's the env_variables form and the inputs exist
                if (tableInput && tableInput.value === 'env_variables' && keyInput && valueInput) {
                    // Check if the key contains 'PASSWORD'
                    if (keyInput.value.toUpperCase().includes('PASSWORD')) {
                        valueInput.setAttribute('type', 'password');
                        // Optional: Clear the value or set placeholder if desired for security
                        valueInput.value = ''; 
                        valueInput.placeholder = 'Enter new password or leave blank to keep unchanged';
                    } else {
                        // Ensure it's text for non-password fields
                        valueInput.setAttribute('type', 'text');
                    }
                }
            });
        }
        
        // --- CMS Content Type Dropdown Functionality ---
        const cmsContentTypeSelect = document.getElementById('cmsContentTypeSelect');
        const addSettingBtn = document.getElementById('addSettingBtn');
        const addPageContentBtn = document.getElementById('addPageContentBtn');
        const allContentView = document.getElementById('all-content-view');
        const systemSettingsView = document.getElementById('system-settings-view');
        const pageContentView = document.getElementById('page-content-view');

        // Handle dropdown change
        cmsContentTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Hide all views first
            allContentView.style.display = 'none';
            systemSettingsView.style.display = 'none';
            pageContentView.style.display = 'none';
            
            // Hide all buttons first
            addSettingBtn.style.display = 'none';
            addPageContentBtn.style.display = 'none';
            
            // Show appropriate view and button based on selection
            switch(selectedType) {
                case 'all-content':
                    allContentView.style.display = 'block';
                    // Show both buttons for "All Content" view
                    addSettingBtn.style.display = 'inline-flex';
                    addPageContentBtn.style.display = 'inline-flex';
                    break;
                case 'system-settings':
                    systemSettingsView.style.display = 'block';
                    addSettingBtn.style.display = 'inline-flex';
                    break;
                case 'page-content':
                    pageContentView.style.display = 'block';
                    addPageContentBtn.style.display = 'inline-flex';
                    break;
            }
        });

        // Initialize default view
        cmsContentTypeSelect.dispatchEvent(new Event('change'));

        // --- Meatball Menu Functionality ---
        // Handle meatball button clicks
        document.addEventListener('click', function(e) {
            // Only handle meatball clicks if we're in the env-variables tab
            const envVariablesTab = document.getElementById('env-variables');
            if (!envVariablesTab || (!envVariablesTab.classList.contains('active') && !envVariablesTab.classList.contains('show'))) {
                return;
            }
            
            // Handle meatball button clicks for environment variables
            if (e.target.closest('.meatball-btn[data-env-id]')) {
                e.preventDefault();
                e.stopPropagation();
                
                const btn = e.target.closest('.meatball-btn');
                const envId = btn.getAttribute('data-env-id');
                const existingDropdown = document.getElementById(`env-dropdown-${envId}`);
                
                // Close all other dropdowns first
                document.querySelectorAll('.meatball-dropdown-portal').forEach(dd => {
                    dd.style.display = 'none';
                });
                
                if (existingDropdown) {
                    existingDropdown.remove();
                }
                
                // Create new dropdown
                const dropdownPortal = document.createElement('div');
                dropdownPortal.className = 'meatball-dropdown-portal';
                dropdownPortal.id = `env-dropdown-${envId}`;
                
                const rect = btn.getBoundingClientRect();
                const top = rect.bottom + window.scrollY;
                const left = rect.left + window.scrollX - 100;
                
                dropdownPortal.style.cssText = `
                    position: absolute;
                    top: ${top}px;
                    left: ${left}px;
                    z-index: 1000;
                    background: white;
                    border: 1px solid #dee2e6;
                    border-radius: 0.375rem;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                    min-width: 120px;
                    padding: 4px 0;
                    display: block;
                `;
                dropdownPortal.innerHTML = `
                    <button class="meatball-dropdown-item edit-btn" data-table="env_variables" data-id="${envId}">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                `;
                document.body.appendChild(dropdownPortal);
            }
            // Handle meatball button clicks for page content
            else if (e.target.closest('.meatball-btn[data-page-id]')) {
                e.preventDefault();
                e.stopPropagation();
                
                const btn = e.target.closest('.meatball-btn');
                const pageId = btn.getAttribute('data-page-id');
                const existingDropdown = document.getElementById(`page-dropdown-${pageId}`);
                
                // Close all other dropdowns first
                document.querySelectorAll('.meatball-dropdown-portal').forEach(dd => {
                    dd.style.display = 'none';
                });
                
                if (existingDropdown) {
                    existingDropdown.remove();
                }
                
                // Create new dropdown
                const dropdownPortal = document.createElement('div');
                dropdownPortal.className = 'meatball-dropdown-portal';
                dropdownPortal.id = `page-dropdown-${pageId}`;
                
                const rect = btn.getBoundingClientRect();
                const top = rect.bottom + window.scrollY;
                const left = rect.left + window.scrollX - 100;
                
                dropdownPortal.style.cssText = `
                    position: absolute;
                    top: ${top}px;
                    left: ${left}px;
                    z-index: 1000;
                    background: white;
                    border: 1px solid #dee2e6;
                    border-radius: 0.375rem;
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                    min-width: 120px;
                    padding: 4px 0;
                    display: block;
                `;
                dropdownPortal.innerHTML = `
                    <button class="meatball-dropdown-item edit-page-btn" data-id="${pageId}">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="meatball-dropdown-item delete-page-btn" data-id="${pageId}">
                        <i class="fas fa-trash-alt"></i>
                        Delete
                    </button>
                `;
                document.body.appendChild(dropdownPortal);
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
                
                // The existing edit-btn, edit-page-btn, and delete-page-btn event handlers will handle the action
                // since we've preserved the same classes on the dropdown items
            }
        });
    });
</script>