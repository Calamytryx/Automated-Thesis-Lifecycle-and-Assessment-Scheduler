<!-- Thesis Topics Tab -->
<div class="tab-pane fade" id="thesis-topics" role="tabpanel" aria-labelledby="thesis-topics-tab">
    <!-- Header Section with Add Button -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <button class="btn feature-btn add-btn" data-table="thesis_topics">
            <i class="fas fa-plus"></i>Add Thesis Topic
        </button>   
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
                        <option value="Architecture">Architecture</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Aeronautical Engineering">Aeronautical Engineering</option>
                        <option value="Civil Engineering">Civil Engineering</option>
                        <option value="Computer Engineering">Computer Engineering</option>
                        <option value="Engineering Technology with a major in Construction Technology and Management">Engineering Technology (Construction Technology and Management)</option>
                        <option value="Electrical Engineering">Electrical Engineering</option>
                        <option value="Electronics Engineering">Electronics Engineering</option>
                        <option value="Industrial Engineering">Industrial Engineering</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                    </select>
                </div>
                <button id="getTopicsBtn" class="btn feature-btn">Get Latest Topics</button>
            </div>
        </div>
        <div id="topicAnalysisResult" class="mt-3">
            <!-- Analysis results will be loaded here --> 
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
            const loadThesisTopics = (page = 1) => {
                fetch(`includes/tabs/get_table.php?table=thesis_topics&page=${page}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        const tbody = document.querySelector('#thesis-topics .db-table tbody');
                        tbody.innerHTML = '';
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

            // Handle Pagination Clicks
            document.querySelector('#thesis-topics .pagination').addEventListener('click', function(e) {
                e.preventDefault();
                if (e.target.tagName === 'A') {
                    const page = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(page)) {
                        loadThesisTopics(page);
                    }
                }
            });
        });
    </script>
</div>