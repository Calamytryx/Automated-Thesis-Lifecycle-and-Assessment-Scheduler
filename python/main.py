import os
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns  # Import seaborn for heatmaps
from sklearn.model_selection import train_test_split, GridSearchCV
from sklearn.svm import SVC
from imblearn.pipeline import Pipeline as ImbPipeline
from imblearn.over_sampling import SMOTE
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import accuracy_score, confusion_matrix, precision_score, recall_score, f1_score, roc_auc_score
from gensim.models import Word2Vec
import joblib
import numpy as np
from sklearn.base import BaseEstimator, TransformerMixin
import nltk
from nltk.tokenize import word_tokenize
import re

# Downloading NLTK resource
def download_nltk_resource(resource_name):
    try:
        nltk.data.find(f"tokenizers/{resource_name}")
    except LookupError:
        nltk.download(resource_name)

# Check if 'punkt' is available
download_nltk_resource('punkt')

# Function to load data from either CSV or TSV
def load_data(filepath):
    _, file_extension = os.path.splitext(filepath)
    
    # Try loading with headers; if it fails, assume no headers and set them manually
    try:
        if file_extension == '.csv':
            data = pd.read_csv(filepath)
        elif file_extension == '.tsv':
            data = pd.read_table(filepath)
        else:
            raise ValueError("Unsupported file format. Please use a CSV or TSV file.")
        
        # Check if 'Sentiment' and 'Review' columns exist
        if 'Sentiment' not in data.columns or 'Review' not in data.columns:
            raise KeyError("'Sentiment' or 'Review' column not found in the file headers.")
    
    except KeyError:
        # If headers are not found, load the file assuming there are no headers
        if file_extension == '.csv':
            data = pd.read_csv(filepath, header=None, names=['Sentiment', 'Review'])
        elif file_extension == '.tsv':
            data = pd.read_table(filepath, header=None, names=['Sentiment', 'Review'])
        else:
            raise ValueError("Unsupported file format. Please use a CSV or TSV file.")
    
    return data

# Word2Vec Vectorizer
class Word2VecVectorizer(BaseEstimator, TransformerMixin):
    def __init__(self, vector_size=100, window=5, min_count=1):
        self.vector_size = vector_size
        self.window = window
        self.min_count = min_count
        self.model = None

    def fit(self, X, y=None):
        tokenized_sentences = [word_tokenize(re.sub(r'\W+', ' ', doc.lower())) for doc in X]
        self.model = Word2Vec(sentences=tokenized_sentences, vector_size=self.vector_size, window=self.window, min_count=self.min_count)
        return self

    def transform(self, X):
        tokenized_sentences = [word_tokenize(re.sub(r'\W+', ' ', doc.lower())) for doc in X]
        return np.array([np.mean([self.model.wv[word] for word in sentence if word in self.model.wv] or [np.zeros(self.vector_size)], axis=0) for sentence in tokenized_sentences])

# Load dataset
data = load_data('opinions.tsv')
X = data['Review']
y = LabelEncoder().fit_transform(data['Sentiment'])

# Split the data
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Create a pipeline using Word2Vec and SMOTE
pipeline = ImbPipeline([
    ('w2v', Word2VecVectorizer(vector_size=100, window=5, min_count=1)),
    ('smote', SMOTE(sampling_strategy='auto', random_state=42)),
    ('svm', SVC(probability=True))
])

# Define the parameter grid
param_grid = {
    'w2v__vector_size': [50, 100, 150],
    'w2v__window': [3, 5, 7],
    'svm__C': [0.01, 0.1, 1, 10, 100],
    'svm__kernel': ['linear', 'rbf', 'poly'],
    'svm__gamma': ['scale', 'auto', 0.001, 0.01]
}

# Perform hyperparameter tuning with GridSearchCV
grid_search = GridSearchCV(pipeline, param_grid, cv=10, scoring='accuracy', n_jobs=-1, verbose=2)
grid_search.fit(X_train, y_train)

# Get results into a DataFrame for plotting
results_df = pd.DataFrame(grid_search.cv_results_)

# Reshape data for plotting accuracy
heatmap_data = results_df.pivot_table(index=['param_svm__C', 'param_svm__kernel', 'param_svm__gamma'], 
                                       columns='param_w2v__window', 
                                       values='mean_test_score')

# Plotting heatmap for accuracy
plt.figure(figsize=(12, 8))
sns.heatmap(heatmap_data, annot=True, cmap='viridis', cbar_kws={'label': 'Mean Accuracy'}, fmt='.2f')
plt.title('Model Accuracy Across Parameter Combinations', fontsize=16)
plt.xlabel('Word2Vec Window Size', fontsize=12)
plt.ylabel('SVM C, Kernel, and Gamma', fontsize=12)
plt.savefig('Accuracy_Heatmap.jpg')
plt.show()

# Evaluate model performance on the test set
y_pred = grid_search.predict(X_test)
accuracy = accuracy_score(y_test, y_pred)
precision = precision_score(y_test, y_pred)
recall = recall_score(y_test, y_pred)
f1 = f1_score(y_test, y_pred)
roc_auc = roc_auc_score(y_test, grid_search.predict_proba(X_test)[:, 1])
confmat = confusion_matrix(y_test, y_pred)

print("\nTest Set Metrics:")
print(f"Accuracy: {accuracy * 100:.2f}%")
print(f"Precision: {precision * 100:.2f}%")
print(f"Recall: {recall * 100:.2f}%")
print(f"F1 Score: {f1 * 100:.2f}%")
print(f"ROC-AUC: {roc_auc * 100:.2f}%")
print("Confusion Matrix:\n", confmat)

# Save the best model
joblib.dump(grid_search.best_estimator_, 'best_svm_w2v_model.pkl')
