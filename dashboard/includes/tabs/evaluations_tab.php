<!-- Evaluations Tab -->
<div class="tab-pane fade" id="evaluations" role="tabpanel" aria-labelledby="evaluations-tab">
    <div class="container-fluid py-4 content-container" id="evaluations-container">
        <!-- Header Row -->
        <div class="row mb-4">
            <div class="col">
                <h3 class="mb-2">Group Evaluations</h3>
                <p class="text-muted">View evaluation results organized by group with aggregated scores</p>
            </div>
        </div>

        <!-- Loading State -->
        <div id="evaluations-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading evaluations...</p>
        </div>

        <!-- Table Container -->
        <div id="evaluations-content" style="display: none;">
            <div class="table-responsive">
                <table class="table db-table" id="evaluations-table">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th class="d-none d-md-table-cell">Research Title</th>
                            <th class="d-none d-lg-table-cell">Program</th>
                            <th class="d-none d-lg-table-cell">Adviser</th>
                            <th class="text-center d-none d-sm-table-cell">Avg Score</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="evaluations-table-body">
                        <!-- Populated via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Evaluations pagination" id="evaluations-pagination-container">
                <ul class="pagination justify-content-center" id="evaluations-pagination">
                    <!-- Populated via JavaScript -->
                </ul>
            </nav>
        </div>

        <!-- Empty State -->
        <div id="evaluations-empty" class="text-center py-5" style="display: none;">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">No records found.</p>
        </div>
    </div>

    <!-- Evaluation Details Modal -->
    <div class="modal fade" id="evaluationDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Group Evaluation Details:
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
</div>

<script>
// ==========================================
// EVALUATIONS TAB FUNCTIONALITY
// ==========================================
let currentEvaluationsPage = 1;

// Load evaluations when tab is shown
$(document).ready(function() {
    $('a[href="#evaluations"]').on('shown.bs.tab', function(e) {
        loadDashboardEvaluations(1);
    });
    
    // Check if evaluations tab is active on page load
    if ($('#evaluations').hasClass('show active')) {
        loadDashboardEvaluations(1);
    }
});

function loadDashboardEvaluations(page = 1) {
    currentEvaluationsPage = page;
    
    $('#evaluations-loading').show();
    $('#evaluations-content').hide();
    $('#evaluations-empty').hide();

    $.ajax({
        url: 'includes/tabs/get_dashboard_evaluations.php',
        method: 'GET',
        data: { page: page },
        dataType: 'json',
        success: function(response) {
            $('#evaluations-loading').hide();
            
            if (response.error) {
                console.error('Error:', response.error);
                $('#evaluations-empty').show();
                return;
            }

            if (!response.data || response.data.length === 0) {
                $('#evaluations-empty').show();
                return;
            }

            renderDashboardEvaluationsTable(response.data);
            renderDashboardEvaluationsPagination(response.page, response.total_pages);
            $('#evaluations-content').show();
        },
        error: function(xhr, status, error) {
            console.error('Error loading evaluations:', error);
            $('#evaluations-loading').hide();
            $('#evaluations-empty').show();
        }
    });
}

function renderDashboardEvaluationsTable(data) {
    const tbody = $('#evaluations-table-body');
    tbody.empty();

    data.forEach(function(team) {
        const researchTitle = team.research_title ? escapeHtml(team.research_title) : '<em class="text-muted">No records found.</em>';
        const adviser = team.adviser ? escapeHtml(team.adviser) : '<em class="text-muted">No records found.</em>';
        const avgScore = team.avg_total_score !== null ? `<strong>${parseFloat(team.avg_total_score).toFixed(2)}</strong>` : '<em class="text-muted">N/A</em>';
        
        const row = `
            <tr>
                <td>
                    ${escapeHtml(team.team_name)}
                    <small class="d-block d-md-none text-muted mt-1">${researchTitle}</small>
                </td>
                <td class="d-none d-md-table-cell">${researchTitle}</td>
                <td class="d-none d-lg-table-cell">${escapeHtml(team.program)}</td>
                <td class="d-none d-lg-table-cell">${adviser}</td>
                <td class="text-center d-none d-sm-table-cell">${avgScore}</td>
                <td class="text-center">
                    <button class="btn btn-sm edit-btn view-eval-details" data-team-id="${team.team_id}">
                        View
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function renderDashboardEvaluationsPagination(currentPage, totalPages) {
    const pagination = $('#evaluations-pagination');
    pagination.empty();

    if (totalPages <= 1) return;

    // Previous button
    const prevDisabled = currentPage <= 1 ? 'disabled' : '';
    pagination.append(`
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">&#8249;</a>
        </li>
    `);

    // Page numbers
    const maxPages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
    let endPage = Math.min(totalPages, startPage + maxPages - 1);

    if (endPage === totalPages) {
        startPage = Math.max(1, endPage - maxPages + 1);
    }

    if (startPage > 1) {
        pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
        if (startPage > 2) {
            pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const active = i === currentPage ? 'active' : '';
        pagination.append(`
            <li class="page-item ${active}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
        }
        pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
    }

    // Next button
    const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
    pagination.append(`
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">&#8250;</a>
        </li>
    `);
}

// Handle pagination clicks
$(document).on('click', '#evaluations-pagination a.page-link', function(e) {
    e.preventDefault();
    const page = parseInt($(this).data('page'));
    if (page && page > 0) {
        loadDashboardEvaluations(page);
    }
});

// Handle view details button
$(document).on('click', '.view-eval-details', function() {
    const teamId = $(this).data('team-id');
    showDashboardEvaluationDetails(teamId);
});

