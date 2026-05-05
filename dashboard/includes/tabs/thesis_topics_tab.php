<!-- Thesis Topics Tab -->
<div class="tab-pane fade" id="thesis-topics" role="tabpanel" aria-labelledby="thesis-topics-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Thesis Topics</h3>
                <p class="text-muted">Manage and explore potential research topics for student teams</p>
            </div>
        </div>

        <!-- Thesis Topics Management Controls -->
        <div class="row">
            <div class="col-12">
                <!-- Mobile-first responsive layout -->
                <div class="thesis-topics-controls-container p-0 mt-3">
                    <!-- Search and Filter Row -->
                    <div class="row g-2 mb-3 align-items-end">
                        <div class="col-12 col-md-3 col-lg-3">
                            <!-- Search container -->
                            <div class="thesis-topics-search-container">
                                <div class="input-group thesis-topic-control-height m-0">
                                    <span class="input-group-text border-0"> 
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control border-0" id="thesisTopicSearchInput" placeholder="Search topics...">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-3 col-lg-2">
                            <!-- Main Action Dropdown -->
                            <div class="thesis-topics-tab-controls">
                                <select class="form-select thesis-topic-control-height" id="thesisTopicActionSelect">
                                    <option value="manage">Manage Topics</option>
                                    <option value="search">Search Topics</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-3 col-lg-3">
                            <!-- Category Filter Dropdown (for manage mode) -->
                            <div class="thesis-topics-filter-controls" id="thesisTopicCategoryFilterContainer">
                                <select class="form-select thesis-topic-control-height" id="thesisTopicCategoryFilter">
                                    <option value="">All Categories</option>
                                    <?php
                                    // Get database connection
                                    require_once '../assets/setup/db.inc.php'; // Adjust path as needed

                                    try {
                                        // Check if $conn is initialized, otherwise try to connect
                                        if (!isset($pdo)) {
                                             // Replace with your actual connection logic if not already connected
                                             throw new Exception("Database connection not available.");
                                        }

                                        $stmt = $pdo->query("SELECT college, name, specialization FROM programs ORDER BY college, name");
                                        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        $groupedPrograms = [];
                                        foreach ($programs as $program) {
                                            $groupedPrograms[$program['college']][] = $program;
                                        }

                                        foreach ($groupedPrograms as $college => $collegePrograms) {
                                            echo '<optgroup label="' . htmlspecialchars($college) . '">';
                                            foreach ($collegePrograms as $program) {
                                                // Use the program name as the base display text and value
                                                $displayText = htmlspecialchars($program['name']);
                                                $optionValue = htmlspecialchars($program['name']);

                                                // If there is a specialization, append it to the display text
                                                if (!empty($program['specialization'])) {
                                                    $displayText .= ' - ' . htmlspecialchars($program['specialization']);
                                                }

                                                // Output the option tag
                                                echo '<option value="' . $optionValue . '">' . $displayText . '</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                    } catch (Exception $e) {
                                        // Log error or display a user-friendly message
                                        error_log("Error fetching programs: " . $e->getMessage());
                                        echo '<option value="" disabled>Error loading categories</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <!-- Field Select Dropdown (for search mode) -->
                            <div class="thesis-topics-field-controls" id="thesisTopicFieldSelectContainer" style="display: none;">
                                <select class="form-select thesis-topic-control-height" id="thesisField">
                                    <option value="">Select a field</option>
                                    <?php
                                    // Reuse the grouped programs for field selection
                                    if (isset($groupedPrograms) && !empty($groupedPrograms)) {
                                        foreach ($groupedPrograms as $college => $collegePrograms) {
                                            echo '<optgroup label="' . htmlspecialchars($college) . '">';
                                            foreach ($collegePrograms as $program) {
                                                $displayText = htmlspecialchars($program['name']);
                                                $optionValue = htmlspecialchars($program['name']);

                                                if (!empty($program['specialization'])) {
                                                    $displayText .= ' - ' . htmlspecialchars($program['specialization']);
                                                }

                                                echo '<option value="' . $optionValue . '">' . $displayText . '</option>';
                                            }
                                            echo '</optgroup>';
                                        }
                                    } else {
                                        echo '<option value="" disabled>Error loading programs</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-3 col-lg-2">
                            <!-- Sort Dropdown (for manage mode) -->
                            <div id="thesisTopicSortContainer">
                                <select class="form-select thesis-topic-control-height" id="thesisTopicSortSelect">
                                    <option value="id:desc">Default (Newest First)</option>
                                    <option value="id:asc">Default (Oldest First)</option>
                                    <option value="topic:asc">Topic (A-Z)</option>
                                    <option value="topic:desc">Topic (Z-A)</option>
                                    <option value="category:asc">Category (A-Z)</option>
                                    <option value="category:desc">Category (Z-A)</option>
                                </select>
                            </div>
                            
                            <!-- Get Topics Button (for search mode) -->
                            <div id="thesisTopicGetTopicsContainer" style="display: none;">
                                <button id="getTopicsBtn" class="btn feature-btn thesis-topic-control-height w-100">
                                    <i class="fas fa-search me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Get Topics</span>
                                    <span class="d-lg-none">Search</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-12 col-md-12 col-lg-2">
                            <!-- Action buttons container (for manage mode) -->
                            <div class="d-flex gap-2" id="thesisTopicAddButtonContainer">
                                <button class="btn feature-btn add-btn thesis-topic-control-height flex-fill" data-table="thesis_topics" id="addThesisTopicBtn">
                                    <i class="fas fa-plus me-1 d-none d-lg-inline"></i>
                                    <span class="d-none d-lg-inline">Add Topic</span>
                                    <span class="d-lg-none">Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Areas -->
        <!-- Decision Tool Section (shown when "Search Topics" is selected) -->
        <div id="thesisTopicDecisionToolContainer" style="display: none;">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="decision-tool-container">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <h6 class="mb-0 feature-title">Thesis Topic Decision Tool</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <label for="thesisFieldDecisionTool" class="form-label">Field of study:</label>
                                    <select id="thesisFieldDecisionTool" class="form-select">
                                        <option value="">Select a field</option>
                                        <?php
                                        // Reuse the grouped programs for the decision tool
                                        if (isset($groupedPrograms) && !empty($groupedPrograms)) {
                                            foreach ($groupedPrograms as $college => $collegePrograms) {
                                                echo '<optgroup label="' . htmlspecialchars($college) . '">';
                                                foreach ($collegePrograms as $program) {
                                                    $displayText = htmlspecialchars($program['name']);
                                                    $optionValue = htmlspecialchars($program['name']);

                                                    if (!empty($program['specialization'])) {
                                                        $displayText .= ' - ' . htmlspecialchars($program['specialization']);
                                                    }

                                                    echo '<option value="' . $optionValue . '">' . $displayText . '</option>';
                                                }
                                                echo '</optgroup>';
                                            }
                                        } else {
                                            echo '<option value="" disabled>Error loading programs</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button id="getTopicsDecisionBtn" class="btn feature-btn">Get Latest Topics</button>
                            </div>
                        </div>
                        <div id="topicAnalysisResult" class="mt-3">
                            <!-- Analysis results will be loaded here --> 
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thesis Topics Table Section (shown when "Manage Topics" is selected) -->
        <div id="thesisTopicManageContainer">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive db-table-container">
                        <table class="table table-bordered table-hover table-sm db-table" id="thesis-topics-table" data-table="thesis_topics">
                            <thead>
                                <tr>
                                    <th class="d-none d-md-table-cell">Topic</th>
                                    <th class="d-table-cell d-md-none">Topic</th>
                                    <th class="d-none d-lg-table-cell">Description</th>
                                    <th class="d-none d-sm-table-cell">Category</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <nav aria-label="Thesis Topics Page navigation">
                        <ul class="pagination justify-content-center flex-wrap mt-2" id="thesisTopicsPagination">
                            <!-- Pagination loaded via AJAX -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        let btnCounter = 1;
        
        // Update the button click handler to use the new decision tool field
        document.getElementById('getTopicsDecisionBtn').addEventListener('click', () => {
            const targetNode = document.getElementById('topicAnalysisResult');
            const config = {
                childList: true,
                subtree: true
            };

            const callback = (mutationsList, observer) => {
                const tableContainer = targetNode.querySelector('.table-responsive .table');
                if (tableContainer) {
                    const headerRow = tableContainer.querySelector('tr');
                    if (!headerRow.querySelector('th:last-child') || !headerRow.querySelector('th:last-child').textContent.includes('Actions')) {
                        const th = document.createElement('th');
                        th.textContent = 'Actions';
                        headerRow.appendChild(th);

                        const rows = tableContainer.querySelectorAll('tbody tr');
                        Array.from(rows).slice(1).forEach((row) => {
                            const td = document.createElement('td');
                            td.innerHTML = `<button class="btn btn-primary btn-sm add-btn feature-btn btn-${btnCounter}" data-table="thesis_topics">Add</button>`;
                            btnCounter++;
                            row.appendChild(td);
                        });
                    }
                    observer.disconnect();
                }
            };

            const observer = new MutationObserver(callback);
            observer.observe(targetNode, config);
        });
        
        $(document).on('click', '.add-btn', function(e) {
            // Only process this if it's a thesis topic button
            if (!$(this).closest('#thesis-topics').length && $(this).data('table') !== 'thesis_topics') {
                return true; // Exit and let other handlers take over
            }
            
            // Correctly reference the clicked button using 'this'
            var button = $(this);
            console.log('Thesis topic add button clicked');

            // Retrieve the 'btn-n' class if needed
            var btnClasses = button.attr('class').split(' ');
            var btnNumber = btnClasses.find(cls => cls.startsWith('btn-') && cls !== 'btn-primary' && cls !== 'btn-sm');
            if (btnNumber) {
                btnNumber = btnNumber.replace('btn-', '');
                console.log('Button number:', btnNumber);
            }

            // Get the current row
            var row = button.closest('tr');

            // Extract data from the row
            var topic = row.find('td:nth-child(1)').text().trim();
            var description = row.find('td:nth-child(2)').text().trim();
            var potentialImpact = row.find('td:nth-child(3)').text().trim();

            // Get the selected category from the dropdown (use decision tool field)
            var category = $('#thesisFieldDecisionTool').val();

            // Debugging: Log extracted values
            console.log('Topic:', topic);
            console.log('Description:', description);
            console.log('Potential Impact:', potentialImpact);
            console.log('Category:', category);

            // Populate the modal form fields
            $('#addModal').off('shown.bs.modal').on('shown.bs.modal', function() {
                const topicInput = document.querySelector('#addForm #topic');
                const descriptionInput = document.querySelector('#addForm #description');
                const categoryInput = document.querySelector('#addForm #category');
                if (topicInput && descriptionInput && categoryInput) {
                    topicInput.value = topic;
                    descriptionInput.value = description + ' ' + potentialImpact;
                    categoryInput.value = category;
                } else {
                    console.error('One or more input elements not found. Details:');
                    if (!topicInput) console.error('Topic input element not found.');
                    if (!descriptionInput) console.error('Description input element not found.');
                    if (!categoryInput) console.error('Category input element not found.');
                }
            });
            
            // Explicitly show the modal for thesis topics
            $('#addModal').modal('show'); 
            
            return false; // Prevent other handlers from processing this
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle thesis topics mode switching
            const handleThesisTopicModeSwitch = (mode) => {
                console.log('Switching thesis topics mode to:', mode);
                
                // Get all containers
                const decisionToolContainer = document.getElementById('thesisTopicDecisionToolContainer');
                const manageContainer = document.getElementById('thesisTopicManageContainer');
                const categoryFilterContainer = document.getElementById('thesisTopicCategoryFilterContainer');
                const fieldSelectContainer = document.getElementById('thesisTopicFieldSelectContainer');
                const sortContainer = document.getElementById('thesisTopicSortContainer');
                const getTopicsContainer = document.getElementById('thesisTopicGetTopicsContainer');
                const addButtonContainer = document.getElementById('thesisTopicAddButtonContainer');
                
                if (mode === 'search') {
                    // Show decision tool, hide manage topics
                    decisionToolContainer.style.display = 'block';
                    manageContainer.style.display = 'none';
                    
                    // Show field select, hide category filter
                    categoryFilterContainer.style.display = 'none';
                    fieldSelectContainer.style.display = 'block';
                    
                    // Show get topics button, hide sort and add button
                    sortContainer.style.display = 'none';
                    getTopicsContainer.style.display = 'block';
                    addButtonContainer.style.display = 'none';
                } else { // mode === 'manage'
                    // Show manage topics, hide decision tool
                    decisionToolContainer.style.display = 'none';
                    manageContainer.style.display = 'block';
                    
                    // Show category filter, hide field select
                    categoryFilterContainer.style.display = 'block';
                    fieldSelectContainer.style.display = 'none';
                    
                    // Show sort and add button, hide get topics button
                    sortContainer.style.display = 'block';
                    getTopicsContainer.style.display = 'none';
                    addButtonContainer.style.display = 'block';
                }
            };

            // Handle action dropdown change
            document.getElementById('thesisTopicActionSelect').addEventListener('change', function() {
                const selectedMode = this.value;
                handleThesisTopicModeSwitch(selectedMode);
                
                // If switching to manage mode, reload the topics
                if (selectedMode === 'manage') {
                    reloadCurrentView(1);
                }
            });

            // Initialize with manage mode by default
            handleThesisTopicModeSwitch('manage');

            // Function to load thesis topics with search, filter, and sorting
            const loadThesisTopics = (page = 1, search = '', category = '', sort = 'id:desc') => {
                let url = `includes/tabs/get_table.php?table=thesis_topics&page=${page}`;
                
                if (search) {
                    url += `&search=${encodeURIComponent(search)}`;
                }
                
                if (category) {
                    url += `&category=${encodeURIComponent(category)}`;
                }
                
                if (sort) {
                    const [sortField, sortOrder] = sort.split(':');
                    url += `&sort_by=${encodeURIComponent(sortField)}&sort_dir=${encodeURIComponent(sortOrder)}`;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        const tbody = document.querySelector('#thesis-topics-table tbody');
                        tbody.innerHTML = '';
                        
                        // Show a message if no results
                        if (data.data.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No records found.</td>
                                </tr>
                            `;
                            return;
                        }
                        
                        data.data.forEach(topic => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>
                                        <div class="thesis-topic-title">${topic.topic}</div>
                                        <div class="d-md-none small text-muted mt-1">${topic.description}</div>
                                        <div class="d-sm-none small text-muted mt-1">${topic.category}</div>
                                    </td>
                                    <td class="d-none d-lg-table-cell">${topic.description}</td>
                                    <td class="d-none d-sm-table-cell d-lg-none">${topic.category}</td>
                                    <td class="d-none d-sm-table-cell d-lg-table-cell">${topic.category}</td>
                                    <td class="action-buttons">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm edit-btn" data-table="thesis_topics" data-id="${topic.id}">
                                                <i class="fas fa-edit d-none d-lg-inline me-1"></i>
                                                <span class="d-none d-lg-inline">Edit</span>
                                                <i class="fas fa-edit d-lg-none"></i>
                                            </button>
                                            <button class="btn btn-sm delete-btn" data-table="thesis_topics" data-id="${topic.id}">
                                                <i class="fas fa-trash-alt d-none d-lg-inline me-1"></i>
                                                <span class="d-none d-lg-inline">Delete</span>
                                                <i class="fas fa-trash-alt d-lg-none"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        // Update Pagination
                        const pagination = document.querySelector('#thesisTopicsPagination');
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

            // Function to get current filters
            const getCurrentFilters = () => {
                return {
                    search: document.getElementById('thesisTopicSearchInput').value,
                    category: document.getElementById('thesisTopicCategoryFilter').value,
                    sort: document.getElementById('thesisTopicSortSelect').value
                };
            };

            // Function to reload current view
            const reloadCurrentView = (page = 1) => {
                const filters = getCurrentFilters();
                loadThesisTopics(page, filters.search, filters.category, filters.sort);
            };

            // Initial Load
            loadThesisTopics();

            // Handle Search Input with debouncing
            let searchTimeout;
            document.getElementById('thesisTopicSearchInput').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    reloadCurrentView(1);
                }, 300);
            });

            // Handle Category Filter Change
            document.getElementById('thesisTopicCategoryFilter').addEventListener('change', function() {
                reloadCurrentView(1);
            });

            // Handle Sort Dropdown Change
            document.getElementById('thesisTopicSortSelect').addEventListener('change', function() {
                reloadCurrentView(1);
            });

            // Handle Pagination Clicks
            document.querySelector('#thesisTopicsPagination').addEventListener('click', function(e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        reloadCurrentView(page);
                    }
                }
            });

            // Initialize when the thesis topics tab becomes visible
            document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    if (e.target.id === 'thesis-topics-tab') {
                        reloadCurrentView(1);
                    }
                });
            });
        });
    </script>
</div>