<?php
/**
 * Shared, comprehensive Dashboard/Home guide.
 *
 * Single source of truth for every feature across the whole system. Each module
 * is described once and rendered into BOTH the "Modern" card/modal view and the
 * "Wiki" article view, on BOTH the admin dashboard and the home page.
 *
 * Modules are filtered by the current user's role (`audiences`) so each person
 * only sees guides for the features they can actually reach. Note that faculty
 * - especially assigned research teachers - can reach management features such
 * as defense scheduling and field-of-specialization assignment, so those guides
 * are shared with them too.
 *
 * Roles: 'admin' (super/institution admin), 'chair' (program chair),
 *        'faculty' (research teacher), 'student'.
 */

if (!function_exists('guide_resolve_audience')) {
    /**
     * Resolve the current viewer's role from the session.
     */
    function guide_resolve_audience(): string
    {
        $userType = (int)($_SESSION['usertype'] ?? -1);
        $isChair  = ($userType === 0 && (int)($_SESSION['program_chair'] ?? 0) === 1);

        if ($isChair) {
            return 'chair';
        }
        if ($userType === 0) {
            return 'admin';
        }
        if ($userType === 2) {
            return 'faculty';
        }
        return 'student';
    }
}

if (!function_exists('guide_get_modules')) {
    /**
     * The full module catalogue. `audiences` lists the roles that can reach each
     * feature; `snippet` is trusted author HTML (an inert preview of the real UI).
     */
    function guide_get_modules(): array
    {
        return [
            'getting-started' => [
                'icon' => 'fa-rocket',
                'title' => 'Getting Started',
                'summary' => 'Log in, find your way around, and use this manual.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'This is the complete user manual for the system. It documents every feature you can reach, plus the shared concepts (roles, statuses, panels) that apply everywhere. Start here for how to sign in, navigate, and read this manual.',
                'snippet' => '
                    <div class="border rounded p-2 bg-white" style="max-width:240px">
                      <div class="text-muted small text-uppercase mb-1">Overview</div>
                      <div class="py-1"><i class="fas fa-house me-2"></i>Overview</div>
                      <div class="text-muted small text-uppercase mt-2 mb-1">Help</div>
                      <div class="py-1 fw-semibold text-primary"><i class="fas fa-book me-2"></i>User Manual</div>
                    </div>',
                'actions' => [
                    'Sign in with your institutional account',
                    'Use the left sidebar to move between features',
                    'Switch this manual between Modern (cards) and Wiki views',
                    'Search the Wiki view to jump straight to a topic',
                ],
                'steps' => [
                    'Log in from the login page',
                    'Pick a feature from the sidebar',
                    'Open this manual any time from the sidebar',
                    'Use Modern cards for browsing or the Wiki for searching',
                ],
                'tip' => ['type' => 'info', 'text' => 'Your sidebar and this manual only list features your role can access, so they may look different from a colleague with a different role.'],
            ],
            'your-role' => [
                'icon' => 'fa-user-shield',
                'title' => 'Your Role & Access',
                'summary' => 'What each role can do and how data is scoped.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'The system has four roles. Your role decides which features appear and how much data you see. Most data is scoped to your college or program, so you only work with what is relevant to you.',
                'body_html' => '
                    <table class="table table-sm table-bordered bg-white">
                      <thead class="table-light"><tr><th>Role</th><th>Can do</th><th>Sees</th></tr></thead>
                      <tbody>
                        <tr><td>Admin</td><td>Manage the entire system</td><td>All colleges and programs</td></tr>
                        <tr><td>Program Chair</td><td>Manage their program and college</td><td>Their college only</td></tr>
                        <tr><td>Faculty (research teacher)</td><td>Advise groups, run scheduling, sit on panels, evaluate, assign specializations</td><td>Their program and assigned teams/sections</td></tr>
                        <tr><td>Student</td><td>Accept titles, submit requirements, view schedule and results</td><td>Their own group</td></tr>
                      </tbody>
                    </table>',
                'tip' => ['type' => 'info', 'text' => 'Admins see everything; chairs are scoped to their college; faculty to their program and assigned teams; students to their own group.'],
            ],
            'panelists' => [
                'icon' => 'fa-pen-to-square',
                'title' => 'Panelist Grading Sheet',
                'summary' => 'Grade a defense across the Research Paper and Score Sheet tabs.',
                'audiences' => ['admin', 'chair', 'faculty'],
                'overview' => 'As a panelist you grade a group\'s defense from the grading sheet, which has two tabs: a Research Paper tab (a PDF viewer for the manuscript plus an AI analysis) and a Score Sheet tab (the rubric where you enter your scores). Try the tabs in the preview below.',
                'snippet' => '
                    <ul class="nav nav-tabs mb-3" role="tablist">
                      <li class="nav-item"><button class="nav-link active" data-demo-tab="ps-research">Research Paper</button></li>
                      <li class="nav-item"><button class="nav-link" data-demo-tab="ps-score">Score Sheet</button></li>
                    </ul>
                    <div data-demo-pane="ps-research">
                      <div class="btn-group w-100 mb-2" role="group" aria-label="View toggles">
                        <button class="btn btn-primary"><i class="fas fa-file-pdf me-1"></i>PDF View</button>
                        <button class="btn btn-outline-primary"><i class="fas fa-robot me-1"></i>AI Analysis</button>
                      </div>
                      <div class="border rounded bg-white p-4 text-center text-muted">
                        <i class="fas fa-file-pdf fa-2x mb-2"></i><br>Submitted manuscript renders here
                      </div>
                    </div>
                    <div data-demo-pane="ps-score" style="display:none">
                      <table class="table table-sm table-bordered mb-2 bg-white">
                        <thead class="table-light"><tr><th>Criterion</th><th style="width:90px">Score</th></tr></thead>
                        <tbody>
                          <tr><td>Content &amp; Originality</td><td><input class="form-control form-control-sm" value="9"></td></tr>
                          <tr><td>Methodology</td><td><input class="form-control form-control-sm" value="8"></td></tr>
                          <tr><td>Presentation</td><td><input class="form-control form-control-sm" value="9"></td></tr>
                        </tbody>
                      </table>
                      <button class="btn btn-sm btn-success"><i class="fas fa-save me-1"></i>Submit Scores</button>
                    </div>',
                'body_html' => '
                    <p class="text-muted">The grading sheet is organised into two tabs:</p>
                    <h6><i class="fas fa-file-pdf me-2 text-primary"></i>1. Research Paper tab</h6>
                    <p class="text-muted">Toggles between <strong>PDF View</strong> - the group\'s submitted manuscript shown inline - and <strong>AI Analysis</strong>, an AI evaluation that summarises strengths, weaknesses and points worth probing during the defense. The AI analysis is decision support, not a grade.</p>
                    <h6><i class="fas fa-list-check me-2 text-primary"></i>2. Score Sheet tab</h6>
                    <p class="text-muted">The rubric-based score sheet where you rate each criterion and add comments. Your submitted scores feed the group\'s Evaluations.</p>',
                'actions' => [
                    'Read the manuscript on the Research Paper tab (PDF View)',
                    'Switch to AI Analysis for an AI assessment',
                    'Open the Score Sheet tab and score each criterion',
                    'Add comments and submit your scores',
                ],
                'steps' => [
                    'Open the group\'s grading sheet',
                    'On the Research Paper tab, use PDF View / AI Analysis',
                    'Switch to the Score Sheet tab',
                    'Enter your scores on the rubric and submit',
                ],
                'tip' => ['type' => 'warning', 'text' => 'The AI analysis is decision support only - your professional judgment as a panelist is final.'],
            ],
            'status-reference' => [
                'icon' => 'fa-tags',
                'title' => 'Statuses & Badges',
                'summary' => 'What each coloured badge and status means.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'Throughout the system, coloured badges show status at a glance. Here is what they mean.',
                'body_html' => '
                    <ul class="list-unstyled mb-0">
                      <li class="mb-2"><span class="badge bg-success">Approved</span> <span class="badge bg-info text-dark">Finalized</span> &mdash; confirmed; finalized schedules are locked and appear on calendars.</li>
                      <li class="mb-2"><span class="badge bg-warning text-dark">Pending</span> &mdash; awaiting review or action.</li>
                      <li class="mb-2"><span class="badge bg-danger">Rejected</span> <span class="badge bg-danger">No submission</span> &mdash; needs revision, or a deadline passed with nothing submitted.</li>
                      <li class="mb-2"><span class="badge bg-success">Open</span> <span class="badge bg-secondary">Closed</span> &mdash; a requirement still accepting submissions, or past its deadline.</li>
                      <li><span class="badge bg-success">Submitted</span> &mdash; a deliverable was turned in on time.</li>
                    </ul>',
                'tip' => ['type' => 'info', 'text' => 'Badge colours are consistent everywhere: green = good/done, amber = pending, red = problem/overdue.'],
            ],
            'account-profile' => [
                'icon' => 'fa-id-card',
                'title' => 'Account & Profile',
                'summary' => 'Update your profile, password, expertise and availability.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'Manage your account details from your profile. Faculty should keep their field of expertise and working hours current, because the scheduler uses them to build panels.',
                'snippet' => '
                    <div class="border rounded p-2 bg-white">
                      <div class="mb-2"><label class="form-label small mb-0">Area of expertise</label><input class="form-control form-control-sm" value="Artificial Intelligence, Cybersecurity"></div>
                      <div><label class="form-label small mb-0">Working hours</label><input class="form-control form-control-sm" value="08:00 - 17:00"></div>
                    </div>',
                'actions' => [
                    'Edit your name and contact details',
                    'Change or reset your password',
                    'Set your area of expertise (faculty)',
                    'Set your working hours / availability (faculty)',
                ],
                'steps' => [
                    'Open your profile from the top-right menu',
                    'Edit the fields you need',
                    'Save your changes',
                ],
                'tip' => ['type' => 'warning', 'text' => 'Faculty: accurate expertise and working hours directly affect panel assignment and scheduling.'],
            ],
            'files-repository' => [
                'icon' => 'fa-folder-open',
                'title' => 'Research Repository',
                'summary' => 'Browse and access stored research files.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'The Research Repository is the shared store of research files and documents, opened from the sidebar.',
                'actions' => [
                    'Open the repository from the sidebar',
                    'Browse stored research documents',
                ],
                'steps' => [
                    'Click "Research Repository" in the sidebar',
                    'Locate the file you need',
                ],
                'tip' => ['type' => 'info', 'text' => 'The repository opens in a new browser tab.'],
            ],
            'overview' => [
                'icon' => 'fa-chart-line',
                'title' => 'Overview',
                'summary' => 'Your landing page: key metrics, requirement progress and upcoming defenses.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'The Overview is your home base. Admins and chairs see system-wide metrics; faculty and students see their own groups, requirement progress and the defenses coming up. Everything here is scoped to what you are allowed to see.',
                'snippet' => '
                    <div class="row g-2">
                      <div class="col-4"><div class="border rounded p-2 text-center bg-white"><div class="fw-bold fs-5">128</div><small class="text-muted">Users</small></div></div>
                      <div class="col-4"><div class="border rounded p-2 text-center bg-white"><div class="fw-bold fs-5">34</div><small class="text-muted">Groups</small></div></div>
                      <div class="col-4"><div class="border rounded p-2 text-center bg-white"><div class="fw-bold fs-5">5</div><small class="text-muted">Upcoming</small></div></div>
                    </div>
                    <div class="mt-2">
                      <small class="text-muted">Manuscript &mdash; 72% complete</small>
                      <div class="progress" style="height:8px;"><div class="progress-bar" style="width:72%"></div></div>
                    </div>',
                'actions' => [
                    'See counts of users, groups and research titles (admins/chairs)',
                    'Track requirement completion for your groups',
                    'View the upcoming approved/finalized defense calendar',
                    'Open any metric card for a detailed breakdown',
                ],
                'steps' => [
                    'Review the cards and progress widgets at the top',
                    'Click a card to open its detailed modal',
                    'Use requirement progress to spot groups needing attention',
                    'Check the defense calendar for what is coming up',
                ],
                'tip' => ['type' => 'info', 'text' => 'Requirement progress reflects real submission status: a group is only counted as "No submission" once a requirement\'s deadline has passed. The calendar shows only approved/finalized defenses within your scope.'],
            ],
            'research-title-acceptance' => [
                'icon' => 'fa-check-circle',
                'title' => 'Research Title Acceptance',
                'summary' => 'Check a research title for uniqueness and accept or revise it.',
                'audiences' => ['faculty', 'student'],
                'overview' => 'The Research Title Acceptance tool checks the uniqueness of your proposed research title, surfaces similar existing titles, and lets your group accept or revise the title before it moves into the workflow.',
                'snippet' => '
                    <label class="form-label small mb-1">Enter your research title</label>
                    <input class="form-control form-control-sm mb-2" placeholder="Enter your research title here..." value="IoT-Based Flood Monitoring System">
                    <label class="form-label small mb-1">Field of study</label>
                    <input class="form-control form-control-sm mb-2" placeholder="e.g., Computer Science" value="Computer Science">
                    <button class="btn btn-sm btn-primary mb-2"><i class="fas fa-magnifying-glass me-1"></i>Uniqueness Check</button>
                    <div class="border rounded p-2 bg-white d-flex align-items-center justify-content-between">
                      <span><small class="text-muted">Result:</small> <span class="badge bg-success">Unique (12% similarity)</span></span>
                      <button class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Accept</button>
                    </div>',
                'actions' => [
                    'Check a title against existing titles for similarity',
                    'Review suggested or conflicting titles',
                    'Accept the title or send it back for revision',
                ],
                'steps' => [
                    'Enter or review your proposed title',
                    'Run the uniqueness check',
                    'Review the similarity result and any matches',
                    'Accept the title or revise and re-check',
                ],
                'tip' => ['type' => 'info', 'text' => 'A low similarity score means your title is sufficiently distinct. Resolve conflicts before accepting.'],
            ],
            'requirement-checker' => [
                'icon' => 'fa-list-check',
                'title' => 'Requirement Checker',
                'summary' => 'Track and submit thesis requirements and watch deadlines.',
                'audiences' => ['faculty', 'student'],
                'overview' => 'The Requirement Checker tracks every thesis requirement for your group, shows deadlines and submission status, and is where students submit deliverables. Faculty advisers use it to monitor their groups.',
                'snippet' => '
                    <select class="form-select form-select-sm mb-3" style="max-width:240px"><option>Team Alpha</option><option>Team Beta</option></select>
                    <div class="row g-2">
                      <div class="col-6"><div class="border rounded p-2 bg-white h-100"><div class="fw-semibold small">Manuscript</div><div class="text-muted small mb-1">Due Jun 30, 2026</div><span class="badge bg-warning text-dark">Pending</span> <button class="btn btn-sm btn-outline-primary mt-1"><i class="fas fa-upload me-1"></i>Submit</button></div></div>
                      <div class="col-6"><div class="border rounded p-2 bg-white h-100"><div class="fw-semibold small">Title Proposal</div><div class="text-muted small mb-1">Due Jun 1, 2026</div><span class="badge bg-success">Submitted</span></div></div>
                      <div class="col-6"><div class="border rounded p-2 bg-white h-100"><div class="fw-semibold small">Endorsement</div><div class="text-muted small mb-1">Due May 20, 2026</div><span class="badge bg-danger">No submission</span></div></div>
                    </div>',
                'actions' => [
                    'See each requirement, its deadline and your status',
                    'Submit deliverables for open requirements',
                    'Track which requirements are still missing',
                ],
                'steps' => [
                    'Open the Requirement Checker',
                    'Find the requirement you need to submit',
                    'Upload your deliverable before the deadline',
                    'Confirm the status changes to Submitted',
                ],
                'tip' => ['type' => 'warning', 'text' => 'A requirement is only flagged "No submission" after its deadline passes. Submit before the deadline to stay clear.'],
            ],
            'group-evaluations' => [
                'icon' => 'fa-comments',
                'title' => 'Group Evaluations',
                'summary' => 'Faculty score defenses with rubrics; students view their results.',
                'audiences' => ['faculty', 'student'],
                'overview' => 'Group Evaluations is where faculty panelists score a group\'s defense using the configured rubrics, and where students see their aggregated results and feedback once evaluations are released.',
                'snippet' => '
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 bg-white align-middle">
                      <thead class="table-light"><tr><th>Group Name</th><th>Research Title</th><th>Program</th><th class="text-center">Action</th></tr></thead>
                      <tbody>
                        <tr><td>Team Alpha</td><td>IoT-Based Flood Monitoring</td><td>BSCS</td><td class="text-center"><button class="btn btn-sm btn-primary"><i class="fas fa-pen-to-square me-1"></i>Evaluate</button></td></tr>
                        <tr><td>Team Beta</td><td>Campus Navigation App</td><td>BSCS</td><td class="text-center"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye me-1"></i>View</button></td></tr>
                      </tbody>
                    </table></div>',
                'actions' => [
                    'Score a group against each rubric criterion (faculty)',
                    'Add comments and submit the evaluation (faculty)',
                    'View aggregated scores and feedback (students)',
                ],
                'steps' => [
                    'Open the group being evaluated',
                    'Score each criterion using the rubric',
                    'Add feedback and submit',
                    'Students: open the tab to view released results',
                ],
                'tip' => ['type' => 'info', 'text' => 'Evaluations use the rubric group attached to the defense, so scores stay consistent across panelists.'],
            ],
            'class-record' => [
                'icon' => 'fa-table',
                'title' => 'Class Record',
                'summary' => 'Section teachers review the class record for their sections.',
                'audiences' => ['faculty'],
                'overview' => 'The Class Record gives an assigned section teacher a roster-level view of the groups and students in the sections they handle, for the selected academic year.',
                'snippet' => '
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-semibold">BSCS 3-A</span>
                      <button class="btn btn-sm btn-outline-secondary">A.Y. 2025-2026</button>
                    </div>
                    <table class="table table-sm table-bordered mb-0 bg-white">
                      <thead class="table-light"><tr><th>Student</th><th>Group</th><th>Status</th></tr></thead>
                      <tbody>
                        <tr><td>Dela Cruz, Juan</td><td>Team Alpha</td><td><span class="badge bg-success">On track</span></td></tr>
                        <tr><td>Santos, Maria</td><td>Team Beta</td><td><span class="badge bg-warning text-dark">Behind</span></td></tr>
                      </tbody>
                    </table>',
                'actions' => [
                    'View the students and groups in your sections',
                    'Switch academic year',
                    'Check per-student progress at a glance',
                ],
                'steps' => [
                    'Open Class Record',
                    'Pick the section and academic year',
                    'Review the roster and progress',
                ],
                'tip' => ['type' => 'info', 'text' => 'Only sections assigned to you (via Faculty Assignments) appear here.'],
            ],
            'teams' => [
                'icon' => 'fa-user-friends',
                'title' => 'Groups',
                'summary' => 'Create research groups, assign advisers and track progress.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'The Groups tab is where research groups are created, members and advisers are assigned, and progress is tracked through the thesis lifecycle. Faculty see groups in their program; students see their own group.',
                'snippet' => '
                    <div class="d-flex gap-2 mb-2 flex-wrap">
                      <input class="form-control form-control-sm" style="max-width:180px" placeholder="Search groups...">
                      <button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Team</button>
                      <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-import me-1"></i>Bulk Add</button>
                    </div>
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 bg-white align-middle">
                      <thead class="table-light"><tr><th>Group Code</th><th>Group Name</th><th>Research Title</th><th>Adviser</th><th class="text-center">Action</th></tr></thead>
                      <tbody>
                        <tr><td>G-001</td><td>Team Alpha</td><td>IoT-Based Flood Monitoring</td><td>Reyes</td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                        <tr><td>G-002</td><td>Team Beta</td><td><span class="text-danger">No research title</span></td><td>Santos</td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                      </tbody>
                    </table></div>',
                'actions' => [
                    'Create and edit groups, including bulk import',
                    'Assign students and an adviser to each group',
                    'Spot groups that still have no research title',
                ],
                'steps' => [
                    'Browse existing groups and their titles',
                    'Click "Add Team" to create a group and add members',
                    'Assign a faculty member as adviser',
                    'Watch the "Groups Without Research Titles" section',
                ],
                'tip' => ['type' => 'info', 'text' => 'Groups are scoped to your college/program, so you only manage the ones relevant to you.'],
            ],
            'research-titles' => [
                'icon' => 'fa-file-alt',
                'title' => 'Research Titles',
                'summary' => 'Manage group titles and their approval status.',
                'audiences' => ['admin', 'chair', 'faculty'],
                'overview' => 'The Research Titles tab tracks each group\'s title through the submission, review and approval workflow. Advisers and chairs review and approve or reject titles.',
                'snippet' => '
                    <table class="table table-sm table-bordered mb-0 bg-white">
                      <tbody>
                        <tr><td>IoT-Based Flood Monitoring</td><td><span class="badge bg-success">Approved</span></td></tr>
                        <tr><td>Campus Navigation App</td><td><span class="badge bg-warning text-dark">Pending</span></td></tr>
                        <tr><td>Smart Attendance</td><td><span class="badge bg-danger">Rejected</span></td></tr>
                      </tbody>
                    </table>',
                'actions' => [
                    'Add, edit and delete research titles',
                    'Assign a title to a group',
                    'Approve or reject titles',
                ],
                'steps' => [
                    'Browse titles and check their status column',
                    'Add a new title and link it to a group',
                    'Use edit to update the approval status',
                    'Track which titles still need approval',
                ],
                'tip' => ['type' => 'info', 'text' => 'Titles typically flow Submitted -> Under Review -> Approved/Rejected.'],
            ],
            'defense-schedules' => [
                'icon' => 'fa-calendar-alt',
                'title' => 'Defense Schedules',
                'summary' => 'Auto-generate and manage defense sessions; students view their own.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'The Defense Schedules tab manages defense sessions. Faculty research teachers and chairs auto-generate conflict-free schedules with the genetic-algorithm scheduler or add sessions manually, then approve and finalize them. Students see their own approved schedule.',
                'snippet' => '
                    <div class="d-flex flex-wrap gap-2 mb-2">
                      <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-cog me-1"></i>Scheduler Settings</button>
                      <button class="btn btn-sm btn-primary"><i class="fas fa-magic me-1"></i>Generate Schedule</button>
                      <button class="btn btn-sm btn-success"><i class="fas fa-check-double me-1"></i>Approve All</button>
                    </div>
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 bg-white align-middle">
                      <thead class="table-light"><tr><th>Date &amp; Time</th><th>Group</th><th>Panelist 1</th><th>Panelist 2</th><th>Panelist 3</th><th>Room</th><th>Status</th></tr></thead>
                      <tbody>
                        <tr><td>Jun 20, 9:00 AM</td><td>Team Alpha</td><td>Reyes</td><td>Cruz</td><td>Lim (Ext.)</td><td>Lab 1</td><td><span class="badge bg-success">Approved</span></td></tr>
                        <tr><td>Jun 20, 10:00 AM</td><td>Team Beta</td><td>Santos</td><td>Reyes</td><td>Tan (Ext.)</td><td>Lab 2</td><td><span class="badge bg-info text-dark">Finalized</span></td></tr>
                      </tbody>
                    </table></div>',
                'body_html' => '
                    <h6><i class="fas fa-table-columns me-2 text-primary"></i>The schedule table</h6>
                    <p class="text-muted">Each row is one defense: its date &amp; time, the group, the three panelists, the room and the status. Pending rows can be approved individually or with <strong>Approve All</strong>; finalized rows are locked.</p>',
                'actions' => [
                    'Configure scheduler settings (slots, rooms, dates, duration)',
                    'Auto-generate conflict-free schedules for many groups',
                    'Add or edit individual sessions and reassign panelists',
                    'Approve schedules individually or all at once, then finalize',
                ],
                'steps' => [
                    'Open "Scheduler Settings" and set slots, rooms and date range',
                    'Click "Generate Defense Schedule" to auto-create sessions',
                    'Review and adjust the generated sessions',
                    'Approve/finalize so they appear on dashboards',
                ],
                'tip' => ['type' => 'success', 'text' => 'Only approved or finalized schedules show on the Overview calendar, and each user only sees the ones within their scope. The scheduler avoids class-time, panelist and room conflicts automatically.'],
            ],
            'faculty-assignments' => [
                'icon' => 'fa-chalkboard-teacher',
                'title' => 'Faculty Assignments',
                'summary' => 'Assign subject teachers to class sections.',
                'audiences' => ['admin', 'chair'],
                'overview' => 'The Faculty Assignments tab links faculty members to the class sections they teach. These assignments drive the scheduler\'s class-conflict checks and the Class Record view.',
                'snippet' => '
                    <div class="row g-2 align-items-end mb-2">
                      <div class="col-12 col-sm-5"><label class="form-label small mb-1">Select a section</label><select class="form-select form-select-sm"><option>BSCS 3-A</option></select></div>
                      <div class="col-12 col-sm-5"><label class="form-label small mb-1">Select a subject teacher</label><select class="form-select form-select-sm"><option>Dela Cruz, Juan</option></select></div>
                      <div class="col-12 col-sm-2"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-plus me-1"></i>Assign</button></div>
                    </div>
                    <table class="table table-sm table-bordered mb-1 bg-white">
                      <thead class="table-light"><tr><th>Section</th><th>Subject Teacher</th><th></th></tr></thead>
                      <tbody>
                        <tr><td>BSCS 3-A</td><td>Dela Cruz, Juan</td><td><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Remove</button></td></tr>
                        <tr><td>BSCS 3-B</td><td>Dela Cruz, Juan</td><td><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Remove</button></td></tr>
                      </tbody>
                    </table>
                    <small class="text-muted">The same teacher can be assigned to multiple classes.</small>',
                'actions' => [
                    'Assign a faculty member to a section',
                    'Assign one faculty member to multiple sections',
                    'Remove an assignment',
                ],
                'steps' => [
                    'Pick a section from the dropdown',
                    'Pick a subject teacher',
                    'Click "Assign" - the same teacher can take other sections too',
                    'Use "Remove" to undo an assignment',
                ],
                'tip' => ['type' => 'info', 'text' => 'A single faculty member can handle multiple classes. The teacher list is scoped to your college/program.'],
            ],
            'allied-programs' => [
                'icon' => 'fa-sitemap',
                'title' => 'Allied Programs',
                'summary' => 'Configure cross-program adjacencies used by the scheduler.',
                'audiences' => ['admin', 'chair'],
                'overview' => 'Allied Programs defines which programs count as "adjacent" expertise. The defense scheduler uses these links when a same-program panelist is unavailable, and for the external/validator seat.',
                'snippet' => '
                    <div class="border rounded p-2 bg-white">
                      <div class="mb-2"><strong>BS Computer Science</strong> <small class="text-muted">is allied with:</small></div>
                      <span class="badge bg-info text-dark me-1">BS Information Technology <i class="fas fa-times ms-1"></i></span>
                      <span class="badge bg-info text-dark me-1">BS Information Systems <i class="fas fa-times ms-1"></i></span>
                      <button class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-1"></i>Add allied</button>
                    </div>',
                'actions' => [
                    'Link a program to one or more allied programs',
                    'Remove an allied relationship',
                ],
                'steps' => [
                    'Select a base program',
                    'Add the programs that should count as allied',
                    'Save - the scheduler will use these as panelist fallbacks',
                ],
                'tip' => ['type' => 'warning', 'text' => 'If the allied list is empty, the scheduler falls back to same-college panelists only. Configure it to widen the valid panelist pool.'],
            ],
            'specialization-management' => [
                'icon' => 'fa-brain',
                'title' => 'Field of Specialization',
                'summary' => 'Maintain the specialization pool and assign fields to users and groups.',
                'audiences' => ['admin', 'chair', 'faculty'],
                'overview' => 'This area manages the pool of specializations (fields of expertise) and assigns them to faculty and groups. The scheduler uses these to align panelists with a group\'s expertise, so keeping them accurate improves panel quality.',
                'snippet' => '
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="fw-semibold small">Specialization pool</span>
                      <button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Specialization</button>
                    </div>
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-2 bg-white align-middle">
                      <thead class="table-light"><tr><th>Name</th><th>Department</th><th>College</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
                      <tbody>
                        <tr><td>Artificial Intelligence</td><td>Computer Science</td><td>College of Science</td><td><span class="badge bg-success">Active</span></td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                        <tr><td>Cybersecurity</td><td>Computer Science</td><td>College of Science</td><td><span class="badge bg-success">Active</span></td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                      </tbody>
                    </table></div>
                    <small class="text-muted d-block">Only your college\'s fields are listed (no Engineering fields for a CS user).</small>',
                'actions' => [
                    'Add, edit, activate/deactivate specializations (admins/chairs)',
                    'Assign specializations to faculty and to your groups (faculty)',
                ],
                'steps' => [
                    'Open the specialization pool',
                    'Add or pick a specialization',
                    'Assign it to the relevant user or group',
                ],
                'tip' => ['type' => 'info', 'text' => 'The specialization list is scoped to your college, so a CS user no longer sees Engineering fields (and vice-versa). The super admin sees every college.'],
            ],
            'rubrics' => [
                'icon' => 'fa-clipboard-list',
                'title' => 'Rubrics',
                'summary' => 'Design evaluation rubrics with criteria and scoring levels.',
                'audiences' => ['admin', 'chair'],
                'overview' => 'The Rubrics tab builds evaluation instruments with customisable criteria and scoring, either numerical or pass/fail, and assigns them to programs.',
                'snippet' => '
                    <div class="d-flex justify-content-end mb-2"><button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add New Rubric</button></div>
                    <table class="table table-sm table-bordered mb-2 bg-white align-middle">
                      <thead class="table-light"><tr><th>Name</th><th>Type</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
                      <tbody>
                        <tr><td>Manuscript Evaluation</td><td><span class="badge bg-secondary">Numerical</span></td><td><span class="badge bg-success">Active</span></td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                        <tr><td>Defense Pass/Fail</td><td><span class="badge bg-secondary">Pass/Fail</span></td><td><span class="badge bg-success">Active</span></td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                      </tbody>
                    </table>
                    <div class="border rounded bg-white p-2"><small class="text-muted">Criteria preview &mdash; Content: Excellent 10 / Good 7 / Fair 4</small></div>',
                'actions' => [
                    'Create numerical or pass/fail rubrics',
                    'Define criteria, quality levels and point values',
                    'Assign rubrics to programs',
                ],
                'steps' => [
                    'Click "Add Rubric" and choose the type',
                    'Define quality levels and criteria',
                    'Configure scoring and assign to programs',
                    'Preview, then save',
                ],
                'tip' => ['type' => 'warning', 'text' => 'Plan the structure before saving - changing criteria later can affect existing evaluations.'],
            ],
            'rubric-groups' => [
                'icon' => 'fa-layer-group',
                'title' => 'Rubric Groups',
                'summary' => 'Combine rubrics into weighted evaluation frameworks.',
                'audiences' => ['admin', 'chair', 'faculty'],
                'overview' => 'Rubric Groups bundle multiple rubrics into a single weighted framework used to evaluate a defense, so panelists score consistently.',
                'snippet' => '
                    <div class="d-flex justify-content-end mb-2"><button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Rubric Group</button></div>
                    <table class="table table-sm table-bordered mb-2 bg-white align-middle">
                      <thead class="table-light"><tr><th>Name</th><th>Description</th><th>Rubric Count</th><th class="text-center">Actions</th></tr></thead>
                      <tbody>
                        <tr><td>Final Defense Framework</td><td>Manuscript + presentation + Q&amp;A</td><td><span class="badge bg-secondary">3</span></td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                      </tbody>
                    </table>
                    <div class="border rounded bg-white p-2">
                      <small class="text-muted">Weights inside the group:</small>
                      <div class="d-flex justify-content-between"><span>Manuscript</span><span class="fw-semibold">40%</span></div>
                      <div class="d-flex justify-content-between"><span>Presentation</span><span class="fw-semibold">30%</span></div>
                      <div class="d-flex justify-content-between"><span>Q &amp; A</span><span class="fw-semibold">30%</span></div>
                      <div class="d-flex justify-content-between border-top pt-1 mt-1"><span class="fw-bold">Total</span><span class="fw-bold text-success">100%</span></div>
                    </div>',
                'actions' => [
                    'Create rubric groups',
                    'Add rubrics and assign percentage weights',
                    'Reorder rubrics within a group',
                ],
                'steps' => [
                    'Click "Add Rubric Group" and name it',
                    'Add rubrics from the available list',
                    'Assign weights totalling 100%',
                    'Save the group',
                ],
                'tip' => ['type' => 'info', 'text' => 'Weights let you emphasise the criteria that matter most (e.g. 40% content, 30% presentation, 30% Q&A).'],
            ],
            'evaluations' => [
                'icon' => 'fa-star',
                'title' => 'Evaluations',
                'summary' => 'Review scoring breakdowns and performance analytics.',
                'audiences' => ['admin', 'chair', 'faculty'],
                'overview' => 'The Evaluations tab shows evaluation results, switchable between an evaluator view and an aggregated student view, and highlights missing or incomplete evaluations.',
                'snippet' => '
                    <div class="btn-group btn-group-sm mb-2">
                      <button class="btn btn-primary">Evaluator View</button>
                      <button class="btn btn-outline-primary">Student View</button>
                    </div>
                    <table class="table table-sm table-bordered mb-0 bg-white">
                      <tbody>
                        <tr><td>Juan Dela Cruz</td><td>88 / 100</td><td><span class="badge bg-success">Complete</span></td></tr>
                        <tr><td>Maria Santos</td><td>&mdash;</td><td><span class="badge bg-warning text-dark">Pending</span></td></tr>
                      </tbody>
                    </table>',
                'actions' => [
                    'View individual evaluations by evaluator',
                    'View aggregated scores and feedback per student',
                    'Spot missing or incomplete evaluations',
                ],
                'steps' => [
                    'Use "Change View" to switch perspectives',
                    'Inspect evaluator submissions and dates',
                    'Switch to student view for averages and comments',
                ],
                'tip' => ['type' => 'info', 'text' => 'Use both views together for detail and summary.'],
            ],
            'requirements' => [
                'icon' => 'fa-tasks',
                'title' => 'Requirements',
                'summary' => 'Define submission requirements, deadlines and rules.',
                'audiences' => ['admin', 'chair'],
                'overview' => 'The Requirements tab defines what groups must submit, by when, and with what rules. Groups then submit and track these through the Requirement Checker.',
                'snippet' => '
                    <div class="d-flex justify-content-end mb-2"><button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Requirement</button></div>
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-1 bg-white align-middle">
                      <thead class="table-light"><tr><th>Name</th><th>Defense Type</th><th>Multi-Submit</th><th>Due Date</th><th class="text-center">Action</th></tr></thead>
                      <tbody>
                        <tr><td>Manuscript</td><td><span class="badge bg-success">Final Defense</span></td><td><span class="badge bg-warning text-dark">No</span></td><td>Jun 30, 2026</td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                        <tr><td>Title Proposal</td><td><span class="badge bg-info">Title Proposal</span></td><td><span class="badge bg-success">Yes (Max: 3)</span></td><td>Jun 1, 2026</td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                      </tbody>
                    </table></div>
                    <small class="text-muted">"No submission" is only counted once a deadline has passed.</small>',
                'actions' => [
                    'Add, edit and delete requirements',
                    'Choose the defense type, deadline and whether multiple submissions are allowed',
                    'Attach a template file for groups to follow',
                    'Track completion across groups',
                ],
                'steps' => [
                    'Create a requirement with name, description and deadline',
                    'Configure file rules and submission limits',
                    'Monitor which groups have submitted',
                ],
                'tip' => ['type' => 'warning', 'text' => 'A group is only flagged "No submission" after the requirement\'s deadline has passed - before then it is simply not yet due.'],
            ],
            'program-requirements' => [
                'icon' => 'fa-clipboard-check',
                'title' => 'Program Requirements',
                'summary' => 'Define requirements specific to your program.',
                'audiences' => ['chair'],
                'overview' => 'Program Requirements lets a program chair tailor submission requirements for their own program, complementing the system-wide requirements.',
                'snippet' => '
                    <table class="table table-sm table-bordered mb-1 bg-white">
                      <thead class="table-light"><tr><th>Requirement</th><th>Deadline</th><th>State</th></tr></thead>
                      <tbody>
                        <tr><td>Capstone Poster</td><td>Due Jul 15, 2026</td><td><span class="badge bg-success">Open</span></td></tr>
                      </tbody>
                    </table>
                    <small class="text-muted">Applies only to your program\'s groups.</small>',
                'actions' => [
                    'Add program-specific requirements',
                    'Edit deadlines and rules',
                ],
                'steps' => [
                    'Open Program Requirements',
                    'Add or edit a requirement for your program',
                    'Save and monitor completion',
                ],
                'tip' => ['type' => 'info', 'text' => 'These complement the system-wide requirements and apply to your program\'s groups.'],
            ],
            'users' => [
                'icon' => 'fa-users',
                'title' => 'Users',
                'summary' => 'Manage administrators, students and faculty accounts.',
                'audiences' => ['admin', 'chair'],
                'overview' => 'The Users tab manages all system accounts with full create, edit and delete operations, plus CSV bulk import. Chairs see only users within their college.',
                'snippet' => '
                    <div class="d-flex gap-2 mb-2 flex-wrap">
                      <input class="form-control form-control-sm" style="max-width:170px" placeholder="Search users...">
                      <select class="form-select form-select-sm" style="max-width:120px"><option>All types</option></select>
                      <button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add User</button>
                      <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-import me-1"></i>Bulk</button>
                    </div>
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 bg-white align-middle">
                      <thead class="table-light"><tr><th>Username</th><th>Email</th><th>Name</th><th>User Type</th><th class="text-center">Action</th></tr></thead>
                      <tbody>
                        <tr><td>jdelacruz</td><td>juan@email.com</td><td>Juan Dela Cruz</td><td><span class="badge bg-success">Faculty</span></td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                        <tr><td>msantos</td><td>maria@email.com</td><td>Maria Santos</td><td><span class="badge bg-primary">Student</span></td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                      </tbody>
                    </table></div>',
                'actions' => [
                    'Add, edit and delete user accounts',
                    'Bulk import users from a CSV file',
                    'Search and filter by name, email or role',
                ],
                'steps' => [
                    'Search or filter to find a user',
                    'Click "Add User" to create an account and assign a role',
                    'Use "Bulk Add Users" to import several at once',
                    'Use the row action buttons to edit or remove a user',
                ],
                'tip' => ['type' => 'warning', 'text' => 'Deleting a user cannot be undone. Program chairs only see users within their own college.'],
            ],
            'programs' => [
                'icon' => 'fa-graduation-cap',
                'title' => 'Programs',
                'summary' => 'Manage academic programs and their colleges.',
                'audiences' => ['admin', 'chair'],
                'overview' => 'The Programs tab organises academic programs under their parent colleges. This mapping drives scoping across the whole system.',
                'snippet' => '
                    <div class="d-flex justify-content-end mb-2"><button class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Program</button></div>
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 bg-white align-middle">
                      <thead class="table-light"><tr><th>College</th><th>Program Name</th><th>Specialization</th><th class="text-center">Actions</th></tr></thead>
                      <tbody>
                        <tr><td>College of Science</td><td>BS Computer Science</td><td>&mdash;</td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                        <tr><td>College of Engineering</td><td>BS Civil Engineering</td><td>Structural</td><td class="text-center text-nowrap"><button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></td></tr>
                      </tbody>
                    </table></div>',
                'actions' => [
                    'View programs grouped by college',
                    'Add, edit and delete programs',
                ],
                'steps' => [
                    'Expand a college header to see its programs',
                    'Click "Add Program" and pick the college',
                    'Use edit to update a program',
                ],
                'tip' => ['type' => 'info', 'text' => 'Program -> college mapping drives scoping across the whole dashboard, so keep it accurate.'],
            ],
            'team-management' => [
                'icon' => 'fa-users-cog',
                'title' => 'Team Management',
                'summary' => 'Advanced membership and adviser administration.',
                'audiences' => ['admin', 'chair'],
                'overview' => 'Team Management provides deeper control over group membership, roles and adviser assignment than the Groups tab.',
                'snippet' => '
                    <div class="border rounded p-2 bg-white">
                      <div class="fw-semibold mb-2">Team Alpha</div>
                      <span class="badge bg-primary me-1">Reyes (Adviser)</span>
                      <span class="badge bg-secondary me-1">Cruz (Leader)</span>
                      <span class="badge bg-secondary me-1">Lim (Member)</span>
                      <button class="btn btn-sm btn-outline-primary ms-1"><i class="fas fa-user-plus me-1"></i>Manage</button>
                    </div>',
                'actions' => [
                    'Adjust group members and roles',
                    'Reassign advisers',
                ],
                'steps' => [
                    'Select a group',
                    'Update members or the adviser',
                    'Save your changes',
                ],
                'tip' => ['type' => 'info', 'text' => 'Use this when the Groups tab does not give you enough control over membership.'],
            ],
            'content-management' => [
                'icon' => 'fa-cogs',
                'title' => 'Content Management',
                'summary' => 'System settings and website/page content.',
                'audiences' => ['admin'],
                'overview' => 'Content Management (Environment Variables) configures system settings and editable website content such as pages and announcements.',
                'snippet' => '
                    <div class="d-flex gap-2 mb-2">
                      <select class="form-select form-select-sm" style="max-width:220px"><option>System Settings</option><option>Page Content</option></select>
                    </div>
                    <table class="table table-sm table-bordered mb-0 bg-white">
                      <tbody>
                        <tr><td>SMTP Host</td><td>smtp.example.com</td></tr>
                        <tr><td>Site Title</td><td>Thesis Scheduler</td></tr>
                      </tbody>
                    </table>',
                'actions' => [
                    'Configure system settings (email, auth, defaults)',
                    'Manage website pages and announcements',
                ],
                'steps' => [
                    'Pick a content category',
                    'Edit settings or page content',
                    'Save your changes',
                ],
                'tip' => ['type' => 'danger', 'text' => 'Incorrect system settings can affect functionality - change them carefully.'],
            ],
            'faq' => [
                'icon' => 'fa-circle-question',
                'title' => 'FAQ & Troubleshooting',
                'summary' => 'Answers to the most common questions and issues.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'Quick answers to the things people ask most often.',
                'body_html' => '
                    <div class="mb-3"><strong>A feature is missing from my sidebar.</strong><br><span class="text-muted">Your role may not have access to it, or it is scoped to a different college/program.</span></div>
                    <div class="mb-3"><strong>No defenses show on my Overview calendar.</strong><br><span class="text-muted">Only approved or finalized schedules within your scope appear. Ask your chair or research teacher to finalize them.</span></div>
                    <div class="mb-3"><strong>A group is marked "No submission" but the deadline has not passed.</strong><br><span class="text-muted">That should not happen now &mdash; "No submission" only shows after the deadline. Refresh; if it persists, check the requirement\'s due date.</span></div>
                    <div class="mb-3"><strong>I can\'t find a field of specialization in the list.</strong><br><span class="text-muted">The list is scoped to your college. Ask an admin to add it for your college if it is missing.</span></div>
                    <div class="mb-3"><strong>I can\'t assign a faculty member to a section.</strong><br><span class="text-muted">A section can have one teacher, but a teacher can hold many sections. Check the section is not already taken.</span></div>
                    <div><strong>The scheduler can\'t place a group.</strong><br><span class="text-muted">It needs enough eligible panelists. Check Field of Specialization, Allied Programs, and faculty working hours.</span></div>',
                'tip' => ['type' => 'info', 'text' => 'Still stuck? Contact your program chair or system administrator.'],
            ],
            'glossary' => [
                'icon' => 'fa-book-open',
                'title' => 'Glossary',
                'summary' => 'Definitions of key terms used across the system.',
                'audiences' => ['admin', 'chair', 'faculty', 'student'],
                'overview' => 'Key terms used throughout the system.',
                'body_html' => '
                    <dl class="row mb-0">
                      <dt class="col-sm-4">Defense types</dt><dd class="col-sm-8">The title proposal, pre-oral and final defense stages a group progresses through.</dd>
                      <dt class="col-sm-4">Panel</dt><dd class="col-sm-8">The three faculty members who evaluate a defense.</dd>
                      <dt class="col-sm-4">External / Validator</dt><dd class="col-sm-8">The third panel seat, often from an allied or external program.</dd>
                      <dt class="col-sm-4">Allied program</dt><dd class="col-sm-8">A program treated as adjacent expertise for panel assignment.</dd>
                      <dt class="col-sm-4">Field of specialization</dt><dd class="col-sm-8">An area of expertise assigned to faculty and groups.</dd>
                      <dt class="col-sm-4">Rubric group</dt><dd class="col-sm-8">A weighted bundle of rubrics used to score a defense.</dd>
                      <dt class="col-sm-4">Finalized schedule</dt><dd class="col-sm-8">An approved schedule locked from further changes.</dd>
                    </dl>',
            ],
        ];
    }
}