function showDashboardEvaluationDetails(teamId) {
    const modal = new bootstrap.Modal(document.getElementById('evaluationDetailsModal'));
    modal.show();

    $('#evaluationDetailsBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-3">Loading evaluation details...</p>
        </div>
    `);

    // Load team evaluation details
    $.ajax({
        url: 'includes/tabs/get_evaluation_details.php',
        method: 'GET',
        data: { team_id: teamId },
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                $('#evaluationDetailsBody').html(`
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>${data.error}
                    </div>
                `);
                return;
            }
            
            const team = data.team_info;
            const panelists = data.panelists || [];
            const students = data.students || [];
            const evaluationsByStudent = data.evaluations_by_student || {};
            const passThreshold = data.pass_threshold_3 || 75;
            const warningThreshold = Math.max(passThreshold - 15, 60);
            
            let html = `
                <div class="mb-4">
                    <h3 class="border-bottom pb-2">${escapeHtml(team.team_name)}</h3>
                    <p class="mb-1"><strong>Research Title:</strong> ${team.research_title ? escapeHtml(team.research_title) : '<em class="text-muted">No title yet</em>'}</p>
                    <p class="mb-1"><strong>Program:</strong> ${escapeHtml(team.program || 'N/A')}</p>
                    <p class="mb-0"><strong>Adviser:</strong> ${team.adviser || '<em class="text-muted">N/A</em>'}</p>
                </div>
                
                <h6 class="text-dark mb-3">Evaluation Summary:</h6>
            `;
            
            if (panelists.length === 0 || Object.keys(evaluationsByStudent).length === 0) {
                html += `<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No records found.</div>`;
            } else {
                html += `
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered team-eval-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    ${panelists.map(p => `<th class="text-center"><small>${escapeHtml(p.evaluator_name)}</small></th>`).join('')}
                                    <th class="text-center table-primary"><strong>Average</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                let allAverages = [];
                students.forEach(student => {
                    const studentData = evaluationsByStudent[student.student_id];
                    let scores = [];
                    
                    let cells = panelists.map(panelist => {
                        const scoreData = studentData?.panelist_scores?.[panelist.evaluator_id];
                        if (scoreData && scoreData.total_score !== null) {
                            const score = parseFloat(scoreData.total_score);
                            scores.push(score);
                            return `<td class="text-center text-dark">${score.toFixed(2)}</td>`;
                        }
                        return '<td class="text-center text-muted">-</td>';
                    }).join('');
                    
                    let avgDisplay = '<span class="text-muted">-</span>';
                    if (scores.length > 0) {
                        const avg = scores.reduce((a, b) => a + b, 0) / scores.length;
                        allAverages.push(avg);
                        const avgClass = avg >= passThreshold ? 'text-success' : avg >= warningThreshold ? 'text-warning' : 'text-danger';
                        avgDisplay = `<strong class="${avgClass}">${avg.toFixed(2)}</strong>`;
                    }
                    
                    html += `<tr><td>${escapeHtml(student.student_name)}</td>${cells}<td class="text-center table-primary">${avgDisplay}</td></tr>`;
                });
                
                // Team average
                let teamAvgDisplay = '<span class="text-muted">-</span>';
                if (allAverages.length > 0) {
                    const teamAvg = allAverages.reduce((a, b) => a + b, 0) / allAverages.length;
                    const teamAvgClass = teamAvg >= passThreshold ? 'text-success' : teamAvg >= warningThreshold ? 'text-warning' : 'text-danger';
                    teamAvgDisplay = `<strong class="${teamAvgClass}">${teamAvg.toFixed(2)}</strong>`;
                }
                
                html += `
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr><th>Group Average</th>${panelists.map(() => '<td></td>').join('')}<th class="text-center">${teamAvgDisplay}</th></tr>
                            </tfoot>
                        </table>
                    </div>
                `;
                
                // Add comments section if there are any (one per evaluator to avoid duplication)
                const evaluatorComments = new Map();
                
                Object.entries(evaluationsByStudent).forEach(([studentId, studentData]) => {
                    Object.entries(studentData.panelist_scores || {}).forEach(([panelistId, scoreData]) => {
                        if (scoreData.comments && scoreData.comments.trim() && !evaluatorComments.has(panelistId)) {
                            evaluatorComments.set(panelistId, {
                                evaluator_name: panelists.find(p => p.evaluator_id == panelistId)?.evaluator_name || 'Unknown',
                                comments: scoreData.comments
                            });
                        }
                    });
                });
                
                if (evaluatorComments.size > 0) {
                    let commentsHtml = `
                        <h6 class="text-dark mt-4 mb-3">Evaluator Comments</h6>
                        <div class="accordion accordion-flush" id="commentsAccordion">
                    `;
                    
                    let accordionIndex = 0;
                    evaluatorComments.forEach((commentData, panelistId) => {
                        commentsHtml += `
                            <div class="accordion-item evaluator-comment-item mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#comment${accordionIndex}">
                                        ${escapeHtml(commentData.evaluator_name)}
                                    </button>
                                </h2>
                                <div id="comment${accordionIndex}" class="accordion-collapse collapse" data-bs-parent="#commentsAccordion">
                                    <div class="accordion-body">
                                        ${escapeHtml(commentData.comments)}
                                    </div>
                                </div>
                            </div>
                        `;
                        accordionIndex++;
                    });
                    
                    commentsHtml += `</div>`;
                    html += commentsHtml;
                }
            }
            
            $('#evaluationDetailsBody').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error loading team details:', error);
            $('#evaluationDetailsBody').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>Failed to load evaluation details.
                </div>
            `);
        }
    });
}

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
</script>