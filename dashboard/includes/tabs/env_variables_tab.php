<!-- Environment Variables Tab -->
<div class="tab-pane fade" id="env-variables" role="tabpanel"
    aria-labelledby="env-variables-tab">
    <div class="d-flex justify-content-between align-items-center mb-3 my-3">
        <button class="btn btn-primary btn-sm add-btn feature-btn" data-table="env_variables">Add
            Environment Variable</button>
    </div>
    <div class="table-responsive">
        <?php 
        $groupedVariables = [];
        foreach ($envVariables as $variable) {
            $prefix = explode('_', $variable['key'])[0];
            $groupedVariables[$prefix][] = $variable;
        }
        foreach ($groupedVariables as $prefix => $variables): ?>
            <h5><?php echo htmlspecialchars($prefix == 'ALLOWED' ? $prefix . ' Inactivity Time' : $prefix . ' Variables'); ?></h5>
            <table class="table table-bordered table-hover table-sm db-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($variables as $variable): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($variable['key']); ?></td>
                            <td>
                                <?php 
                                if (strpos($variable['key'], 'PASSWORD') !== false) {
                                    echo '********';
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
                            <td class="text-center align-middle">
                                <button class="btn btn-primary btn-sm edit-btn"
                                    data-table="env_variables"
                                    data-id="<?php echo $variable['id']; ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    </div>
</div>