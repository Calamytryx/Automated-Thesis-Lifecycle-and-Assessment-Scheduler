<!-- Thesis Topics Tab -->
<div class="tab-pane fade" id="thesis-topics" role="tabpanel" aria-labelledby="thesis-topics-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="thesis_topics">Add Thesis Topic</button>
    </div>
    <div class="my-3 home-sidebar-box">
        <h6 class="border-bottom border-secondary pb-2 mb-0 feature-title">Thesis Topic Decision Tool</h6>
        <div class="media text-muted pt-3">
            <div class="form-group">
                <label for="thesisField">Select a field:</label>
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
            <button id="getTopicsBtn" class="btn btn-primary mt-3 feature-btn">Get Latest Topics</button>
        </div>
        <div id="topicAnalysisResult" class="mt-3">
        </div>
    </div>

    <div class="table-responsive">
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
        $(document).on('click', '.add-btn', function() {
            // Correctly reference the clicked button using 'this'
            var button = $(this);
            console.log('Add button clicked');

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
            $('#addModal').on('shown.bs.modal', function() {
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
                                    <td class="text-center align-middle">
                                        <button class="btn btn-primary btn-sm edit-btn" data-table="thesis_topics" data-id="${topic.id}">Edit</button>
                                        <button class="btn btn-danger btn-sm delete-btn" data-table="thesis_topics" data-id="${topic.id}">Delete</button>
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