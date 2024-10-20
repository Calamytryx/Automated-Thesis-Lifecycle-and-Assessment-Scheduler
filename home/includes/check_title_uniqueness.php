
<script type="module">
/**
 * This script initializes a chat session and handles the submission of a research title form.
 * It uses the Gemini AI model to analyze the research title for clarity, specificity, potential impact,
 * uniqueness, and originality. If improvements are needed, it suggests up to three alternative titles.
 *
 * Features:
 * - Initializes the chat session when the page loads.
 * - Listens for the submission of the research title form.
 * - Prevents the default form submission behavior.
 * - Sends the research title and field to the Gemini AI model for analysis.
 * - Displays the AI analysis and suggestions on the webpage.
 * - Handles errors and displays an error message if the analysis fails.
 *
 * Dependencies:
 * - jQuery library
 * - mainModule.js (for initializeChatSession and sendMessageToModel functions)
 *
 * HTML Elements:
 * - #titleSubmissionForm: The form element for submitting the research title.
 * - #researchTitle: Input field for the research title.
 * - #researchField: Input field for the research field.
 * - #uniquenessResult: Element to display the AI analysis result.
 * - #aiSuggestions: Element to display AI suggestions for alternative titles.
 */
import { initializeChatSession, sendMessageToModel } from '../assets/js/mainModule.js';

$(document).ready(function() {
    // Initialize the chat session when the page loads
    initializeChatSession();

    // Research Title Acceptance Tool
    $('#titleSubmissionForm').on('submit', async function(e) {
        e.preventDefault();
        var title = $('#researchTitle').val();
        var field = $('#researchField').val();
        
        $('#uniquenessResult').html('<p>Analyzing title...</p>');
        $('#aiSuggestions').html('');

        try {
            // Use Gemini AI for analysis and suggestions
            const prompt = `Analyze the following research title in the field of ${field}: "${title}". 
            Provide feedback on its clarity, specificity, and potential impact. 
            Also, assess its potential uniqueness and originality.
            If improvements are needed, suggest up to three alternative titles.`;

            const aiResponse = await sendMessageToModel(prompt);
            
            $('#uniquenessResult').html('<h5>AI Analysis:</h5>');
            $('#aiSuggestions').html('<p>' + aiResponse + '</p>');
        } catch (error) {
            console.error('Error:', error);
            $('#uniquenessResult').html('<p>Error analyzing title. Please try again.</p>');
            $('#aiSuggestions').html('');
        }
    });

    // ... (keep the rest of your existing code)
});
</script>