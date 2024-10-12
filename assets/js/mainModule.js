import { HarmBlockThreshold, HarmCategory, GoogleGenerativeAI } from "@google/generative-ai";

const API_KEY = "AIzaSyBSE1RdMjnZA7w83hBJW9EwF4fpuRdgp_c";
const genAI = new GoogleGenerativeAI(API_KEY);
// Model configuration
const model = genAI.getGenerativeModel({
    model: "gemini-1.5-flash-002",
    systemInstruction: ``
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