<!-- Evaluations Tab -->
<div class="tab-pane fade" id="evaluations" role="tabpanel" aria-labelledby="evaluations-tab">
    <div class="container-fluid py-4 content-container" id="evaluations-container">
        <!-- Header Row -->
        <div class="row mb-4">
            <div class="col">
                <h3 class="mb-2" id="evaluations-view-title">Evaluator View</h3>
                <p class="text-muted" id="evaluations-view-description">View detailed evaluation results for each evaluator's assessment of students and teams.</p>
            </div>
        </div>

        <!-- Action Buttons Row -->
        <div class="row mb-3">
            <div class="col">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button type="button" class="btn feature-btn evaluations-switch-btn" id="change-view">
                        <i class="fas fa-exchange-alt"></i> Switch to Student View
                    </button>
                </div>
            </div> 
        </div>

        <!-- Evaluations Table -->
        <div class="table-responsive">
            <table class="table table-hover db-table" id="evaluations-table">
                <thead>
                    <!-- Headers will be dynamically populated via AJAX -->
                    <tr>
                        <!-- Default headers for evaluator view -->
                        <th>Team Name</th>
                        <th>Evaluator</th>
                        <th>Student</th>
                        <th>Group Score</th>
                        <th>Individual Score</th>
                        <th>Total Score</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically populated via AJAX based on evaluation_per_panel and related tables -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Page navigation" id="evaluations-pagination">
            <ul class="pagination justify-content-center">
                <!-- Pagination will be dynamically populated via AJAX -->
            </ul>
        </nav>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentView = 'evaluator'; // Initial view state: 'evaluator' or 'student'
        const evaluationsTableBody = document.querySelector('#evaluations-table tbody');
        const evaluationsTableHeadRow = document.querySelector('#evaluations-table thead tr');
        const evaluationsPagination = document.querySelector('#evaluations-pagination .pagination');
        const changeViewButton = document.getElementById('change-view');
        const viewTitle = document.getElementById('evaluations-view-title');
        const viewDescription = document.getElementById('evaluations-view-description');

        // Function to update table headers based on the view
        const updateTableHeaders = (view) => {
            if (view === 'student') {
                // Headers for aggregated student view
                evaluationsTableHeadRow.innerHTML = `
                    <th>Team Name</th>
                    <th>Student</th>
                    <th>Avg. Group Score</th>
                    <th>Avg. Individual Score</th>
                    <th>Avg. Total Score</th>
                `;
            } else {
                // Headers for detailed evaluator view (based on evaluation_per_panel)
                evaluationsTableHeadRow.innerHTML = `
                    <th>Team Name</th>
                    <th>Evaluator</th>
                    <th>Student</th>
                    <th>Group Score</th>
                    <th>Individual Score</th>
                    <th>Total Score</th>
                    <th>Comments</th>
                `;
            }
        };

        // Function to render table rows based on the view and data
        const renderTableRows = (data, view) => {
            evaluationsTableBody.innerHTML = ''; // Clear existing rows

            if (!data || data.length === 0) {
                 console.log('renderTableRows: No data or empty data array received.'); // Log if data is empty
                 evaluationsTableBody.innerHTML = `<tr><td colspan="${evaluationsTableHeadRow.children.length}" class="text-center">No evaluations found.</td></tr>`;
                 return; // Exit if no data
            }

             // Log the structure of the first item for debugging if data exists
            if (data.length > 0) {
                console.log('renderTableRows: First data item structure:', data[0]);
            }


            if (view === 'student') {
                // Aggregate data for student view
                const studentAggregates = {};

                data.forEach(evaluation => {
                    // Use a unique student identifier (student_id)
                    const studentKey = evaluation.student_id;
                    if (!studentKey) return; // Skip if student_id is missing

                    if (!studentAggregates[studentKey]) {
                        studentAggregates[studentKey] = {
                            // Assume team_name is fetched via JOIN in the backend
                            team_name: evaluation.team_name || 'N/A',
                            // Assume student names are fetched via JOIN in the backend
                            student_name: `${evaluation.student_first_name || ''} ${evaluation.student_last_name || ''}`.trim() || `Student ID: ${studentKey}`,
                            group_scores: [],
                            solo_scores: [],
                            total_scores: []
                        };
                    }
                    // Collect scores for averaging - handle potential nulls/non-numeric from DB
                    if (evaluation.group_score !== null && !isNaN(parseFloat(evaluation.group_score))) {
                        studentAggregates[studentKey].group_scores.push(parseFloat(evaluation.group_score));
                    }
                    if (evaluation.solo_score !== null && !isNaN(parseFloat(evaluation.solo_score))) {
                        studentAggregates[studentKey].solo_scores.push(parseFloat(evaluation.solo_score));
                    }
                     if (evaluation.total_score !== null && !isNaN(parseFloat(evaluation.total_score))) {
                        studentAggregates[studentKey].total_scores.push(parseFloat(evaluation.total_score));
                    }
                });

                // Calculate averages and render rows
                Object.values(studentAggregates).forEach(student => {
                    const avgGroupScore = student.group_scores.length > 0
                        ? (student.group_scores.reduce((a, b) => a + b, 0) / student.group_scores.length).toFixed(2)
                        : 'N/A';
                    const avgSoloScore = student.solo_scores.length > 0
                        ? (student.solo_scores.reduce((a, b) => a + b, 0) / student.solo_scores.length).toFixed(2)
                        : 'N/A';
                    const avgTotalScore = student.total_scores.length > 0
                        ? (student.total_scores.reduce((a, b) => a + b, 0) / student.total_scores.length).toFixed(2)
                        : 'N/A';


                    evaluationsTableBody.innerHTML += `
                        <tr>
                            <td>${student.team_name}</td>
                            <td>${student.student_name}</td>
                            <td>${avgGroupScore}</td>
                            <td>${avgSoloScore}</td>
                            <td>${avgTotalScore}</td>
                        </tr>
                    `;
                });

            } else {
                // Render evaluator view (one row per evaluation_per_panel record)
                data.forEach(evaluation => {
                    // Use data potentially joined from evaluation_per_panel, users (evaluator, student), teams etc. in the backend
                    const evaluatorName = `${evaluation.evaluator_first_name || ''} ${evaluation.evaluator_last_name || ''}`.trim();
                    const studentName = `${evaluation.student_first_name || ''} ${evaluation.student_last_name || ''}`.trim();

                    evaluationsTableBody.innerHTML += `
                        <tr>
                            <td>${evaluation.team_name || 'N/A'}</td>
                            <td>${evaluatorName || `Evaluator ID: ${evaluation.evaluator_id}`}</td>
                            <td>${studentName || `Student ID: ${evaluation.student_id}`}</td>
                            <td>${evaluation.group_score ?? 'N/A'}</td>
                            <td>${evaluation.solo_score ?? 'N/A'}</td>
                            <td>${evaluation.total_score ?? 'N/A'}</td>
                            <td>${evaluation.comments || ''}</td>
                        </tr>
                    `;
                });
            }
        };

        // Function to update pagination controls
        const updatePagination = (currentPage, totalPages) => {
            evaluationsPagination.innerHTML = ''; // Clear existing pagination

            if (totalPages <= 1) return; // Don't show pagination if only one page

            // Previous Button
            evaluationsPagination.innerHTML += `
                <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">&#8249;</a>
                </li>
            `;

            // Page Numbers (simplified, consider adding ellipsis for many pages)
            const maxPagesToShow = 5; // Adjust as needed
            let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

            // Adjust startPage if endPage reaches the limit first
             if (endPage === totalPages) {
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }

            if (startPage > 1) {
                 evaluationsPagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
                 if (startPage > 2) {
                     evaluationsPagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                 }
            }


            for (let i = startPage; i <= endPage; i++) {
                evaluationsPagination.innerHTML += `
                    <li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }

             if (endPage < totalPages) {
                 if (endPage < totalPages - 1) {
                     evaluationsPagination.innerHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                 }
                 evaluationsPagination.innerHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
            }


            // Next Button
            evaluationsPagination.innerHTML += `
                <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">&#8250;</a>
                </li>
            `;
        };

        // Function to fetch and display evaluations
        const loadEvaluations = (page = 1, view = currentView) => { // Default page = 1, view = currentView
            console.log(`Loading evaluations - Page: ${page}, View: ${view}`); // Add logging

            // Construct the URL - Removed the 'view' parameter as backend doesn't use it for fetching
            const fetchUrl = `includes/tabs/get_table.php?table=evaluations&page=${page}`;
            console.log('Fetching from URL:', fetchUrl); // Log the URL

            fetch(fetchUrl)
                .then(response => { // Step 1: Check response status and prepare for parsing
                    console.log('Fetch response status:', response.status); // Log status
                    if (!response.ok) {
                        // Try to get text even on error for debugging
                        return response.text().then(text => {
                            console.error('Error Response Text:', text);
                            throw new Error(`HTTP error! status: ${response.status}, message: ${text || 'No error message body'}`);
                        });
                    }
                    // Clone response to read text first for debugging, then parse JSON
                    return response.clone().text().then(text => {
                        console.log('Raw Response Text:', text); // Log raw text
                        try {
                            if (!text) {
                                // Handle empty response body if it's unexpected
                                console.warn("Empty response received from server.");
                                // Depending on API design, this might be an error or just mean no data
                                // If it should always return JSON, treat as error:
                                throw new Error("Empty response received from server.");
                                // Or return a default structure if empty is valid (e.g., for no results):
                                // return { data: [], total_pages: 0, current_page: 1 };
                            }
                            return JSON.parse(text); // Attempt to parse
                        } catch (e) {
                            console.error("JSON Parsing Error:", e, "Raw text was:", text);
                            throw new Error(`Failed to parse JSON response: ${e.message}`); // Re-throw with context
                        }
                    });
                })
                .then(result => { // Step 2: Process the successfully parsed JSON data
                    console.log('Parsed data:', result); // Log parsed data

                    if (result.error) {
                        console.error('API Error:', result.error);
                        // Throw an error to be caught by the .catch block
                        throw new Error(`API Error: ${result.error}`);
                    }

                    // Check if data exists and is an array before proceeding
                    if (!result.data || !Array.isArray(result.data)) {
                         console.warn('API Warning: Data is missing or not an array.', result);
                         // Render empty state gracefully
                         updateTableHeaders(view); // Update headers to match view
                         evaluationsTableBody.innerHTML = `<tr><td colspan="${evaluationsTableHeadRow.children.length}" class="text-center">No evaluation data available.</td></tr>`;
                         updatePagination(0, 0); // Clear pagination
                         // Still update view title/button even if no data
                         if (view === 'student') {
                            viewTitle.textContent = 'Student View (Aggregated)';
                            viewDescription.textContent = 'View aggregated evaluation scores and averages for each student across all evaluators.';
                            changeViewButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Switch to Evaluator View';
                         } else {
                            viewTitle.textContent = 'Evaluator View (Detailed)';
                            viewDescription.textContent = 'View detailed evaluation results for each evaluator\'s assessment of students and teams.';
                            changeViewButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Switch to Student View';
                         }
                         return; // Stop further processing in this .then block
                    }

                    // --- Success Case: Data is valid ---
                    updateTableHeaders(view); // Set headers first
                    renderTableRows(result.data, view); // Populate table body
                    // Use result.page and result.total_pages from the JSON response
                    updatePagination(result.page || page, result.total_pages || 1); // Update pagination controls

                    // Update view title and button text
                    if (view === 'student') {
                        viewTitle.textContent = 'Student View (Aggregated)';
                        viewDescription.textContent = 'View aggregated evaluation scores and averages for each student across all evaluators.';
                        changeViewButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Switch to Evaluator View';
                    } else {
                        viewTitle.textContent = 'Evaluator View (Detailed)';
                        viewDescription.textContent = 'View detailed evaluation results for each evaluator\'s assessment of students and teams.';
                        changeViewButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Switch to Student View';
                    }
                })
                .catch(error => { // Step 3: Catch any errors from fetch or .then blocks
                    console.error('Load Evaluations Error:', error); // Log the caught error
                    updateTableHeaders(view); // Update headers to match the attempted view
                    // Display a user-friendly error message in the table
                    evaluationsTableBody.innerHTML = `<tr><td colspan="${evaluationsTableHeadRow.children.length}" class="text-danger text-center">Could not load evaluations: ${error.message}. Check console for details.</td></tr>`;
                    updatePagination(0, 0); // Clear pagination on error
                    // Optionally update title/button on error too
                     if (view === 'student') {
                        viewTitle.textContent = 'Student View (Error)';
                        viewDescription.textContent = 'Unable to load student evaluation data. Please try again later.';
                        changeViewButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Switch to Evaluator View';
                     } else {
                        viewTitle.textContent = 'Evaluator View (Error)';
                        viewDescription.textContent = 'Unable to load evaluator assessment data. Please try again later.';
                        changeViewButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Switch to Student View';
                     }
                });
        }; // End of loadEvaluations function definition

        // --- Event Listeners ---

        // Handle Change View Button Click
        changeViewButton.addEventListener('click', function () {
            currentView = currentView === 'evaluator' ? 'student' : 'evaluator'; // Toggle view
            loadEvaluations(1, currentView); // Reload evaluations from page 1 with the new view
        });

        // Handle Pagination Clicks (using event delegation)
        evaluationsPagination.addEventListener('click', function (e) {
            e.preventDefault();
            if (e.target.tagName === 'A' && e.target.hasAttribute('data-page')) {
                const pageLink = e.target;
                // Prevent clicking on disabled links or the current page link
                if (pageLink.parentElement.classList.contains('disabled') || pageLink.parentElement.classList.contains('active')) {
                    return;
                }
                const page = parseInt(pageLink.getAttribute('data-page'));
                if (!isNaN(page) && page > 0) {
                    loadEvaluations(page, currentView); // Load the clicked page for the current view
                }
            }
        });

        // --- Initial Load ---
        loadEvaluations(); // Load the initial view (evaluator) on page load

    });
</script>
