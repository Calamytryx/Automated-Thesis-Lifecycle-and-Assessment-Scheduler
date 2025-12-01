<?php
session_start();
require_once '../assets/setup/db.inc.php';

// Check authorization - admins only
if (!isset($_SESSION['id']) || $_SESSION['usertype'] != 0) {
    header('Location: ../login');
    exit;
}

$userId = $_SESSION['id'];

// Get all sections for dropdown
$sectionsStmt = $pdo->prepare("
    SELECT id, name, course_code, academic_year, semester 
    FROM sections 
    ORDER BY academic_year DESC, semester, name
");
$sectionsStmt->execute();
$sections = $sectionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all faculty members
$facultyStmt = $pdo->prepare("
    SELECT id, first_name, last_name, email, username 
    FROM users 
    WHERE usertype = 2 
    ORDER BY last_name, first_name
");
$facultyStmt->execute();
$faculty = $facultyStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all active teams with their defense type
$teamsStmt = $pdo->prepare("
    SELECT DISTINCT 
        t.id,
        t.name,
        t.program,
        MAX(CASE WHEN dt.override_type IS NOT NULL THEN dt.override_type ELSE ds.defense_type END) as current_defense_type
    FROM teams t
    LEFT JOIN defense_type_overrides dt ON t.id = dt.team_id AND dt.active = 1
    LEFT JOIN defense_schedules ds ON t.id = ds.team_id
    GROUP BY t.id
    ORDER BY t.name
");
$teamsStmt->execute();
$teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Assignments - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .nav-tabs .nav-link.active {
            background-color: #0d6efd;
            color: white;
            border: none;
        }
        .nav-tabs .nav-link {
            color: #0d6efd;
        }
        .assignment-card {
            border-left: 4px solid #0d6efd;
            transition: transform 0.2s;
        }
        .assignment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.85rem;
        }
        .pending { background-color: #fff3cd; color: #856404; }
        .accepted { background-color: #d4edda; color: #155724; }
        .rejected { background-color: #f8d7da; color: #721c24; }
        .completed { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-person-badge"></i> Professor Assignments</h1>
            <a href="../dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#section-assignments" data-bs-toggle="tab">
                    <i class="bi bi-building"></i> Section Assignments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#team-assignments" data-bs-toggle="tab">
                    <i class="bi bi-people"></i> Team Assignments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#assignment-history" data-bs-toggle="tab">
                    <i class="bi bi-clock-history"></i> Assignment History
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Section Assignments Tab -->
            <div class="tab-pane fade show active" id="section-assignments" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Assign Professors to Sections</h5>
                    </div>
                    <div class="card-body">
                        <form id="sectionAssignmentForm">
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="sectionSelect" class="form-label">Select Section</label>
                                    <select id="sectionSelect" class="form-select" required>
                                        <option value="">-- Choose a section --</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?php echo $section['id']; ?>">
                                                <?php echo htmlspecialchars($section['name']); ?>
                                                (<?php echo htmlspecialchars($section['course_code'] ?? 'N/A'); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label for="professorSelect" class="form-label">Select Professor</label>
                                    <select id="professorSelect" class="form-select" required>
                                        <option value="">-- Choose a professor --</option>
                                        <?php foreach ($faculty as $prof): ?>
                                            <option value="<?php echo $prof['id']; ?>">
                                                <?php echo htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']); ?>
                                                (<?php echo htmlspecialchars($prof['username']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" onclick="assignProfessorToSection()">
                                        <i class="bi bi-plus"></i> Assign
                                    </button>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        <h6 class="mb-3">Current Section Assignments</h6>
                        <div id="sectionAssignmentsList" class="row">
                            <div class="col-12">
                                <p class="text-muted">Select a section to view assignments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Assignments Tab -->
            <div class="tab-pane fade" id="team-assignments" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Assign Professors to Teams</h5>
                    </div>
                    <div class="card-body">
                        <p class="alert alert-info mb-3">
                            <i class="bi bi-info-circle"></i> Professors are only assigned to teams for <strong>title_defense and above</strong>.
                            For title_proposal stage, teams work with their section professors.
                        </p>
                        
                        <form id="teamAssignmentForm">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="teamSelect" class="form-label">Select Team</label>
                                    <select id="teamSelect" class="form-select" required>
                                        <option value="">-- Choose a team --</option>
                                        <?php foreach ($teams as $team): ?>
                                            <option value="<?php echo $team['id']; ?>" data-defense="<?php echo htmlspecialchars($team['current_defense_type'] ?? 'title_proposal'); ?>">
                                                <?php echo htmlspecialchars($team['name']); ?> 
                                                (<?php echo htmlspecialchars($team['current_defense_type'] ?? 'title_proposal'); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="teamProfessorSelect" class="form-label">Select Professor</label>
                                    <select id="teamProfessorSelect" class="form-select" required>
                                        <option value="">-- Choose a professor --</option>
                                        <?php foreach ($faculty as $prof): ?>
                                            <option value="<?php echo $prof['id']; ?>">
                                                <?php echo htmlspecialchars($prof['first_name'] . ' ' . $prof['last_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-success w-100" onclick="assignProfessorToTeam()">
                                        <i class="bi bi-plus"></i> Assign to Team
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="assignmentNotes" class="form-label">Notes (Optional)</label>
                                <textarea id="assignmentNotes" class="form-control" rows="2" placeholder="e.g., Specific requirements or instructions"></textarea>
                            </div>
                        </form>

                        <hr class="my-4">

                        <h6 class="mb-3">Recent Team Assignments</h6>
                        <div id="teamAssignmentsList"></div>
                    </div>
                </div>
            </div>

            <!-- Assignment History Tab -->
            <div class="tab-pane fade" id="assignment-history" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Assignment History</h5>
                    </div>
                    <div class="card-body">
                        <div id="assignmentHistoryList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function assignProfessorToSection() {
            const sectionId = document.getElementById('sectionSelect').value;
            const professorId = document.getElementById('professorSelect').value;

            if (!sectionId || !professorId) {
                Swal.fire('Error', 'Please select both a section and a professor', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'assign_professor_to_section');
            formData.append('section_id', sectionId);
            formData.append('professor_id', professorId);

            fetch('../api/professor_assignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Professor assigned to section', 'success');
                    document.getElementById('sectionSelect').value = '';
                    document.getElementById('professorSelect').value = '';
                    loadSectionAssignments(sectionId);
                } else {
                    Swal.fire('Error', data.message || 'Failed to assign professor', 'error');
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'Failed to assign professor', 'error');
            });
        }

        function assignProfessorToTeam() {
            const teamId = document.getElementById('teamSelect').value;
            const professorId = document.getElementById('teamProfessorSelect').value;
            const notes = document.getElementById('assignmentNotes').value;

            if (!teamId || !professorId) {
                Swal.fire('Error', 'Please select both a team and a professor', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'assign_professor_to_team');
            formData.append('team_id', teamId);
            formData.append('professor_id', professorId);
            formData.append('defense_type', 'title_defense');
            formData.append('notes', notes);

            fetch('../api/professor_assignments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', 'Professor assigned to team', 'success');
                    document.getElementById('teamAssignmentForm').reset();
                    loadTeamAssignments();
                } else {
                    Swal.fire('Error', data.message || 'Failed to assign professor', 'error');
                }
            })
            .catch(error => {
                console.error(error);
                Swal.fire('Error', 'Failed to assign professor', 'error');
            });
        }

        function loadSectionAssignments(sectionId) {
            if (!sectionId) return;

            fetch(`../api/professor_assignments.php?action=list_section_professors&section_id=${sectionId}`)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(prof => {
                            html += `
                                <div class="col-md-4 mb-3">
                                    <div class="card assignment-card">
                                        <div class="card-body">
                                            <h6>${prof.first_name} ${prof.last_name}</h6>
                                            <small class="text-muted">${prof.email}</small><br>
                                            <span class="badge ${prof.status}">${prof.status}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        html = '<div class="col-12"><p class="text-muted">No professors assigned to this section</p></div>';
                    }
                    document.getElementById('sectionAssignmentsList').innerHTML = html;
                });
        }

        function loadTeamAssignments() {
            fetch('../api/professor_assignments.php?action=list_team_professors')
                .then(response => response.json())
                .then(data => {
                    // Load recent team assignments
                })
                .catch(error => console.error(error));
        }

        // Event listeners
        document.getElementById('sectionSelect').addEventListener('change', function() {
            loadSectionAssignments(this.value);
        });

        // Initialize
        window.addEventListener('load', function() {
            loadTeamAssignments();
        });
    </script>
</body>
</html>
