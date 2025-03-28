<!-- Evaluations Tab -->
<div class="tab-pane fade" id="evaluations" role="tabpanel" aria-labelledby="evaluations-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button type="button" class="btn btn-primary" id="change-view">
            Change View
        </button>
    </div>
    <div class="table-responsive db-table-container">
        <table class="table table-bordered table-hover table-sm db-table" id="evaluations-table">
            <thead>
                <tr>
                    <th>Team Name</th>
                    <th>Evaluator</th>
                    <th>Student</th>
                    <th>Group Score</th>
                    <th>individual Score</th>
                    <th>Total Score</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be dynamically populated via AJAX -->
            </tbody>
        </table>
    </div>
    <nav aria-label="Page navigation" id="evaluations-pagination">
        <ul class="pagination justify-content-center">
            <!-- Pagination will be dynamically populated via AJAX -->
        </ul>
    </nav>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentView = 'evaluator'; // Add view state

        const loadEvaluations = (page = 1, view = currentView) => { // Modify function to accept view
            fetch(`includes/tabs/get_table.php?table=evaluations&page=${page}&view=${view}`) // Include view parameter
                .then(response => response.json())
                .then(data => {
                    console.log('Fetched data:', data); // Log fetched data for debugging
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    const thead = document.querySelector('#evaluations-table thead tr');
                    if (view === 'student') { // Modify table headers for student view
                        thead.innerHTML = `
                            <tr>
                                <th>Team Name</th>
                                <th>Student</th>
                                <th>Group Score</th>
                                <th>Individual Score</th>
                                <th>Total Score</th>
                            </tr>
                        `;
                    } else {
                        thead.innerHTML = `
                            <tr>
                                <th>Team Name</th>
                                <th>Evaluator</th>
                                <th>Student</th>
                                <th>Group Score</th>
                                <th>Individual Score</th>
                                <th>Total Score</th>
                                <th>Comments</th>
                            </tr>
                        `;
                    }

                    const tbody = document.querySelector('#evaluations-table tbody');
                    tbody.innerHTML = '';

                    if (view === 'student') { // Add aggregation for student view
                        const studentMap = {};

                        data.data.forEach(evaluation => {
                            const studentKey = `${evaluation.student_first_name} ${evaluation.student_last_name}`;
                            if (!studentMap[studentKey]) {
                                studentMap[studentKey] = {
                                    team_name: evaluation.team_name,
                                    student: studentKey,
                                    group_score: parseFloat(evaluation.group_score),
                                    solo_scores: []
                                };
                            }
                            if (evaluation.solo_score) {
                                studentMap[studentKey].solo_scores.push(parseFloat(evaluation.solo_score));
                            }
                        });

                        Object.values(studentMap).forEach(student => {
                            const soloTotal = student.solo_scores.reduce((a, b) => a + b, 0);
                            const soloCount = student.solo_scores.length;
                            const solo_avg = soloCount > 0 ? (soloTotal / soloCount).toFixed(2) : 'N/A';
                            const total_avg = solo_avg !== 'N/A' ? ((student.group_score + parseFloat(solo_avg))).toFixed(2) : 'N/A';

                            tbody.innerHTML += `
                                <tr>
                                    <td>${student.team_name}</td>
                                    <td>${student.student}</td>
                                    <td>${student.group_score}</td>
                                    <td>${solo_avg}</td>
                                    <td>${total_avg}</td>
                                </tr>
                            `;
                        });
                    } else { // Existing evaluator view rendering
                        data.data.forEach(evaluation => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${evaluation.team_name}</td>
                                    <td>${evaluation.evaluator_first_name} ${evaluation.evaluator_last_name}</td>
                                    <td>${evaluation.student_first_name} ${evaluation.student_last_name}</td>
                                    <td>${evaluation.group_score}</td>
                                    <td>${evaluation.solo_score}</td>
                                    <td>${evaluation.total_score}</td>
                                    <td>${evaluation.comments}</td>
                                </tr>
                            `;
                        });
                    }

                    // Update Pagination
                    const pagination = document.querySelector('#evaluations-pagination .pagination');
                    pagination.innerHTML = '';

                    // Previous Button
                    pagination.innerHTML += `
                        <li class="page-item ${page <= 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">&#8249;</a>
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

                    // Next Button
                    pagination.innerHTML += `
                        <li class="page-item ${page >= data.total_pages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">&#8250;</a>
                        </li>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching evaluations:', error); // Log any fetch errors
                });
        };

        // Initial Load
        loadEvaluations();

        // Handle Change View Button Click
        document.getElementById('change-view').addEventListener('click', function () {
            currentView = currentView === 'evaluator' ? 'student' : 'evaluator'; // Toggle view
            loadEvaluations(); // Reload evaluations with new view
        });

        // Handle Pagination Clicks
        document.querySelector('#evaluations-pagination .pagination').addEventListener('click', function (e) {
            e.preventDefault();
            if (e.target.tagName === 'A') {
                const page = parseInt(e.target.getAttribute('data-page'));
                if (!isNaN(page)) {
                    loadEvaluations(page);
                }
            }
        });
    });
</script>
