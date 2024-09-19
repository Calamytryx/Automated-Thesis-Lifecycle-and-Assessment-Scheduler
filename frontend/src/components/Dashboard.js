// src/components/Dashboard.js
import React, { useEffect, useState } from 'react';
import axios from 'axios';

const Dashboard = () => {
    const [schedule, setSchedule] = useState([]);
    const [checklist, setChecklist] = useState([]);

    useEffect(() => {
        // Fetch schedules and checklist from the backend API
        axios.get('/api/schedules')
            .then(response => setSchedule(response.data))
            .catch(error => console.error('Error fetching schedules:', error));
        
        axios.get('/api/checklist')
            .then(response => setChecklist(response.data))
            .catch(error => console.error('Error fetching checklist:', error));
    }, []);

    return (
        <div>
            <h1>Dashboard</h1>
            <div>
                <h2>Schedules</h2>
                <ul>
                    {schedule.map((item) => (
                        <li key={item.schedule_id}>
                            {item.defense_date} - {item.panelist_id}
                        </li>
                    ))}
                </ul>
            </div>
            <div>
                <h2>Requirement Checklist</h2>
                <ul>
                    {checklist.map((item, index) => (
                        <li key={index}>
                            {item.requirement}
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
};

export default Dashboard;