if (!function_exists('guide_render_body')) {
    /**
     * Render the shared body of a module (used by both the modal and the wiki article).
     * Text fields are escaped; `snippet` is trusted author HTML rendered verbatim.
     */
    function guide_render_body(array $m): string
    {
        $tipClasses = [
            'info' => 'alert-info',
            'success' => 'alert-success',
            'warning' => 'alert-warning',
            'danger' => 'alert-danger',
        ];

        $html = '<p class="lead">' . htmlspecialchars($m['overview']) . '</p>';

        if (!empty($m['snippet'])) {
            $html .= '<div class="guide-snippet-preview">'
                . '<span class="guide-snippet-label"><i class="fas fa-hand-pointer me-1"></i>Try it</span>'
                . '<div class="guide-snippet">' . $m['snippet'] . '</div>'
                . '</div>';
        }

        // Free-form manual content (trusted author HTML), e.g. reference tables,
        // FAQ entries or a glossary.
        if (!empty($m['body_html'])) {
            $html .= $m['body_html'];
        }

        if (!empty($m['actions'])) {
            $html .= '<h6><i class="fas fa-tools me-2 text-primary"></i>What you can do</h6><ul>';
            foreach ($m['actions'] as $action) {
                $html .= '<li>' . htmlspecialchars($action) . '</li>';
            }
            $html .= '</ul>';
        }

        if (!empty($m['steps'])) {
            $html .= '<h6><i class="fas fa-step-forward me-2 text-primary"></i>Step-by-step</h6><ol>';
            foreach ($m['steps'] as $step) {
                $html .= '<li>' . htmlspecialchars($step) . '</li>';
            }
            $html .= '</ol>';
        }

        if (!empty($m['tip'])) {
            $alertClass = $tipClasses[$m['tip']['type']] ?? 'alert-info';
            $html .= '<div class="alert ' . $alertClass . '"><i class="fas fa-lightbulb me-2"></i>'
                . htmlspecialchars($m['tip']['text']) . '</div>';
        }

        return $html;
    }
}

