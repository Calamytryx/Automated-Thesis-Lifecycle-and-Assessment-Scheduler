/**
 * This script checks for user inactivity and redirects to the login page if the user is inactive.
 * It sends an asynchronous AJAX request to check the user's activity status at regular intervals.
 * If the server responds with 'logout_redirect', the user is redirected to the login page.
 *
 * @file c:\xampp\htdocs\atlas\assets\js\check_inactive.js
 * @requires jQuery
 */
$(document).ready(function() {
    // Track if a request is in progress to prevent overlapping requests
    let checkInProgress = false;
    
    // Use a longer interval (30 seconds instead of 5) to reduce server load
    const checkInterval = 30000; 
    
    // Function to check user activity status
    function checkUserActivity() {
        // Don't send a new request if one is already in progress
        if (checkInProgress) return;
        
        checkInProgress = true;
        $.ajax({
            type: 'GET',
            url: '/assets/includes/checkinactive.ajax.php',
            // Removed async:false to prevent UI blocking
            timeout: 15000, // Allow a little more room if the PHP session was briefly busy
            success: function(response) {
                if (response == 'logout_redirect') {
                    location.href = "../login/";
                }
            },
            error: function(xhr, status, error) {
                // Ignore transient aborts/timeouts; session locking or navigation can cause readyState 0.
                if (status === 'timeout' || status === 'abort') {
                    return;
                }
                console.log("Session check error:", xhr, status, error);
            },
            complete: function() {
                checkInProgress = false;
            }
        });
    }
    
    // Set up interval for checking
    setInterval(checkUserActivity, checkInterval);
    
    // Also check on user interaction (resets the server-side timer)
    // Note: Removed _.debounce due to undefined error. Consider adding Lodash/Underscore
    // or implementing a custom debounce if frequent calls become an issue.
    $(document).on('click keypress', function() {
        checkUserActivity();
    }); 
});