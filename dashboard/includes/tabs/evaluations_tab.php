<!-- Evaluations Tab -->
<div class="tab-pane fade" id="evaluations" role="tabpanel" aria-labelledby="evaluations-tab">
    <div class="container-fluid py-4 content-container" id="evaluations-container">
        <!-- Header Row -->
        <div class="row mb-4">
            <div class="col">
                <h3 class="mb-2" id="evaluations-view-title">Team View</h3>
                <p class="text-muted" id="evaluations-view-description">View evaluation results organized by team with aggregated scores and evaluation counts.</p>
            </div>
        </div>

        <!-- Action Buttons Row -->
        <div class="row mb-3">
            <div class="col">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <button type="button" class="btn feature-btn evaluations-switch-btn" id="change-view">
                        <i class="fas fa-exchange-alt"></i> Switch to Detailed View
                    </button>
                </div>
            </div> 
        </div>

        <!-- Evaluations Table -->
        <div class="table-responsive">
            <table class="table db-table" id="evaluations-table">
                <thead>
                    <!-- Headers will be dynamically populated via AJAX -->
                    <tr>
                        <!-- Default headers for team view -->
                        <th>Team Name</th>
                        <th class="d-none d-md-table-cell">Research Title</th>
                        <th class="d-none d-lg-table-cell">Program</th>
                        <th class="d-none d-lg-table-cell">Adviser</th>
                        <th class="text-center">Evaluations</th>
                        <th class="text-center d-none d-sm-table-cell">Avg Score</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically populated via AJAX -->
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

