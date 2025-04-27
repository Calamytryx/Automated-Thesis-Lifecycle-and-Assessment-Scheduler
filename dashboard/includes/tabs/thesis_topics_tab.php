<!-- Thesis Topics Tab -->
<div class="tab-pane fade" id="thesis-topics" role="tabpanel" aria-labelledby="thesis-topics-tab">
    <div class="container-fluid py-4">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Thesis Topics</h3>
                <p class="text-muted">Manage and explore potential research topics for student teams</p>
            </div>
        </div>

        <!-- Decision Tool Section -->
        <div class="decision-tool-container mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 feature-title">Thesis Topic Decision Tool</h6>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="thesisField" class="form-label">Field of study:</label>
                        <select id="thesisField" class="form-select">
                            <option value="">Select a field</option>
                            <?php
                            // Assuming $conn is your database connection object (e.g., PDO or mysqli)
                            // Include your database connection file if necessary
                            require_once '../assets/setup/db.inc.php'; // Adjust path as needed

                            try {
                                // Check if $conn is initialized, otherwise try to connect
                                if (!isset($pdo)) {
                                     // Replace with your actual connection logic if not already connected
                                     // Example using PDO:
                                     // $dsn = 'mysql:host=localhost;dbname=your_db_name;charset=utf8mb4';
                                     // $username = 'your_username';
                                     // $password = 'your_password';
                                     // $options = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC ];
                                     // $conn = new PDO($dsn, $username, $password, $options);

                                     // For this example, let's assume connection is handled elsewhere or throw error
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
                                        // Use the program name as the base display text
                                        $displayText = htmlspecialchars($program['name']);
                                        // Use the program name as the default value
                                        $optionValue = htmlspecialchars($program['name']);

                                        // If there is a specialization, append it to the display text
                                        if (!empty($program['specialization'])) {
                                            $displayText .= ' with specialization in ' . htmlspecialchars($program['specialization']) . '';
                                            // Optionally, you could change the value here if needed, e.g.:
                                            // $optionValue = htmlspecialchars($program['name'] . ' - ' . $program['specialization']);
                                        }

                                        // Output the option tag
                                        echo '<option value="' . $optionValue . '">' . $displayText . '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                            } catch (Exception $e) {
                                // Log error or display a user-friendly message
                                error_log("Error fetching programs: " . $e->getMessage());
                                echo '<option value="" disabled>Error loading programs</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <button id="getTopicsBtn" class="btn feature-btn">Get Latest Topics</button>
                </div>
            </div>
            <div id="topicAnalysisResult" class="mt-3">
                <!-- Analysis results will be loaded here --> 
            </div>
        </div>

        <!-- Search, Filter and Add Button Row -->
        <div class="d-flex justify-content-end align-items-center mb-4 flex-wrap">
            <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                <div class="input-group mb-2 mb-md-0" style="width: 250px;">
                    <input type="text" class="form-control" id="topicSearchInput" placeholder="Search topics...">
                    <button class="btn btn-outline-secondary" type="button" id="topicSearchButton">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <select class="form-select mb-2 mb-md-0" id="topicCategoryFilter" style="width: 180px;">
                    <option value="">All Categories</option>
                    <option value="Architecture">Architecture</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Information Technology">Information Technology</option>
                    <option value="Aeronautical Engineering">Aeronautical Engineering</option>
                    <option value="Civil Engineering">Civil Engineering</option>
                    <option value="Computer Engineering">Computer Engineering</option>
                    <option value="Electrical Engineering">Electrical Engineering</option>
                    <option value="Electronics Engineering">Electronics Engineering</option>
                    <option value="Industrial Engineering">Industrial Engineering</option>
                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                </select>
                <select class="form-select mb-2 mb-md-0" id="topicSortSelect" style="width: 180px;">
                    <option value="id:desc">Default (Newest First)</option>
                    <option value="id:asc">Default (Oldest First)</option>
                    <option value="topic:asc">Topic (A-Z)</option>
                    <option value="topic:desc">Topic (Z-A)</option>
                    <option value="category:asc">Category (A-Z)</option>
                    <option value="category:desc">Category (Z-A)</option>
                </select>
                <button class="btn feature-btn add-btn" data-table="thesis_topics">
                    <i class="fas fa-plus me-2"></i>Add Thesis Topic
                </button>
            </div>
        </div>

        <!-- Topics Table Section -->
        <div class="table-responsive db-table-container">
            <table class="table table-bordered table-hover table-sm db-table">
                <thead>
                    <tr>
                        <th>Topic</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded via AJAX -->
                </tbody>
            </table>

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <!-- Pagination loaded via AJAX -->
                </ul>
            </nav>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        let btnCounter = 1;
        document.getElementById('getTopicsBtn').addEventListener('click', () => {
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

            // Get the selected category from the dropdown
            var category = $('#thesisField').val();

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

                        const tbody = document.querySelector('#thesis-topics .db-table tbody');
                        tbody.innerHTML = '';
                        
                        // Show a message if no results
                        if (data.data.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="4" class="text-center">No matching topics found</td>
                                </tr>
                            `;
                            return;
                        }
                        
                        data.data.forEach(topic => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${topic.topic}</td>
                                    <td>${topic.description}</td>
                                    <td>${topic.category}</td>
                                    <td class="action-buttons">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-sm edit-btn" data-table="thesis_topics" data-id="${topic.id}">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm delete-btn" data-table="thesis_topics" data-id="${topic.id}">
                                                <i class="fas fa-trash-alt me-1"></i>Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });

                        // Update Pagination
                        const pagination = document.querySelector('#thesis-topics .pagination');
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

            // Initial Load
            loadThesisTopics();

            // Handle Search Button Click
            document.getElementById('topicSearchButton').addEventListener('click', function() {
                const searchTerm = document.getElementById('topicSearchInput').value;
                const categoryFilter = document.getElementById('topicCategoryFilter').value;
                const sortValue = document.getElementById('topicSortSelect').value;
                loadThesisTopics(1, searchTerm, categoryFilter, sortValue);
            });

            // Handle Search on Enter Key
            document.getElementById('topicSearchInput').addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = this.value;
                    const categoryFilter = document.getElementById('topicCategoryFilter').value;
                    const sortValue = document.getElementById('topicSortSelect').value;
                    loadThesisTopics(1, searchTerm, categoryFilter, sortValue);
                }
            });

            // Handle Category Filter Change
            document.getElementById('topicCategoryFilter').addEventListener('change', function() {
                const searchTerm = document.getElementById('topicSearchInput').value;
                const categoryFilter = this.value;
                const sortValue = document.getElementById('topicSortSelect').value;
                loadThesisTopics(1, searchTerm, categoryFilter, sortValue);
            });

            // Handle Sort Dropdown Change
            document.getElementById('topicSortSelect').addEventListener('change', function() {
                const searchTerm = document.getElementById('topicSearchInput').value;
                const categoryFilter = document.getElementById('topicCategoryFilter').value;
                const sortValue = this.value;
                loadThesisTopics(1, searchTerm, categoryFilter, sortValue);
            });

            // Handle Pagination Clicks
            document.querySelector('#thesis-topics .pagination').addEventListener('click', function(e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        const searchTerm = document.getElementById('topicSearchInput').value;
                        const categoryFilter = document.getElementById('topicCategoryFilter').value;
                        const sortValue = document.getElementById('topicSortSelect').value;
                        loadThesisTopics(page, searchTerm, categoryFilter, sortValue);
                    }
                }
            });

            // Initialize when the thesis topics tab becomes visible
            document.querySelectorAll('#v-pills-tab .nav-link').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(e) {
                    if (e.target.id === 'thesis-topics-tab') {
                        const searchTerm = document.getElementById('topicSearchInput').value;
                        const categoryFilter = document.getElementById('topicCategoryFilter').value;
                        const sortValue = document.getElementById('topicSortSelect').value;
                        loadThesisTopics(1, searchTerm, categoryFilter, sortValue);
                    }
                });
            });
        });
    </script>
</div>