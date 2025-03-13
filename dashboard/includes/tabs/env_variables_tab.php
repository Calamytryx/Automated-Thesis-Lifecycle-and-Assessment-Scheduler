<!-- Environment Variables Tab -->
<div class="tab-pane fade" id="env-variables" role="tabpanel"
    aria-labelledby="env-variables-tab">
    <div class="container-fluid py-4">
        <!-- Header with title and description -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="mb-2">Content Management System</h3>
                <p class="text-muted">Manage website content, system settings, and display options</p>
            </div>
        </div>

        <!-- Content Management Navigation Tabs -->
        <div class="row">
            <div class="col-12">
                <ul class="nav nav-tabs border-bottom border-dark" id="cmsContentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-content-tab" data-bs-toggle="tab" 
                                data-bs-target="#all-content" type="button" role="tab" 
                                aria-controls="all-content" aria-selected="true">
                            <i class="fas fa-th-list me-2"></i>All Content
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="system-settings-tab" data-bs-toggle="tab" 
                                data-bs-target="#system-settings" type="button" role="tab" 
                                aria-controls="system-settings" aria-selected="false">
                            <i class="fas fa-cogs me-2"></i>System Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="page-content-tab" data-bs-toggle="tab" 
                                data-bs-target="#page-content" type="button" role="tab" 
                                aria-controls="page-content" aria-selected="false">
                            <i class="fas fa-file-alt me-2"></i>Page Content
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="announcements-tab" data-bs-toggle="tab" 
                                data-bs-target="#announcements" type="button" role="tab" 
                                aria-controls="announcements" aria-selected="false">
                            <i class="fas fa-bullhorn me-2"></i>Announcements
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="email-templates-tab" data-bs-toggle="tab" 
                                data-bs-target="#email-templates" type="button" role="tab" 
                                aria-controls="email-templates" aria-selected="false">
                            <i class="fas fa-envelope me-2"></i>Email Templates
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Content Management Tab Content -->
        <div class="tab-content pt-4" id="cmsContentTabsContent">
            <!-- All Content Tab -->
            <div class="tab-pane fade show active" id="all-content" role="tabpanel" aria-labelledby="all-content-tab">
                <!-- System Settings Section -->
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">System Settings</h4>
                        <button class="btn feature-btn add-btn" data-table="env_variables">
                            <i class="fas fa-plus me-2"></i>Add Setting
                        </button>
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
                                    <table class="table table-bordered table-hover table-sm db-table">
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
                                                        <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                                data-table="env_variables" 
                                                                data-id="<?php echo $variable['id']; ?>">
                                                            <i class="fas fa-edit me-1"></i>Edit
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
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Page Content</h4>
                        <button class="btn feature-btn add-btn" id="addPageContent">
                            <i class="fas fa-plus me-2"></i>Add Page Content
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm db-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">Page</th>
                                    <th>Last Updated</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        Home Page
                                        <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Page ID: 1 | URL: /home | Created: 2023-01-15 | Last Modified By: Admin"></i>
                                    </td>
                                    <td>2023-05-15</td>
                                    <td><span class="badge bg-success">Published</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        About Page
                                        <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Page ID: 2 | URL: /about | Created: 2023-01-20 | Last Modified By: Admin"></i>
                                    </td>
                                    <td>2023-04-20</td>
                                    <td><span class="badge bg-success">Published</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        FAQ Page
                                        <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Page ID: 3 | URL: /faq | Created: 2023-02-05 | Last Modified By: Admin"></i>
                                    </td>
                                    <td>2023-03-10</td>
                                    <td><span class="badge bg-warning">Draft</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr class="border-dark my-5">
                </div>

                <!-- Announcements Section -->
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Announcements</h4>
                        <button class="btn feature-btn add-btn" id="addAnnouncement">
                            <i class="fas fa-plus me-2"></i>Add Announcement
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm db-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">Title</th>
                                    <th>Date</th>
                                    <th>Target Audience</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        Thesis Defense Schedule Released
                                        <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Announcement ID: 1 | Created: 2023-05-18 | Author: Admin | Content: The thesis defense schedule for this semester has been released. Please check your email for details."></i>
                                    </td>
                                    <td>2023-05-20</td>
                                    <td>All Students</td>
                                    <td><span class="badge bg-success">Published</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        New Rubric Guidelines
                                        <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Announcement ID: 2 | Created: 2023-05-12 | Author: Admin | Content: New rubric guidelines have been published for faculty members. Please review them before the next evaluation."></i>
                                    </td>
                                    <td>2023-05-15</td>
                                    <td>Faculty</td>
                                    <td><span class="badge bg-success">Published</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr class="border-dark my-5">
                </div>

                <!-- Email Templates Section -->
                <div class="mb-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Email Templates</h4>
                        <button class="btn feature-btn add-btn" id="addEmailTemplate">
                            <i class="fas fa-plus me-2"></i>Add Template
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm db-table">
                            <thead>
                                <tr>
                                    <th class="ps-4">Template Name</th>
                                    <th>Subject</th>
                                    <th>Last Updated</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        Welcome Email
                                        <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Template ID: 1 | Created: 2023-04-05 | Variables: {user_name}, {login_url} | Content: Welcome to the Thesis Management System, {user_name}! Your account has been created successfully."></i>
                                    </td>
                                    <td>Welcome to the Thesis Management System</td>
                                    <td>2023-04-10</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-medium">
                                        Defense Schedule Notification
                                        <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Template ID: 2 | Created: 2023-03-20 | Variables: {user_name}, {defense_date}, {defense_time}, {defense_location} | Content: Dear {user_name}, your thesis defense has been scheduled for {defense_date} at {defense_time} in {defense_location}."></i>
                                    </td>
                                    <td>Your Defense Schedule Has Been Set</td>
                                    <td>2023-03-25</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Settings Tab -->
            <div class="tab-pane fade" id="system-settings" role="tabpanel" aria-labelledby="system-settings-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">System Settings</h4>
                    <button class="btn feature-btn add-btn" data-table="env_variables">
                        <i class="fas fa-plus me-2"></i>Add Setting
                    </button>
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
                                <table class="table table-bordered table-hover table-sm db-table">
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
                                                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                            data-table="env_variables" 
                                                            data-id="<?php echo $variable['id']; ?>">
                                                        <i class="fas fa-edit me-1"></i>Edit
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

            <!-- Page Content Tab -->
            <div class="tab-pane fade" id="page-content" role="tabpanel" aria-labelledby="page-content-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Page Content</h4>
                    <button class="btn feature-btn add-btn" id="addPageContent">
                        <i class="fas fa-plus me-2"></i>Add Page Content
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Page</th>
                                <th>Last Updated</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-medium">
                                    Home Page
                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Page ID: 1 | URL: /home | Created: 2023-01-15 | Last Modified By: Admin"></i>
                                </td>
                                <td>2023-05-15</td>
                                <td><span class="badge bg-success">Published</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">
                                    About Page
                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Page ID: 2 | URL: /about | Created: 2023-01-20 | Last Modified By: Admin"></i>
                                </td>
                                <td>2023-04-20</td>
                                <td><span class="badge bg-success">Published</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">
                                    FAQ Page
                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Page ID: 3 | URL: /faq | Created: 2023-02-05 | Last Modified By: Admin"></i>
                                </td>
                                <td>2023-03-10</td>
                                <td><span class="badge bg-warning">Draft</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Announcements Tab -->
            <div class="tab-pane fade" id="announcements" role="tabpanel" aria-labelledby="announcements-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Announcements</h4>
                    <button class="btn feature-btn add-btn" id="addAnnouncement">
                        <i class="fas fa-plus me-2"></i>Add Announcement
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Title</th>
                                <th>Date</th>
                                <th>Target Audience</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-medium">
                                    Thesis Defense Schedule Released
                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Announcement ID: 1 | Created: 2023-05-18 | Author: Admin | Content: The thesis defense schedule for this semester has been released. Please check your email for details."></i>
                                </td>
                                <td>2023-05-20</td>
                                <td>All Students</td>
                                <td><span class="badge bg-success">Published</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">
                                    New Rubric Guidelines
                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Announcement ID: 2 | Created: 2023-05-12 | Author: Admin | Content: New rubric guidelines have been published for faculty members. Please review them before the next evaluation."></i>
                                </td>
                                <td>2023-05-15</td>
                                <td>Faculty</td>
                                <td><span class="badge bg-success">Published</span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Email Templates Tab -->
            <div class="tab-pane fade" id="email-templates" role="tabpanel" aria-labelledby="email-templates-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Email Templates</h4>
                    <button class="btn feature-btn add-btn" id="addEmailTemplate">
                        <i class="fas fa-plus me-2"></i>Add Template
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm db-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Template Name</th>
                                <th>Subject</th>
                                <th>Last Updated</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-medium">
                                    Welcome Email
                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Template ID: 1 | Created: 2023-04-05 | Variables: {user_name}, {login_url} | Content: Welcome to the Thesis Management System, {user_name}! Your account has been created successfully."></i>
                                </td>
                                <td>Welcome to the Thesis Management System</td>
                                <td>2023-04-10</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-medium">
                                    Defense Schedule Notification
                                    <i class="fas fa-info-circle text-primary ms-2 info-icon" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Template ID: 2 | Created: 2023-03-20 | Variables: {user_name}, {defense_date}, {defense_time}, {defense_location} | Content: Dear {user_name}, your thesis defense has been scheduled for {defense_date} at {defense_time} in {defense_location}."></i>
                                </td>
                                <td>Your Defense Schedule Has Been Set</td>
                                <td>2023-03-25</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal for CMS Content -->
