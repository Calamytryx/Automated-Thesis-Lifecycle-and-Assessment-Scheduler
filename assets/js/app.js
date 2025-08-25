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

async function analyzeTitle(title, field, problem) {
    try {
        // Sanitize inputs before sending to external APIs
        const sanitizedTitle = title.replace(/<[^>]*>/g, '').trim();
        const sanitizedField = field.replace(/<[^>]*>/g, '').trim();
        const sanitizedProblem = problem.replace(/<[^>]*>/g, '').trim();
        
        // Additional validation before API calls
        if (!sanitizedTitle || !sanitizedField || !sanitizedProblem) {
            throw new Error('Invalid input data after sanitization');
        }
        
        // First prompt to check similarity
        const similarityPrompt = `Check the similarity of the following research title in terms of final output with existing titles: "${sanitizedTitle}". Existing titles: ${existingTitles}. Rate the similarity on a scale of 1 to 10 and provide the most similar title. Format the response strictly as "score(number only): 'title'".`;
        
        console.log("Sending similarity prompt to AI:", similarityPrompt);
        const similarityResponse = await sendMessageToModel(similarityPrompt);
        console.log("Received similarity response:", similarityResponse);
        
        const [similarityScore, similarTitleMatch] = similarityResponse.split(':');
        console.log("Similarity Score:", similarityScore)
        const scoreMatch = similarityScore.match(/\d+/);
        const similarityScoreFloat = scoreMatch ? parseFloat(scoreMatch[0]) : 0;
        // const similarityScoreFloat = parseFloat(similarityScore.trim());
        const similarTitle = similarTitleMatch ? similarTitleMatch.trim().replace(/['"]/g, '') : 'N/A';
        
        if (similarityScoreFloat < 5) {
            // Proceed to analyze as usual
            const analysisPrompt = `Analyze the following research title in the field of ${sanitizedField}: "${sanitizedTitle}" with the problem to solve of ${sanitizedProblem}. 
                Provide feedback on its 1. clarity, 2. specificity, and 3. potential impact. 
                Also, assess its potential uniqueness and originality.
                Additionally, evaluate if the research problem is feasible or if further investigation is needed to determine its feasibility.
                If improvements are needed, suggest up to three alternative titles.
                
                Format the response as follows:
                H2 Analysis of Research Title: "${sanitizedTitle}"
                strong Clarity: (feedback)
                strong Specificity: (feedback)
                strong Potential Impact: (feedback)
                strong Uniqueness: (feedback)
                strong Originality: (feedback)
                strong Problem: (feedback)
                H3 Alternative Titles:
                1. (title 1)
                2. (title 2)
                3. (title 3)
                
                Ensure the feedback is clear, concise, and actionable. Do not use tilde or code blocks.`;
        
            console.log("Sending analysis prompt to AI:", analysisPrompt);
            const aiResponse = await sendMessageToModel(analysisPrompt);
            console.log("Received AI response:", aiResponse);
            
            // Update uniqueness result
            document.getElementById('uniquenessResult').innerHTML = `
                <div class="d-flex align-items-center mb-3">
                    <div class="badge bg-success me-3 px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i>
                        Unique (${similarityScoreFloat}% similarity)
                    </div>
                    <div>
                        <div class="fw-semibold text-success">Good Uniqueness Score</div>
                        <small class="text-muted">Your title appears to be sufficiently unique</small>
                    </div>
                </div>
                ${similarTitle !== 'N/A' ? `<p class="text-muted mb-0"><strong>Most similar title:</strong> ${similarTitle}</p>` : ''}
            `;
            
            // Update AI suggestions
            document.getElementById('aiSuggestions').innerHTML = marked.parse(aiResponse);
            
        } else if (confirm(`The title is similar to an existing title (${similarityScoreFloat}% similarity): '${similarTitle}'. Do you still want to proceed with the analysis?`)) {
            // separated the two conditions so this confirm will only show if similarity is higher than 5.
            // Proceed to analyze as usual
            const analysisPrompt = `Analyze the following research title in the field of ${sanitizedField}: "${sanitizedTitle}" with the problem to solve of ${sanitizedProblem}. 
                Provide feedback on its 1. clarity, 2. specificity, and 3. potential impact. 
                Also, assess its potential uniqueness and originality.
                Additionally, evaluate if the research problem is feasible or if further investigation is needed to determine its feasibility.
                If improvements are needed, suggest up to three alternative titles.
                
                Format the response as follows:
                H2 Analysis of Research Title: "${sanitizedTitle}"
                strong Clarity: (feedback)
                strong Specificity: (feedback)
                strong Potential Impact: (feedback)
                strong Uniqueness: (feedback)
                strong Originality: (feedback)
                strong Problem: (feedback)
                H3 Alternative Titles:
                1. (title 1)
                2. (title 2)
                3. (title 3)
                
                Ensure the feedback is clear, concise, and actionable. Do not use tilde or code blocks.`;
        
            console.log("Sending analysis prompt to AI:", analysisPrompt);
            const aiResponse = await sendMessageToModel(analysisPrompt);
            console.log("Received AI response:", aiResponse);
            
            // Update uniqueness result with warning
            document.getElementById('uniquenessResult').innerHTML = `
                <div class="d-flex align-items-center mb-3">
                    <div class="badge bg-warning text-dark me-3 px-3 py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Similar (${similarityScoreFloat}% similarity)
                    </div>
                    <div>
                        <div class="fw-semibold text-warning">Moderate Similarity Detected</div>
                        <small class="text-muted">Consider revising for better uniqueness</small>
                    </div>
                </div>
                <div class="alert alert-warning mb-0">
                    <strong>Most similar title:</strong> ${similarTitle}
                    <br><small>You may want to revise your title to improve uniqueness.</small>
                </div>
            `;
            
            // Update AI suggestions
            document.getElementById('aiSuggestions').innerHTML = marked.parse(aiResponse);
            
        } else {
            // User canceled analysis
            document.getElementById('uniquenessResult').innerHTML = `
                <div class="d-flex align-items-center mb-3">
                    <div class="badge bg-danger me-3 px-3 py-2">
                        <i class="bi bi-x-circle me-1"></i>
                        Too Similar (${similarityScoreFloat}% similarity)
                    </div>
                    <div>
                        <div class="fw-semibold text-danger">High Similarity Detected</div>
                        <small class="text-muted">Significant revision recommended</small>
                    </div>
                </div>
                <div class="alert alert-danger mb-0">
                    <strong>Very similar to:</strong> ${similarTitle}
                    <br><small>Please revise your title to ensure uniqueness and avoid potential issues.</small>
                </div>
            `;
            
            document.getElementById('aiSuggestions').innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-lightbulb text-muted mb-3" style="font-size: 2.5rem;"></i>
                    <h6 class="text-muted">Analysis Cancelled</h6>
                    <p class="text-muted mb-0">Please revise your title and try again for a complete analysis.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error in analyzeTitle:', error);
        document.getElementById('uniquenessResult').innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Analysis Error</strong><br>
                <small>Unable to analyze title. Please check your connection and try again.</small>
            </div>
        `;
        document.getElementById('aiSuggestions').innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-exclamation-triangle text-danger mb-3" style="font-size: 2.5rem;"></i>
                <h6 class="text-danger">Error Generating Suggestions</h6>
                <p class="text-muted mb-0">Please try again later.</p>
            </div>
        `;
    }
}

// Function to validate form and update button state
function validateForm() {
    const title = document.getElementById('researchTitle')?.value.trim();
    const field = document.getElementById('researchField')?.value.trim();
    const problem = document.getElementById('problem')?.value.trim();
    const submitBtn = document.getElementById('submitTitleBtn');
    const statusText = document.querySelector('.research-title-status small');
    
    // Check if all fields have content and are valid
    const hasContent = title && field && problem;
    const isValid = hasContent && ValidationUtils.validateForm();
    
    if (isValid) {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-search me-2"></i>Analyze Title';
        }
        if (statusText) {
            statusText.textContent = 'Ready to analyze your research title';
            statusText.style.color = 'var(--primary-600)';
        }
    } else {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-search me-2"></i>Analyze Title';
        }
        if (statusText) {
            if (!hasContent) {
                statusText.textContent = 'Fill in all fields to analyze your title';
            } else {
                statusText.textContent = 'Please fix validation errors before proceeding';
            }
            statusText.style.color = 'var(--neutral-600)';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log("Document ready, initializing chat session...");
    // Initialize chat session only if in the relevant page
    if (typeof initializeChatSession === 'function') {
        initializeChatSession();
    }

    // Only add these event listeners if the elements exist
    const submitTitleBtn = document.getElementById('submitTitleBtn');
    if (submitTitleBtn) {
        submitTitleBtn.addEventListener('click', function() {
            console.log("Submit button clicked");
            
            // First validate all fields using ValidationUtils
            if (!validateResearchTitleForm()) {
                console.log("Form validation failed");
                return;
            }
            
            var title = document.getElementById('researchTitle').value.trim();
            var field = document.getElementById('researchField').value.trim();
            var problem = document.getElementById('problem').value.trim();
            
            // Basic presence validation (already checked by ValidationUtils, but kept for consistency)
            if (!title || !field || !problem) {
                alert('Please fill in all fields before analyzing your title.');
                return;
            }
            
            console.log("Analyzing title:", title, "in field:", field + " with problem to solve of " + problem);
            
            // Hide empty state and show loading
            document.getElementById('emptyState').style.display = 'none';
            showLoadingState();
            
            // Show result cards
            document.getElementById('uniquenessCard').style.display = 'block';
            document.getElementById('suggestionsCard').style.display = 'block';

            analyzeTitle(title, field, problem);
        });
        
        // Add input validation listeners using ValidationUtils
        const requiredFields = ['researchTitle', 'researchField', 'problem'];
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', validateForm);
            }
        });
        
        // Setup real-time validation
        ValidationUtils.setupRealTimeValidation();
    }

    // Add reset button functionality
    const resetFieldsBtn = document.getElementById('resetFieldsBtn');
    if (resetFieldsBtn) {
        resetFieldsBtn.addEventListener('click', function() {
            console.log("Reset button clicked");
            resetResearchTitleForm();
        });
    }

    // Call the new AI processing function on page load
    processOutputToAI();
});

