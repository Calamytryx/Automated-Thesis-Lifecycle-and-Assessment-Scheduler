// ... existing HTML and PHP ...

<script>
// ... existing setup and event listeners ...

evaluationForm.addEventListener('submit', function(event) {
    event.preventDefault();
    // ... existing data collection logic ...

    // Submit the data
    fetch('submit_evaluation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            // Handle HTTP errors (like 404, 500)
            return response.text().then(text => {
                throw new Error(`HTTP error ${response.status}: ${text || 'Server error'}`);
            });
        }
        return response.json(); // Parse JSON body
    })
    .then(result => {
        console.log('Submission response:', result); // Log the result for debugging

        // **** CORRECTED LOGIC ****
        // Check the 'status' field in the JSON response
        if (result.status === 'success') {
            // Handle success
            alert('Success! ' + result.message); // Or use a more sophisticated notification
            // Optionally redirect or clear the form
            // window.location.href = 'thank_you_page.php'; // Example redirect
        } else {
            // Handle application-level errors reported by the backend
            throw new Error(result.message || 'An unknown error occurred during submission.');
        }
        // **** END CORRECTED LOGIC ****
    })
    .catch(error => {
        // Handle fetch errors, network errors, or errors thrown from .then() blocks
        console.error('Submission Error:', error);
        alert('Error! Could not submit evaluation: ' + error.message); // Display the error
    });
});

// ... other existing JavaScript ...
</script>

// ... existing HTML ...
