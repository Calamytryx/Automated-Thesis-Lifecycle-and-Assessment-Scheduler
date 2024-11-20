console.log('FullCalendar loaded:', typeof FullCalendar !== 'undefined');
    $(document).ready(function() {
        // Thesis Topic Decision Tool
        $('#topicSuggestionForm').on('submit', function(e) {
            e.preventDefault();
            var field = $('#field').val();
            // AJAX call to get topic suggestions
            $.ajax({
                url: 'includes/get_topic_suggestions.php',
                method: 'POST',
                data: {
                    field: field
                },
                dataType: 'json',
                success: function(response) {
                    var suggestionsHtml = '<ul>';
                    response.suggestions.forEach(function(suggestion) {
                        suggestionsHtml += '<li>' + suggestion + '</li>';
                    });
                    suggestionsHtml += '</ul>';
                    $('#suggestedTopics').html(suggestionsHtml);
                },
                error: function() {
                    $('#suggestedTopics').html('<p>Error fetching suggestions. Please try again.</p>');
                }
            });
        });

        // Scheduling System
        function loadUserSchedule() {
            $.ajax({
                url: 'includes/get_user_schedule.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log("AJAX response:", response);
                    if (response.success) {
                        var events = [];
                        // Add user schedules to events
                        response.user_schedules.forEach(function(event) {
                            events.push({
                                title: event.description,
                                start: event.date + 'T' + event.start_time,
                                end: event.date + 'T' + event.end_time,
                            });
                        });
                        // Add defense schedules to events
                        response.defense_schedules.forEach(function(event) {
                            events.push({
                                title: event.description,
                                start: event.date + 'T' + event.start_time,
                                end: event.date + 'T' + event.end_time,
                            });
                        });
                        console.log("Events to be rendered:", events);
                        initializeCalendar(events);
                    } else {
                        $('#userSchedule').html('<p>Error loading schedules: ' + response.error + '</p>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX error:", textStatus, errorThrown);
                    $('#userSchedule').html('<p>Error loading schedules. Please try again later.</p>');
                }
            });
        }

        function initializeCalendar(events) {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 'auto', // or set a specific height like '600px'
                events: events, // Use the dynamically loaded events
                eventClick: function(info) {
                    alert('Event: ' + info.event.title);
                }
            });
            calendar.render();
        }

    });

    

    const calendarEl = document.getElementById('calendar');

    // Function to get the next date for a given day of the week
    function getNextDateForDay(day) {
        const daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const today = new Date();
        const targetDayIndex = daysOfWeek.indexOf(day);
        if (targetDayIndex === -1) {
            return day; // Return original if invalid day
        }
        const resultDate = new Date(today);
        resultDate.setDate(today.getDate() + ((7 + targetDayIndex - today.getDay()) % 7));
        return resultDate.toISOString().split('T')[0];
    }


    /**
     * Function to redirect to decision-support with the team_id as a POST value.
     * @param {number} teamId - The ID of the team to send via POST.
     */
    function redirectToDecisionSupport(teamId) {
        if (!teamId) {
            console.error('Invalid teamId. Cannot redirect.');
            alert('Team information is missing. Cannot proceed.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../decision-support/';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'team_id';
        input.value = teamId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    // Select the target element (#scheduling)
    const targetNode = document.querySelector('#scheduling');

    // Initial check: run if the 'show' class is already present on page load
    if (targetNode && targetNode.classList.contains('show')) {
        // Fetch events and requirements via AJAX
        $.ajax({
            url: 'includes/get_user_schedule.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const events = [];

                    response.defense_schedules.forEach(defense => {
                        events.push({
                            title: defense.description,
                            start: `${defense.date}T${defense.start_time}`,
                            end: `${defense.date}T${defense.end_time}`,
                            location: defense.room,
                            eventType: 'defense',
                            team_id: defense.team_id
                        });
                    });

                    response.user_schedules.forEach(schedule => {
                        events.push({
                            title: schedule.description,
                            start: `${getNextDateForDay(schedule.date)}T${schedule.start_time}`,
                            end: `${getNextDateForDay(schedule.date)}T${schedule.end_time}`,
                            location: schedule.room,
                            eventType: 'user'
                        });
                    });

                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        height: 'auto',
                        events: events,
                        eventClick: function(info) {
                            const eventType = info.event.extendedProps.eventType;

                            if (eventType === 'defense' && <?php echo $_SESSION['usertype']; ?> != 1) {
                                const teamId = info.event.extendedProps.team_id;
                                if (teamId) {
                                    redirectToDecisionSupport(teamId);
                                } else {
                                    console.error('team_id is undefined for this defense event.');
                                    alert('Unable to retrieve team information for this event.');
                                }
                            } else {
                                const title = info.event.title;
                                const room = info.event.extendedProps.location;
                                alert(`Event: ${title}\nRoom: ${room}`);
                            }
                        },
                        dateClick: function(info) {
                            const currentView = calendar.view.type;
                            if (currentView === 'dayGridMonth') {
                                calendar.changeView('timeGridWeek');
                            } else if (currentView === 'timeGridWeek') {
                                calendar.changeView('timeGridDay');
                            }
                            calendar.gotoDate(info.dateStr);
                        }
                    });

                    calendar.render();
                } else {
                    console.error('Error fetching schedules:', response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", textStatus, errorThrown);
            }
        });
    }

    // Create an observer instance
    const observer = new MutationObserver((mutationsList) => {
        mutationsList.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                // Trigger only when the 'show' class is added
                if (targetNode.classList.contains('show')) {
                    // Fetch events and requirements via AJAX
                    $.ajax({
                        url: 'includes/get_user_schedule.php',
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                const events = [];

                                response.defense_schedules.forEach(defense => {
                                    events.push({
                                        title: defense.description,
                                        start: `${defense.date}T${defense.start_time}`,
                                        end: `${defense.date}T${defense.end_time}`,
                                        location: defense.room,
                                        eventType: 'defense',
                                        team_id: defense.team_id
                                    });
                                });

                                response.user_schedules.forEach(schedule => {
                                    events.push({
                                        title: schedule.description,
                                        start: `${getNextDateForDay(schedule.date)}T${schedule.start_time}`,
                                        end: `${getNextDateForDay(schedule.date)}T${schedule.end_time}`,
                                        location: schedule.room,
                                        eventType: 'user'
                                    });
                                });

                                const calendar = new FullCalendar.Calendar(calendarEl, {
                                    initialView: 'dayGridMonth',
                                    headerToolbar: {
                                        left: 'prev,next today',
                                        center: 'title',
                                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                                    },
                                    height: 'auto',
                                    events: events,
                                    eventClick: function(info) {
                                        const eventType = info.event.extendedProps.eventType;

                                        if (eventType === 'defense' && <?php echo $_SESSION['usertype']; ?> != 1) {
                                            const teamId = info.event.extendedProps.team_id;
                                            if (teamId) {
                                                redirectToDecisionSupport(teamId);
                                            } else {
                                                console.error('team_id is undefined for this defense event.');
                                                alert('Unable to retrieve team information for this event.');
                                            }
                                        } else {
                                            const title = info.event.title;
                                            const room = info.event.extendedProps.location;
                                            alert(`Event: ${title}\nRoom: ${room}`);
                                        }
                                    },
                                    dateClick: function(info) {
                                        const currentView = calendar.view.type;
                                        if (currentView === 'dayGridMonth') {
                                            calendar.changeView('timeGridWeek');
                                        } else if (currentView === 'timeGridWeek') {
                                            calendar.changeView('timeGridDay');
                                        }
                                        calendar.gotoDate(info.dateStr);
                                    }
                                });

                                calendar.render();
                            } else {
                                console.error('Error fetching schedules:', response.error);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX error:", textStatus, errorThrown);
                        }
                    });
                }
            }
        });
    });

    // Set up the configuration for the observer: watch for attribute changes
    const config = {
        attributes: true, // Watch for changes to attributes
        attributeFilter: ['class'], // Only watch changes to the 'class' attribute
    };

    // Start observing the target node
    if (targetNode) {
        observer.observe(targetNode, config);
    }