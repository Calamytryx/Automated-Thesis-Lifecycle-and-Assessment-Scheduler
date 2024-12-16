<?php
// Include database connection
require '../assets/setup/db.inc.php';

// Retrieve team_id from POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['team_id'])) {
    $team_id = intval($_POST['team_id']);
  }
}

$scheduleStmt = $pdo->prepare("SELECT id FROM coecsa_thesis.defense_schedules WHERE team_id = ?");
$scheduleStmt->execute([$team_id]);
$schedule = $scheduleStmt->fetch(PDO::FETCH_ASSOC);

// Fetch file_name from team_requirements where team_id = $team_id and requirement_id = 5
$requirementStmt = $pdo->prepare("SELECT file_name FROM coecsa_thesis.team_requirements WHERE team_id = ? AND requirement_id = 5");
$requirementStmt->execute([$team_id]);
$requirement = $requirementStmt->fetch(PDO::FETCH_ASSOC);

if ($requirement) {
  $fileName = htmlspecialchars($requirement['file_name']);
  // You can use $fileName as needed, for example:
  // echo "<p>File Name: {$fileName}</p>";
} else {
  echo "<p class='text-danger'>Requirement not found.</p>" . $team_id;
  exit;
}

try {
  // Fetch team details
  $teamStmt = $pdo->prepare("SELECT name, course FROM coecsa_thesis.teams WHERE id = ?");
  $researchTitleStmt = $pdo->prepare("SELECT title FROM coecsa_thesis.research_titles WHERE team_id = ?");

  if (isset($team_id)) {
    $teamStmt->execute([$team_id]);
    $researchTitleStmt->execute([$team_id]);
  } else {
    echo "<script>window.location.href = '../home/index.php;</script>";
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
        SELECT CONCAT(users.first_name, ' ', users.last_name) AS fullname, team_members.role, user_id
        FROM coecsa_thesis.team_members 
        JOIN users ON coecsa_thesis.team_members.user_id = users.id 
        WHERE coecsa_thesis.team_members.team_id = ? AND coecsa_thesis.team_members.role != 'Adviser'
    ");
  $membersStmt->execute([$team_id]);
  $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

  // Get the count of team members excluding the adviser
  $membersCountStmt = $pdo->prepare("
    SELECT COUNT(*) as total_members
    FROM coecsa_thesis.team_members
    WHERE team_id = ? AND role != 'Adviser'
");
  $membersCountStmt->execute([$team_id]);
  $totalMembers = $membersCountStmt->fetchColumn();

  // Fetch adviser information
  $adviserStmt = $pdo->prepare("
        SELECT CONCAT(users.first_name, ' ', users.last_name) AS fullname
        FROM coecsa_thesis.team_members 
        JOIN users ON coecsa_thesis.team_members.user_id = users.id 
        WHERE coecsa_thesis.team_members.team_id = ? AND coecsa_thesis.team_members.role = 'Adviser'
        LIMIT 1
    ");
  $adviserStmt->execute([$team_id]);
  $adviser = $adviserStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "<p class='text-danger'>Error fetching team data: " . htmlspecialchars($e->getMessage()) . "</p>";
  exit;
}

define('TITLE', "Defense");
include '../assets/layouts/header.php';

?>

<script type="module">
  import {
    getDocument,
    GlobalWorkerOptions
  } from 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.7.76/pdf.min.mjs';

  // Specify the worker script source
  GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.7.76/pdf.worker.min.mjs';

  const predefinedPdfUrl = `../assets/uploads/submission/<?php echo $fileName ?>`; // Replace with your PDF URL

  window.extractText = async function(pdfUrl) {
    const filenameInput = document.getElementById('filename');
    const output = document.getElementById('output-pdf');

    try {
      const response = await fetch(pdfUrl);
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      const arrayBuffer = await response.arrayBuffer();
      const pdfData = new Uint8Array(arrayBuffer);

      // Extract filename from URL
      const filename = pdfUrl.split('/').pop();
      filenameInput.value = `File: ${filename}`;

      const pdf = await getDocument(pdfData).promise;
      let extractedText = '';

      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const textContent = await page.getTextContent();

        let pageText = `--- Page ${pageNum} ---\n`;
        let lastY = null;

        textContent.items.forEach(item => {
          const currentY = item.transform[5];

          if (lastY !== null && Math.abs(currentY - lastY) > 5) {
            pageText += '\n';
          }

          pageText += item.str;
          lastY = currentY;
        });

        extractedText += pageText + '\n\n';
      }

      output.value = extractedText.trim();
    } catch (error) {
      alert('Failed to load PDF file.');
      console.error(error);
    }
  }

  // Load the PDF on page load
  window.addEventListener('DOMContentLoaded', () => {
    extractText(predefinedPdfUrl);
  });
</script>
<input type="hidden" id="filename">
<input type="hidden" id="output-pdf">
<main role="main">
  <section class="jumbotron text-center py-5">
    <div class="container">
        <h1 class="jumbotron-heading mb-4 fw-bold"><?php echo htmlspecialchars($researchTitle); ?></h1>
        <div class="row mb-3">
            <div class="col">
                <strong>Members:</strong>
                <div class="d-flex flex-wrap justify-content-center">
                    <?php
                    if (!empty($members)) {
                        foreach ($members as $member) {
                            echo '<span class="badge bg-primary me-2 mb-2">' . htmlspecialchars($member['fullname']) . '</span>';
                        }
                    } else {
                        echo "No members found.<br>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <hr class="my-3">
        <div class="row">
            <div class="col">
                <strong>Adviser:</strong> <?php echo htmlspecialchars($adviser['fullname'] ?? 'No adviser assigned'); ?>
            </div>
            <div class="col">
                <strong>Course:</strong> <?php echo htmlspecialchars($team['course']); ?>
            </div>
        </div>
    </div>
</section> 

  <div class="album py-5">
    <div class="container">

      <div class="row">
        <div class="container">
          <div class="card mb box-shadow">
            <div class="card-body">
              <p class="card-text">PDF VIEW</p>
              <button class="btn btn-primary mt-2" onclick="toggleFullScreen()">Full Screen</button>
              <div class="d-flex justify-content-between align-items-center">
                <iframe id="pdf" src="../assets/uploads/submission/viewer.html?file=<?php echo $fileName; ?> " frameborder="0" style="width: 100%; height: 100%;" allowfullscreen></iframe>
                <script>
                  function toggleFullScreen() {
                    var iframe = document.getElementById('pdf');
                    if (iframe.requestFullscreen) {
                      iframe.requestFullscreen();
                    } else if (iframe.mozRequestFullScreen) {
                      /* Firefox */
                      iframe.mozRequestFullScreen();
                    } else if (iframe.webkitRequestFullscreen) {
                      /* Chrome, Safari & Opera */
                      iframe.webkitRequestFullscreen();
                    } else if (iframe.msRequestFullscreen) {
                      /* Edge */
                      iframe.msRequestFullscreen();
                    }
                  }
                </script>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container border-0">
      <!-- table 1 -->
      <div class="card mb box-shadow">
        <div class="card-body">
          <h4>Content (40%)</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="content-table">
              <thead>
                <tr>
                  <th class="text-center">Evaluation Area</th>
                  <th class="text-center">Unacceptable (1-3)</th>
                  <th class="text-center">Fairly Acceptable (3)</th> 
                  <th class="text-center">Acceptable (4)</th>
                  <th class="text-center">Highly Acceptable (5)</th>
                  <th class="text-center">Rating/Score</th>
                </tr> 
              </thead>
              <tbody>
                <tr>
                  <td><b>1. Thesis Statement / Researh Objective(s)</b></td>
                  <td>Thesis is unclear or poorly defined. The research objectives are not clearly stated or justified.</td>
                  <td>Thesis and objectives are vaguely stated but lack adequate justification or clarity.</td>
                  <td>Thesis clearly stated with a reasonable justification for the research objectives.</td>
                  <td>Thesis and objectives are clearly stated and fully justified, providing a strong foundation for the research.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td><b>2. Significance of the Study / Rationale</b></td>
                  <td>Significance of the study are weak or missing. The connection to the research objectives is unclear.</td>
                  <td>Score justification is provided but lacks depth or clear connection to the research objectives.</td>
                  <td>The significance of the study is well-argued and aligned with the research objectives.</td>
                  <td>The study's significance is compellingly argued with thorough rationale, clearly showing the research's value.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td><b>3. Extent and Sufficiency of the Literature Review and References Cited</b></td>
                  <td>Literature review is insufficient, lacking relevant sources or proper citations.</td>
                  <td>Literature review includes some relevant sources, but many gaps or weak citations exist.</td>
                  <td>Literature review is comprehensive with well-chosen and sufficent sources cited.</td>
                  <td>Literature review is exhaustive, with high-quality, relevant sources, showing thorough research and citations.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td><b>4. Appropriate Data and Methodology used</b></td>
                  <td>Data and methodology are inapproprate or insufficient for the research objectives.</td>
                  <td>Methodology is somewhat suitable but lacks clarity or sufficient data.</td>
                  <td>Appropriate data and methodology are used, with a clear explanation of their relevance.</td>
                  <td>Data and methodology are well-chosen and highly-appropriate for the research, fully supporting the objectives.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td colspan="5" class="text-end"><b>TOTAL</b></td>
                  <td class="text-end" id="content-score"> /20</td>
                </tr>
                <tr>
                  <td colspan="5" class="text-end"><b>PERCENTAGE</b></td>
                  <td class="text-end" id="content-percentage"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- Table 2 -->
      <div class="card mb box-shadow">
        <div class="card-body">
          <h4>Organization (10%)</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="organization-table">
              <thead>
                <tr>
                  <th colspan="2">Evaluation Area</th>
                  <th>Unacceptable (1-2)</th>
                  <th>Fairly Acceptable (3)</th>
                  <th>Acceptable (4)</th>
                  <th>Highly Acceptable (5)</th>
                  <th>Rating/Score</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="7"><i>Presentation and development of ideas are clear, logical and exhibits high standards of scholarship</i></td>
                </tr>
                <tr>
                  <td colspan="2"><b>a. Clarity of Ideas</b></td>
                  <td>Ideas are vague, confusing, or difficult to understand; lacks coherence.</td>
                  <td>Ideas are presented with some clarity but may require effort to interpret.</td>
                  <td>Ideas are mostly clear, with minor ambiguities or areas for refinement.</td>
                  <td>Ideas are exceptionally clear, precise, and immediately understandable.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td colspan="2"><b>b. Logical Flow</b></td>
                  <td>Lacks organization; ideas are presented in a disjointed or incoherent manner.</td>
                  <td>Ideas follow a basic sequence but may lack smooth transitions.</td>
                  <td>Ideas are logical and cohesive, with minor inconsistencies.</td>
                  <td>Ideas are flawlessly organized, with smooth and seamless transitions.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td colspan="2"><b>c. Standards of Scholarship</b></td>
                  <td>Content lacks depth, accuracy, or relevance; minimal research or evidence is used.</td>
                  <td>Content demonstrates some depth and accuracy, with limited research or evidence.</td>
                  <td>Content shows good research and depth, with some room for improvement.</td>
                  <td>Content reflects outstanding depth, accuracy, and relevance, with comprehensive evidence.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td colspan="6" align="right"><b>TOTAL</b></td>
                  <td class="text-end" id="organization-score">/15</td>
                <tr>
                  <td colspan="6" class="text-end"><b>PERCENTAGE</b></td>
                  <td class="text-end" id="organization-percentage"></td>
                </tr>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- table 3 -->
      <div class="card mb box-shadow">
        <div class="card-body">
          <h4>Novelty and Impact (10%)</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="novelty-table">
              <thead>
                <tr>
                  <th class="text-center">Evaluation Area</th>
                  <th class="text-center">Unacceptable (1-3)</th>
                  <th class="text-center">Fairly Acceptable (3)</th>
                  <th class="text-center">Acceptable (4)</th>
                  <th class="text-center">Highly Acceptable (5)</th>
                  <th class="text-center">Rating/Score</th>
                </tr>
              </thead>
              <tbody>
                <tr class="iock">
                  <td class="fw-bold" colspan="6">Innovation, Originality, and Contribution to Knowledge (5%)</td>
                </tr>
                <tr class="iock">
                  <td><b>a. Originality of the System and Algorithm</b></td>
                  <td>The prototype and algorithm replicate existing solutions with no new insights or approaches.</td>
                  <td>The prototype and algorithm show some originality but remain heavily based on existing technologies or methods.</td>
                  <td>The prototype and algorithm introduces new concepts or approaches, offering some level of originality.</td>
                  <td>The prototype and algorithm are highly original, introducing novel concepts, techniques, or methodologies that significantly advance the field.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr class="iock">
                  <td><b>b. Novelty in Solving Problems or Addressing Gaps</b></td>
                  <td>The system and algorithm do not address any significant problem or gap in the current body of knowledge.</td>
                  <td>The system and algorithm address a problem, but the solution is not entirely new or substantial in its contribution.</td>
                  <td>The system and algorithm address a known gap, providing a creative or valuable solution to an existing problem.</td>
                  <td>The system and algorithm address an important gap, providing a groundbreaking solution that substantially advances knowledge or practice in the field.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr class="iock">
                  <td><b>c. Contribution to the Existing Body of Knowledge</b></td>
                  <td>The system and algorithm fail to add value to the current state of research or practical knowledge.</td>
                  <td>The system and algorithm contribute moderately to the existing body of knowledge, with limited innovation or application.</td>
                  <td>The system and algorithm contribute meaningfully advancing knowledge, or theoretically or practically, in the field.</td>
                  <td>The system and algorithm make a significant contribution to the field, enhancing theoretical understanding or providing impactful, or practical solutions.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td colspan="5" class="text-end fw-bold">TOTAL</td>
                  <td class="text-end" id="iock-score"> /15</td>
                </tr>
                <tr>
                  <td colspan="5" class="text-end fw-bold">PERCENTAGE</td>
                  <td class="text-end" id="iock-percentage"></td>
                </tr>
                <tr class="isb">
                  <td class="fw-bold" colspan="6">Impact and Societal Benefit (5%)</td>
                </tr>
                <tr class="isb">
                  <td><b>a. Practical Applications for Society and Community</b></td>
                  <td>The system and algorithm have no evident real-world application or societal relevance.</td>
                  <td>The system and algorithm have limited practical applications, impacting a small group or niche.</td>
                  <td>The system and algorithm have clear and meaningful applications, benefitting a specific communities or sectors.</td>
                  <td>The system and algorithm have broad, positive implications, offering scalable solutions with substantial benefits for a wide range of communities or industries.</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr class="isb">
                  <td><b>b. Accessibilty and Inclusivity</b></td>
                  <td>The system and algorithm are inaccessible or exclude significant groups from benefit.</td>
                  <td>The system and algorithm provide some accessibility features, but exlude certain groups or limit their impact.</td>
                  <td>The system and algorithm contribute accessible to a broad range of users, wih a focus on inclusivity and diverse needs.</td>
                  <td>The system and algorithm are highly accessible, and inclusive, designed to benefit diverse user groups and address accessibility challenges</td>
                  <td><input type="number" class="form-control" placeholder="Rating" min="1" max="5"></td>
                </tr>
                <tr>
                  <td colspan="5" class="text-end fw-bold">TOTAL</td>
                  <td class="text-end" id="isb-score"> /10</td>
                </tr>
                <tr>
                  <td colspan="5" class="text-end fw-bold">PERCENTAGE</td>
                  <td class="text-end" id="isb-percentage"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- total -->
      <div class="card mb box-shadow">
        <div class="card-body">
          <h4>GRADE SUMMARY:</h4>
          <div class="table-responsive">
            <table class="table table-bordered table-hover" id="summary-table">
              <thead>
                <th>Criteria</td>
                <th>Rating/Score</td>
              </thead>
              <tr>
                <td>Content (40%)</td>
                <td id="content-total"></td>
              </tr>
              <tr>
                <td>Organization (10%)</td>
                <td id="organization-total"></td>
              </tr>
              <tr>
                <td>Novelty and Impact (10%)</td>
                <td id="novelty-total"></td>
              </tr>
              <tr>
                <th>RESEARCH PAPER PROJECT TOTAL</td>
                <td id="init-total"></td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <!-- report -->
      <div class="card mb box-shadow">
        <div class="card-body">
          <h4>Research Proposal Defense Score Sheet</h4>
          <table class="table table-bordered table-hover" id="score-sheet">
            <thead>
              <tr>
                <th colspan="5">CRITERIA</th>
                <th colspan="2">RATING</th>
                <th>MAXIMUM</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="5"><b>RESEARCH PAPER/PROJECT</b></td>
                <td colspan="3"><b>Group Grade</b></td>
              </tr>
              <tr>
                <td colspan="7"><b>Content</b></td>
                <td>40%</td>
              </tr>
              <tr>
                <td colspan="5">1. Thesis statement / research objective(s) are well stated and justified</td>
                <td colspan="2" id="content-sheet1"></td>
                <td>10%</td>
              </tr>
              <tr>
                <td colspan="5">2. Significance of the Study/Rationale are well argued</td>
                <td colspan="2" id="content-sheet2"></td>
                <td>10%</td>
              </tr>
              <tr>
                <td colspan="5">3. Extent and sufficiency of Literature Review and References cited</td>
                <td colspan="2" id="content-sheet3"></td>
                <td>10%</td>
              </tr>
              <tr>
                <td colspan="5">4. Appropriate data and methodology used</td>
                <td colspan="2" id="content-sheet4"></td>
                <td>10%</td>
              </tr>
              <tr>
                <td colspan="7"><b>Organization</b></td>
                <td>10%</td>
              </tr>
              <tr>
                <td colspan="5">Presentation and development of ideas are <i>clear, logical</i> and <i>exhibits high standards of scholarship</i></td>
                <td colspan="2" id="organization-sheet1"></td>
                <td>10%</td>
              </tr>
              <tr>
                <td colspan="7"><b>Novelty and Impact</b></td>
                <td>10%</td>
              </tr>
              <tr>
                <td colspan="5">1. The research endeavor exhibits a degree of innovation, originality, and ability to contribute to the existent body of knowledge</td>
                <td colspan="2" id="novelty-sheet1"></td>
                <td>5%</td>
              </tr>
              <tr>
                <td colspan="5">2. Its impact and benefit to the society or community</td>
                <td colspan="2" id="novelty-sheet2"></td>
                <td>5%</td>
              </tr>
              <tr>
                <td colspan="5"><b>Subtotal – Group Grade</b></td>
                <td colspan="2" id="group-total"></td>
                <td>60%</td>
              </tr>
              <tr>
                <td colspan="<?php echo (5 - $totalMembers); ?>"><b>ORAL DEFENSE</b></td>
                <td colspan="<?php echo (11 - $totalMembers); ?>"><b>Individual Grades</b></td>
              </tr>
              <!-- Generate headers for team members -->
              <tr>
                <td><b>Presentation</b></td>
                <?php
                foreach ($members as $index => $member) {
                  echo "<th>" . chr(65 + $index) . "</th>"; // A, B, C, etc.
                }
                ?>
                <td>30%</td>
              </tr>
              <!-- Then, in the subsequent rows, generate input cells for each member -->
              <?php
              $presentationCriteria = [
                ['Time allotted for presentation are met', '5%'],
                ['The visual presentation exemplified ideas, concisely and comprehensively', '5%']
              ];

              foreach ($presentationCriteria as $key => $criterion): ?>
                <tr>
                  <td><?php echo ($key + 1) . '. ' . $criterion[0]; ?></td>
                  <?php foreach ($members as $index => $member): ?>
                    <td><input type="number" name="solo-<?php echo $index; ?>-pres-<?php echo $key; ?>" class="form-control" placeholder="Rating" min="1" max="5"></td>
                  <?php endforeach; ?>
                  <td><?php echo $criterion[1]; ?></td>
                </tr>
              <?php endforeach; ?>

              <!-- Add other sections as needed, such as 'Question and Answer' -->
              <tr>
                <td><b>Question and Answer</b></td>
                <?php foreach ($members as $index => $member): ?>
                  <th><?php echo chr(65 + $index); ?></th>
                <?php endforeach; ?>
                <td>10%</td>
              </tr>

              <?php
              $qaCriteria = [
                ['Presenter is well prepared, appeared relaxed and confident ', '10%'],
                ['Presenter is able to communicate effectively the ideas', '10%'],
                ['Exemplified mastery and reasoning ability in defending his/her proposal/section', '10%'],
              ];

              foreach ($qaCriteria as $key => $criterion): ?>
                <tr>
                  <td><?php echo ($key + 1) . '. ' . $criterion[0]; ?></td>
                  <?php foreach ($members as $index => $member): ?>
                    <td><input type="number" name="solo-<?php echo $index; ?>-qa-<?php echo $key; ?>" class="form-control" placeholder="Rating" min="1" max="10"></td>
                  <?php endforeach; ?>
                  <td><?php echo $criterion[1]; ?></td>
                </tr>
              <?php endforeach; ?>

              <!-- Update subtotal and total rows -->
              <tr>
                <td><b>Subtotal – Individual Grade</b></td>
                <?php
                foreach ($members as $index => $member) {
                  echo '<td id="solo-' . $index . '"></td>';
                }
                ?>
                <td id="subtotal-individual">/40</td>
              </tr>
              <tr>
                <td>
                  <b>TOTAL</b><br>
                  <i>(Add the group grade [60%] to the individual grades [40%])</i>
                </td>
                <?php
                foreach ($members as $index => $member) {
                  echo '<td id="total-' . $index . '"></td>';
                }
                ?>
                <td>100%</td>
              </tr>
            </tbody>
          </table>

        </div>
      </div>

      <form action="submit_evaluation.php" method="POST">
        <div class="card mb box-shadow">
          <div class="card-body">
            <h4>Comments, Evaluation and Recommendations</h4>
            <textarea name="comments" class="form-control" rows="3" placeholder="Comments, Evaluation and Recommendations"></textarea>
            <input type="hidden" name="defense_schedule_id" value="<?php echo $schedule['id']; ?>">
            <input type="hidden" name="evaluator_id" value="<?php echo $_SESSION['id']; ?>">
            <input type="hidden" name="group_score" id="group-grade">
            <?php for ($i = 0; $i < $totalMembers; $i++): ?>
              <?php if (isset($members[$i])): ?>
                <input type="hidden" name="student_ids[]" value="<?php echo $members[$i]['user_id']; ?>">
              <?php endif; ?>
              <input type="hidden" name="solo_scores[]" id="solo<?php echo $i; ?>-grade">
              <input type="hidden" name="total_scores[]" id="total<?php echo $i; ?>-grade">
            <?php endfor; ?>
            <div class="d-flex justify-content-between align-items-center">
              <div class="btn-group col-9">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
              </div>
              <small class="text-muted col-3 ms-auto text-end">/3 Panelist Complete</small>
            </div>
          </div>
        </div>
      </form>

      <!-- AI -->
      <div class="card mb box-shadow">
        <div class="card-body">
          <h4>AI Evaluation</h4>
          <div class="d-flex justify-content-between align-items-center">
            <div id="ai-output"></div>
          </div>
        </div>
      </div>
    </div>


</main>

<script>
  // filepath: /c:/xampp/htdocs/coecsathesis/decision-support/index.php

  document.addEventListener('DOMContentLoaded', () => {
    const calculateSection = (inputs, maxScore, scoreElementId, percentageElementId) => {
      let total = 0;
      inputs.forEach(input => {
        const val = parseInt(input.value) || 0;
        total += val;
      });
      document.getElementById(scoreElementId).textContent = `${total}/${maxScore}`;
      const percentage = (total / maxScore) * 100;
      document.getElementById(percentageElementId).textContent = percentage.toFixed(2) + '%';
      return percentage;
    };

    const updateTotals = () => {
      const contentInputs = document.querySelectorAll('#content-table input[placeholder="Rating"]');
      const organizationInputs = document.querySelectorAll('#organization-table input[placeholder="Rating"]');
      const iockInputs = document.querySelectorAll('#novelty-table .iock input[placeholder="Rating"]');
      const isbInputs = document.querySelectorAll('#novelty-table .isb input[placeholder="Rating"]'); // Assuming ISB is part of novelty-table

      const contentPercentage = calculateSection(contentInputs, 20, 'content-score', 'content-percentage');
      const organizationPercentage = calculateSection(organizationInputs, 15, 'organization-score', 'organization-percentage');
      const iockPercentage = calculateSection(iockInputs, 15, 'iock-score', 'iock-percentage');
      const isbPercentage = calculateSection(isbInputs, 10, 'isb-score', 'isb-percentage');

      // Calculate total novelty as average of iock and isb
      const noveltyPercentage = ((iockPercentage + isbPercentage) / 2).toFixed(2);
      document.getElementById('novelty-total').textContent = `${noveltyPercentage}%`;

      // Calculate group total based on weights: Content (40%), Organization (10%), Novelty (10%)
      const groupTotalPercentage = (contentPercentage * 0.4) + (organizationPercentage * 0.1) + (parseFloat(noveltyPercentage) * 0.1);
      document.getElementById('group-total').textContent = `${groupTotalPercentage}`;
      document.getElementById('group-grade').value = groupTotalPercentage;

      // Update Grade Summary with percentages
      document.getElementById('content-total').textContent = `${contentPercentage.toFixed(2)}%`;
      document.getElementById('organization-total').textContent = `${organizationPercentage.toFixed(2)}%`;
      document.getElementById('novelty-total').textContent = `${noveltyPercentage}%`;
      document.getElementById('init-total').textContent = `${((contentPercentage  + organizationPercentage  + parseFloat(noveltyPercentage) )/3).toFixed(2)}%`;

      // Calculate individual grades by summing all inputs with the same name
      const individualGrades = {};
      const totalMembers = <?php echo $totalMembers; ?>;
      for (let i = 0; i < totalMembers; i++) {
        const inputs = document.querySelectorAll(`#score-sheet input[name^="solo-${i}-"]`);
        individualGrades[i] = Array.from(inputs).reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);
        document.getElementById(`solo-${i}`).textContent = individualGrades[i];
        document.getElementById(`solo${i}-grade`).value = individualGrades[i];
      } 

      // Update total individual grade
      const individualTotal = Object.values(individualGrades).reduce((sum, grade) => sum + grade, 0);
      document.getElementById('subtotal-individual').textContent = `40/40`;

      // Calculate combined total for each member
      for (let i = 0; i < totalMembers; i++) {
        const total = groupTotalPercentage + individualGrades[i];
        document.getElementById(`total-${i}`).textContent = `${total.toFixed(2)}`;
        document.getElementById(`total${i}-grade`).value = total;
      }

      // Map Content scores to Score Sheet
      contentInputs.forEach((input, index) => {
        const scoreCell = document.getElementById(`content-sheet${index + 1}`);
        if (scoreCell) {
          const value = parseInt(input.value) || 0;
          const percentage = (value / 5) * 100;
          const calculatedScore = (percentage / 100) * 10;
          scoreCell.textContent = calculatedScore;
        }
      });

      // Map Organization score to Score Sheet
      const organizationTotalScore = Array.from(organizationInputs).reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);
      const organizationAverageScore = organizationTotalScore / organizationInputs.length;
      const organizationCell = document.getElementById('organization-sheet1');
      if (organizationCell) {
        const percentage = (organizationAverageScore / 5) * 100;
        const organizationScore = (percentage / 100) * 10;
        organizationCell.textContent = organizationScore;
      }

      // Map Novelty scores to Score Sheet
      // IOCK scores mapping
      const iockTotalScore = Array.from(iockInputs).reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);
      const iockAverageScore = iockTotalScore / iockInputs.length;
      const iockCell = document.getElementById('novelty-sheet1');
      if (iockCell) {
        const percentage = (iockAverageScore / 5) * 100;
        const iockScore = (percentage / 100) * 10;
        iockCell.textContent = iockAverageScore;
      }

      // ISB scores mapping
      const isbTotalScore = Array.from(isbInputs).reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);
      const isbAverageScore = isbTotalScore / isbInputs.length;
      const isbCell = document.getElementById('novelty-sheet2');
      if (isbCell) {
        const percentage = (isbAverageScore / 5) * 100;
        const isbScore = (percentage / 100) * 10;
        isbCell.textContent = isbAverageScore;
      }
    };

    const inputs = document.querySelectorAll('input[type="number"][placeholder="Rating"]');
    inputs.forEach(input => {
      input.addEventListener('input', updateTotals);
    });

    updateTotals();

    const numericInputs = document.querySelectorAll('input[type="number"]');
  
  numericInputs.forEach(input => {
    // Prevent keyboard input
    input.addEventListener('keydown', (e) => {
      // Allow only arrow keys, tab, and delete/backspace
      if (!['ArrowUp', 'ArrowDown', 'Tab', 'Backspace', 'Delete'].includes(e.key)) {
        e.preventDefault();
      }
    });

    // Prevent paste
    input.addEventListener('paste', (e) => {
      e.preventDefault();
    });

    // Prevent drop
    input.addEventListener('drop', (e) => {
      e.preventDefault();
    });

    // Ensure values stay within min/max bounds when changed
    input.addEventListener('change', () => {
      const min = parseInt(input.getAttribute('min')) || 0;
      const max = parseInt(input.getAttribute('max')) || 100;
      let value = parseInt(input.value) || 0;

      // Clamp value between min and max
      value = Math.max(min, Math.min(max, value));
      input.value = value;
    });

    // Add custom spinner buttons
    const wrapper = document.createElement('div');
    wrapper.className = 'input-spinner-wrapper position-relative';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    // Add custom styling
    input.style.paddingRight = '20px';
    
    // Create custom spinner buttons
    const spinnerButtons = document.createElement('div');
    spinnerButtons.className = 'position-absolute end-0 top-50 translate-middle-y d-flex flex-column';
    spinnerButtons.style.height = '100%';
    
    const upButton = document.createElement('button');
    upButton.type = 'button';
    upButton.className = 'btn btn-sm p-0 border-0';
    upButton.innerHTML = '▲';
    upButton.style.height = '50%';
    upButton.style.fontSize = '8px';
    upButton.style.lineHeight = '1';
    
    const downButton = document.createElement('button');
    downButton.type = 'button';
    downButton.className = 'btn btn-sm p-0 border-0';
    downButton.innerHTML = '▼';
    downButton.style.height = '50%';
    downButton.style.fontSize = '8px';
    downButton.style.lineHeight = '1';

    spinnerButtons.appendChild(upButton);
    spinnerButtons.appendChild(downButton);
    wrapper.appendChild(spinnerButtons);

    // Add click handlers for custom buttons
    upButton.addEventListener('click', () => {
      const max = parseInt(input.getAttribute('max')) || 100;
      const currentValue = parseInt(input.value) || 0;
      if (currentValue < max) {
        input.value = currentValue + 1;
        input.dispatchEvent(new Event('input'));
        input.dispatchEvent(new Event('change'));
      }
    });

    downButton.addEventListener('click', () => {
      const min = parseInt(input.getAttribute('min')) || 0;
      const currentValue = parseInt(input.value) || 0;
      if (currentValue > min) {
        input.value = currentValue - 1;
        input.dispatchEvent(new Event('input'));
        input.dispatchEvent(new Event('change'));
      }
    });
  });

  // Add form submission validation
  const form = document.querySelector('form');
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    
    // Check if all required inputs have values
    const requiredInputs = form.querySelectorAll('input[type="number"]');
    let isValid = true;
    let firstInvalid = null;

    requiredInputs.forEach(input => {
      if (!input.value) {
        isValid = false;
        input.classList.add('is-invalid');
        if (!firstInvalid) firstInvalid = input;
      } else {
        input.classList.remove('is-invalid');
      }
    });

    // Check if comments are provided
    const comments = form.querySelector('textarea[name="comments"]');
    if (!comments.value.trim()) {
      isValid = false;
      comments.classList.add('is-invalid');
      if (!firstInvalid) firstInvalid = comments;
    } else {
      comments.classList.remove('is-invalid');
    }

    if (!isValid) {
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      // Show alert
      const alert = document.createElement('div');
      alert.className = 'alert alert-danger alert-dismissible fade show';
      alert.innerHTML = `
        <strong>Error!</strong> Please fill in all required fields.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      form.insertBefore(alert, form.firstChild);
      return; 
    }

    // If all validations pass, submit the form
    form.submit();
  });
  });
</script>

<!-- AI GEMINI MODULE -->
<!-- Main Module JS -->
<script type="module" src="../assets/js/mainModule.js"></script>
<!-- app.js -->
<script type="module" src="../assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<?php
include '../assets/layouts/footer.php'
?>