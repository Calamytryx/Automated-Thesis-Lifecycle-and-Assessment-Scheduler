<?php
// Include database connection
require '../assets/setup/db.inc.php';

// Retrieve team_id from POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['team_id'])) {
        $team_id = intval($_POST['team_id']);
    }
}

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
        echo "<script>window.location.href = '../home\index.php;</script>";
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
        SELECT CONCAT(users.first_name, ' ', users.last_name) AS fullname, team_members.role 
        FROM coecsa_thesis.team_members 
        JOIN users ON coecsa_thesis.team_members.user_id = users.id 
        WHERE coecsa_thesis.team_members.team_id = ? AND coecsa_thesis.team_members.role != 'Adviser'
    ");
    $membersStmt->execute([$team_id]);
    $members = $membersStmt->fetchAll(PDO::FETCH_ASSOC);

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

    const predefinedPdfUrl = `../assets/uploads/submission/`; // Replace with your PDF URL

    if (window.location.pathname.includes('coecsathesis/decision-support/<?php if (isset($fileName)) echo $fileName; ?>')) {
        window.extractText = async function(pdfUrl) {
            const filenameInput = document.getElementById('filename');
            const output = document.getElementById('output');

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
    }

    // Load the PDF on page load
    window.addEventListener('DOMContentLoaded', () => {
        extractText(predefinedPdfUrl);
    });
</script>


<main role="main">

    <section class="jumbotron text-center py-5">
        <div class="container">
            <h1 class="jumbotron-heading mb-4"><?php echo htmlspecialchars($researchTitle); ?></h1>
            <p class="text-muted">
                <strong>Members:</strong><br>
                <?php
                if (!empty($members)) {
                    foreach ($members as $member) {
                        echo htmlspecialchars($member['fullname']) . " - " . htmlspecialchars($member['role']) . "<br>";
                    }
                } else {
                    echo "No members found.<br>";
                }
                ?>
                <hr class="my-3">
                <strong>Adviser:</strong> <?php echo htmlspecialchars($adviser['fullname'] ?? 'No adviser assigned'); ?>
                <hr class="my-3">
                <strong>Course:</strong> <?php echo htmlspecialchars($team['course']); ?>
            </p>
        </div>
    </section>

    <div class="album py-5">
        <div class="container">

            <div class="text-center text-muted mb-5">
                <h2>The full paper Goes here</h2>
                <hr>
            </div>

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
                        <small class="text-muted">[under development]</small>
                    </div>
                </div>
            </div>
        </div>
        <!-- title proposal -->
<div class="container border-0">
    <div class="card mb box-shadow">
      <div class="card-body">
        <h4>Content (40%)</h4>
        <div class="table-responsive">
          <table class="table table-bordered">
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
                <td>1. Thesis Statement / Researh Objective(s)</td>
                <td>Thesis is unclear or poorly defined. The research objectives are not clearly stated or justified.</td>
                <td>Thesis and objectives are vaguely stated but lack adequate justification or clarity.</td>
                <td>Thesis clearly stated with a reasonable justification for the research objectives.</td>
                <td>Thesis and objectives are clearly stated and fully justified, providing a strong foundation for the research.</td>
                <td></td>
              </tr>
              <tr>
                <td>2. Significance of the Study / Rationale</td>
                <td>Significance of the study are weak or missing. The connection to the research objectives is unclear.</td>
                <td>Score justification is provided but lacks depth or clear connection to the research objectives.</td>
                <td>The significance of the study is well-argued and aligned with the research objectives.</td>
                <td>The study's significance is compellingly argued with thorough rationale, clearly showing the research's value.</td>
                <td></td>
              </tr>
              <tr>
                <td>3. Extent and Sufficiency of the Literature Review and References Cited</td>
                <td>Literature review is insufficient, lacking relevant sources or proper citations.</td>
                <td>Literature review includes some relevant sources, but many gaps or weak citations exist.</td>
                <td>Literature review  is comprehensive with well-chosen and sufficent sources cited.</td>
                <td>Literature review is exhaustive, with high-quality, relevant sources, showing thorough research and citations.</td>
                <td></td>
              </tr>
              <tr>
                <td>4. Appropriate Data and Methodology used</td>
                <td>Data and methodology are inapproprate or insufficient for the research objectives.</td>
                <td>Methodology is somewhat suitable but lacks clarity or sufficient data.</td>
                <td>Appropriate data and methodology are used, with a clear explanation of their relevance.</td>
                <td>Data and methodology are well-chosen and highly-appropriate for the research, fully supporting the objectives.</td>
                <td></td>
              </tr>
              <tr>
                <td colspan="5" class="text-end">TOTAL</td>
                <td class="text-end"> /20</td>
              </tr>
              <tr>
                <td colspan="5" class="text-end">PERCENTAGE</td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<!-- Table 2 -->
    <div class="card mb box-shadow">
      <div class="card-body">
        <h4>Novelty and Impact (10%)</h4>
        <div class="table-responsive">
          <table class="table table-bordered">
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
                <td class="fw-bold" colspan="6">Innovation, Originality, and Contribution to Knowledge (5%)</td>
              </tr>
              <tr>
                <td>a. Originality of the System and Algorithm</td>
                <td>The prototype and algorithm replicate existing solutions with no new insights or approaches.</td>
                <td>The prototype and algorithm show some originality but remain heavily based on existing technologies or methods.</td>
                <td>The prototype and algorithm introduces new concepts or approaches, offering some level of originality.</td>
                <td>The prototype and algorithm are highly original, introducing novel concepts, techniques, or methodologies that significantly advance the field.</td>
                <td></td>
              </tr>
              <tr>
                <td>b. Novelty in Solving Problems or Addressing Gaps</td>
                <td>The system and algorithm do not address any significant problem or gap in the current body of knowledge.</td>
                <td>The system and algorithm address a problem, but the solution is not entirely new or substantial in its contribution.</td>
                <td>The system and algorithm address a known gap, providing a creative or valuable solution to an existing problem.</td>
                <td>The system and algorithm address an important gap, providing a groundbreaking solution that substantially advances knowledge or practice in the field.</td>
                <td></td>
              </tr>
              <tr>
                <td>C. Contribution to the Existing Body of Knowledge</td>
                <td>The system and algorithm fail to add value to the current state of research or practical knowledge.</td>
                <td>The system and algorithm  contribute moderately to the existing body of knowledge, with limited innovation or application.</td>
                <td>The system and algorithm contribute meaningfully advancing knowledge, or theoretically or practically, in the field.</td>
                <td>The system and algorithm make a significant contribution to the field, enhancing theoretical understanding or providing impactful, or practical solutions.</td>
                <td></td>
              </tr>
              <tr>
                <tr>
                  <td colspan="5" class="text-end fw-bold">TOTAL</td>
                  <td class="text-end"> /15</td>
                </tr>
                <tr>
                  <td colspan="5" class="text-end fw-bold">PERCENTAGE</td>
                  <td></td>
                </tr>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- New Table without title -->
    <div class="card mb box-shadow">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
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
                <td class="fw-bold" colspan="6">Innovation, Originality, and Contribution to Knowledge (5%)</td>
              </tr>
              <tr>
                <td>a. Practical Applications for Society and Community</td>
                <td>The system and algorithm have no evident real-world application or societal relevance.</td>
                <td>The system and algorithm have limited practical applications, impacting a small group or niche.</td>
                <td>The system and algorithm have clear and meaningful applications, benefitting a specific communities or sectors.</td>
                <td>The system and algorithm have broad, positive implications, offering scalable solutions with substantial benefits for a wide range of communities or industries.</td>
                <td></td>
              </tr>
              <tr>
                <td>b. Potential for Address, Social, Economic, and Environmental Issues</td>
                <td>The system and algorithm have no address to significant social, economic, or environmental problems.</td>
                <td>The system and algorithm address social, economic, or environmental issues in a limited capacity or with minimal impact.</td>
                <td>The system and algorithm offer viable solutions to relevant issues, demonstrating clear benefits to society or specific communities.</td>
                <td>The system and algorithm offer transformative solutions to pressing social, economic, or environmental challenges, with potential for lasting impact.</td>
                <td></td>
              </tr>
              <tr>
                <td>C. Accessibilty and Inclusivity</td>
                <td>The system and algorithm are inaccessible or exclude significant groups from benefit.</td>
                <td>The system and algorithm provide some accessibility features, but exlude certain groups or limit their impact.</td>
                <td>The system and algorithm contribute accessible to a broad range of users, wih a focus on inclusivity and diverse needs.</td>
                <td>The system and algorithm are highly accessible, and inclusive, designed to benefit diverse user groups and address accessibility challenges</td>
                <td></td>
              </tr>
              <tr>
                <tr>
                  <td colspan="5" class="text-end fw-bold">TOTAL</td>
                  <td class="text-end"> /15</td>
                </tr>
                <tr>
                  <td colspan="5" class="text-end fw-bold">PERCENTAGE</td>
                  <td></td>
                </tr>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>


</main>


<?php

include '../assets/layouts/footer.php'

?>