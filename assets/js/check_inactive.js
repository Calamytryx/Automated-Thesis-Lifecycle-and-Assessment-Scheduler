/**
 * This script checks for user inactivity and redirects to the login page if the user is inactive.
 * It sends an AJAX GET request to the server every 5 seconds to check the user's activity status.
 * If the server responds with 'logout_redirect', the user is redirected to the login page.
 *
 * @file /c:/xampp/htdocs/coecsathesis/assets/js/check_inactive.js
 * @requires jQuery
 */
$(document).ready(function() {
    setInterval(function() {
        $.ajax({
            type: 'GET',
            async: false,
            url: '../assets/includes/checkinactive.ajax.php',
            success: function(response) {
                if (response == 'logout_redirect') {
                    location.href = "../login/";
                }
            }
        });
    }, 5000);
});