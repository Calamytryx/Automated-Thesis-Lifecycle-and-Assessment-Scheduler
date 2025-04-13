<?php
// Include database connection
require '../assets/setup/db.inc.php';

// Assume $_SESSION['id'] holds the evaluator's ID
// session_start(); // Make sure session is started if not already

// --- Configuration ---
// TODO: Determine how to select the correct rubric group.
// Option 1: Pass group_id via POST/GET
// Option 2: Fetch based on defense_schedule_id or team program/defense type
// For now, let's hardcode a group ID for demonstration. Replace with dynamic logic.
$rubric_group_id = 1; // <<< HARDCODED - REPLACE WITH DYNAMIC LOGIC
$evaluator_id = $_SESSION['id'] ?? 0; // Get evaluator ID

// --- Fetch Team & Schedule Data (Simplified) ---
$_POST['team_id'] = 32; // Example team_id
$team_id = $_POST['team_id'] ?? null;

if (!$team_id) {
    echo "Error: Team ID not provided.";
    exit;
}

// Fetch schedule ID (assuming one schedule per team for simplicity)
$scheduleStmt = $pdo->prepare("SELECT id FROM defense_schedules WHERE team_id = ? ORDER BY schedule_date DESC LIMIT 1");
$scheduleStmt->execute([$team_id]);
$schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);
$defense_schedule_id = $schedule['id'] ?? 0;

