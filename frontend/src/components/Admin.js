// src/components/Admin.js
import React, { useState, useEffect } from 'react';
import axios from 'axios';

const Admin = () => {
    const [rubrics, setRubrics] = useState([]);

    useEffect(() => {
        // Fetch rubrics from the backend API
        axios.get('/api/rubrics')
            .then(response => setRubrics(response.data))
            .catch(error => console.error('Error fetching rubrics:', error));
    }, []);

    const handleUpdateRubric = (rubricId, updatedRubric) => {
        axios.put(`/api/rubrics/${rubricId}`, updatedRubric)
            .then(response => {
                // Update the local state with the updated rubric
                setRubrics(rubrics.map(rubric => rubric.rubric_id === rubricId ? response.data : rubric));
            })
            .catch(error => console.error('Error updating rubric:', error));
    };

    return (
        <div>
            <h1>Admin</h1>
            <div>
                <h2>Rubrics</h2>
                <ul>
                    {rubrics.map((rubric) => (
                        <li key={rubric.rubric_id}>
                            <div>
                                <strong>{rubric.criterion}</strong>: {rubric.max_score}
                                {/* Add functionality to update rubric */}
                                <button onClick={() => handleUpdateRubric(rubric.rubric_id, { criterion: 'New Criterion', max_score: 100 })}>
                                    Update
                                </button>
                            </div>
                        </li>
                    ))}
                </ul>
            </div>
            {/* Add more CMS functionalities as needed */}
        </div>
    );
};

export default Admin;
