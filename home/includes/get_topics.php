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
        echo "<table class='table table-bordered mt-4'>";
        echo "<thead class='thead-dark'><tr><th>Topic</th><th>Description</th></tr></thead>";
        echo "<tbody>";
        foreach ($topics as $row) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['topic']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p class='mt-4 text-warning'>No topics found for the selected field.</p>";
    }
}
?>
