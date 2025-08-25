<?php

define('TITLE', "Edit Profile");
include '../assets/layouts/header.php';
check_verified();

//XSS filter for session variables
function xss_filter($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>


<main class="container-fluid p-0 profile-edit-main-container">
    <div class="row g-0 profile-edit-row">
        <div class="col-sm-12 d-flex flex-column profile-edit-col">
            <div id="profileEditMainContent" class="d-flex flex-column flex-grow-1 profile-edit-main-content">
                <div id="profileEditCardContainer" class="d-flex flex-column align-items-center w-100">
                    <div class="profile-edit-card">
                        <div class="edit-section-title">
                            <span>Edit Your Profile</span>
                            <a href="../profile/" class="btn view-profile-btn">View Profile</a>
                        </div>
                        
                        <form class="form-auth" action="includes/profile-edit.inc.php" method="post" enctype="multipart/form-data" autocomplete="off">
                            <?php insert_csrf_token(); ?>
                            <div class="edit-profile-grid">
                                <!-- Left: Profile Image & Status -->
                                <div class="edit-profile-left">
                                    <div class="profile-image-editor">
                                        <div class="avatar-upload">
                                            <div class="avatar-preview">
                                                <div id="imagePreview" style="background-image: url( ../assets/uploads/users/<?php echo $_SESSION['profile_image'] ?> );"></div>
                                            </div>
                                        </div>
                                        <div class="avatar-edit">
                                            <input name='avatar' id="avatar" type='file' accept="image/*" />
                                            <label for="avatar"><i class="fas fa-camera"></i> Edit Photo</label>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted">Max file size: 5MB. Supported formats: JPG, PNG, GIF</small>
                                        </div>
                                        <div class="text-center mt-2">
                                            <sub class="text-danger">
                                                <?php if (isset($_SESSION['ERRORS']['imageerror'])) echo $_SESSION['ERRORS']['imageerror']; ?>
                                            </sub>
                                        </div>
                                        <div class="validation-error" id="avatar-error" style="display: none;"></div>
                                        <div class="text-center">
                                            <small class="text-success font-weight-bold">
                                                <?php if (isset($_SESSION['STATUS']['editstatus'])) echo $_SESSION['STATUS']['editstatus']; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <!-- Right: Form Fields -->
                                <div class="edit-profile-right">
                                    <div class="row profile-edit-card-row">
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="first_name">First Name</label>
                                                <p id="first_name" class="form-control"><?php echo xss_filter($_SESSION['first_name']); ?></p>
                                                <div class="validation-error" id="first_name-error" style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="last_name">Last Name</label>
                                                <p id="last_name" class="form-control" > <?php echo xss_filter($_SESSION['last_name']); ?> </p>
                                                <div class="validation-error" id="last_name-error" style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row profile-edit-card-row">
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="username">Username</label>
                                                <p id="username" class="form-control" ><?php echo xss_filter($_SESSION['username']); ?></p>
                                                <sub class="text-danger">
                                                    <?php if (isset($_SESSION['ERRORS']['usernameerror'])) echo $_SESSION['ERRORS']['usernameerror']; ?>
                                                </sub>
                                                <div class="validation-error" id="username-error" style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="email">Email address</label>
                                                <?php if ($_SESSION['usertype'] == 0): // Only admins can edit email ?>
                                                    <input type="email" id="email" name="email" class="form-control" placeholder="Email address" value="<?php echo xss_filter($_SESSION['email']); ?>">
                                                <?php else: ?>
                                                    <p type="email" id="email" class="form-control" readonly style="background-color: #f8f9fa; cursor: not-allowed;"><?php echo xss_filter($_SESSION['email']); ?></p>
                                                    <small class="form-text text-muted">Only administrators can change email addresses</small>
                                                <?php endif; ?>
                                                <sub class="text-danger">
                                                    <?php if (isset($_SESSION['ERRORS']['emailerror'])) echo $_SESSION['ERRORS']['emailerror']; ?>
                                                </sub>
                                                <div class="validation-error" id="email-error" style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row profile-edit-card-row">
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label for="headline">Headline</label>
                                                <input type="text" id="headline" name="headline" class="form-control" placeholder="Headline (e.g. Computer Engineering Student)" value="<?php echo xss_filter($_SESSION['headline']); ?>">
                                                <div class="validation-error" id="headline-error" style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 profile-edit-card-col">
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <div class="gender-options mt-1">
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="male" name="gender" class="custom-control-input" value="m" <?php if ($_SESSION['gender'] == 'm') echo 'checked' ?> >
                                                        <label class="custom-control-label" for="male">Male</label>
                                                    </div>
                                                    <div class="custom-control custom-radio">
                                                        <input type="radio" id="female" name="gender" class="custom-control-input" value="f" <?php if ($_SESSION['gender'] == 'f') echo 'checked' ?> >
                                                        <label class="custom-control-label" for="female">Female</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="bio">About</label>
                                        <textarea type="text" id="bio" name="bio" class="form-control" placeholder="Tell us about yourself..." rows="4"><?php echo xss_filter($_SESSION['bio']); ?></textarea>
                                        <div class="validation-error" id="bio-error" style="display: none;"></div>
                                    </div>
                                    <div class="password-section">
                                        <h3 class="password-title">Change Password</h3>
                                        <sub class="text-danger mb-4">
                                            <?php if (isset($_SESSION['ERRORS']['passworderror'])) echo $_SESSION['ERRORS']['passworderror']; ?>
                                        </sub>
                                        <div class="validation-error" id="password-general-error" style="display: none;"></div>
                                        <div class="row profile-edit-card-row">
                                            <div class="col-md-4 profile-edit-card-col">
                                                <div class="form-group">
                                                    <label for="password">Current Password</label>
                                                    <input type="password" id="password" name="password" class="form-control" placeholder="Current Password" autocomplete="new-password">
                                                    <div class="validation-error" id="password-error" style="display: none;"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 profile-edit-card-col">
                                                <div class="form-group">
                                                    <label for="newpassword">New Password</label>
                                                    <input type="password" id="newpassword" name="newpassword" class="form-control" placeholder="New Password" autocomplete="new-password">
                                                    <div class="validation-error" id="newpassword-error" style="display: none;"></div>
                                                    <small class="form-text text-muted">Must be 8+ characters with uppercase, lowercase, number, and special character</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 profile-edit-card-col">
                                                <div class="form-group">
                                                    <label for="confirmpassword">Confirm Password</label>
                                                    <input type="password" id="confirmpassword" name="confirmpassword" class="form-control" placeholder="Confirm Password" autocomplete="new-password">
                                                    <div class="validation-error" id="confirmpassword-error" style="display: none;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group text-center mt-4 d-flex justify-content-end">
                                        <button class="btn edit-submit-btn" type="submit" name='update-profile'>Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../assets/layouts/footer.php'; ?>

<script type="text/javascript">
    // Helper function for showing toasts (dashboard style)
    function showToast(title, message, type = 'success') {
        // Create toast container if it doesn't exist
        if (!$('#toastContainer').length) {
            $('body').append(`
    <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    </div>
`);

        }

        // Generate unique ID for the toast
        const toastId = 'toast-' + Date.now();

        // Modern universal toast styling and structure
        const icon = type === 'success' ?
            `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#eaf0fe;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#1304ee"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>` :
            type === 'error' ?
            `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#fbeaea;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#dc3545"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>` :
            `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#fffbe6;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#ffc107"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg></span>`;

        const bgColor = type === 'success' ? '#f6fffa' : (type === 'error' ? '#fff6f6' : '#fffbe6');
        const borderColor = type === 'success' ? '#1304ee' : (type === 'error' ? '#dc3545' : '#ffc107');
        const textColor = '#222';
        const toast = `
<div id="${toastId}" class="toast align-items-center border-0 shadow-lg"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    style="min-width:320px;max-width:400px;opacity:1;background:${bgColor};border-left:5px solid ${borderColor};border-radius:12px;margin-bottom:1rem;box-shadow:0 4px 24px 0 rgba(0,0,0,0.10);">
    <div class="d-flex align-items-center" style="padding:1rem 1.25rem;">
        ${icon}
        <div class="toast-body p-0" style="font-size:1rem;color:${textColor};line-height:1.5;">
            <div style="font-weight:600;font-size:1.08rem;margin-bottom:2px;">${title}</div>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close" style="margin-left:1.5rem;"></button>
    </div>
</div>
`;

        // Add toast to container
        $('#toastContainer').append(toast);

        // Initialize and show the toast with modified options
        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: true,
            delay: 3000,
            animation: true
        });
        toastElement.show();

        // Remove toast element after it's hidden
        $(`#${toastId}`).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Profile picture validation
    function validateProfilePicture(file) {
        const errors = [];
        
        if (!file) {
            return errors; // No file selected is OK
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            errors.push('Invalid file type. Please upload JPG, PNG, or GIF images only');
        }
        
        // Check file size (5MB limit)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (file.size > maxSize) {
            errors.push('File size too large. Maximum 5MB allowed');
        }
        
        // Check minimum dimensions (optional)
        return new Promise((resolve) => {
            if (errors.length > 0) {
                resolve(errors);
                return;
            }
            
            const img = new Image();
            img.onload = function() {
                // Check minimum dimensions
                if (this.width < 100 || this.height < 100) {
                    errors.push('Image must be at least 100x100 pixels');
                }
                
                // Check aspect ratio (optional - warn if very distorted)
                const aspectRatio = this.width / this.height;
                if (aspectRatio < 0.5 || aspectRatio > 2) {
                    errors.push('Image aspect ratio seems unusual. Consider using a more square image');
                }
                
                resolve(errors);
            };
            
            img.onerror = function() {
                errors.push('Invalid image file');
                resolve(errors);
            };
            
            img.src = URL.createObjectURL(file);
        });
    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate the file first
            validateProfilePicture(file).then(errors => {
                const errorDiv = document.getElementById('avatar-error');
                
                if (errors.length > 0) {
                    // Show errors
                    errorDiv.textContent = errors.join(', ');
                    errorDiv.style.display = 'block';
                    errorDiv.style.color = '#dc3545';
                    errorDiv.style.fontSize = '0.875rem';
                    errorDiv.style.marginTop = '0.25rem';
                    
                    // Clear the file input
                    input.value = '';
                    
                    // Show toast error
                    showToast('Image Validation Error', errors.join(', '), 'error');
                    return;
                } else {
                    // Clear any previous errors
                    errorDiv.style.display = 'none';
                }
                
                // If validation passes, show the preview
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
                    $('#imagePreview').hide();
                    $('#imagePreview').fadeIn(650);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    $("#avatar").change(function() {
        readURL(this);
    });

    // Show validation error from server side using toast
    <?php if (isset($_SESSION['ERRORS']['validationerror'])): ?>
        $(document).ready(function() {
            showToast('Validation Error', '<?php echo addslashes($_SESSION['ERRORS']['validationerror']); ?>', 'error');
        });
        <?php unset($_SESSION['ERRORS']['validationerror']); ?>
    <?php endif; ?>

    // Profile Edit Validation - Based on Dashboard Pattern
    const ProfileValidation = {
        // Check for emojis
        containsEmoji: function(text) {
            const emojiRegex = /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/u;
            return emojiRegex.test(text);
        },

        // Check if text is too long
        isTooLong: function(text, maxLength = 100) {
            return text.length > maxLength;
        },

        // Check if text is too short
        isTooShort: function(text, minLength = 2) {
            return text.trim().length < minLength;
        },

        // Check for only numbers (for name fields)
        isOnlyNumbers: function(text) {
            return /^\d+$/.test(text.trim());
        },

        // Check for special characters (allow only letters, numbers, spaces, basic punctuation)
        hasInvalidCharacters: function(text) {
            const validPattern = /^[a-zA-Z0-9\s\.\,\-\_\@\(\)]+$/;
            return !validPattern.test(text);
        },

        // Check for HTML tags
        containsHTML: function(text) {
            const htmlRegex = /<[^>]*>/;
            return htmlRegex.test(text);
        },

        // Check if email is valid
        isValidEmail: function(email) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailPattern.test(email);
        },

        // Check username format (for student IDs like 20xx-x-xxxxx)
        isValidUsername: function(username, usertype) {
            if (usertype == 1) { // Student
                const studentPattern = /^20\d{2}-\d{1}-\d{5}$/;
                return studentPattern.test(username);
            }
            // For admin/faculty, allow alphanumeric with basic characters
            const generalPattern = /^[a-zA-Z0-9\._-]{3,50}$/;
            return generalPattern.test(username);
        },

        // Password validation methods
        validatePasswordStrength: function(password) {
            const errors = [];
            
            if (!password || password.trim() === '') {
                return ['Password is required'];
            }

            const trimmedPassword = password.trim();

            // Check for emojis
            if (this.containsEmoji(trimmedPassword)) {
                errors.push('Emojis are not allowed in passwords');
            }

            // Check for HTML/script tags
            if (this.containsHTML(trimmedPassword)) {
                errors.push('HTML tags and scripts are not allowed in passwords');
            }

            // Check minimum length
            if (trimmedPassword.length < 8) {
                errors.push('Password must be at least 8 characters long');
            }

            // Check maximum length (prevent overly long passwords)
            if (trimmedPassword.length > 128) {
                errors.push('Password cannot exceed 128 characters');
            }

            // Check for required character types
            const hasUppercase = /[A-Z]/.test(trimmedPassword);
            const hasLowercase = /[a-z]/.test(trimmedPassword);
            const hasNumber = /\d/.test(trimmedPassword);
            const hasSpecialChar = /[!@#$%^&*\(\)\-_=+\[\]{};:'\",.<>\/?\\|~`]/.test(trimmedPassword);

            if (!hasUppercase) {
                errors.push('Password must contain at least one uppercase letter');
            }
            if (!hasLowercase) {
                errors.push('Password must contain at least one lowercase letter');
            }
            if (!hasNumber) {
                errors.push('Password must contain at least one number');
            }
            if (!hasSpecialChar) {
                errors.push('Password must contain at least one special character');
            }

            // Check for common weak patterns
            if (/(.)\1{2,}/.test(trimmedPassword)) {
                errors.push('Password cannot contain 3 or more consecutive identical characters');
            }

            // Check for sequential characters (like 123, abc)
            if (/(?:abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz|123|234|345|456|567|678|789)/i.test(trimmedPassword)) {
                errors.push('Password cannot contain sequential characters');
            }

            // Check for common weak passwords
            const commonPasswords = ['password', 'password123', '12345678', 'qwerty', 'admin', 'letmein'];
            if (commonPasswords.some(common => trimmedPassword.toLowerCase().includes(common))) {
                errors.push('Password contains common weak patterns');
            }

            return errors;
        },

        validatePasswordMatch: function(password, confirmPassword) {
            if (password !== confirmPassword) {
                return ['Passwords do not match'];
            }
            return [];
        },

        validatePasswordChange: function(currentPassword, newPassword, confirmPassword) {
            const errors = [];

            // Validate current password is not empty
            if (!currentPassword || currentPassword.trim() === '') {
                errors.push('Current password is required');
            }

            // Validate new password strength
            const strengthErrors = this.validatePasswordStrength(newPassword);
            errors.push(...strengthErrors);

            // Validate password match
            const matchErrors = this.validatePasswordMatch(newPassword, confirmPassword);
            errors.push(...matchErrors);

            // Check if new password is different from current
            if (currentPassword && newPassword && currentPassword === newPassword) {
                errors.push('New password must be different from current password');
            }

            return errors;
        },

        // Validate individual field
        validateField: function(fieldName, value, usertype = null) {
            const errors = [];
            
            if (!value || value.trim() === '') {
                if (fieldName !== 'headline' && fieldName !== 'bio') { // These can be optional
                    return ['This field is required'];
                }
                return [];
            }

            const trimmedValue = value.trim();

            // Check for emojis
            if (this.containsEmoji(trimmedValue)) {
                errors.push('Emojis are not allowed');
            }

            // Check for HTML tags and sanitize them
            if (this.containsHTML(trimmedValue)) {
                errors.push('HTML tags are not allowed');
            }

            // Field-specific validations
            switch (fieldName) {
                case 'username':
                    if (!this.isValidUsername(trimmedValue, usertype)) {
                        if (usertype == 1) {
                            errors.push('Student ID must be in format: 20XX-X-XXXXX');
                        } else {
                            errors.push('Username must be 3-50 characters, alphanumeric only');
                        }
                    }
                    break;

                case 'email':
                    if (!this.isValidEmail(trimmedValue)) {
                        errors.push('Please enter a valid email address');
                    }
                    break;

                case 'first_name':
                case 'last_name':
                    if (this.isTooShort(trimmedValue)) {
                        errors.push('Name must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 50)) {
                        errors.push('Name cannot exceed 50 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Name cannot be only numbers');
                    }
                    if (this.hasInvalidCharacters(trimmedValue)) {
                        errors.push('Name contains invalid characters');
                    }
                    break;

                case 'headline':
                    if (trimmedValue && this.isTooLong(trimmedValue, 25)) {
                        errors.push('Headline cannot exceed 25 characters');
                    }
                    break;

                case 'bio':
                    if (trimmedValue && this.isTooLong(trimmedValue, 120)) {
                        errors.push('Bio cannot exceed 120 characters');
                    }
                    break;
            }

            return errors;
        },

        // Display error for specific field
        displayFieldError: function(fieldName, errors) {
            const errorDiv = document.getElementById(fieldName + '-error');
            const inputField = document.getElementById(fieldName);
            
            if (errors.length > 0) {
                errorDiv.textContent = errors.join(', ');
                errorDiv.style.display = 'block';
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontSize = '0.875rem';
                errorDiv.style.marginTop = '0.25rem';
                inputField.classList.add('is-invalid');
                return false;
            } else {
                errorDiv.style.display = 'none';
                inputField.classList.remove('is-invalid');
                return true;
            }
        },

        // Validate entire form
        validateForm: function() {
            const form = document.querySelector('.form-auth');
            const formData = new FormData(form);
            let isValid = true;
            
            // Get user type from session
            const usertype = <?php echo $_SESSION['usertype']; ?>;
            
            // Fields to validate (excluding password fields)
            const fieldsToValidate = ['first_name', 'last_name', 'username', 'headline', 'bio'];
            
            // Only validate email if user is admin
            if (usertype == 0) {
                fieldsToValidate.push('email');
            }

            fieldsToValidate.forEach(fieldName => {
                const value = formData.get(fieldName) || '';
                const fieldErrors = this.validateField(fieldName, value, usertype);
                const fieldValid = this.displayFieldError(fieldName, fieldErrors);
                if (!fieldValid) {
                    isValid = false;
                }
            });

            // Validate password fields if any password field is filled
            const currentPassword = formData.get('password') || '';
            const newPassword = formData.get('newpassword') || '';
            const confirmPassword = formData.get('confirmpassword') || '';

            if (currentPassword || newPassword || confirmPassword) {
                const passwordErrors = this.validatePasswordChange(currentPassword, newPassword, confirmPassword);
                
                if (passwordErrors.length > 0) {
                    this.displayPasswordErrors(passwordErrors);
                    isValid = false;
                } else {
                    this.clearPasswordErrors();
                }
            } else {
                this.clearPasswordErrors();
            }

            return isValid;
        },

        // Display password errors
        displayPasswordErrors: function(errors) {
            const generalErrorDiv = document.getElementById('password-general-error');
            
            if (errors.length > 0) {
                generalErrorDiv.textContent = errors.join(', ');
                generalErrorDiv.style.display = 'block';
                generalErrorDiv.style.color = '#dc3545';
                generalErrorDiv.style.fontSize = '0.875rem';
                generalErrorDiv.style.marginTop = '0.25rem';
                
                // Add invalid class to password fields
                ['password', 'newpassword', 'confirmpassword'].forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field && field.value) {
                        field.classList.add('is-invalid');
                    }
                });
            }
        },

        // Clear password errors
        clearPasswordErrors: function() {
            const generalErrorDiv = document.getElementById('password-general-error');
            generalErrorDiv.style.display = 'none';
            
            ['password', 'newpassword', 'confirmpassword'].forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const errorDiv = document.getElementById(fieldName + '-error');
                if (field) field.classList.remove('is-invalid');
                if (errorDiv) errorDiv.style.display = 'none';
            });
        }
    };

    // Setup real-time validation
    document.addEventListener('DOMContentLoaded', function() {
        const usertype = <?php echo $_SESSION['usertype']; ?>;
        const fieldsToValidate = ['first_name', 'last_name', 'username', 'headline', 'bio'];
        
        // Only validate email if user is admin
        if (usertype == 0) {
            fieldsToValidate.push('email');
        }

        fieldsToValidate.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', function() {
                    const value = this.value;
                    const fieldErrors = ProfileValidation.validateField(fieldName, value, usertype);
                    ProfileValidation.displayFieldError(fieldName, fieldErrors);
                });
            }
        });

        // Real-time password validation
        const passwordFields = ['password', 'newpassword', 'confirmpassword'];
        
        passwordFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', function() {
                    const currentPassword = document.getElementById('password').value;
                    const newPassword = document.getElementById('newpassword').value;
                    const confirmPassword = document.getElementById('confirmpassword').value;
                    
                    // Only validate if any password field has content
                    if (currentPassword || newPassword || confirmPassword) {
                        const errors = ProfileValidation.validatePasswordChange(currentPassword, newPassword, confirmPassword);
                        
                        if (errors.length > 0) {
                            ProfileValidation.displayPasswordErrors(errors);
                        } else {
                            ProfileValidation.clearPasswordErrors();
                        }
                    } else {
                        ProfileValidation.clearPasswordErrors();
                    }
                });
            }
        });

        // Form submission validation
        const form = document.querySelector('.form-auth');
        form.addEventListener('submit', function(e) {
            if (!ProfileValidation.validateForm()) {
                e.preventDefault();
                // Show toast instead of alert
                showToast('Validation Error', 'Please fix the validation errors before submitting', 'error');
                return false;
            }
        });
    });
</script>

<style>
/* Form validation error styles */
.validation-error {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.is-invalid:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}
</style>