// Function to show loading state in result cards
function showLoadingState() {
    const uniquenessResult = document.getElementById('uniquenessResult');
    const aiSuggestions = document.getElementById('aiSuggestions');
    
    if (uniquenessResult) {
        uniquenessResult.innerHTML = `
            <div class="research-title-loading">
                <div class="spinner-border" role="status"></div>
                <span>Analyzing title uniqueness...</span>
            </div>
        `;
    }
    
    if (aiSuggestions) {
        aiSuggestions.innerHTML = `
            <div class="research-title-loading">
                <div class="spinner-border" role="status"></div>
                <span>Generating AI suggestions...</span>
            </div>
        `;
    }
}

// Function to reset the research title form to empty state
function resetResearchTitleForm() {
    // Hide result cards
    document.getElementById('uniquenessCard').style.display = 'none';
    document.getElementById('suggestionsCard').style.display = 'none';
    
    // Show empty state
    document.getElementById('emptyState').style.display = 'block';
    
    // Clear form fields
    document.getElementById('researchTitle').value = '';
    document.getElementById('researchField').value = '';
    document.getElementById('problem').value = '';
    
    // Reset validation
    validateForm();
}

// Add reset functionality when form is cleared
const formFields = ['researchTitle', 'researchField', 'problem'];
formFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
        field.addEventListener('input', function() {
            // If all fields are empty, show empty state
            const allEmpty = formFields.every(id => 
                !document.getElementById(id)?.value.trim()
            );
            
            if (allEmpty) {
                setTimeout(() => {
                    const cardsVisible = document.getElementById('uniquenessCard').style.display !== 'none' ||
                                      document.getElementById('suggestionsCard').style.display !== 'none';
                    if (cardsVisible) {
                        resetResearchTitleForm();
                    }
                }, 500); // Small delay to prevent flickering while typing
            }
        });
    }
});

