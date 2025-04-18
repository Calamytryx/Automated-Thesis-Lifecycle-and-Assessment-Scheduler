<?php
/**
 * Page content viewer
 * 
 * This file handles displaying page content based on the slug in the URL
 */

define('TITLE', "Page");
include '../assets/layouts/header.php';
include '../assets/setup/db.inc.php';

// Get the slug from the URL
$slug = isset($_GET['slug']) ? filter_var($_GET['slug'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : '';

$page = null;
$error_message = '';

if (!empty($slug)) {
    try {
        // Fetch the page content
        $query = "SELECT * FROM page_content WHERE slug = :slug AND status = 'published'";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
        $stmt->execute();
         
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$page) {
            $error_message = 'The requested page could not be found.';
        }
    } catch (PDOException $e) {
        $error_message = 'An error occurred while fetching the page.';
        error_log('Page viewing error: ' . $e->getMessage());
    }
} else {
    $error_message = 'No page specified.';
}
?>

<main class="container my-5">
    <?php if ($page): ?>
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <article class="page-content">
                    <h1 class="page-title mb-4"><?php echo htmlspecialchars($page['title']); ?></h1>
                    <div class="page-body">
                        <?php echo $page['content']; // Content is already sanitized when saved ?>
                    </div>
                </article>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-6 mx-auto text-center">
                <div class="alert alert-warning">
                    <h4 class="alert-heading">Page Not Found</h4>
                    <p><?php echo $error_message; ?></p>
                    <hr>
                    <p class="mb-0">
                        <a href="../home" class="btn btn-primary">Return to Home</a>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include '../assets/layouts/footer.php'; ?>