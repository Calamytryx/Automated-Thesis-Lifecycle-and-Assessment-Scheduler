<!-- Research Titles Tab -->
<div class="tab-pane fade" id="research-titles" role="tabpanel"
    aria-labelledby="research-titles-tab">
    <div class="container-fluid py-4 content-container">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Research Titles Management</h3>
                <p class="text-muted">Manage research titles assigned to teams and track their approval status</p>
            </div>
        </div>

        <!-- Research Titles Management Controls -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-end align-items-center mb-3">
                    <button class="btn feature-btn add-btn" data-table="research_titles">
                        <i class="fas fa-plus"></i>Add Research Title
                    </button>
                </div>
            </div>
        </div>

        <!-- Research Titles Content -->
        <div class="row">
            <div class="col-12">
    <div class="table-responsive db-table-container">
        <table class="table table-bordered table-hover table-sm db-table">
            <thead>
                <tr>
                    <th class="d-none d-md-table-cell">Title</th>
                    <th class="d-table-cell d-md-none">Research Title</th>
                    <th class="d-none d-sm-table-cell">Team</th>
                    <th class="d-none d-lg-table-cell">Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Pagination logic
                $limit = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                $stmt = $pdo->prepare("SELECT * FROM research_titles LIMIT :limit OFFSET :offset");
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $researchTitles = $stmt->fetchAll();

                foreach ($researchTitles as $title):
                    $team = getTeamName($pdo, $title['team_id']);
                    $isApproved = $title['approved_at'] ? true : false;
                    $statusText = $isApproved ? 'Approved' : 'Pending';
                    $statusClass = $isApproved ? 'approved' : 'pending';
                    $statusDate = date('Y-m-d H:i:s', strtotime($title['updated_at']));
                ?>
                    <tr>
                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($title['title']); ?></td>
                        <td class="d-table-cell d-md-none">
                            <div class="fw-semibold"><?php echo htmlspecialchars($title['title']); ?></div>
                            <div class="text-muted small d-sm-none"><?php echo htmlspecialchars($team); ?></div>
                            <div class="d-lg-none mt-1">
                                <span class="status-badge <?php echo $statusClass; ?>" title="<?php echo $statusText . ' (' . $statusDate . ')'; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </div>
                        </td>
                        <td class="d-none d-sm-table-cell"><?php echo htmlspecialchars($team); ?></td>
                        <td class="d-none d-lg-table-cell">
                            <span class="status-badge <?php echo $statusClass; ?>" title="<?php echo $statusText . ' (' . $statusDate . ')'; ?>">
                                <?php echo $statusText; ?>
                            </span>
                        </td>
                        <td class="action-buttons text-center">
                            <button class="meatball-btn" data-title-id="<?php echo $title['id']; ?>" aria-label="Actions">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Pagination controls
    $stmt = $pdo->query("SELECT COUNT(*) FROM research_titles");
    $totalTitles = $stmt->fetchColumn();
    $totalPages = ceil($totalTitles / $limit);
    ?>

    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                    &#8249;
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                    &#8250;
                </a>
            </li>
        </ul>
    </nav>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to create meatball dropdowns for research titles
    function createMeatballDropdowns() {
        // Clear any existing dropdowns for research titles
        document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-"]').forEach(portal => {
            if (portal.querySelector('[data-table="research_titles"]')) {
                portal.remove();
            }
        });
        
        // Create dropdowns for each meatball button
        document.querySelectorAll('#research-titles .meatball-btn').forEach(btn => {
            const titleId = btn.getAttribute('data-title-id');
            
            if (!titleId) {
                console.error('No title ID found for meatball button');
                return;
            }
            
            // Create dropdown portal outside table
            const dropdownPortal = document.createElement('div');
            dropdownPortal.className = 'meatball-dropdown-portal';
            dropdownPortal.id = `dropdown-${titleId}`;
            dropdownPortal.style.cssText = `
                position: fixed;
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 9999;
                min-width: 120px;
                padding: 4px 0;
                display: none;
            `;
            dropdownPortal.innerHTML = `
                <button class="meatball-dropdown-item edit-item edit-btn" data-table="research_titles" data-id="${titleId}">
                    <i class="fas fa-edit"></i>
                    Edit
                </button>
                <button class="meatball-dropdown-item delete-item delete-btn" data-table="research_titles" data-id="${titleId}">
                    <i class="fas fa-trash-alt"></i>
                    Delete
                </button>
            `;
            document.body.appendChild(dropdownPortal);
        });
    }

    // Initial creation of dropdowns
    createMeatballDropdowns();

    // Re-create dropdowns when the research titles tab becomes active
    document.addEventListener('shown.bs.tab', function (e) {
        if (e.target.getAttribute('aria-controls') === 'research-titles') {
            setTimeout(createMeatballDropdowns, 100);
        }
    });

    // Also try creating dropdowns with delays in case content loads later
    setTimeout(createMeatballDropdowns, 500);
    setTimeout(createMeatballDropdowns, 1000);

    // Check if research titles tab is currently active
    function isResearchTitlesTabActive() {
        const researchTitlesTab = document.getElementById('research-titles');
        return researchTitlesTab && (researchTitlesTab.classList.contains('active') || researchTitlesTab.classList.contains('show'));
    }

    // Handle meatball button clicks specifically for research titles
    document.addEventListener('click', function(e) {
        const researchTitlesTab = document.getElementById('research-titles');
        
        // Only handle if we're in the research titles tab and it's active/visible
        if (!researchTitlesTab || (!researchTitlesTab.classList.contains('active') && !researchTitlesTab.classList.contains('show'))) {
            return;
        }
        
        // Handle meatball button clicks
        if (e.target.closest('.meatball-btn') && e.target.closest('#research-titles')) {
            e.preventDefault();
            e.stopPropagation();
            
            const btn = e.target.closest('.meatball-btn');
            const titleId = btn.getAttribute('data-title-id');
            
            if (!titleId) {
                console.error('No title ID found');
                return;
            }
            
            const dropdown = document.getElementById(`dropdown-${titleId}`);
            
            if (!dropdown) {
                console.error('Dropdown not found for title:', titleId);
                return;
            }
            
            const isCurrentlyOpen = dropdown.style.display === 'block';
            
            // Close all research title dropdowns first
            document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-"]').forEach(dd => {
                if (dd.id.startsWith('dropdown-') && dd.querySelector('[data-table="research_titles"]')) {
                    dd.style.display = 'none';
                }
            });
            
            // Toggle current dropdown
            if (!isCurrentlyOpen) {
                // Position the dropdown relative to the button
                const btnRect = btn.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const dropdownWidth = 120;
                
                // Calculate position
                let left = btnRect.right - dropdownWidth;
                let top = btnRect.bottom + 5;
                
                // Adjust for mobile screens
                if (viewportWidth < 768) {
                    // On mobile, center the dropdown below the button
                    left = btnRect.left + (btnRect.width / 2) - (dropdownWidth / 2);
                }
                
                // Ensure dropdown doesn't go off-screen
                if (left < 10) left = 10;
                if (left + dropdownWidth > viewportWidth - 10) {
                    left = viewportWidth - dropdownWidth - 10;
                }
                
                dropdown.style.position = 'fixed';
                dropdown.style.top = `${top}px`;
                dropdown.style.left = `${left}px`;
                dropdown.style.display = 'block';
            }
            
            return; // Prevent other handlers
        } 
        // Close dropdown when clicking outside (only for research titles)
        else if (!e.target.closest('.meatball-dropdown-portal') && !e.target.closest('.meatball-btn')) {
            document.querySelectorAll('.meatball-dropdown-portal[id^="dropdown-"]').forEach(dd => {
                if (dd.querySelector('[data-table="research_titles"]')) {
                    dd.style.display = 'none';
                }
            });
        }
    });

    // Handle meatball dropdown item clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.meatball-dropdown-item')) {
            const item = e.target.closest('.meatball-dropdown-item');
            
            // Only handle if this is a research titles dropdown item
            if (!item.hasAttribute('data-table') || item.getAttribute('data-table') !== 'research_titles') {
                return;
            }
            
            // Close the dropdown
            const dropdown = item.closest('.meatball-dropdown-portal');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
            
            // The existing edit-btn and delete-btn event handlers will handle the action
            // since we've preserved the same classes on the dropdown items
        }
    });

    // Re-create dropdowns when pagination changes or page content updates
    // This handles cases where the table content is dynamically updated
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && 
                mutation.target.closest('#research-titles .db-table tbody')) {
                // Table content changed, recreate dropdowns
                setTimeout(createMeatballDropdowns, 100);
            }
        });
    });

    // Start observing the research titles table body for changes
    const tableBody = document.querySelector('#research-titles .db-table tbody');
    if (tableBody) {
        observer.observe(tableBody, { childList: true, subtree: true });
    }
});
</script>