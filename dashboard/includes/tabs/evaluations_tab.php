<!-- Evaluations Tab -->
<div class="tab-pane fade" id="evaluations" role="tabpanel" aria-labelledby="evaluations-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="evaluations">Add Evaluation</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm db-table" id="evaluations-table">
            <thead>
                <tr>
                    <th>Team Name</th>
                    <th>Evaluator</th>
                    <th>Student</th>
                    <th>Group Score</th>
                    <th>Solo Score</th>
                    <th>Total Score</th>
                    <th>Comments</th>
                    <th>Action</th>
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
        const loadEvaluations = (page = 1) => {
            fetch(`includes/tabs/get_table.php?table=evaluations&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Fetched data:', data); // Log fetched data for debugging
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    const tbody = document.querySelector('#evaluations .db-table tbody');
                    tbody.innerHTML = '';
                    data.data.forEach(evaluation => {
                        console.log('Processing evaluation:', evaluation); // Log each evaluation for debugging
                        tbody.innerHTML += `
                            <tr>
                                <td>${evaluation.team_name}</td>
                                <td>${evaluation.evaluator_first_name} ${evaluation.evaluator_last_name}</td>
                                <td>${evaluation.student_first_name} ${evaluation.student_last_name}</td>
                                <td>${evaluation.group_score}</td>
                                <td>${evaluation.solo_score}</td>
                                <td>${evaluation.total_score}</td>
                                <td>${evaluation.comments}</td>
                                <td class="text-center align-middle">
                                    <button class="btn btn-primary btn-sm edit-btn" data-table="evaluations" data-id="${evaluation.id}">Edit</button>
                                    <button class="btn btn-danger btn-sm delete-btn" data-table="evaluations" data-id="${evaluation.id}">Delete</button>
                                </td>
                            </tr>
                        `;
                    });

                    // Update Pagination
                    const pagination = document.querySelector('#evaluations .pagination');
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

        // Handle Pagination Clicks
        document.querySelector('#evaluations .pagination').addEventListener('click', function (e) {
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
