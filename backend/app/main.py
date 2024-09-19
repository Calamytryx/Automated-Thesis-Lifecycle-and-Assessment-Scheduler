# backend/app/main.py
from fastapi import FastAPI
from .api import auth
from fastapi.middleware.cors import CORSMiddleware
from .database import engine, Base

# Create all tables in the database
Base.metadata.create_all(bind=engine)

app = FastAPI()

# CORS configuration
origins = [
    "http://localhost:3000",  # React app domain
    # Add other origins if needed
]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include the routers
app.include_router(auth.router, tags=["auth"])