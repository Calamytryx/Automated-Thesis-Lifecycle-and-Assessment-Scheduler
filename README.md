# AI-Driven System for Efficient Scheduling and Performance Assessment of College Research Presentations

This system is designed for the **Lyceum of the Philippines University of Cavite Department of Computer Studies (LPU-C DCS)** and serves as a decision support tool to streamline scheduling and assessing research presentations.

## Prerequisites

Before running the system, ensure you have the following tools installed:

### 1. **Node Version Manager (NVM)**
- Download and install NVM for Windows [here](https://github.com/coreybutler/nvm-windows/releases/tag/1.1.12).

After installation, verify the NVM, npm, and npx versions:

```bash
nvm -v
# Expected output: 1.1.12

npm -v
# Expected output: 10.8.3

npx -v
# Expected output: 10.8.3
```

To install `npx` globally, run:

```bash
npm install -g npx
```

### 2. **Python 3.12.6**
- Ensure you have Python 3.12.6 installed. You can download it from the [official Python website](https://www.python.org/downloads/).

After Python is installed, verify the installation:

```bash
python --version
# Expected output: Python 3.12.6
```

### 3. **Install Backend Dependencies**
Navigate to the `/backend/` folder and run the provided `install requirements.bat` file to install the required dependencies for the backend:

```bash
/backend/install_requirements.bat
```

## Running the System

### 1. **Start the Frontend**
Navigate to your frontend project directory and start the frontend server:

```bash
npm start
```

The frontend will be available at [http://localhost:3000](http://localhost:3000).

### 2. **Start the Backend**
Navigate to your backend project directory and run the following command to start the backend server:

```bash
uvicorn app.main:app --reload
```

The backend will be available at [http://localhost:8000](http://localhost:8000).

## System Overview

This AI-driven system focuses on making the research presentation process more efficient and helping ensure the quality of student research projects. It leverages **Support Vector Machines (SVM)** and **GPT models** to provide intelligent decision-making features.

### Features

1. **Thesis Topic Decision Tool**
   - Suggests relevant topics based on current trends to help students choose quality capstone/thesis projects.

2. **Research Title Acceptance Tool**
   - Analyzes existing studies to assess the uniqueness of a title, helping to avoid plagiarism or low-quality topics.

3. **Scheduling System**
   - Aligns the defense schedules of both students and panelists based on their availability.

4. **Content Management System**
   - Allows modular modification of rubrics, team lists, title lists, and panelist assignments, making the system adaptable to different needs.

5. **Requirement Checker Tool**
   - Helps students and advisers track the completion of necessary documents before the defense.

6. **Pass Recommendation System**
   - Provides decision support based on panelists' feedback to determine if a student should pass the defense.

## Technologies Used
- **Support Vector Machine (SVM):** Used for analyzing research titles and making pass recommendations.
- **GPT (Generative Pre-trained Transformer):** Helps in suggesting thesis topics and analyzing content for plagiarism.

## Additional Notes

- Make sure all necessary dependencies are installed and up to date.
- For further assistance or troubleshooting, consult me or project documentation.

Enjoy using the system!