<?php
/**
 * Dashboard Guide tab.
 *
 * Single source of truth: every module is described once in $guideModules and
 * rendered into BOTH the "Modern" card/modal view and the "Wiki" article view.
 * Modules are filtered by the current user's audience so each user type only
 * sees guides for the tabs that are actually available to them.
 */

// Resolve the current audience from the session.
$guideUserType = (int)($_SESSION['usertype'] ?? -1);
$guideIsChair  = ($guideUserType === 0 && (int)($_SESSION['program_chair'] ?? 0) === 1);

if ($guideIsChair) {
    $guideAudience = 'chair';
} elseif ($guideUserType === 0) {
    $guideAudience = 'admin';
} elseif ($guideUserType === 2) {
    $guideAudience = 'faculty';
} else {
    $guideAudience = 'student';
}

/**
 * Module catalogue. `audiences` controls who sees each guide and mirrors the
 * tabs included per dashboard branch in index.php.
 */
$guideModules = [
    'overview' => [
        'icon' => 'fa-chart-line',
        'title' => 'Overview',
        'summary' => 'System metrics, requirement progress and the upcoming-defense calendar at a glance.',
        'audiences' => ['admin', 'chair', 'faculty', 'student'],
        'overview' => 'The Overview tab is your daily landing page. It summarises users, groups, research titles, requirement progress and the defenses coming up.',
        'actions' => [
            'See total counts of users, groups and research titles',
            'Track requirement completion across groups',
            'View the upcoming-defense calendar',
            'Open any metric card for a detailed breakdown',
        ],
        'steps' => [
            'Review the metric cards at the top of the page',
            'Click a card to open its detailed modal',
            'Use the requirement-progress section to spot groups that need attention',
            'Check the defense calendar for what is coming up',
        ],
        'tip' => ['type' => 'info', 'text' => 'Requirement progress now reflects real submission status: a group is only counted as "No submission" once a requirement\'s deadline has passed. The defense calendar shows only approved/finalized schedules within your scope.'],
    ],
    'users' => [
        'icon' => 'fa-users',
        'title' => 'Users',
        'summary' => 'Manage administrators, students and faculty accounts.',
        'audiences' => ['admin', 'chair'],
        'overview' => 'The Users tab manages all system accounts with full create, edit and delete operations, plus CSV bulk import.',
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
    'teams' => [
        'icon' => 'fa-user-friends',
        'title' => 'Groups',
        'summary' => 'Create research groups, assign advisers and track progress.',
        'audiences' => ['admin', 'chair', 'faculty', 'student'],
        'overview' => 'The Groups tab is where research groups are created, members and advisers are assigned, and progress is tracked through the thesis lifecycle.',
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
    'thesis-topics' => [
        'icon' => 'fa-lightbulb',
        'title' => 'Thesis Topics',
        'summary' => 'Explore and manage research topics with AI-assisted suggestions.',
        'audiences' => ['admin', 'chair', 'faculty', 'student'],
        'overview' => 'The Thesis Topics tab helps you discover and curate research topics, with optional AI-powered recommendations and impact analysis.',
        'actions' => [
            'Search AI-generated topic suggestions by research field',
            'Add, edit and delete thesis topics',
            'Filter topics by category',
        ],
        'steps' => [
            'Choose "Search Topics" or "Manage Topics"',
            'In Search mode, pick a field and get suggestions',
            'Add promising topics to the database',
            'In Manage mode, edit or remove existing topics',
        ],
        'tip' => ['type' => 'success', 'text' => 'Use the AI suggestions as a starting point, then refine topics to fit your program.'],
    ],
    'research-titles' => [
        'icon' => 'fa-file-alt',
        'title' => 'Research Titles',
        'summary' => 'Manage group titles and their approval status.',
        'audiences' => ['admin', 'chair', 'faculty', 'student'],
        'overview' => 'The Research Titles tab tracks each group\'s title through the submission, review and approval workflow.',
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
        'tip' => ['type' => 'info', 'text' => 'Titles typically flow Submitted → Under Review → Approved/Rejected.'],
    ],
    'programs' => [
        'icon' => 'fa-graduation-cap',
        'title' => 'Programs',
        'summary' => 'Manage academic programs and their colleges.',
        'audiences' => ['admin', 'chair'],
        'overview' => 'The Programs tab organises academic programs under their parent colleges.',
        'actions' => [
            'View programs grouped by college',
            'Add, edit and delete programs',
        ],
        'steps' => [
            'Expand a college header to see its programs',
            'Click "Add Program" and pick the college',
            'Use edit to update a program',
        ],
        'tip' => ['type' => 'info', 'text' => 'Program → college mapping drives scoping across the whole dashboard, so keep it accurate.'],
    ],
    'allied-programs' => [
        'icon' => 'fa-sitemap',
        'title' => 'Allied Programs',
        'summary' => 'Configure cross-program adjacencies used by the scheduler.',
        'audiences' => ['admin', 'chair'],
        'overview' => 'Allied Programs defines which programs count as "adjacent" expertise. The defense scheduler uses these links when a same-program panelist is unavailable, and for the external/validator seat.',
        'actions' => [
            'Link a program to one or more allied programs',
            'Remove an allied relationship',
        ],
        'steps' => [
            'Select a base program',
            'Add the programs that should count as allied',
            'Save — the scheduler will use these as panelist fallbacks',
        ],
        'tip' => ['type' => 'warning', 'text' => 'If the allied list is empty, the scheduler falls back to same-college panelists only. Configure it to widen the valid panelist pool.'],
    ],
    'faculty-assignments' => [
        'icon' => 'fa-chalkboard-teacher',
        'title' => 'Faculty Assignments',
        'summary' => 'Assign subject teachers to class sections.',
        'audiences' => ['admin', 'chair'],
        'overview' => 'The Faculty Assignments tab links faculty members to the class sections they teach. These assignments feed the scheduler\'s class-conflict checks.',
        'actions' => [
            'Assign a faculty member to a section',
            'Assign one faculty member to multiple sections',
            'Remove an assignment',
        ],
        'steps' => [
            'Pick a section from the dropdown',
            'Pick a subject teacher',
            'Click "Assign" — the same teacher can be assigned to other sections too',
            'Use "Remove" to undo an assignment',
        ],
        'tip' => ['type' => 'info', 'text' => 'A single faculty member can now handle multiple classes. The teacher list is scoped to your college/program.'],
    ],
    'specialization-management' => [
        'icon' => 'fa-brain',
        'title' => 'Field of Specialization',
        'summary' => 'Maintain the specialization pool and assign fields to users and groups.',
        'audiences' => ['admin', 'chair', 'faculty', 'student'],
        'overview' => 'This area manages the pool of specializations (fields of expertise) and assigns them to faculty and groups. The scheduler uses these to align panelists with a group\'s expertise.',
        'actions' => [
            'Add, edit, activate/deactivate specializations (admins/chairs)',
            'Assign specializations to faculty and groups',
        ],
        'steps' => [
            'Open the specialization pool',
            'Add or pick a specialization',
            'Assign it to the relevant user or group',
        ],
        'tip' => ['type' => 'info', 'text' => 'The specialization list is scoped to your college, so a CS user no longer sees Engineering fields (and vice-versa). The super admin sees every college.'],
    ],
    'defense-schedules' => [
        'icon' => 'fa-calendar-alt',
        'title' => 'Defense Schedules',
        'summary' => 'Create, auto-generate and manage defense sessions.',
        'audiences' => ['admin', 'chair', 'faculty', 'student'],
        'overview' => 'The Defense Schedules tab manages defense sessions. Admins/chairs can auto-generate conflict-free schedules with the genetic-algorithm scheduler or add sessions manually.',
        'actions' => [
            'Configure scheduler settings (slots, rooms, dates, duration)',
            'Auto-generate schedules for many groups',
            'Add or edit individual sessions',
            'Approve and finalize schedules',
        ],
        'steps' => [
            'Open "Scheduler Settings" and set slots, rooms and date range',
            'Click "Generate Defense Schedule" to auto-create sessions',
            'Review and adjust the generated sessions',
            'Approve/finalize so they appear on dashboards',
        ],
        'tip' => ['type' => 'success', 'text' => 'Only approved or finalized schedules show on the Overview calendar, and each user only sees the ones within their scope.'],
    ],
    'rubrics' => [
        'icon' => 'fa-clipboard-list',
        'title' => 'Rubrics',
        'summary' => 'Design evaluation rubrics with criteria and scoring levels.',
        'audiences' => ['admin', 'chair'],
        'overview' => 'The Rubrics tab builds evaluation instruments with customisable criteria and scoring, either numerical or pass/fail.',
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
        'tip' => ['type' => 'warning', 'text' => 'Plan the structure before saving — changing criteria later can affect existing evaluations.'],
    ],
    'rubric-groups' => [
        'icon' => 'fa-layer-group',
        'title' => 'Rubric Groups',
        'summary' => 'Combine rubrics into weighted evaluation frameworks.',
        'audiences' => ['admin', 'chair', 'faculty', 'student'],
        'overview' => 'Rubric Groups bundle multiple rubrics into a single weighted framework used to evaluate a defense.',
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
        'audiences' => ['admin', 'chair'],
        'overview' => 'The Evaluations tab shows evaluation results, switchable between an evaluator view and an aggregated student view.',
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
        'summary' => 'Manage submission requirements, deadlines and completion.',
        'audiences' => ['admin', 'chair', 'faculty', 'student'],
        'overview' => 'The Requirements tab defines what groups must submit, by when, and tracks completion.',
        'actions' => [
            'Add, edit and delete requirements',
            'Set deadlines and submission rules',
            'Track completion across groups',
        ],
        'steps' => [
            'Create a requirement with name, description and deadline',
            'Configure file rules and submission limits',
            'Monitor which groups have submitted',
        ],
        'tip' => ['type' => 'warning', 'text' => 'A group is only flagged "No submission" after the requirement\'s deadline has passed — before then it is simply not yet due.'],
    ],
    'program-requirements' => [
        'icon' => 'fa-clipboard-check',
        'title' => 'Program Requirements',
        'summary' => 'Define requirements specific to your program.',
        'audiences' => ['chair'],
        'overview' => 'Program Requirements lets a program chair tailor submission requirements for their own program.',
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
    'team-management' => [
        'icon' => 'fa-users-cog',
        'title' => 'Team Management',
        'summary' => 'Advanced membership and adviser administration.',
        'audiences' => ['admin', 'chair'],
        'overview' => 'Team Management provides deeper control over group membership, roles and adviser assignment.',
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
        'actions' => [
            'Configure system settings (email, auth, defaults)',
            'Manage website pages and announcements',
        ],
        'steps' => [
            'Pick a content category',
            'Edit settings or page content',
            'Save your changes',
        ],
        'tip' => ['type' => 'danger', 'text' => 'Incorrect system settings can affect functionality — change them carefully.'],
    ],
];

// Filter to the modules visible to this audience.
$guideVisibleModules = array_filter($guideModules, static function ($m) use ($guideAudience) {
    return in_array($guideAudience, $m['audiences'], true);
});

/**
 * Render the shared body of a module (used by both the modal and the wiki article).
 */
if (!function_exists('guide_render_body')) {
    function guide_render_body(array $m): string
    {
        $tipClasses = [
            'info' => 'alert-info',
            'success' => 'alert-success',
            'warning' => 'alert-warning',
            'danger' => 'alert-danger',
        ];

        $html  = '<p class="lead">' . htmlspecialchars($m['overview']) . '</p>';

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
?>
<div class="tab-pane fade" id="guide" role="tabpanel" aria-labelledby="guide-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title, description and the view toggle -->
        <div class="row mb-4 align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-2">Dashboard Guide</h3>
                <p class="text-muted mb-0">A complete manual for the tabs available to you. Switch between a spacious card view and a searchable wiki.</p>
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
                <?php foreach ($guideVisibleModules as $key => $m): ?>
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
                                <?php $first = true; foreach ($guideVisibleModules as $key => $m): ?>
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
                            <?php $first = true; foreach ($guideVisibleModules as $key => $m): ?>
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
</div>

<!-- ===================== MODERN VIEW MODALS ===================== -->
<?php foreach ($guideVisibleModules as $key => $m): ?>
    <div class="modal fade" id="guideModal-<?php echo htmlspecialchars($key); ?>" tabindex="-1" aria-labelledby="guideModalLabel-<?php echo htmlspecialchars($key); ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
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
    // ---- View toggle (Modern <-> Wiki) ----
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

    // ---- Wiki navigation ----
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

    // ---- Wiki search ----
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
})();
</script>
