<nav class="navbar navbar-expand-md bg-main shadow-sm p-2">

    <div class="container">
        <a class="navbar-brand" href="../home">

            <img src="../assets/images/logo_full_lightbg.png" alt="" width="88" height="10%" class="10%">


            <!-- <?php echo APP_NAME; ?> -->

        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>


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

                <li class="nav-item"> 
                    <a class="nav-link" href="../decision-support">Defense</a>
                </li>
            
                <!-- <li class="nav-item">
                    <a class="nav-link" href="../contact">Contact Us</a>
                </li> -->

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img class="navbar-img" src="../assets/uploads/users/<?php echo $_SESSION['profile_image'] ?>" alt="Profile">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end bg-color" aria-labelledby="navbarDropdown" id="nav-ul">
                        <li><a class="dropdown-item" href="../profile"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <!-- <li><a class="dropdown-item" href="../profile-edit"><i class="fas fa-pencil-alt me-2"></i> Edit Profile</a></li> -->
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>