<div class="modal fade" id="cmsContentModal" tabindex="-1" aria-labelledby="cmsContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cmsContentModalLabel">Edit Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form will be dynamically inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCmsContent">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal for CMS Content -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Content Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <!-- Details will be dynamically inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                html: true,
                container: 'body'
            });
        });
        
        // Add click event for info icons to show modal with full details
        document.querySelectorAll('.info-icon').forEach(function(icon) {
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Get the tooltip content
                var content = this.getAttribute('data-bs-original-title') || this.getAttribute('title');
                
                // Get the item name for the modal title
                var itemName = this.parentElement.textContent.trim().split('\n')[0].trim();
                document.getElementById('detailModalLabel').innerHTML = '<i class="fas fa-info-circle text-primary me-2"></i>' + itemName;
                
                // Format the content for the modal
                var formattedContent = '';
                content.split('|').forEach(function(item) {
                    var parts = item.split(':');
                    if (parts.length > 1) {
                        formattedContent += '<div class="detail-item">';
                        formattedContent += '<div class="detail-label">' + parts[0].trim() + '</div>';
                        formattedContent += '<div class="detail-value">' + parts.slice(1).join(':').trim() + '</div>';
                        formattedContent += '</div>';
                    }
                });
                
                // Set the modal content and show it
                document.getElementById('detailModalBody').innerHTML = formattedContent;
                var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
                detailModal.show();
            });
        });
    });
</script>