if (!function_exists('guide_render')) {
    /**
     * Render the entire guide component (style + toggle + modern cards + wiki +
     * modals + script) filtered to the given audience. Designed to be dropped
     * inside a tab-pane on either the dashboard or the home page.
     */
    function guide_render(?string $audience = null): void
    {
        $audience = $audience ?: guide_resolve_audience();

        $modules = array_filter(guide_get_modules(), static function ($m) use ($audience) {
            return in_array($audience, $m['audiences'], true);
        });
        ?>
        <style>
        .guide-snippet-preview {
            position: relative;
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: .5rem;
            padding: 1.25rem 1rem 1rem;
            margin: .25rem 0 1.25rem;
            background: #f8f9fa;
        }
        .guide-snippet-preview .guide-snippet-label {
            position: absolute;
            top: -.65rem;
            left: .75rem;
            background: #fff;
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 1rem;
            font-size: .7rem;
            padding: .05rem .6rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        /* Previews are interactive demos, isolated from the real app. */
        .guide-snippet .btn { cursor: pointer; }
        .guide-snippet input, .guide-snippet textarea { cursor: text; }
        .guide-snippet select { cursor: pointer; }
        </style>

        <div class="container-fluid py-4 content-container">
            <!-- Header with title, description and the view toggle -->
            <div class="row mb-4 align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-2">User Manual</h3>
                    <p class="text-muted mb-0">The complete manual for the features available to you, with previews of what each screen looks like and shared concepts like roles and defense panels. Switch between a spacious card view and a searchable wiki.</p>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0 text-lg-end">
                    <div class="btn-group" role="group" aria-label="Guide view mode">
                        <button type="button" class="btn btn-primary" id="guideModeModernBtn" data-mode="modern">
                            <i class="fas fa-th-large me-1"></i>Modern
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="guideModeWikiBtn" data-mode="wiki">
                            <i class="fas fa-book me-1"></i>Wiki
                        </button>
                    </div>
                </div>
            </div>

            <!-- ===================== MODERN (card) VIEW ===================== -->
            <div id="guideModernView">
                <div class="row g-4">
                    <?php foreach ($modules as $key => $m): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 guide-card" data-bs-toggle="modal" data-bs-target="#guideModal-<?php echo htmlspecialchars($key); ?>" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas <?php echo htmlspecialchars($m['icon']); ?> fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($m['title']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($m['summary']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ===================== WIKI VIEW ===================== -->
            <div id="guideWikiView" style="display: none;">
                <div class="row g-4">
                    <div class="col-lg-4 col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="guideWikiSearch" placeholder="Search the guide...">
                                    </div>
                                </div>
                                <div class="list-group guide-wiki-nav" id="guideWikiNav">
                                    <?php $first = true; foreach ($modules as $key => $m): ?>
                                        <button type="button"
                                                class="list-group-item list-group-item-action guide-wiki-nav-item<?php echo $first ? ' active' : ''; ?>"
                                                data-target="guideArticle-<?php echo htmlspecialchars($key); ?>">
                                            <i class="fas <?php echo htmlspecialchars($m['icon']); ?> me-2"></i><?php echo htmlspecialchars($m['title']); ?>
                                        </button>
                                    <?php $first = false; endforeach; ?>
                                </div>
                                <p class="text-muted small mt-2 mb-0" id="guideWikiNoResults" style="display: none;">No matching topics.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-xl-9">
                        <div class="card">
                            <div class="card-body">
                                <?php $first = true; foreach ($modules as $key => $m): ?>
                                    <article class="guide-wiki-article" id="guideArticle-<?php echo htmlspecialchars($key); ?>" style="<?php echo $first ? '' : 'display: none;'; ?>">
                                        <h4 class="mb-3"><i class="fas <?php echo htmlspecialchars($m['icon']); ?> me-2 text-primary"></i><?php echo htmlspecialchars($m['title']); ?></h4>
                                        <?php echo guide_render_body($m); ?>
                                    </article>
                                <?php $first = false; endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== MODERN VIEW MODALS ===================== -->
        <?php foreach ($modules as $key => $m): ?>
            <div class="modal fade" id="guideModal-<?php echo htmlspecialchars($key); ?>" tabindex="-1" aria-labelledby="guideModalLabel-<?php echo htmlspecialchars($key); ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="guideModalLabel-<?php echo htmlspecialchars($key); ?>">
                                <i class="fas <?php echo htmlspecialchars($m['icon']); ?> me-2"></i><?php echo htmlspecialchars($m['title']); ?> Guide
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php echo guide_render_body($m); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <script>
        (function () {
            var modernBtn = document.getElementById('guideModeModernBtn');
            var wikiBtn = document.getElementById('guideModeWikiBtn');
            var modernView = document.getElementById('guideModernView');
            var wikiView = document.getElementById('guideWikiView');

            function setMode(mode) {
                var isWiki = (mode === 'wiki');
                if (modernView) modernView.style.display = isWiki ? 'none' : '';
                if (wikiView) wikiView.style.display = isWiki ? '' : 'none';
                if (modernBtn) {
                    modernBtn.classList.toggle('btn-primary', !isWiki);
                    modernBtn.classList.toggle('btn-outline-primary', isWiki);
                }
                if (wikiBtn) {
                    wikiBtn.classList.toggle('btn-primary', isWiki);
                    wikiBtn.classList.toggle('btn-outline-primary', !isWiki);
                }
            }

            if (modernBtn) modernBtn.addEventListener('click', function () { setMode('modern'); });
            if (wikiBtn) wikiBtn.addEventListener('click', function () { setMode('wiki'); });

            var navItems = document.querySelectorAll('.guide-wiki-nav-item');
            var articles = document.querySelectorAll('.guide-wiki-article');

            navItems.forEach(function (item) {
                item.addEventListener('click', function () {
                    var targetId = item.getAttribute('data-target');
                    navItems.forEach(function (n) { n.classList.remove('active'); });
                    item.classList.add('active');
                    articles.forEach(function (article) {
                        article.style.display = (article.id === targetId) ? '' : 'none';
                    });
                });
            });

            var search = document.getElementById('guideWikiSearch');
            var noResults = document.getElementById('guideWikiNoResults');
            if (search) {
                search.addEventListener('input', function () {
                    var term = search.value.trim().toLowerCase();
                    var visibleCount = 0;
                    navItems.forEach(function (item) {
                        var match = item.textContent.toLowerCase().indexOf(term) !== -1;
                        item.style.display = match ? '' : 'none';
                        if (match) visibleCount++;
                    });
                    if (noResults) noResults.style.display = (visibleCount === 0) ? '' : 'none';
                });
            }

            // ---- Interactive (but isolated) preview demos ----
            // Snippets are clickable/typeable sandboxes. We stop every event from
            // bubbling out so a demo control can never trigger the real app.
            document.querySelectorAll('.guide-snippet').forEach(function (snippet) {
                snippet.addEventListener('submit', function (e) { e.preventDefault(); e.stopPropagation(); });
                snippet.addEventListener('change', function (e) { e.stopPropagation(); });
                snippet.addEventListener('input', function (e) { e.stopPropagation(); });

                snippet.addEventListener('click', function (e) {
                    e.stopPropagation();

                    // Tab switching (e.g. the grading sheet's Research Paper / Score Sheet tabs).
                    var tab = e.target.closest('[data-demo-tab]');
                    if (tab && snippet.contains(tab)) {
                        e.preventDefault();
                        var nav = tab.closest('.nav, .nav-tabs');
                        if (nav) {
                            nav.querySelectorAll('[data-demo-tab]').forEach(function (t) { t.classList.remove('active'); });
                        }
                        tab.classList.add('active');
                        var paneKey = tab.getAttribute('data-demo-tab');
                        snippet.querySelectorAll('[data-demo-pane]').forEach(function (pane) {
                            pane.style.display = (pane.getAttribute('data-demo-pane') === paneKey) ? '' : 'none';
                        });
                        return;
                    }

                    var btn = e.target.closest('button, a.btn');
                    if (!btn || !snippet.contains(btn)) return;
                    e.preventDefault();

                    var group = btn.closest('.btn-group, .btn-group-sm');
                    if (group) {
                        // Toggle behaviour: light up the clicked button in the group.
                        group.querySelectorAll('.btn').forEach(function (sib) {
                            sib.classList.remove('btn-primary', 'active');
                            sib.classList.add('btn-outline-primary');
                        });
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-primary', 'active');
                    } else {
                        // Brief press feedback for standalone demo buttons.
                        btn.classList.add('active');
                        setTimeout(function () { btn.classList.remove('active'); }, 180);
                    }
                });
            });
        })();
        </script>
        <?php
    }
}
