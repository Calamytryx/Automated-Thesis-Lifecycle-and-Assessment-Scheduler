import subprocess
import time
import psutil
from langchain_ollama import OllamaLLM
import numpy as np
from textblob import TextBlob

# Function to run the command
def run_command(command):
    process = subprocess.Popen(command, shell=True)
    return process

# Function to check if the ollama server is running
def is_ollama_running():
    for proc in psutil.process_iter(['pid', 'name']):
        if 'ollama' in proc.info['name']:
            return True
    return False

# Initialize the LLM after running the command
def start_ollama_server():
    if is_ollama_running():
        print("Ollama server is already running. Skipping start command.")
        return None  # No need to start the server
    else:
        command = "ollama serve"
        process = run_command(command)
        
        # Give the server some time to start up
        time.sleep(5)  # Adjust the sleep time as needed
        
        return process

# Start the server
process = start_ollama_server()

# Initialize the LLM
llm = OllamaLLM(model="llama3.1")

def analyze_thesis(scores, comments):
    # Calculate the mean scores for each category
    mean_scores = {key: np.mean([score[key] for score in scores]) for key in scores[0]}
    
    # Determine pass or fail based on overall mean score
    overall_mean = mean_scores['overall']
    pass_fail = "pass" if overall_mean >= 75 else "fail"

    # Categorize comments using sentiment analysis
    positive_comments = []
    neutral_comments = []
    negative_comments = []

    # Flatten comments from all panelists
    flat_comments = [comment for sublist in comments for comment in sublist]

    for comment in flat_comments:
        analysis = TextBlob(comment).sentiment
        if analysis.polarity > 0.1:
            positive_comments.append(comment)
        elif -0.1 <= analysis.polarity <= 0.1:
            neutral_comments.append(comment)
        else:
            negative_comments.append(comment)

    # Generate a decision support explanation using the LLM
    explanation_prompt = (
        f"Based on the following analysis:\n"
        f"Mean scores: {mean_scores}, Overall mean: {overall_mean}, Decision: {pass_fail}\n"
        f"Positive comments: {', '.join(positive_comments) if positive_comments else 'None'}\n"
        f"Neutral comments: {', '.join(neutral_comments) if neutral_comments else 'None'}\n"
        f"Negative comments: {', '.join(negative_comments) if negative_comments else 'None'}\n"
        f"Provide a detailed explanation of the findings and recommendations for improvement."
    )
    
    explanation = llm.invoke(explanation_prompt)

    # Construct the final response
    analysis = (
        f"Based on the mean of all 3 panelists: {', '.join([f'{key}: {mean_scores[key]:.2f}' for key in mean_scores])}, "
        f"it is likely to {pass_fail}.\n\n"
        f"According to the comments of the 3 panelists:\n"
        f"Positive comments: {', '.join(positive_comments) if positive_comments else 'None'}\n"
        f"Neutral comments: {', '.join(neutral_comments) if neutral_comments else 'None'}\n"
        f"Negative comments: {', '.join(negative_comments) if negative_comments else 'None'}\n\n"
        f"{explanation}\n"
        f"So all in all, this research should {pass_fail} because the overall sentiment is based on the average score and panelists' feedback."
    )

    return analysis

# Panelist scores and comments
panelist_scores = [
    {
        "content_quality": 90,
        "originality": 85,
        "clarity": 80,
        "organization": 75,
        "overall": 83
    },
    {
        "content_quality": 75,
        "originality": 80,
        "clarity": 85,
        "organization": 70,
        "overall": 76
    },
    {
        "content_quality": 88,
        "originality": 90,
        "clarity": 82,
        "organization": 78,
        "overall": 84
    }
]

panelist_comments = [
    [
        "The content is well-researched and insightful.",
        "Some arguments lack clarity and need further elaboration.",
        "The organization of sections could be improved for better flow."
    ],
    [
        "The thesis presents interesting ideas but feels derivative at times.",
        "The clarity of writing is good, but the structure needs reworking.",
        "More examples could strengthen the arguments presented."
    ],
    [
        "Excellent originality in approach and argumentation.",
        "There are sections that are well-articulated, but transitions between ideas can be abrupt.",
        "Overall a strong thesis, but consider refining the organization for coherence."
    ]
]

# Analyze the thesis
result = analyze_thesis(panelist_scores, panelist_comments)
print(f"Thesis Analysis:\n{result}\n")

# Optional: Terminate the server when done (if needed)
# if process:
#     process.terminate()