// Call validateForm on page load to set initial state
setTimeout(validateForm, 100);

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
        Do not use tilde or code blocks.
        Ensure the topics are general enough to encompass multiple potential specific projects. Format the response as a simple HTML table without any classes or styles.`;

        console.log("Sending thesis topic prompt to AI:", prompt);
        const aiResponse = await sendMessageToModel(prompt);
        console.log("Received AI response for thesis topics:", aiResponse);
        
        // Replace the default table with a Bootstrap styled table
        let formattedResponse = aiResponse.replace('<table>', '<table class="table table-hover table-bordered table-striped rounded overflow-hidden">');

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
    const getTopicsBtn = document.getElementById('getTopicsBtn');
    if (getTopicsBtn) {
        getTopicsBtn.addEventListener('click', function() {
            const field = document.getElementById('thesisField').value;
            
            if (!field) {
                alert('Please select a field');
                return;
            }

        console.log("Getting top thesis topics for field:", field);
        document.getElementById('topicAnalysisResult').innerHTML = '<p>Generating top thesis topics...</p>';

        getTopThesisTopics(field);
        });
    }
});

// New function to send output to AI and display the response
async function processOutputToAI() {
    const aiOutputElement = document.getElementById('ai-output');
    const outputPdfElement = document.getElementById('output-pdf');
    
    // Check if elements exist before trying to use them
    if (!aiOutputElement || !outputPdfElement) {
        console.log('Required elements are not available on this page');
        return;
    }
    
    aiOutputElement.innerHTML = 'Processing output...';
    let outputValue = '';
    while (!outputValue) {
        await new Promise(resolve => setTimeout(resolve, 1000));
        outputValue = outputPdfElement.value;
    }
    
    try {
        const prompt = `Analyze the following thesis content and provide a chapter-by-chapter summary and analysis:

        First, identify if this is a proposal (contains only introduction, literature review, methodology) or a final paper (includes chapters 4 and 5: results, discussion, conclusion).
        
        For each chapter and its major sections (background, statement of the problem, objectives, etc.), provide:
        1. A brief summary (2-3 sentences)
        2. Key points identified
        
        Then provide an overall analysis with:
        - Strengths
        - Weaknesses
        - Suggested revisions
        
        Format your response in markdown as follows:
        H2 Chapter-by-Chapter Summary of [Proposal/Final] Paper: [TITLE]
        
        H3 Chapter 1: Introduction
        H4 Background of the Study
        - Summary: [brief summary]
        - Key points: [bullet points]
        
        H4 Statement of the Problem
        - Summary: [brief summary]
        - Key points: [bullet points]
        
        [Continue for each section and chapter]
        
        H3 Overall Analysis
        strong Strengths:
        - [list strengths]
        
        strong Weaknesses:
        - [list weaknesses]
        
        strong Suggested Revisions:
        - [list revision suggestions]
        
        Analyze this:
        \n\n${outputValue}`;
        const aiResponse = await sendMessageToModel(prompt);
        document.getElementById('ai-output').innerHTML = marked.parse(aiResponse);
    } catch (error) {
        console.error('Error processing AI response:', error);
        document.getElementById('ai-output').innerText = 'An error occurred while processing the AI response.';
    }
}

document.addEventListener('DOMContentLoaded', function() {
        processOutputToAI();
});

// ============================================
// UNIVERSAL VALIDATION FRAMEWORK
// ============================================

/**
 * Universal validation framework for consistent input validation across all dashboard tabs
 * Provides field-level error messages and prevents form submission until issues are resolved
 */
window.UniversalValidator = (function() {
    
    // Simple patterns for client-side validation (server-side has comprehensive patterns)
    const PATTERNS = {
        HTML: /<[^>]*>/g,
        // Simplified emoji pattern covering most common emoji ranges
        EMOJI: /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/u
    };
    
    /**
     * Check if string contains HTML tags
     */
    function containsHtml(str) {
        return PATTERNS.HTML.test(str);
    }
    
    /**
     * Check if string contains emojis
     */
    function containsEmojis(str) {
        return PATTERNS.EMOJI.test(str);
    }
    
    /**
     * Clear validation errors for a specific input
     */
    function clearValidationErrors($input) {
        $input.removeClass('is-invalid');
        $input.siblings('.invalid-feedback').remove();
    }
    
    /**
     * Show validation error for a specific input
     */
    function showValidationError($input, message) {
        $input.addClass('is-invalid');
        $input.after(`<div class="invalid-feedback">${message}</div>`);
    }
    
    /**
     * Validate a text field for HTML and emoji content
     * @param {jQuery} $input - The input element to validate
     * @param {string} fieldName - Human-readable field name for error messages
     * @param {Object} options - Validation options
     * @param {boolean} options.allowHtml - Whether to allow HTML tags (default: false)
     * @param {boolean} options.allowEmojis - Whether to allow emojis (default: false)
     * @returns {boolean} - True if valid, false if invalid
     */
    function validateTextField($input, fieldName, options = {}) {
        const value = $input.val();
        const allowHtml = options.allowHtml || false;
        const allowEmojis = options.allowEmojis || false;
        let isValid = true;
        
        // Clear any previous validation errors
        clearValidationErrors($input);
        
        // Check for HTML tags (unless explicitly allowed)
        if (!allowHtml && containsHtml(value)) {
            showValidationError($input, `HTML tags are not allowed in ${fieldName}.`);
            isValid = false;
        }
        
        // Check for emojis (unless explicitly allowed)
        if (!allowEmojis && containsEmojis(value)) {
            showValidationError($input, `Emojis are not allowed in ${fieldName}.`);
            isValid = false;
        }
        
        return isValid;
    }
    
    /**
     * Validate multiple text fields at once
     * @param {Array} fieldsConfig - Array of field configuration objects
     * @returns {boolean} - True if all fields are valid, false if any are invalid
     */
    function validateMultipleFields(fieldsConfig) {
        let allValid = true;
        
        fieldsConfig.forEach(config => {
            const $input = $(config.selector);
            const fieldName = config.fieldName;
            const options = config.options || {};
            
            if ($input.length > 0) {
                const isValid = validateTextField($input, fieldName, options);
                if (!isValid) {
                    allValid = false;
                }
            }
        });
        
        return allValid;
    }
    
    /**
     * Setup real-time validation for a form (prevents duplicate listeners)
     * @param {string} formSelector - CSS selector for the form container
     */
    function setupRealtimeValidation(formSelector) {
        // Remove any existing validation listeners for this form to prevent duplicates
        $(document).off('blur.universalValidation input.universalValidation', formSelector + ' input, ' + formSelector + ' textarea');
        
        // Set up validation for text inputs and textareas within the specified form
        $(document).on('blur.universalValidation', formSelector + ' input[type="text"], ' + formSelector + ' input[name="name"], ' + formSelector + ' input[name="college"], ' + formSelector + ' input[name="department"], ' + formSelector + ' input[name="specialization"], ' + formSelector + ' textarea', function() {
            const $input = $(this);
            const fieldName = $input.attr('name') || $input.attr('id') || 'field';
            validateTextField($input, fieldName);
        });
        
        // Also validate on input to clear errors as user types valid content
        $(document).on('input.universalValidation', formSelector + ' input[type="text"], ' + formSelector + ' input[name="name"], ' + formSelector + ' input[name="college"], ' + formSelector + ' input[name="department"], ' + formSelector + ' input[name="specialization"], ' + formSelector + ' textarea', function() {
            const $input = $(this);
            const value = $input.val();
            
            // Only clear errors if the content is now valid
            if (!containsHtml(value) && !containsEmojis(value)) {
                clearValidationErrors($input);
            }
        });
    }
    
    // Public API
    return {
        containsHtml: containsHtml,
        containsEmojis: containsEmojis,
        validateTextField: validateTextField,
        validateMultipleFields: validateMultipleFields,
        setupRealtimeValidation: setupRealtimeValidation,
        clearValidationErrors: clearValidationErrors,
        showValidationError: showValidationError
    };
})();

// Custom validation function for research title form
function validateResearchTitleForm() {
    let isValid = true;
    const fields = ['researchTitle', 'researchField', 'problem'];
    
    fields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input && !ValidationUtils.validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded - initializing research title validation");
    // ValidationUtils handles real-time validation setup
    ValidationUtils.setupRealTimeValidation();
});