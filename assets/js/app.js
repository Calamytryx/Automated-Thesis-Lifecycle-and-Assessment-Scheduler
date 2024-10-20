/**
 * Initializes the chat session and sets up event listeners for the document.
 * 
 * @function
 * @name document.addEventListener
 * @param {string} event - The event type to listen for.
 * @param {function} listener - The function to execute when the event is triggered.
 */

import { initializeChatSession, sendMessageToModel, performWebSearch } from './mainModule.js';

/**
 * Analyzes a research title in a given field and provides feedback on its clarity, specificity, potential impact, uniqueness, and originality.
 * If improvements are needed, suggests up to three alternative titles.
 * 
 * @async
 * @function analyzeTitle
 * @param {string} title - The research title to be analyzed.
 * @param {string} field - The field of research for the title.
 * @returns {Promise<void>}
 */

async function analyzeTitle(title, field) {
    try {
        const prompt = `Analyze the following research title in the field of ${field}: "${title}". 
        Provide feedback on its clarity, specificity, and potential impact. 
        Also, assess its potential uniqueness and originality.
        If improvements are needed, suggest up to three alternative titles.
        Format your response in markdown.`;

        console.log("Sending prompt to AI:", prompt);
        const aiResponse = await sendMessageToModel(prompt);
        console.log("Received AI response:", aiResponse);
        
        document.getElementById('uniquenessResult').innerHTML = '<h5>AI Analysis:</h5>';
        document.getElementById('aiSuggestions').innerHTML = marked.parse(aiResponse);
    } catch (error) {
        console.error('Error in analyzeTitle:', error);
        document.getElementById('uniquenessResult').innerHTML = '<p>Error analyzing title. Please try again.</p>';
        document.getElementById('aiSuggestions').innerHTML = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log("Document ready, initializing chat session...");
    initializeChatSession();

    document.getElementById('submitTitleBtn').addEventListener('click', function() {
        console.log("Submit button clicked");
        var title = document.getElementById('researchTitle').value;
        var field = document.getElementById('researchField').value;
        
        console.log("Analyzing title:", title, "in field:", field);
        document.getElementById('uniquenessResult').innerHTML = '<p>Analyzing title...</p>';
        document.getElementById('aiSuggestions').innerHTML = '';

        analyzeTitle(title, field);
    });
});


/**
 * Retrieves the top thesis topics for a given field by performing a web search and generating a table of broad research areas.
 * 
 * @async
 * @function getTopThesisTopics
 * @param {string} field - The field of research for which to generate thesis topics.
 * @returns {Promise<void>}
 */

async function getTopThesisTopics(field) {
    try {
        // Perform a web search first
        const searchQuery = `current research areas in ${field}`;
        const searchResults = await performWebSearch(searchQuery);

        // Prepare the prompt with web search results
        const prompt = `Based on the following web search results about current research areas in ${field}:
        ${searchResults.map(result => `- ${result.title}: ${result.snippet}`).join('\n')}

        Generate a table of 10 broad and generalized thesis topic areas for ${field}. These should be overarching themes or areas of research rather than specific project titles. The table should have three columns: 
        1. "Research Area" (a broad topic or theme)
        2. "Description" (a brief explanation of the research area)
        3. "Potential Impact" (the significance of research in this area)
        
        Ensure the topics are general enough to encompass multiple potential specific projects. Format the response as a simple HTML table without any classes or styles.`;

        console.log("Sending thesis topic prompt to AI:", prompt);
        const aiResponse = await sendMessageToModel(prompt);
        console.log("Received AI response for thesis topics:", aiResponse);
        
        // Replace the default table with a Bootstrap styled table
        let formattedResponse = marked.parse(aiResponse).replace('<table>', '<table class="table table-hover table-bordered table-striped rounded overflow-hidden">');
        
        // Wrap the table in a responsive div
        formattedResponse = `<div class="table-responsive">${formattedResponse}</div>`;
        
        // Add a note about the nature of the topics
        formattedResponse = `
            <p class="text-muted mb-3">These are broad research areas in ${field} based on current trends and AI analysis. Each area can encompass multiple specific thesis topics.</p>
            ${formattedResponse}
            <p class="text-muted mt-3">Consider these areas as starting points for developing more specific thesis topics aligned with your interests and the latest developments in ${field}.</p>
        `;
        
        document.getElementById('topicAnalysisResult').innerHTML = formattedResponse;
    } catch (error) {
        console.error('Error in getTopThesisTopics:', error);
        document.getElementById('topicAnalysisResult').innerHTML = '<p class="text-danger">Error generating thesis topics. Please try again.</p>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('getTopicsBtn').addEventListener('click', function() {
        const field = document.getElementById('thesisField').value;
        
        if (!field) {
            alert('Please select a field');
            return;
        }

        console.log("Getting top thesis topics for field:", field);
        document.getElementById('topicAnalysisResult').innerHTML = '<p>Generating top thesis topics...</p>';

        getTopThesisTopics(field);
    });
});