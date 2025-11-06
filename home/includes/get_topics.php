<?php
session_start();
include '../../assets/setup/db.inc.php'; // Make sure to include your database connection here

if (isset($_POST['field'])) {
    $field = $_POST['field'];
    $_SESSION['field'] = $field;

    // Prepare the query to filter topics based on the selected field
    $stmt = $pdo->prepare("SELECT topic, description FROM thesis_topics WHERE category = :field");
    $stmt->bindParam(':field', $field, PDO::PARAM_STR);
    $stmt->execute();
    $topics = $stmt->fetchAll();

    if ($topics) {
        echo "<div class='mt-4'>";
        echo "<div class='d-md-none mb-3'>";
        echo "<small class='text-muted'><i class='bi bi-info-circle me-1'></i>Tap any row to view full details</small>";
        echo "</div>";
        echo "<div class='topic-table-container'>";
        echo "<table class='table table-hover'>";
        echo "<thead><tr><th>Topic</th><th class='d-none d-md-table-cell'>Description</th><th class='d-md-none text-center'>Actions</th></tr></thead>";
        echo "<tbody>";
        foreach ($topics as $row) {
            $topic = htmlspecialchars($row['topic']);
            $description = htmlspecialchars($row['description']);
            echo "<tr class='topic-row' data-topic='" . $topic . "' data-description='" . $description . "'>";
            echo "<td class='fw-semibold'>" . $topic . "</td>";
            echo "<td class='d-none d-md-table-cell'>" . $description . "</td>";
            echo "<td class='d-md-none text-center'>";
            echo "<button class='btn btn-sm btn-outline-primary view-topic-btn' data-topic='" . $topic . "' data-description='" . $description . "'>";
            echo "<i class='bi bi-eye'></i> View";
            echo "</button>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<p class='mt-4 text-warning'>No topics found for the selected field.</p>";
    }
}
?>
