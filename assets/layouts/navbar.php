<nav class="navbar navbar-expand-md bg-main py-2">

    <div class="container-fluid px-4">
        <a class="navbar-brand" href="../home">

            <img src="../assets/images/<?php echo APP_LOGO_NAVBAR; ?>" alt="" width="88" height="10%" class="10%">

            <!-- <?php echo APP_NAME; ?> -->

        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>        
        <div class="user-type-pill">
            <?php
            $userTypeClass = '';
            $userTypeText = '';
            
            if ($_SESSION['usertype'] == 0) {
                $userTypeClass = 'bg-danger';
                $userTypeText = 'Administrator';
            } elseif ($_SESSION['usertype'] == 1) {
                $userTypeClass = 'bg-primary';
                $userTypeText = 'Student';
            } elseif ($_SESSION['usertype'] == 2) {
                $userTypeClass = 'bg-success';
                $userTypeText = 'Faculty';
            } else {
                $userTypeClass = 'bg-secondary';
                $userTypeText = 'User';
            }
            ?>
            <span class="badge rounded-pill <?php echo $userTypeClass; ?> px-3 py-2" style="font-size: 0.8rem; font-weight: 500;">
                <?php echo $userTypeText; ?>
            </span>
        </div>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">

            <!-- All navbar items aligned to the right -->
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="../home">Home</a>
                </li>

                <?php if ($_SESSION['usertype'] == 0): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard">Dashboard</a>
                    </li>
                <?php endif; ?>
                <!-- <?php //if ($_SESSION['usertype'] == 2 || $_SESSION['usertype'] == 0): ?>
                    <li class="nav-item"> 
                    <a class="nav-link" href="../decision-support">Defense</a>
                </li>
                <?php //endif; ?> -->
            
                <!-- <li class="nav-item">
                    <a class="nav-link" href="../contact">Contact Us</a>
                </li> -->

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Pages
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="pagesDropdown">
                        <?php
                        // Get all published pages for the menu
                        $pages = getPublishedPages($pdo);
                        if (!empty($pages)) {
                            foreach ($pages as $page) {
                                echo '<li><a class="dropdown-item" href="../page/?slug=' . $page['slug'] . '">' . htmlspecialchars($page['title']) . '</a></li>';
                            }
                        } else {
                            echo '<li><a class="dropdown-item disabled">No pages available</a></li>';
                        }
                        ?>
                    </ul>                </li>
                <!-- Profile and logout moved to sidebar -->
            </ul>
        </div>
    </div>
</nav>