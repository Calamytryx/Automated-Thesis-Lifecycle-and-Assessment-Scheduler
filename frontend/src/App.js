// src/App.js
import React from 'react';
import { BrowserRouter as Router, Route, Routes, Link } from 'react-router-dom';
import Login from './components/Login';
import Register from './components/Register';
import Dashboard from './components/Dashboard';
import Home from './components/Home';
import Admin from './components/Admin';

function App() {
    return (
        <Router>
            <div>
                {/* Welcome Message */}
                <header>
                    <h1>Welcome to the AI-Driven Research System</h1>
                    <nav>
                        <ul>
                            <li><Link to="/login">Login</Link></li>
                            <li><Link to="/register">Register</Link></li>
                            <li><Link to="/dashboard">Dashboard</Link></li>
                            <li><Link to="/home">Home</Link></li>
                            <li><Link to="/admin">Admin</Link></li>
                        </ul>
                    </nav>
                </header>

                {/* Routes */}
                <Routes>
                    <Route path="/login" element={<Login />} />
                    <Route path="/register" element={<Register />} />
                    <Route path="/dashboard" element={<Dashboard />} />
                    <Route path="/home" element={<Home />} />
                    <Route path="/admin" element={<Admin />} />
                </Routes>
            </div>
        </Router>
    );
}

export default App;
