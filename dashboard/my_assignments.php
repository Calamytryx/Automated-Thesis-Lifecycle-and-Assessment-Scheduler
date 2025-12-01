<?php
session_start();
require_once '../assets/setup/db.inc.php';

// Check authorization - faculty only
if (!isset($_SESSION['id']) || $_SESSION['usertype'] != 2) {
    header('Location: ../login');
    exit;
}

$userId = $_SESSION['id'];
$userName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Get pending assignments
$pendingStmt = $pdo->prepare("
    SELECT 
        tpa.id,
        tpa.team_id,
        tpa.defense_type,
        tpa.assignment_status,
        tpa.created_at,
        tpa.assignment_notes,
        t.name AS team_name,
        t.program,
        GROUP_CONCAT(CONCAT(tm.first_name, ' ', tm.last_name) SEPARATOR ', ') as team_members
    FROM team_professor_assignments tpa
    JOIN teams t ON tpa.team_id = t.id
    LEFT JOIN team_members tm ON t.id = tm.team_id AND tm.user_id IN (SELECT user_id FROM team_members WHERE team_id = t.id LIMIT 3)
    WHERE tpa.professor_id = ? AND tpa.assignment_status = 'pending'
    GROUP BY tpa.id
    ORDER BY tpa.created_at DESC
");
$pendingStmt->execute([$userId]);
$pendingAssignments = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

// Get accepted assignments
$acceptedStmt = $pdo->prepare("
    SELECT 
        tpa.id,
        tpa.team_id,
        tpa.defense_type,
        tpa.assignment_status,
        tpa.accepted_at,
        t.name AS team_name,
        t.program,
        GROUP_CONCAT(CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') as team_members
    FROM team_professor_assignments tpa
    JOIN teams t ON tpa.team_id = t.id
    LEFT JOIN team_members tm ON t.id = tm.team_id
    LEFT JOIN users u ON tm.user_id = u.id
    WHERE tpa.professor_id = ? AND tpa.assignment_status = 'accepted'
    GROUP BY tpa.id
    ORDER BY tpa.accepted_at DESC
");
$acceptedStmt->execute([$userId]);
$acceptedAssignments = $acceptedStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor Assignments</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .container {
            max-width: 900px;
        }
        .assignment-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }
        .assignment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        .pending-badge {
            background-color: #fff3cd;
            color: #856404;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .accepted-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .team-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .defense-type-badge {
            display: inline-block;
            padding: 5px 10px;
            background-color: #e7f3ff;
            color: #0066cc;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 10px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        .btn-action {
            border-radius: 6px;
            font-weight: 500;
        }
        .header-welcome {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .alert-custom {
            background-color: rgba(255,255,255,0.95);
            border: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header-welcome">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-person-badge"></i> My Assignments</h1>
                    <p class="text-muted mb-0">Welcome, <?php echo htmlspecialchars($userName); ?></p>
                </div>
                <a href="../home" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-house"></i> Home
                </a>
            </div>
        </div>

        <!-- Pending Assignments Section -->
        <div class="mb-4">
            <h3 class="text-white mb-3">
                <i class="bi bi-hourglass-split"></i> Pending Assignments
                <?php if (count($pendingAssignments) > 0): ?>
                    <span class="badge bg-warning text-dark"><?php echo count($pendingAssignments); ?></span>
                <?php endif; ?>
            </h3>

            <?php if (count($pendingAssignments) > 0): ?>
                <div class="row">
                    <?php foreach ($pendingAssignments as $assignment): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card assignment-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <?php echo htmlspecialchars($assignment['team_name']); ?>
                                            </h5>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($assignment['program']); ?>
                                            </small>
                                        </div>
                                        <span class="pending-badge">Pending</span>
                                    </div>

                                    <div class="team-info">
                                        <strong>Defense Type:</strong>
                                        <span class="defense-type-badge">
                                            <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($assignment['defense_type']))); ?>
                                        </span>
                                    </div>

                                    <?php if ($assignment['assignment_notes']): ?>
                                        <div class="alert alert-info alert-custom mb-3">
                                            <i class="bi bi-info-circle"></i>
                                            <strong>Notes:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($assignment['assignment_notes'])); ?>
                                        </div>
                                    <?php endif; ?>

                                    <small class="text-muted d-block mb-3">
                                        <i class="bi bi-calendar"></i>
                                        Assigned: <?php echo date('F d, Y \a\t H:i', strtotime($assignment['created_at'])); ?>
                                    </small>

                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-success btn-action flex-grow-1" onclick="acceptAssignment(<?php echo $assignment['id']; ?>, '<?php echo htmlspecialchars($assignment['team_name']); ?>')">
                                            <i class="bi bi-check-circle"></i> Accept
                                        </button>
                                        <button type="button" class="btn btn-danger btn-action flex-grow-1" onclick="rejectAssignment(<?php echo $assignment['id']; ?>)">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card assignment-card">
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>No Pending Assignments</h5>
                        <p class="text-muted">You have no pending team assignments at the moment.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Accepted Assignments Section -->
        <div class="mb-4">
            <h3 class="text-white mb-3">
                <i class="bi bi-check-circle"></i> My Accepted Assignments
                <?php if (count($acceptedAssignments) > 0): ?>
                    <span class="badge bg-success"><?php echo count($acceptedAssignments); ?></span>
                <?php endif; ?>
            </h3>

            <?php if (count($acceptedAssignments) > 0): ?>
                <div class="row">
                    <?php foreach ($acceptedAssignments as $assignment): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card assignment-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">
                                                <?php echo htmlspecialchars($assignment['team_name']); ?>
                                            </h5>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($assignment['program']); ?>
                                            </small>
                                        </div>
                                        <span class="accepted-badge">Accepted</span>
                                    </div>

                                    <div class="team-info">
                                        <strong>Defense Type:</strong>
                                        <span class="defense-type-badge">
                                            <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($assignment['defense_type']))); ?>
                                        </span>
                                    </div>

                                    <small class="text-muted d-block">
                                        <i class="bi bi-calendar-check"></i>
                                        Accepted: <?php echo date('F d, Y \a\t H:i', strtotime($assignment['accepted_at'])); ?>
                                    </small>

                                    <div class="mt-3">
                                        <a href="../decision-support/index.php?team_id=<?php echo $assignment['team_id']; ?>" class="btn btn-info btn-sm btn-action">
                                            <i class="bi bi-eye"></i> View Team
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card assignment-card">
                    <div class="empty-state">
                        <i class="bi bi-clipboard-check"></i>
                        <h5>No Accepted Assignments</h5>
                        <p class="text-muted">Accept pending assignments to see them here.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function acceptAssignment(assignmentId, teamName) {
            Swal.fire({
                title: 'Accept Assignment?',
                html: `You are accepting the assignment for <strong>${teamName}</strong>.<br><br>This will notify the team and admin of your acceptance.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Accept',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745'
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'accept_assignment');
                    formData.append('assignment_id', assignmentId);

                    fetch('../api/professor_assignments.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Accepted!',
                                text: 'Assignment accepted successfully.',
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Failed to accept assignment', 'error');
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        Swal.fire('Error', 'Failed to accept assignment', 'error');
                    });
                }
            });
        }

        function rejectAssignment(assignmentId) {
            Swal.fire({
                title: 'Reject Assignment?',
                html: 'Please provide a reason for rejecting this assignment:',
                input: 'textarea',
                inputLabel: 'Reason for rejection',
                inputPlaceholder: 'Enter your reason here...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                inputValidator: (value) => {
                    if (!value || value.trim() === '') {
                        return 'Please provide a reason'
                    }
                }
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'reject_assignment');
                    formData.append('assignment_id', assignmentId);
                    formData.append('reason', result.value);

                    fetch('../api/professor_assignments.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Rejected',
                                text: 'Assignment rejected. Admin will be notified.',
                                icon: 'info'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message || 'Failed to reject assignment', 'error');
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        Swal.fire('Error', 'Failed to reject assignment', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>
