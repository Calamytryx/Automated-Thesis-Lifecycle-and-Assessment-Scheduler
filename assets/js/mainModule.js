import { HarmBlockThreshold, HarmCategory, GoogleGenerativeAI } from "@google/generative-ai";
import { GoogleAIFileManager } from "@google/generative-ai/server";

const API_KEY = "AIzaSyBSE1RdMjnZA7w83hBJW9EwF4fpuRdgp_c";
const genAI = new GoogleGenerativeAI(API_KEY);
const fileManager = new GoogleAIFileManager(API_KEY);

// Model configuration
const model = genAI.getGenerativeModel({
    model: "gemini-1.5-flash-002",
    systemInstruction: `You are ATLAS: Advanced Thesis Logistics and AI System for Lyceum of the Philippines University Cavite. 
    Your primary function is to assist with the "AI-Driven System for Efficient Scheduling and Performance Assessment of College Research Presentations in the College of Engineering, Computer Studies, and Architecture (COECSA) at Lyceum of the Philippines University-Cavite Campus (LPU-C)".
    
    Your capabilities include:
    1. Analyzing and providing feedback on research titles and thesis topics.
    2. Suggesting relevant and innovative thesis topics in various fields of engineering, computer studies, and architecture.
    3. Assisting with scheduling of research presentations.
    4. Providing performance assessments and constructive feedback for research presentations.
    5. Offering insights on research methodologies and best practices specific to COECSA disciplines.

    When interacting, always consider the context of LPU-C and the specific needs of COECSA students and faculty. Provide accurate, helpful, and encouraging responses that align with academic standards and promote innovative research in engineering, computer studies, and architecture fields.`
});

// Generation configuration
const generationConfig = {
    temperature: 2,
    topP: 0.95,
    topK: 40,
    maxOutputTokens: 8192,
    responseMimeType: "text/plain",
};

// Safety settings configuration
const safetySettings = [
    {
        category: HarmCategory.HARM_CATEGORY_HARASSMENT,
        threshold: HarmBlockThreshold.BLOCK_NONE,
    },
    {
        category: HarmCategory.HARM_CATEGORY_HATE_SPEECH,
        threshold: HarmBlockThreshold.BLOCK_NONE,
    },
    {
        category: HarmCategory.HARM_CATEGORY_SEXUALLY_EXPLICIT,
        threshold: HarmBlockThreshold.BLOCK_NONE,
    },
    {
        category: HarmCategory.HARM_CATEGORY_DANGEROUS_CONTENT,
        threshold: HarmBlockThreshold.BLOCK_NONE,
    }
];

// Create a variable to store the chat session
let chatSession = null;

/**
 * Initializes the chat session with the AI model.
 * This function is called only once to set up the chat session.
 * 
 * @returns {Promise<void>} A promise that resolves when the chat session is initialized.
 */

// Function to initialize the chat session (only done once)
export async function initializeChatSession() {
    console.log("Initializing chat session in mainModule.js");
    if (!chatSession) {
        chatSession = model.startChat({
            generationConfig,
            safetySettings,
        });
    }
}

// Add this function to log all API calls
function logApiCall(functionName, input, output) {
    console.log(`API Call to ${functionName}:`);
    console.log("Input:", input);
    console.log("Output:", output);
}

/**
 * Sends a message to the AI model and logs the API call.
 * 
 * @param {string} userMessage - The message to send to the AI model.
 * @returns {Promise<string>} A promise that resolves to the AI model's response.
 * @throws Will throw an error if the chat session fails.
 */

// Modify the sendMessageToModel function in mainModule.js to include logging
export async function sendMessageToModel(userMessage) {
    try {
        if (!chatSession) {
            await initializeChatSession();
        }

        console.log("Sending message to AI model:", userMessage);
        const result = await chatSession.sendMessage(userMessage);
        const response = result.response.text();
        console.log("Received response from AI model:", response);
        logApiCall("sendMessageToModel", userMessage, response);
        return response;
    } catch (error) {
        console.error("Error in sendMessageToModel:", error);
        throw new Error(`Error in chat session: ${error.message}`);
    }
}

/**
 * Performs a web search using the provided query and logs the API call.
 * 
 * @param {string} query - The search query.
 * @returns {Promise<Array<{title: string, snippet: string, url: string}>>} A promise that resolves to an array of search results.
 * @throws Will throw an error if the web search fails.
 */


// Modify the performWebSearch function in mainModule.js to include logging
export async function performWebSearch(query) {
    console.log("Performing web search for:", query);
    const apiKey = 'AIzaSyBOZITEf87HFxtnCMpmk6Z4msjnCcxBemw';
    const searchEngineId = '0454742f1f782413a';
    const url = `https://www.googleapis.com/customsearch/v1?key=${apiKey}&cx=${searchEngineId}&q=${encodeURIComponent(query)}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        console.log("Web search results:", data);
        logApiCall("performWebSearch", query, data);
        
        if (data.items && data.items.length > 0) {
            return data.items.slice(0, 3).map(item => ({
                title: item.title,
                snippet: item.snippet,
                url: item.link
            }));
        } else {
            return [{ title: "No results found", snippet: "No matching results were found for your query.", url: "" }];
        }
    } catch (error) {
        console.error("Error in web search:", error);
        throw new Error("Failed to perform web search");
    }
}

/**
 * Uploads a PDF file to the Google AI File Manager.
 * 
 * @param {string} filePath - The path to the PDF file to upload.
 * @param {string} displayName - The display name for the uploaded file.
 * @returns {Promise<void>} A promise that resolves when the file is uploaded.
 */
export async function uploadPdfFile(filePath, displayName) {
    try {
        const uploadResponse = await fileManager.uploadFile(filePath, {
            mimeType: "application/pdf",
            displayName: displayName,
        });

        console.log(`Uploaded file ${uploadResponse.file.displayName} as: ${uploadResponse.file.uri}`);
        return uploadResponse.file.uri; // Return the URI of the uploaded file
    } catch (error) {
        console.error("Error uploading PDF file:", error);
        throw new Error("Failed to upload PDF file");
    }
}

/**
 * Generates content based on the uploaded PDF file.
 * 
 * @param {string} fileUri - The URI of the uploaded PDF file.
 * @returns {Promise<string>} A promise that resolves to the generated content.
 */
export async function generateContentFromPdf(fileUri) {
    try {
        const result = await model.generateContent([
            {
                fileData: {
                    mimeType: "application/pdf",
                    fileUri: fileUri,
                },
            },
            { text: "Get the following: Chapter I [Background and Rationale of the Study, Objectives of the Study, Significance of the Study, Scope and Limitation]; Chapter II [Literature Review, Conceptual Framework, Definition of Terms]; Chapter III [Research Design, Sampling Technique, Participants of the Study, Research Locale, Research Instrument, Data Gathering Procedure, Multiple Constraints Analysis, System Development Process, System Architecture, Data Analysis, Ethical Considerations]; Chapter IV [Results and Presentation of Data, Presentation of Project Design, Result of Testing, Evaluation and Validation, Discussion, Analysis and Interpretation of Data]; Chapter V [Summary and Conclusion, Recommendations]; Abstract; Literature Cited; Appendices." },
        ]);

        console.log("Generated content:", result.response.text());
        return result.response.text();
    } catch (error) {
        console.error("Error generating content from PDF:", error);
        throw new Error("Failed to generate content from PDF");
    }
}