<!-- Evaluation Details Modal -->
<div class="modal fade" id="evaluationDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar me-2"></i>Team Evaluation Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="evaluationDetailsBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">Loading evaluation details...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentView = 'team'; // Initial view state: 'team' or 'detailed'
        const evaluationsTableBody = document.querySelector('#evaluations-table tbody');
        const evaluationsTableHeadRow = document.querySelector('#evaluations-table thead tr');
        const evaluationsPagination = document.querySelector('#evaluations-pagination .pagination');
        const changeViewButton = document.getElementById('change-view');
        const viewTitle = document.getElementById('evaluations-view-title');
        const viewDescription = document.getElementById('evaluations-view-description');

        // Function to update table headers based on the view
        const updateTableHeaders = (view) => {
            if (view === 'team') {
                // Headers for team-aggregated view
                evaluationsTableHeadRow.innerHTML = `
                    <th>Team Name</th>
                    <th class="d-none d-md-table-cell">Research Title</th>
                    <th class="d-none d-lg-table-cell">Program</th>
                    <th class="d-none d-lg-table-cell">Adviser</th>
                    <th class="text-center">Evaluations</th>
                    <th class="text-center d-none d-sm-table-cell">Avg Score</th>
                    <th class="text-center">Action</th>
                `;
            } else {
                // Headers for detailed evaluator view (evaluation_per_panel records)
                evaluationsTableHeadRow.innerHTML = `
                    <th>Team Name</th>
                    <th class="d-none d-lg-table-cell">Evaluator</th>
                    <th class="d-none d-md-table-cell">Student</th>
                    <th class="text-center d-none d-sm-table-cell">Group Score</th>
                    <th class="text-center d-none d-sm-table-cell">Individual Score</th>
                    <th class="text-center">Total Score</th>
                    <th class="d-none d-lg-table-cell">Comments</th>
                `;
            }
        };

        // Function to render table rows based on the view and data
        const renderTableRows = (data, view) => {
            evaluationsTableBody.innerHTML = ''; // Clear existing rows

            if (!data || data.length === 0) {
                evaluationsTableBody.innerHTML = `<tr><td colspan="${evaluationsTableHeadRow.children.length}" class="text-center">No evaluations found.</td></tr>`;
                return;
            }

            if (view === 'team') {
                // Aggregate data by team
                const teamAggregates = {};

                data.forEach(evaluation => {
                    const teamId = evaluation.team_id;
                    if (!teamId) return;

                    if (!teamAggregates[teamId]) {
                        teamAggregates[teamId] = {
                            team_id: teamId,
                            team_name: evaluation.team_name || 'N/A',
                            research_title: evaluation.research_title || '',
                            program: evaluation.program || 'N/A',
                            adviser: evaluation.adviser || '',
                            evaluation_count: 0,
                            total_scores: []
                        };
                    }

                    teamAggregates[teamId].evaluation_count++;
                    if (evaluation.total_score !== null && !isNaN(parseFloat(evaluation.total_score))) {
                        teamAggregates[teamId].total_scores.push(parseFloat(evaluation.total_score));
                    }
                });

                // Render team rows
                Object.values(teamAggregates).forEach(team => {
                    const avgScore = team.total_scores.length > 0
                        ? (team.total_scores.reduce((a, b) => a + b, 0) / team.total_scores.length).toFixed(2)
                        : 'N/A';
                    
                    const researchTitleDisplay = team.research_title 
                        ? escapeHtml(team.research_title) 
                        : '<em class="text-muted">No title yet</em>';
                    
                    const adviserDisplay = team.adviser || '<em class="text-muted">No adviser</em>';

                    evaluationsTableBody.innerHTML += `
                        <tr>
                            <td>
                                <strong>${escapeHtml(team.team_name)}</strong>
                                <small class="d-block d-md-none text-muted">${researchTitleDisplay}</small>
                            </td>
                            <td class="d-none d-md-table-cell">${researchTitleDisplay}</td>
                            <td class="d-none d-lg-table-cell"><small>${escapeHtml(team.program)}</small></td>
                            <td class="d-none d-lg-table-cell">${adviserDisplay}</td>
                            <td class="text-center"><span class="badge bg-primary">${team.evaluation_count}</span></td>
                            <td class="text-center d-none d-sm-table-cell">${avgScore !== 'N/A' ? `<strong>${avgScore}</strong>` : '<em class="text-muted">N/A</em>'}</td>
                            <td class="text-center">
                                <button class="btn btn-sm edit-btn view-team-details" data-team-id="${team.team_id}">
                                    <i class="fas fa-eye"></i><span class="d-none d-sm-inline"> View</span>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                // Render detailed evaluator view (one row per evaluation_per_panel record)
                data.forEach(evaluation => {
                    const evaluatorName = `${evaluation.evaluator_first_name || ''} ${evaluation.evaluator_last_name || ''}`.trim() || 'N/A';
                    const studentName = `${evaluation.student_first_name || ''} ${evaluation.student_last_name || ''}`.trim() || 'N/A';
                    const comments = evaluation.comments ? escapeHtml(evaluation.comments) : '<em class="text-muted">No comments</em>';

                    evaluationsTableBody.innerHTML += `
                        <tr>
                            <td>
                                <strong>${escapeHtml(evaluation.team_name || 'N/A')}</strong>
                                <small class="d-block d-md-none text-muted">${escapeHtml(studentName)}</small>
                            </td>
                            <td class="d-none d-lg-table-cell">${escapeHtml(evaluatorName)}</td>
                            <td class="d-none d-md-table-cell">${escapeHtml(studentName)}</td>
                            <td class="text-center d-none d-sm-table-cell">${evaluation.group_score ?? 'N/A'}</td>
                            <td class="text-center d-none d-sm-table-cell">${evaluation.solo_score ?? 'N/A'}</td>
                            <td class="text-center"><strong>${evaluation.total_score ?? 'N/A'}</strong></td>
                            <td class="d-none d-lg-table-cell"><small>${comments}</small></td>
                        </tr>
                    `;
                });
            }
        };

        // Function to update pagination controls
        const updatePagination = (currentPage, totalPages) => {
            evaluationsPagination.innerHTML = '';

            if (totalPages <= 1) return;

            // Previous Button
            evaluationsPagination.innerHTML += `
                <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currentPage - 1}">&#8249;</a>
                </li>
            `;

            // Page Numbers
            const maxPagesToShow = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

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
                    <a class="page-link" href="#" data-page="${currentPage + 1}">&#8250;</a>
                </li>
            `;
        };

        // Function to fetch and display evaluations
        const loadEvaluations = (page = 1, view = currentView) => {
            const fetchUrl = `includes/tabs/get_table.php?table=evaluations&page=${page}`;

            fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.error) {
                        throw new Error(`API Error: ${result.error}`);
                    }

                    if (!result.data || !Array.isArray(result.data)) {
                        updateTableHeaders(view);
                        evaluationsTableBody.innerHTML = `<tr><td colspan="${evaluationsTableHeadRow.children.length}" class="text-center">No evaluation data available.</td></tr>`;
                        updatePagination(0, 0);
                        updateViewLabels(view);
                        return;
                    }

                    updateTableHeaders(view);
                    renderTableRows(result.data, view);
                    updatePagination(result.page || page, result.total_pages || 1);
                    updateViewLabels(view);
                })
                .catch(error => {
                    console.error('Load Evaluations Error:', error);
                    updateTableHeaders(view);
                    evaluationsTableBody.innerHTML = `<tr><td colspan="${evaluationsTableHeadRow.children.length}" class="text-danger text-center">Could not load evaluations. Please try again.</td></tr>`;
                    updatePagination(0, 0);
                });
        };

        // Update view labels
        function updateViewLabels(view) {
            if (view === 'team') {
                viewTitle.textContent = 'Team View';
                viewDescription.textContent = 'View evaluation results organized by team with aggregated scores and evaluation counts.';
                changeViewButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Switch to Detailed View';
            } else {
                viewTitle.textContent = 'Detailed View';
                viewDescription.textContent = 'View individual evaluation records for each evaluator\'s assessment of students.';
                changeViewButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Switch to Team View';
            }
        }

        // Handle view switching
        changeViewButton.addEventListener('click', function () {
            currentView = currentView === 'team' ? 'detailed' : 'team';
            loadEvaluations(1, currentView);
        });

        // Handle pagination clicks
        evaluationsPagination.addEventListener('click', function (e) {
            e.preventDefault();
            if (e.target.tagName === 'A' && e.target.hasAttribute('data-page')) {
                const pageLink = e.target;
                if (pageLink.parentElement.classList.contains('disabled') || pageLink.parentElement.classList.contains('active')) {
                    return;
                }
                const page = parseInt(pageLink.getAttribute('data-page'));
                if (!isNaN(page) && page > 0) {
                    loadEvaluations(page, currentView);
                }
            }
        });

        // Handle view team details button
        evaluationsTableBody.addEventListener('click', function(e) {
            const viewBtn = e.target.closest('.view-team-details');
            if (viewBtn) {
                const teamId = viewBtn.getAttribute('data-team-id');
                showTeamEvaluationDetails(teamId);
            }
        });

        // Show team evaluation details modal
        function showTeamEvaluationDetails(teamId) {
            const modal = new bootstrap.Modal(document.getElementById('evaluationDetailsModal'));
            modal.show();

            document.getElementById('evaluationDetailsBody').innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3">Loading evaluation details...</p>
                </div>
            `;

            // Fetch detailed evaluations for this team
            fetch(`includes/tabs/get_table.php?table=evaluations&page=1`)
                .then(response => response.json())
                .then(result => {
                    if (result.error || !result.data) {
                        throw new Error(result.error || 'No data');
                    }

                    // Filter evaluations for this team
                    const teamEvaluations = result.data.filter(e => e.team_id == teamId);
                    
                    if (teamEvaluations.length === 0) {
                        document.getElementById('evaluationDetailsBody').innerHTML = `
                            <div class="alert alert-info">No evaluations found for this team.</div>
                        `;
                        return;
                    }

                    // Get team info from first evaluation
                    const teamInfo = teamEvaluations[0];
                    
                    let detailsHtml = `
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">${escapeHtml(teamInfo.team_name)}</h5>
                            <p class="mb-1"><strong>Research Title:</strong> ${teamInfo.research_title ? escapeHtml(teamInfo.research_title) : '<em class="text-muted">No title yet</em>'}</p>
                            <p class="mb-1"><strong>Program:</strong> ${escapeHtml(teamInfo.program || 'N/A')}</p>
                            <p class="mb-0"><strong>Adviser:</strong> ${teamInfo.adviser || '<em class="text-muted">No adviser</em>'}</p>
                        </div>

                        <h6 class="text-primary mb-3">Evaluation Records</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Evaluator</th>
                                        <th>Student</th>
                                        <th class="text-center">Group</th>
                                        <th class="text-center">Individual</th>
                                        <th class="text-center">Total</th>
                                        <th>Comments</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    teamEvaluations.forEach(eval => {
                        const evaluatorName = `${eval.evaluator_first_name || ''} ${eval.evaluator_last_name || ''}`.trim() || 'N/A';
                        const studentName = `${eval.student_first_name || ''} ${eval.student_last_name || ''}`.trim() || 'N/A';
                        
                        detailsHtml += `
                            <tr>
                                <td>${escapeHtml(evaluatorName)}</td>
                                <td>${escapeHtml(studentName)}</td>
                                <td class="text-center">${eval.group_score ?? 'N/A'}</td>
                                <td class="text-center">${eval.solo_score ?? 'N/A'}</td>
                                <td class="text-center"><strong>${eval.total_score ?? 'N/A'}</strong></td>
                                <td><small>${eval.comments ? escapeHtml(eval.comments) : '<em class="text-muted">No comments</em>'}</small></td>
                            </tr>
                        `;
                    });

                    detailsHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    document.getElementById('evaluationDetailsBody').innerHTML = detailsHtml;
                })
                .catch(error => {
                    console.error('Error loading team details:', error);
                    document.getElementById('evaluationDetailsBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>Failed to load evaluation details.
                        </div>
                    `;
                });
        }

        // Utility function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, m => map[m]);
        }

        // Initial load
        loadEvaluations();
    });
</script>