// Fetch team details, members, adviser, research title (Keep existing logic)
// ... (Keep your existing fetch logic for team, members, adviser, title) ...
try {
  // Fetch team details
  $teamStmt = $pdo->prepare("SELECT name, program FROM icei_38697196_coecsathesis.teams WHERE id = ?");
  $researchTitleStmt = $pdo->prepare("SELECT title FROM icei_38697196_coecsathesis.research_titles WHERE team_id = ?");

  if (isset($team_id)) {
    $teamStmt->execute([$team_id]);
    $researchTitleStmt->execute([$team_id]);
  } else {
    // echo "<script>window.location.href = '../home/index.php;</script>";
    exit;
  }
  $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
  $researchTitle = $researchTitleStmt->fetchColumn();

  if (!$team) {
    echo "<p class='text-danger'>Team not found.</p>";
    exit;
  }

  // Fetch team members excluding the adviser
  $membersStmt = $pdo->prepare("
        SELECT users.id as user_id, CONCAT(users.first_name, ' ', users.last_name) AS fullname, team_members.role
        FROM icei_38697196_coecsathesis.team_members
        JOIN users ON icei_38697196_coecsathesis.team_members.user_id = users.id
        WHERE icei_38697196_coecsathesis.team_members.team_id = ? AND icei_38697196_coecsathesis.team_members.role != 'Adviser'
    ");
  $membersStmt->execute([$team_id]);
  $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);
  $totalMembers = count($members);


  // Fetch adviser information
  $adviserStmt = $pdo->prepare("
        SELECT CONCAT(users.first_name, ' ', users.last_name) AS fullname
        FROM icei_38697196_coecsathesis.team_members
        JOIN users ON icei_38697196_coecsathesis.team_members.user_id = users.id
        WHERE icei_38697196_coecsathesis.team_members.team_id = ? AND icei_38697196_coecsathesis.team_members.role = 'Adviser'
        LIMIT 1
    ");
  $adviserStmt->execute([$team_id]);
  $adviser = $adviserStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "<p class='text-danger'>Error fetching team data: " . htmlspecialchars($e->getMessage()) . "</p>";
  exit;
}


// --- Fetch Rubric Group and its Items ---
$rubricGroup = null;
$rubricItems = [];
$groupWeightTotal = 0; // To check if weights sum to 100

if ($rubric_group_id) {
    try {
        // Fetch group details
        $groupStmt = $pdo->prepare("SELECT * FROM rubric_groups WHERE id = ?");
        $groupStmt->execute([$rubric_group_id]);
        $rubricGroup = $groupStmt->fetch(PDO::FETCH_ASSOC);

        if ($rubricGroup) {
            // Fetch items (rubrics) in the group, ordered, with details
            $itemsStmt = $pdo->prepare("
                SELECT
                    rgi.rubric_id,
                    rgi.order_index,
                    rgi.weight,
                    r.name AS rubric_name,
                    r.rubric_type,
                    r.description AS rubric_description,
                    r.pass_recommendation_text,
                    r.fail_recommendation_text,
                    r.fail_option_text,
                    r.pass_threshold_1,
                    r.pass_threshold_2,
                    r.pass_threshold_3
                FROM rubric_group_items rgi
                JOIN rubrics r ON rgi.rubric_id = r.id
                WHERE rgi.group_id = ? AND r.is_active = 1
                ORDER BY rgi.order_index ASC
            ");
            $itemsStmt->execute([$rubric_group_id]);
            $rubricItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch levels and criteria for each rubric item
            foreach ($rubricItems as $key => $item) {
                // Fetch Levels
                $levelsStmt = $pdo->prepare("SELECT * FROM rubric_levels WHERE rubric_id = ? ORDER BY level_index ASC");
                $levelsStmt->execute([$item['rubric_id']]);
                $rubricItems[$key]['levels'] = $levelsStmt->fetchAll(PDO::FETCH_ASSOC);

                // Fetch Criteria (if applicable)
                if ($item['rubric_type'] === 'numerical' || $item['rubric_type'] === 'yesno') {
                    $criteriaStmt = $pdo->prepare("SELECT * FROM rubric_criteria WHERE rubric_id = ? ORDER BY order_index ASC");
                    $criteriaStmt->execute([$item['rubric_id']]);
                    $rubricItems[$key]['criteria'] = $criteriaStmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $rubricItems[$key]['criteria'] = [];
                }

                // Sum weights for numerical rubrics
                if ($item['rubric_type'] === 'numerical' && $item['weight'] !== null) {
                    $groupWeightTotal += $item['weight'];
                }
            }
        } else {
            echo "<p class='text-danger'>Rubric Group not found.</p>";
            // Handle error appropriately
        }

    } catch (PDOException $e) {
        echo "<p class='text-danger'>Error fetching rubric group data: " . htmlspecialchars($e->getMessage()) . "</p>";
        // Handle error appropriately
    }
} else {
     echo "<p class='text-warning'>No Rubric Group ID specified.</p>";
     // Handle error appropriately
}


// Fetch file_name (Keep existing logic)
// ... (Keep your existing fetch logic for file_name) ...
$requirementStmt = $pdo->prepare("SELECT file_name FROM icei_38697196_coecsathesis.team_requirements WHERE team_id = ? AND requirement_id = 5");
$requirementStmt->execute([$team_id]);
$requirement = $requirementStmt->fetch(PDO::FETCH_ASSOC);
$fileName = $requirement['file_name'] ?? 'default.pdf'; // Provide a default or handle error


define('TITLE', "Defense Evaluation"); // Updated Title
include '../assets/layouts/header.php';

?>

<!-- ... existing PDF.js script ... -->
<input type="hidden" id="filename">
<input type="hidden" id="output-pdf">
<main role="main">
    <!-- ... existing Jumbotron section ... -->
    <section class="jumbotron py-5 mb-4 jbtron">
        <!-- ... keep existing team info display ... -->
    </section>

    <!-- Tab navigation -->
    <div class="container mb-4">
        <ul class="nav nav-tabs" id="defenseContentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="research-paper-tab" data-bs-toggle="tab" data-bs-target="#research-paper" type="button" role="tab" aria-controls="research-paper" aria-selected="true">
                    Research Paper
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="score-sheet-tab" data-bs-toggle="tab" data-bs-target="#score-sheet-content" type="button" role="tab" aria-controls="score-sheet-content" aria-selected="false">
                    Score Sheet <?php echo $rubricGroup ? '- ' . htmlspecialchars($rubricGroup['name']) : ''; ?>
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab content -->
    <div class="tab-content" id="defenseContentTabsContent">
        <!-- Research Paper Tab -->
        <div class="tab-pane fade show active" id="research-paper" role="tabpanel" aria-labelledby="research-paper-tab">
            <!-- ... keep existing PDF viewer and AI section ... -->
        </div>

        <!-- Score Sheet Tab (Dynamically Generated) -->
        <div class="tab-pane fade" id="score-sheet-content" role="tabpanel" aria-labelledby="score-sheet-tab">
            <div class="album">
                <div class="container">
                    <?php if (!empty($rubricItems)): ?>
                        <!-- Toggle Buttons for Rubrics + Summary + Evaluation -->
                        <div class="toggle-container mb-3">
                            <div class="btn-group w-100 flex-wrap" role="group" aria-label="Score sheet toggles">
                                <?php foreach ($rubricItems as $index => $item): ?>
                                    <button type="button" class="btn toggle-btn <?php echo $index === 0 ? 'active' : ''; ?>" data-target="rubric-section-<?php echo $item['rubric_id']; ?>">
                                        <i class="fas fa-list-alt me-2"></i><?php echo htmlspecialchars($item['rubric_name']); ?>
                                        <?php if ($item['rubric_type'] === 'numerical' && $item['weight'] !== null): ?>
                                            <span class="badge bg-secondary ms-1"><?php echo htmlspecialchars($item['weight']); ?>%</span>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                                <button type="button" class="btn toggle-btn" data-target="grade-summary">
                                    <i class="fas fa-chart-bar me-2"></i>Summary
                                </button>
                                <button type="button" class="btn toggle-btn" data-target="evaluation">
                                    <i class="fas fa-clipboard-check me-2"></i>Evaluation
                                </button>
                            </div>
                        </div>

                        <!-- Rubric Sections -->
                        <?php foreach ($rubricItems as $index => $item): ?>
                            <div class="section-toggle <?php echo $index === 0 ? '' : 'd-none'; ?>" id="rubric-section-<?php echo $item['rubric_id']; ?>" data-rubric-id="<?php echo $item['rubric_id']; ?>" data-rubric-type="<?php echo $item['rubric_type']; ?>" data-rubric-weight="<?php echo $item['weight'] ?? ''; ?>">
                                <div class="evaluation-table mb-4">
                                    <div class="evaluation-header">
                                        <h4><?php echo htmlspecialchars($item['rubric_name']); ?>
                                            <?php if ($item['rubric_type'] === 'numerical' && $item['weight'] !== null): ?>
                                                <span class="percentage">(<?php echo htmlspecialchars($item['weight']); ?>%)</span>
                                            <?php endif; ?>
                                        </h4>
                                        <?php if ($item['rubric_description']): ?>
                                            <p class="text-muted small"><?php echo htmlspecialchars($item['rubric_description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="evaluation-content">
                                        <?php if ($item['rubric_type'] === 'numerical'): ?>
                                            <table class="table table-bordered table-hover numerical-rubric-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 30%;">Criteria</th>
                                                        <?php foreach ($item['levels'] as $level): ?>
                                                            <th class="text-center">
                                                                <?php echo htmlspecialchars($level['name']); ?><br>
                                                                <small class="text-muted">
                                                                    <?php
                                                                    if ($level['is_range'] && $level['points_min'] != $level['points_max']) {
                                                                        echo "({$level['points_min']}-{$level['points_max']} pts)";
                                                                    } else {
                                                                        echo "({$level['points_min']} pts)";
                                                                    }
                                                                    ?>
                                                                </small>
                                                                <?php if ($level['description']): ?>
                                                                     <br><small class="text-muted fst-italic">(<?php echo htmlspecialchars($level['description']); ?>)</small>
                                                                <?php endif; ?>
                                                            </th>
                                                        <?php endforeach; ?>
                                                        <th style="width: 10%;" class="text-center">Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($item['criteria'] as $crit_index => $criterion): ?>
                                                        <tr data-criterion-id="<?php echo $criterion['id']; ?>">
                                                            <td><?php echo htmlspecialchars($criterion['criterion_text']); ?></td>
                                                            <?php foreach ($item['levels'] as $level_index => $level): ?>
                                                                <td class="text-center">
                                                                    <input type="radio"
                                                                           class="form-check-input criterion-level-radio"
                                                                           name="rubric_<?php echo $item['rubric_id']; ?>_crit_<?php echo $criterion['id']; ?>"
                                                                           value="<?php echo $level['id']; ?>"
                                                                           data-points-min="<?php echo $level['points_min']; ?>"
                                                                           data-points-max="<?php echo $level['points_max']; ?>"
                                                                           data-is-range="<?php echo $level['is_range']; ?>"
                                                                           required>
                                                                </td>
                                                            <?php endforeach; ?>
                                                            <td class="text-center">
                                                                <span class="criterion-score-display">0</span>
                                                                <!-- Hidden input to store the calculated score for this criterion -->
                                                                <input type="hidden" class="criterion-score-input" name="scores[<?php echo $item['rubric_id']; ?>][<?php echo $criterion['id']; ?>]" value="0">
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="<?php echo count($item['levels']) + 1; ?>" class="text-end">Subtotal</th>
                                                        <th class="text-center rubric-subtotal">0</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        <?php elseif ($item['rubric_type'] === 'yesno'): ?>
                                             <table class="table table-bordered table-hover yesno-rubric-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40%;">Criteria</th>
                                                        <th style="width: 40%;">Description</th>
                                                        <th style="width: 20%;" class="text-center">Option</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($item['criteria'] as $criterion): ?>
                                                        <tr data-criterion-id="<?php echo $criterion['id']; ?>">
                                                            <td><?php echo htmlspecialchars($criterion['criterion_text']); ?></td>
                                                            <td><?php echo htmlspecialchars($criterion['criterion_detail'] ?? ''); ?></td>
                                                            <td class="text-center">
                                                                <select class="form-select yesno-option" name="scores[<?php echo $item['rubric_id']; ?>][<?php echo $criterion['id']; ?>]" required>
                                                                    <option value="Yes">Yes</option>
                                                                    <option value="No" selected>No</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php elseif ($item['rubric_type'] === 'passfail'): ?>
                                            <table class="table table-bordered table-hover passfail-rubric-table">
                                                 <thead>
                                                    <tr>
                                                        <th style="width: 40%;">Recommendation</th>
                                                        <th style="width: 60%;">Options</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Row 1: Pass Options -->
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['pass_recommendation_text'] ?? 'Pass Recommendation'); ?></td>
                                                        <td>
                                                            <?php foreach ($item['levels'] as $level): ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input passfail-radio" type="radio"
                                                                           name="scores[<?php echo $item['rubric_id']; ?>][passfail]"
                                                                           id="rubric_<?php echo $item['rubric_id']; ?>_level_<?php echo $level['id']; ?>"
                                                                           value="pass_<?php echo $level['level_index']; ?>" required>
                                                                    <label class="form-check-label" for="rubric_<?php echo $item['rubric_id']; ?>_level_<?php echo $level['id']; ?>">
                                                                        <?php echo htmlspecialchars($level['description'] ?? "Pass Option {$level['level_index']}"); ?>
                                                                    </label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </td>
                                                    </tr>
                                                     <!-- Row 2: Fail Option -->
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['fail_recommendation_text'] ?? 'Fail Recommendation'); ?></td>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input passfail-radio" type="radio"
                                                                       name="scores[<?php echo $item['rubric_id']; ?>][passfail]"
                                                                       id="rubric_<?php echo $item['rubric_id']; ?>_fail"
                                                                       value="fail" required>
                                                                <label class="form-check-label" for="rubric_<?php echo $item['rubric_id']; ?>_fail">
                                                                    <?php echo htmlspecialchars($item['fail_option_text'] ?? 'Fail'); ?>
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <!-- Pass/Fail Thresholds (Hidden, for JS reference if needed) -->
                                            <input type="hidden" class="pass-threshold-1" value="<?php echo $item['pass_threshold_1']; ?>">
                                            <input type="hidden" class="pass-threshold-2" value="<?php echo $item['pass_threshold_2']; ?>">
                                            <input type="hidden" class="pass-threshold-3" value="<?php echo $item['pass_threshold_3']; ?>">
                                        <?php else: ?>
                                            <p>Unsupported rubric type: <?php echo htmlspecialchars($item['rubric_type']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Grade Summary Section -->
                        <div class="section-toggle d-none" id="grade-summary">
                            <div class="evaluation-table mb-4">
                                <div class="evaluation-header">
                                    <h4>Grade Summary</h4>
                                     <?php if (abs($groupWeightTotal - 100) > 0.01 && $groupWeightTotal > 0): ?>
                                        <div class="alert alert-warning small">Note: Weights for numerical rubrics sum to <?php echo number_format($groupWeightTotal, 2); ?>%, not 100%. Final score will be calculated based on these weights.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="evaluation-content">
                                    <table class="table table-bordered table-hover" id="summary-table">
                                        <thead>
                                            <tr>
                                                <th>Rubric</th>
                                                <th>Type</th>
                                                <th>Weight</th>
                                                <th>Score / Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rubricItems as $item): ?>
                                                <tr data-summary-rubric-id="<?php echo $item['rubric_id']; ?>">
                                                    <td><?php echo htmlspecialchars($item['rubric_name']); ?></td>
                                                    <td><?php echo htmlspecialchars(ucfirst($item['rubric_type'])); ?></td>
                                                    <td><?php echo ($item['rubric_type'] === 'numerical' && $item['weight'] !== null) ? htmlspecialchars($item['weight']) . '%' : 'N/A'; ?></td>
                                                    <td class="summary-score">Pending...</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                         <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Final Weighted Score (Numerical Only)</th>
                                                <th id="finalWeightedScore">0.00%</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                             <!-- Individual Grades Section (Optional - Add if needed) -->
                             <!-- You might need a separate mechanism or rubric type for individual scores -->
                        </div>

                        <!-- Evaluation Section -->
                        <div class="section-toggle d-none" id="evaluation">
                            <div class="evaluation-table">
                                <div class="evaluation-header">
                                    <h4>Comments and Final Evaluation</h4>
                                </div>
                                <div class="evaluation-content p-4">
                                    <form id="evaluationForm" action="submit_evaluation.php" method="POST" class="evaluation-form">
                                        <div class="form-group mb-4">
                                            <label for="comments" class="form-label">Comments / Recommendations</label>
                                            <textarea name="comments" id="comments" class="form-control" rows="5" placeholder="Enter your comments, evaluation and recommendations here" required></textarea>
                                        </div>

                                        <!-- Hidden inputs -->
                                        <input type="hidden" name="defense_schedule_id" value="<?php echo $defense_schedule_id; ?>">
                                        <input type="hidden" name="evaluator_id" value="<?php echo $evaluator_id; ?>">
                                        <input type="hidden" name="rubric_group_id" value="<?php echo $rubric_group_id; ?>">
                                        <!-- Scores will be collected via JS and added dynamically or as a JSON string -->
                                        <input type="hidden" name="evaluation_data" id="evaluationDataInput">


                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <button type="submit" class="btn btn-primary">Submit Evaluation</button>
                                            <!-- TODO: Add logic for panelist completion count -->
                                            <!-- <small class="text-muted">X/3 Panelist Complete</small> -->
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-warning">No rubrics found for the selected group or the group was not specified correctly.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// filepath: c:\xampp\htdocs\atlas\decision-support\index.php

document.addEventListener('DOMContentLoaded', () => {
    const scoreSheetContent = document.getElementById('score-sheet-content');
    if (!scoreSheetContent) return; // Exit if score sheet content not found

    // --- Toggle Button Logic ---
    const toggleBtns = document.querySelectorAll('#score-sheet-content .toggle-btn');
    const sections = document.querySelectorAll('#score-sheet-content .section-toggle');

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            sections.forEach(section => {
                section.classList.add('d-none');
                section.classList.remove('d-block'); // Use d-block or similar if needed
            });

            const targetSection = document.getElementById(this.dataset.target);
            if (targetSection) {
                targetSection.classList.remove('d-none');
                targetSection.classList.add('d-block'); // Use d-block or similar if needed
            }
            // Recalculate summary when switching views
            calculateAndUpdateSummary();
        });
    });

     // Show first rubric when score sheet tab is clicked initially
    const scoreSheetTab = document.getElementById('score-sheet-tab');
    if (scoreSheetTab) {
        scoreSheetTab.addEventListener('shown.bs.tab', function() { // Use shown.bs.tab
            const firstToggleBtn = document.querySelector('#score-sheet-content .toggle-btn');
            if (firstToggleBtn) {
                firstToggleBtn.click();
            }
            calculateAndUpdateSummary(); // Initial calculation
        });
    }

    // --- Numerical Rubric Calculation ---
    const numericalTables = scoreSheetContent.querySelectorAll('.numerical-rubric-table');
    numericalTables.forEach(table => {
        const radios = table.querySelectorAll('.criterion-level-radio');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    const row = this.closest('tr');
                    const scoreDisplay = row.querySelector('.criterion-score-display');
                    const scoreInput = row.querySelector('.criterion-score-input');
                    let points = 0;
                    // Use max points if range, min points otherwise (as per previous logic)
                    if (this.dataset.isRange == 1) {
                        points = parseInt(this.dataset.pointsMax) || 0;
                    } else {
                        points = parseInt(this.dataset.pointsMin) || 0;
                    }
                    scoreDisplay.textContent = points;
                    scoreInput.value = points; // Store score in hidden input
                    updateRubricSubtotal(table);
                    calculateAndUpdateSummary(); // Update overall summary
                }
            });
        });
    });

    function updateRubricSubtotal(table) {
        let subtotal = 0;
        table.querySelectorAll('.criterion-score-input').forEach(input => {
            subtotal += parseInt(input.value) || 0;
        });
        const subtotalCell = table.querySelector('.rubric-subtotal');
        if (subtotalCell) {
            subtotalCell.textContent = subtotal;
        }
    }

    // --- Yes/No and Pass/Fail Handling (Trigger Summary Update) ---
     const otherSelects = scoreSheetContent.querySelectorAll('.yesno-option, .passfail-radio');
     otherSelects.forEach(input => {
         input.addEventListener('change', calculateAndUpdateSummary);
     });


    // --- Summary Calculation ---
    function calculateAndUpdateSummary() {
        console.log("Calculating summary...");
        let finalWeightedScore = 0;
        let totalWeightApplied = 0;

        scoreSheetContent.querySelectorAll('.section-toggle[data-rubric-id]').forEach(section => {
            const rubricId = section.dataset.rubricId;
            const rubricType = section.dataset.rubricType;
            const weight = parseFloat(section.dataset.rubricWeight) || 0;
            const summaryRow = document.querySelector(`#summary-table tr[data-summary-rubric-id="${rubricId}"]`);
            const summaryScoreCell = summaryRow ? summaryRow.querySelector('.summary-score') : null;

            if (!summaryScoreCell) return;

            if (rubricType === 'numerical') {
                let maxPossibleScore = 0;
                let currentScore = 0;
                section.querySelectorAll('.numerical-rubric-table tbody tr').forEach(row => {
                    const selectedRadio = row.querySelector('.criterion-level-radio:checked');
                    let criterionMax = 0;
                    row.querySelectorAll('.criterion-level-radio').forEach(radio => {
                         criterionMax = Math.max(criterionMax, parseInt(radio.dataset.pointsMax) || 0);
                    });
                    maxPossibleScore += criterionMax;
                    if (selectedRadio) {
                        // Use max points if range, min otherwise
                         let points = (selectedRadio.dataset.isRange == 1)
                                    ? (parseInt(selectedRadio.dataset.pointsMax) || 0)
                                    : (parseInt(selectedRadio.dataset.pointsMin) || 0);
                        currentScore += points;
                    }
                });

                const percentageScore = (maxPossibleScore > 0) ? (currentScore / maxPossibleScore) * 100 : 0;
                summaryScoreCell.textContent = `${currentScore} / ${maxPossibleScore} (${percentageScore.toFixed(2)}%)`;

                if (weight > 0) {
                    finalWeightedScore += (percentageScore / 100) * weight;
                    totalWeightApplied += weight; // Track the total weight used in calculation
                }

            } else if (rubricType === 'yesno') {
                const selects = section.querySelectorAll('.yesno-option');
                const totalCriteria = selects.length;
                let yesCount = 0;
                selects.forEach(select => {
                    if (select.value === 'Yes') {
                        yesCount++;
                    }
                });
                 summaryScoreCell.textContent = `${yesCount} Yes / ${totalCriteria - yesCount} No`;

            } else if (rubricType === 'passfail') {
                 const selectedRadio = section.querySelector('.passfail-radio:checked');
                 if (selectedRadio) {
                     const selectedValue = selectedRadio.value; // e.g., "pass_1", "fail"
                     const label = section.querySelector(`label[for="${selectedRadio.id}"]`);
                     summaryScoreCell.textContent = label ? label.textContent.trim() : selectedValue;
                 } else {
                     summaryScoreCell.textContent = 'Pending...';
                 }
            } else {
                 summaryScoreCell.textContent = 'N/A';
            }
        });

         // Display final weighted score
         // Normalize if total weight applied is not 100 but greater than 0
         let displayScore = finalWeightedScore;
         // if (totalWeightApplied > 0 && Math.abs(totalWeightApplied - 100) > 0.01) {
         //     // Optional: Normalize score as if weights did sum to 100
         //     // displayScore = (finalWeightedScore / totalWeightApplied) * 100;
         //     // Or just display the calculated score based on actual weights
         // }

        const finalScoreCell = document.getElementById('finalWeightedScore');
        if (finalScoreCell) {
            finalScoreCell.textContent = `${displayScore.toFixed(2)}%`;
        }
    }

    // --- Form Submission ---
    const evaluationForm = document.getElementById('evaluationForm');
    if (evaluationForm) {
        evaluationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Basic validation: Check comments
            const comments = document.getElementById('comments');
            if (!comments.value.trim()) {
                 showToast('Error', 'Please provide comments/recommendations.', 'error');
                 comments.focus();
                 // Switch to evaluation tab if not active
                 const evalToggle = document.querySelector('.toggle-btn[data-target="evaluation"]');
                 if (evalToggle && !evalToggle.classList.contains('active')) {
                     evalToggle.click();
                 }
                 return;
            }

            // Check if all required inputs/radios/selects have values
            let allInputsValid = true;
            let firstInvalidElement = null;
            scoreSheetContent.querySelectorAll('input[required], select[required]').forEach(input => {
                if (input.type === 'radio') {
                    const groupName = input.name;
                    if (!evaluationForm.querySelector(`input[name="${groupName}"]:checked`)) {
                        allInputsValid = false;
                        if (!firstInvalidElement) firstInvalidElement = input.closest('.section-toggle');
                    }
                } else if (!input.value) {
                    allInputsValid = false;
                     if (!firstInvalidElement) firstInvalidElement = input.closest('.section-toggle');
                }
            });

            if (!allInputsValid) {
                 showToast('Error', 'Please complete all rubric scoring sections.', 'error');
                 // Switch to the tab containing the first invalid element
                 if (firstInvalidElement) {
                     const targetId = firstInvalidElement.id;
                     const invalidToggle = document.querySelector(`.toggle-btn[data-target="${targetId}"]`);
                     if (invalidToggle && !invalidToggle.classList.contains('active')) {
                         invalidToggle.click();
                         // Try focusing the first invalid input within that section
                         const firstInput = firstInvalidElement.querySelector('input[required], select[required]');
                         if(firstInput) firstInput.focus();
                     }
                 }
                 return;
            }


            // Collect evaluation data
            const evaluationData = {};
            scoreSheetContent.querySelectorAll('.section-toggle[data-rubric-id]').forEach(section => {
                const rubricId = section.dataset.rubricId;
                const rubricType = section.dataset.rubricType;
                evaluationData[rubricId] = { type: rubricType, scores: {} };

                if (rubricType === 'numerical') {
                    section.querySelectorAll('.criterion-score-input').forEach(input => {
                        const criterionId = input.closest('tr').dataset.criterionId;
                        evaluationData[rubricId].scores[criterionId] = input.value;
                    });
                } else if (rubricType === 'yesno') {
                     section.querySelectorAll('.yesno-option').forEach(select => {
                        const criterionId = select.closest('tr').dataset.criterionId;
                        evaluationData[rubricId].scores[criterionId] = select.value;
                    });
                } else if (rubricType === 'passfail') {
                    const selectedRadio = section.querySelector('.passfail-radio:checked');
                    if (selectedRadio) {
                         evaluationData[rubricId].scores['passfail'] = selectedRadio.value;
                    }
                }
            });

            // Add collected data to the hidden input
            document.getElementById('evaluationDataInput').value = JSON.stringify(evaluationData);

            // Submit the form via AJAX
            const formData = new FormData(evaluationForm);

            console.log("Submitting evaluation data:", Object.fromEntries(formData));
            // Log the detailed evaluation data separately
            console.log("Detailed Scores JSON:", document.getElementById('evaluationDataInput').value);


            fetch(evaluationForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect or refresh as needed
                            // window.location.href = '../home'; // Example redirect
                             window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to submit evaluation.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Submission Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred during submission.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        });
    }

    // Initial calculation on load (if the tab might be active initially)
    if (scoreSheetTab && scoreSheetTab.classList.contains('active')) {
         calculateAndUpdateSummary();
    }

});

// --- PDF Fullscreen Logic (Keep existing) ---
function toggleFullScreen() {
    // ... keep existing fullscreen logic ...
}

// --- AI Module Logic (Keep existing) ---
// ... keep existing AI module scripts and calls ...

</script>

<!-- AI GEMINI MODULE -->
<!-- ... keep existing AI script includes ... -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
include '../assets/layouts/footer.php'
?>