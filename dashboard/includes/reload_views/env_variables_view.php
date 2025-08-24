<?php
// Reload view for environment variables
require_once __DIR__ . '/../../../assets/setup/db.inc.php';

// Function to fetch all environment variables
function fetchAllEnvVariables($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM env_variables");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$envVariables = fetchAllEnvVariables($pdo);
?>

<!-- Header with title and description -->
<div class="row mb-4">
    <div class="col-12">
        <h3 class="mb-2">Content Management System</h3>
        <p class="text-muted">Manage website content, system settings, and display options</p>
    </div>
</div>

<!-- Content Management Controls -->
<div class="row">
    <div class="col-12">
        <div class="user-controls-container p-0">
            <!-- Search and Filter Row -->
            <div class="row g-2 mb-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-3">
                    <!-- CMS Content Type Filter -->
                    <label class="form-label text-muted small">Content Type</label>
                    <select class="form-select user-control-height" id="cmsContentTypeSelect">
                        <option value="all-content">All Content</option>
                        <option value="system-settings">System Settings</option>
                        <option value="page-content">Page Content</option>
                    </select>
                </div>
                
                <div class="col-12 col-md-8 col-lg-9">
                    <!-- Action buttons container -->
                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                        <button class="btn feature-btn add-btn user-control-height" data-table="env_variables" id="addSettingBtn" style="display: none;">
                            <i class="fas fa-plus me-2"></i>Add Setting
                        </button>
                        <button class="btn feature-btn page-content-add-btn user-control-height" id="addPageContentBtn" style="display: none;">
                            <i class="fas fa-plus me-2"></i>Add Page Content
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Management Content -->
<div class="pt-4" id="cmsContentContainer">
    <!-- All Content View (Default) -->
    <div id="all-content-view">
        <!-- System Settings Section -->
        <div class="mb-5" id="system-settings-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">System Settings</h4>
            </div>
            <div class="table-responsive">
                <?php 
                $groupedVariables = [];
                foreach ($envVariables as $variable) {
                    $prefix = explode('_', $variable['key'])[0];
                    $groupedVariables[$prefix][] = $variable;
                }
                
                foreach ($groupedVariables as $prefix => $variables): ?>
                    <div class="settings-group mb-4">
                        <div class="settings-group-header px-4 py-3">
                            <h5 class="mb-0"><?php echo htmlspecialchars($prefix == 'ALLOWED' ? $prefix . ' Inactivity Time' : $prefix . ' Settings'); ?></h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm db-table m-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Name</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($variables as $variable): ?>
                                        <tr>
                                            <td class="ps-4 fw-medium">
                                                <?php echo htmlspecialchars($variable['key']); ?>
                                                <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                                   data-bs-toggle="tooltip" 
                                                   data-bs-placement="top" 
                                                   title="<?php echo htmlspecialchars('ID: ' . $variable['id'] . ' | Key: ' . $variable['key'] . ' | Description: ' . ($variable['description'] ?? 'No description')); ?>"></i>
                                            </td>
                                            <td>
                                                <?php 
                                                if (strpos($variable['key'], 'PASSWORD') !== false) {
                                                    echo '<span class="text-muted">••••••••</span>';
                                                } elseif ($variable['key'] == 'ALLOWED_INACTIVITY_TIME') {
                                                    $hours = floor($variable['value'] / 3600);
                                                    $minutes = floor(($variable['value'] % 3600) / 60);
                                                    $seconds = $variable['value'] % 60;
                                                    echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                                } else {
                                                    echo htmlspecialchars($variable['value']);
                                                }
                                                ?>
                                            </td>
                                            <td class="text-muted">
                                                <?php echo !empty($variable['description']) ? htmlspecialchars($variable['description']) : '<em>No description</em>'; ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="meatball-btn" data-env-id="<?php echo $variable['id']; ?>">
                                                    <i class="fas fa-ellipsis-h"></i> 
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr class="border-dark my-5">
        </div>

        <!-- Page Content Section -->
        <div class="mb-5" id="page-content-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Page Content</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm db-table m-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Page Title</th>
                            <th>Slug</th>
                            <th>Last Updated</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="pageContentTableBody">
                        <?php
                        // Get all page content from database
                        $page_content_query = "SELECT * FROM page_content ORDER BY updated_at DESC";
                        try {
                            $page_content_stmt = $pdo->prepare($page_content_query);
                            $page_content_stmt->execute();
                            $pages = $page_content_stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($pages) > 0) {
                                foreach ($pages as $page) {
                                    $status_badge = $page['status'] === 'published' ? 'bg-success' : 'bg-warning';
                                    echo '<tr>
                                        <td class="ps-4 fw-medium">
                                            ' . htmlspecialchars($page['title']) . '
                                            <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Page ID: ' . $page['id'] . ' | URL: /' . $page['slug'] . ' | Created: ' . $page['created_at'] . '"></i>
                                        </td>
                                        <td>' . htmlspecialchars($page['slug']) . '</td>
                                        <td>' . date('Y-m-d', strtotime($page['updated_at'])) . '</td>
                                        <td><span class="badge ' . $status_badge . '">' . ucfirst($page['status']) . '</span></td>
                                        <td class="text-center">
                                            <button class="meatball-btn" data-page-id="' . $page['id'] . '">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center">No pages found</td></tr>';
                            }
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="5" class="text-center text-danger">Error loading pages: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Settings Only View -->
    <div id="system-settings-view" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">System Settings</h4>
        </div>

        <div class="table-responsive">
            <!-- Same grouped variables content as above -->
            <?php foreach ($groupedVariables as $prefix => $variables): ?>
                <div class="settings-group mb-4">
                    <div class="settings-group-header px-4 py-3">
                        <h5 class="mb-0"><?php echo htmlspecialchars($prefix == 'ALLOWED' ? $prefix . ' Inactivity Time' : $prefix . ' Settings'); ?></h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm db-table m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Value</th>
                                    <th>Description</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variables as $variable): ?>
                                    <tr>
                                        <td class="ps-4 fw-medium">
                                            <?php echo htmlspecialchars($variable['key']); ?>
                                            <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="<?php echo htmlspecialchars('ID: ' . $variable['id'] . ' | Key: ' . $variable['key'] . ' | Description: ' . ($variable['description'] ?? 'No description')); ?>"></i>
                                        </td>
                                        <td>
                                            <?php 
                                            if (strpos($variable['key'], 'PASSWORD') !== false) {
                                                echo '<span class="text-muted">••••••••</span>';
                                            } elseif ($variable['key'] == 'ALLOWED_INACTIVITY_TIME') {
                                                $hours = floor($variable['value'] / 3600);
                                                $minutes = floor(($variable['value'] % 3600) / 60);
                                                $seconds = $variable['value'] % 60;
                                                echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                            } else {
                                                echo htmlspecialchars($variable['value']);
                                            }
                                            ?>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo !empty($variable['description']) ? htmlspecialchars($variable['description']) : '<em>No description</em>'; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="meatball-btn" data-env-id="<?php echo $variable['id']; ?>">
                                                <i class="fas fa-ellipsis-h"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Page Content Only View -->
    <div id="page-content-view" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Page Content</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm db-table m-0">
                <thead>
                    <tr>
                        <th class="ps-4">Page Title</th>
                        <th>Slug</th>
                        <th>Last Updated</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Same page content as above -->
                    <?php
                    if (count($pages) > 0) {
                        foreach ($pages as $page) {
                            $status_badge = $page['status'] === 'published' ? 'bg-success' : 'bg-warning';
                            echo '<tr>
                                <td class="ps-4 fw-medium">
                                    ' . htmlspecialchars($page['title']) . '
                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Page ID: ' . $page['id'] . ' | URL: /' . $page['slug'] . ' | Created: ' . $page['created_at'] . '"></i>
                                </td>
                                <td>' . htmlspecialchars($page['slug']) . '</td> 
                                <td>' . date('Y-m-d', strtotime($page['updated_at'])) . '</td>
                                <td><span class="badge ' . $status_badge . '">' . ucfirst($page['status']) . '</span></td>
                                <td class="text-center">
                                    <button class="meatball-btn" data-page-id="' . $page['id'] . '">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </td>
                            </tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center">No pages found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
