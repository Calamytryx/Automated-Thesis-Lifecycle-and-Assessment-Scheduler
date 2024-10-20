<script type="module">
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