<?php

define('TITLE', "Profile");
include '../assets/layouts/header.php';
check_verified();

// Default cover photo if not set
$cover_photo = isset($_SESSION['cover_photo']) ? $_SESSION['cover_photo'] : 'profile_banner.jpg';

// Get user type to display in profile pill
$userType = "Student"; // Default
if (isset($_SESSION['usertype'])) {
    if ($_SESSION['usertype'] == 2) {
        $userType = "Staff";
    } elseif ($_SESSION['usertype'] == 3) {
        $userType = "Admin";
    }
}

// Get educational info for subheading - Based on program from $_SESSION
$educationalInfo = "";
if (isset($_SESSION['program']) && !empty($_SESSION['program'])) {
    $educationalInfo = $_SESSION['program'];
} 

// Determine if the user is part-time
$partTimeStatus = "";
if (isset($_SESSION['is_parttime']) && $_SESSION['is_parttime'] == 1) {
    $partTimeStatus = "(Part-time)";
}

?>

<div class="profile-container py-3">
    <div class="container">
        <!-- Main Profile Card -->
        <div class="profile-card mb-4">
            <!-- Cover photo section-->
            <div class="cover-photo" style="background-image: url('../assets/images/<?php echo $cover_photo; ?>');">
            </div>
            
            <!-- Layout with profile image on left and info on right -->
            <div class="profile-top-section">
                <!-- Profile image positioned over the cover photo -->
                <div class="profile-image-container">
                    <img src="../assets/uploads/users/<?php echo $_SESSION['profile_image']; ?>" alt="Profile Image" class="profile-image">
                </div>
                
                <!-- Profile information section -->
                <div class="profile-info">
                    <div class="profile-header-content">
                        <div class="profile-details">
                            <h1 class="profile-name"><?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?></h1>
                            
                            <div class="profile-headline">
                                <?php echo $_SESSION['headline']; ?>
                            </div>
                            
                            <div class="profile-meta">
                                <?php echo $educationalInfo; ?> <?php echo $partTimeStatus; ?>
                            </div>
                            
                            <div class="profile-location">
                                <i class="fa fa-map-marker"></i> 
                                College of Engineering & Computer Science
                            </div>
                        </div>
                        
                        <!-- Edit profile button positioned on the far right -->
                        <div class="profile-actions">
                            <a href="../profile-edit/" class="btn edit-profile-btn">
                                <i class="fa fa-pencil"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                    
                    <!-- User type pill -->
                    <div class="profile-pills">
                        <span class="profile-pill"><?php echo $userType; ?></span>
                        <?php if (isset($_SESSION['area_of_expertise']) && !empty($_SESSION['area_of_expertise'])): ?>
                            <span class="profile-pill"><?php echo $_SESSION['area_of_expertise']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bio/About section -->
        <div class="bio-section">
            <div class="bio-title">About</div>
            <div class="bio-content">
                <?php echo $_SESSION['bio']; ?>
            </div>
        </div>
    </div>
</div>

<?php
include '../assets/layouts/footer.php';
?>