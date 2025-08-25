<script>

    // Time adjustment functions for both add and edit modals
    function adjustTime(id, type, step) {
        // Always search inside the currently open modal for elements
        const $modal = $('.modal.show');
        let hourEl = $modal.find(`#${id}_hour`)[0];
        let minuteEl = $modal.find(`#${id}_minute`)[0];
        let meridianEl = $modal.find(`#${id}_meridian`)[0];
        let inputEl = $modal.find(`#${id}_time`)[0];

        if (!hourEl || !minuteEl || !meridianEl || !inputEl) return; // If not found, abort

        let hour = parseInt(hourEl.innerText);
        let minute = parseInt(minuteEl.innerText);
        let meridian = meridianEl.innerText;

        // Convert to 24-hour time for easier math
        let militaryHour = hour % 12;
        if (meridian === 'PM') militaryHour += 12;

        let totalMinutes = militaryHour * 60 + minute;

        // ⏫ Apply step
        if (type === 'hour') {
            totalMinutes += step * 60;
        } else if (type === 'minute') {
            totalMinutes += step;
        }

        // 🔄 Loop if out of range
        if (totalMinutes > 1260) {
            totalMinutes = 420; // back to 07:00
        } else if (totalMinutes < 420) {
            totalMinutes = 1260; // back to 21:00
        }

        // 🧮 Convert back to hour/minute
        militaryHour = Math.floor(totalMinutes / 60);
        minute = totalMinutes % 60;

        // Convert 24h to 12h + AM/PM
        meridian = militaryHour >= 12 ? 'PM' : 'AM';
        hour = militaryHour % 12;
        if (hour === 0) hour = 12;

        // ✅ Update UI + hidden input
        hourEl.innerText = String(hour).padStart(2, '0');
        minuteEl.innerText = String(minute).padStart(2, '0');
        meridianEl.innerText = meridian;
        inputEl.value = `${String(militaryHour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;

        // Chronological enforcement: start_time < end_time
        let startInput = $modal.find('#start_time')[0];
        let endInput = $modal.find('#end_time')[0];
        if (startInput && endInput) {
            let startVal = startInput.value;
            let endVal = endInput.value;
            if (id === 'start' && startVal >= endVal) {
                // If start >= end, auto-adjust end to next slot
                let startMinutes = parseInt(startVal.split(':')[0]) * 60 + parseInt(startVal.split(':')[1]);
                let nextEndMinutes = startMinutes + 30;
                if (nextEndMinutes > 1260) nextEndMinutes = 1260;
                let nextEndHour = Math.floor(nextEndMinutes / 60);
                let nextEndMinute = nextEndMinutes % 60;
                endInput.value = `${String(nextEndHour).padStart(2, '0')}:${String(nextEndMinute).padStart(2, '0')}`;
                // Also update UI if present
                let endHourEl = $modal.find('#end_hour')[0];
                let endMinuteEl = $modal.find('#end_minute')[0];
                let endMeridianEl = $modal.find('#end_meridian')[0];
                let endMeridian = nextEndHour >= 12 ? 'PM' : 'AM';
                let endHour = nextEndHour % 12;
                if (endHour === 0) endHour = 12;
                if (endHourEl) endHourEl.innerText = String(endHour).padStart(2, '0');
                if (endMinuteEl) endMinuteEl.innerText = String(nextEndMinute).padStart(2, '0');
                if (endMeridianEl) endMeridianEl.innerText = endMeridian;
            }
            if (id === 'end' && startVal >= endVal) {
                // If end <= start, auto-adjust start to previous slot
                let endMinutes = parseInt(endVal.split(':')[0]) * 60 + parseInt(endVal.split(':')[1]);
                let prevStartMinutes = endMinutes - 30;
                if (prevStartMinutes < 420) prevStartMinutes = 420;
                let prevStartHour = Math.floor(prevStartMinutes / 60);
                let prevStartMinute = prevStartMinutes % 60;
                startInput.value = `${String(prevStartHour).padStart(2, '0')}:${String(prevStartMinute).padStart(2, '0')}`;
                // Also update UI if present
                let startHourEl = $modal.find('#start_hour')[0];
                let startMinuteEl = $modal.find('#start_minute')[0];
                let startMeridianEl = $modal.find('#start_meridian')[0];
                let startMeridian = prevStartHour >= 12 ? 'PM' : 'AM';
                let startHour = prevStartHour % 12;
                if (startHour === 0) startHour = 12;
                if (startHourEl) startHourEl.innerText = String(startHour).padStart(2, '0');
                if (startMinuteEl) startMinuteEl.innerText = String(prevStartMinute).padStart(2, '0');
                if (startMeridianEl) startMeridianEl.innerText = startMeridian;
            }
            if (id === 'end' && startVal == endVal || id === 'start' && startVal == endVal) {
                // If start == end, auto-adjust end to next slot
                let startMinutes = parseInt(startVal.split(':')[0]) * 60 + parseInt(startVal.split(':')[1]);
                let nextEndMinutes = startMinutes + 30;
                if (nextEndMinutes > 1260) nextEndMinutes = 1260;
                let nextEndHour = Math.floor(nextEndMinutes / 60);
                let nextEndMinute = nextEndMinutes % 60;
                endInput.value = `${String(nextEndHour).padStart(2, '0')}:${String(nextEndMinute).padStart(2, '0')}`;
                // Also update UI if present
                let endHourEl = $modal.find('#end_hour')[0];
                let endMinuteEl = $modal.find('#end_minute')[0];
                let endMeridianEl = $modal.find('#end_meridian')[0];
                let endMeridian = nextEndHour >= 12 ? 'PM' : 'AM';
                let endHour = nextEndHour % 12;
                if (endHour === 0) endHour = 12;
                if (endHourEl) endHourEl.innerText = String(endHour).padStart(2, '0');
                if (endMinuteEl) endMinuteEl.innerText = String(nextEndMinute).padStart(2, '0');
                if (endMeridianEl) endMeridianEl.innerText = endMeridian;

            }
        }
    }

    function toggleMeridian(id) {
        // Always search inside the currently open modal for elements
        const $modal = $('.modal.show');
        let hourEl = $modal.find(`#${id}_hour`)[0];
        let minuteEl = $modal.find(`#${id}_minute`)[0];
        let meridianEl = $modal.find(`#${id}_meridian`)[0];
        let inputEl = $modal.find(`#${id}_time`)[0];

        if (!hourEl || !minuteEl || !meridianEl || !inputEl) return;

        const hour = parseInt(hourEl.innerText);
        const minute = parseInt(minuteEl.innerText);
        let meridian = meridianEl.innerText === 'AM' ? 'PM' : 'AM';

        // Convert to 24-hour for validation
        let militaryHour = hour % 12;
        if (meridian === 'PM') militaryHour += 12;
        const totalMinutes = militaryHour * 60 + minute;

        // Validate range: 07:00 (420) to 21:00 (1260)
        if (totalMinutes < 420 || totalMinutes > 1260) return; // ❌ cancel toggle

        meridianEl.innerText = meridian;
        inputEl.value = `${String(militaryHour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;

        // Chronological enforcement after meridian toggle
        let startInput = $modal.find('#start_time')[0];
        let endInput = $modal.find('#end_time')[0];
        if (startInput && endInput) {
            let startVal = startInput.value;
            let endVal = endInput.value;
            if (id === 'start' && startVal >= endVal) {
                // If start >= end, auto-adjust end to next slot
                let startMinutes = parseInt(startVal.split(':')[0]) * 60 + parseInt(startVal.split(':')[1]);
                let nextEndMinutes = startMinutes + 30;
                if (nextEndMinutes > 1260) nextEndMinutes = 1260;
                let nextEndHour = Math.floor(nextEndMinutes / 60);
                let nextEndMinute = nextEndMinutes % 60;
                endInput.value = `${String(nextEndHour).padStart(2, '0')}:${String(nextEndMinute).padStart(2, '0')}`;
                let endHourEl = $modal.find('#end_hour')[0];
                let endMinuteEl = $modal.find('#end_minute')[0];
                let endMeridianEl = $modal.find('#end_meridian')[0];
                let endMeridian = nextEndHour >= 12 ? 'PM' : 'AM';
                let endHour = nextEndHour % 12;
                if (endHour === 0) endHour = 12;
                if (endHourEl) endHourEl.innerText = String(endHour).padStart(2, '0');
                if (endMinuteEl) endMinuteEl.innerText = String(nextEndMinute).padStart(2, '0');
                if (endMeridianEl) endMeridianEl.innerText = endMeridian;
            }
            if (id === 'end' && startVal >= endVal) {
                // If end <= start, auto-adjust start to previous slot
                let endMinutes = parseInt(endVal.split(':')[0]) * 60 + parseInt(endVal.split(':')[1]);
                let prevStartMinutes = endMinutes - 30;
                if (prevStartMinutes < 420) prevStartMinutes = 420;
                let prevStartHour = Math.floor(prevStartMinutes / 60);
                let prevStartMinute = prevStartMinutes % 60;
                startInput.value = `${String(prevStartHour).padStart(2, '0')}:${String(prevStartMinute).padStart(2, '0')}`;
                let startHourEl = $modal.find('#start_hour')[0];
                let startMinuteEl = $modal.find('#start_minute')[0];
                let startMeridianEl = $modal.find('#start_meridian')[0];
                let startMeridian = prevStartHour >= 12 ? 'PM' : 'AM';
                let startHour = prevStartHour % 12;
                if (startHour === 0) startHour = 12;
                if (startHourEl) startHourEl.innerText = String(startHour).padStart(2, '0');
                if (startMinuteEl) startMinuteEl.innerText = String(prevStartMinute).padStart(2, '0');
                if (startMeridianEl) startMeridianEl.innerText = startMeridian;
            }
        }
    }



    // Add helper function to dynamically populate program dropdowns
    // Add helper function to dynamically populate program dropdowns
    function populateProgramDropdown(selectElement, selectedValue) {
        //     if (selectedValue != null) {
        //     selectElement.html('<option value="' + selectedValue + '">' + selectedValue + '</option>');
        // } else {
        //     selectElement.html('<option value="">Loading programs...</option>');
        // }
        console.log(selectedValue)


        $.ajax({
            url: 'includes/get_programs_grouped.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {

                if (response.success && response.programs.length > 0) {
                    let currentCollege = null;
                    let optgroup = null;

                    $.each(response.programs, function (i, program) {
                        // Create new optgroup when college changes
                        if (program.college !== currentCollege) {
                            currentCollege = program.college;
                            optgroup = $('<optgroup>', {
                                label: currentCollege
                            });
                            selectElement.append(optgroup);
                        }

                        // Add program option to current optgroup
                        const option = $('<option>', {
                            value: program.display_name,
                            text: program.display_name
                        });

                        // Set selected if matches by program name
                        if (selectedValue !== null && selectedValue === program.display_name) {
                            option.prop('selected', true);
                        }

                        optgroup.append(option);
                    });
                } else {
                    selectElement.html('<option value="">No programs available</option>');
                }
            },
            error: function () {
                selectElement.html('<option value="">Error loading programs</option>');
                console.error("Failed to load programs");
            }
        });
    }



    $(document).ready(function () {
        // Create toast container if it doesn't exist
        if (!$('#toastContainer').length) {
            $('body').append(
                '<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>');
        }

        // NEW: Connect the saveChanges button to trigger the edit form submission
        $(document).on('click', '#saveChanges', function () {
            console.log('DEBUG: Save changes button clicked, triggering edit form submission');
            $('#editForm').submit();
        });

        // NEW: Add bulk row functionality
        $(document).off('click.addBulkRow').on('click.addBulkRow', '#addBulkRow', function () {
            let count = parseInt($('#rowCountInput').val()) || 1;
            for (let i = 0; i < count; i++) {
                var rowCount = $('#bulkAddTable tbody tr').length;
                var newRow = `
                    <tr>
                        <td><input type="text" class="form-control" name="users[${rowCount}][id]"></td>
                        <td><input type="text" class="form-control" name="users[${rowCount}][name]"></td>
                        <td><input type="text" class="form-control" name="users[${rowCount}][program]"></td>
                        <td class="text-center">
                        <input type="checkbox" name="users[${rowCount}][no_username]">
                        </td>
                    </tr>
                    `;
                $('#bulkAddTable tbody').append(newRow);
            }
        });

        // When the Bulk Add Modal is shown, insert the CSV download link if not already present
        $('#bulkAddModal').on('shown.bs.modal', function () {
            if (!$(this).find('#downloadCsvTemplate').length) {
                $(this).find('.modal-body').prepend(`
                        <div class="mb-3">
                            <a href="#" id="downloadCsvTemplate" class="btn btn-sm btn-secondary">Download CSV Template</a>
                        </div>
                    `);
            }
        });

        // Update Bulk Add Users CSV download handler
        $(document).off('click.downloadCsvTemplate').on('click.downloadCsvTemplate', '#downloadCsvTemplate',
            function (e) {
                e.preventDefault();
                const csvContent = 'ID,Name,Program,No Username\\n';
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'users_template.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });

        // NEW: Bulk Add Teams functionality
        $(document).off('click.bulkAddTeamsBtn').on('click.bulkAddTeamsBtn', '.bulk-add-teams-btn', function (e) {
            e.preventDefault();
            console.log('Bulk Add Teams button clicked');
            $('#bulkAddTeamsModal').modal('show');
        });

        $(document).off('click.addBulkTeamsRow').on('click.addBulkTeamsRow', '#addBulkTeamsRow', function () {
            let count = parseInt($('#teamsRowCountInput').val()) || 1;
            for (let i = 0; i < count; i++) {
                var rowCount = $('#bulkAddTeamsTable tbody tr').length;
                var newRow = `
                    <tr>
                        <td><input type="text" class="form-control" name="teams[${rowCount}][name]"></td>
                        <td><input type="text" class="form-control" name="teams[${rowCount}][title]"></td>
                        <td><input type="text" class="form-control" name="teams[${rowCount}][area_of_expertise]"></td>
                        <td><input type="text" class="form-control" name="teams[${rowCount}][program]"></td>
                    </tr>
                    `;
                $('#bulkAddTeamsTable tbody').append(newRow);
            }
        });

        $(document).off('submit.bulkAddTeamsForm').on('submit.bulkAddTeamsForm', '#bulkAddTeamsForm', function (e) {
            e.preventDefault();
            console.log('Bulk Add Teams form submitted');

            var form = $('#bulkAddTeamsForm');
            var formData = new FormData(form[0]);

            // If pasted bulk text is provided, append it
            var bulkText = $('#bulkTeamsTextInput').val().trim();
            if (bulkText !== "") {
                formData.append('bulk_teams', bulkText);
            }

            $.ajax({
                url: 'includes/bulk_add_teams.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showToast('Success', 'Teams added successfully', 'success');
                        $('#bulkAddTeamsModal').modal('hide');
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        // More descriptive error message
                        showToast('Error', response.message ||
                            'Failed to add teams. Please check your data and try again.',
                            'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    // User-friendly error message
                    let errorMessage =
                        'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            });
        });

        // Event handler for preset buttons in area of expertise fields
        $(document).on('click', '.area-option', function (e) {
            e.preventDefault();
            var presetValue = $(this).data('value');
            $(this).closest('.input-group').find('input[name="area_of_expertise"]').val(presetValue);
        });

        // Event handler for preset buttons in program fields
        $(document).on('click', '.program-option', function (e) {
            e.preventDefault();
            var presetValue = $(this).data('value');
            $(this).closest('.input-group').find('input[name="program"]').val(presetValue);
        });

        const sidebarContainer = $('#sidebarContainer');
        const mainContent = $('#mainContent');
        const toggleButton = $('#toggleSidebar');

        toggleButton.on('click', function () {
            sidebarContainer.toggleClass('collapsed');
            mainContent.toggleClass('expanded');
            toggleButton.toggleClass('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebarContainer.hasClass('collapsed'));
        });

        // Check localStorage for saved sidebar state on page load
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            sidebarContainer.addClass('collapsed');
            mainContent.addClass('expanded');
            toggleButton.addClass('collapsed');
        }

        // Handle window resize
        $(window).on('resize', function () {
            if (window.innerWidth <= 768) {
                mainContent.addClass('expanded');
            } else {
                if (!sidebarContainer.hasClass('collapsed')) {
                    mainContent.removeClass('expanded');
                }
            }
        });
    });

    function updatePanelistDropdowns() {
        // Collect all selected panelist IDs
        var selectedIds = [];
        $('#panelists .panelist select').each(function () {
            var val = $(this).val();
            if (val) selectedIds.push(val);
        });

        $('#panelists .panelist select').each(function () {
            var $select = $(this);
            var currentVal = $select.val();
            $select.find('option').each(function () {
                var $opt = $(this);
                if ($opt.val() && $opt.val() !== currentVal && selectedIds.includes($opt.val())) {
                    $opt.prop('disabled', true);
                } else {
                    $opt.prop('disabled', false);
                }
            });
        });
    }

    function addNewPanelist(staff) {
        // Check current number of panelists
        var currentPanelistCount = $('#panelists .panelist').length;
        if (currentPanelistCount >= 3) {
            showToast('Warning', 'Maximum of 3 panelists allowed.', 'warning');
            return; // Stop if limit is reached
        }

        // Determine the next index based on existing panelists
        var currentIndices = $('#panelists .panelist select').map(function () {
            var name = $(this).attr('name');
            var match = name.match(/\[(\d+)\]/);
            return match ? parseInt(match[1], 10) : -1;
        }).get();
        var nextIndex = currentIndices.length > 0 ? Math.max(...currentIndices) + 1 : 0;

        var newPanelistHtml = `
                <div class="mb-3 row panelist">
                    <div class="col-sm-10">
                        <select class="form-select" name="panelist_id[${nextIndex}]">
                            ${staff.map(member => `<option value="${member.id}">${member.name}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                    </div>
                </div>
                `;

        $('#panelists').append(newPanelistHtml);
        console.log('New panelist added to DOM with name:', `panelist_id[${nextIndex}]`);

        // Disable button if limit is now reached
        if ($('#panelists .panelist').length >= 3) {
            $('#addPanelist').prop('disabled', true);
        }
        // Update dropdowns to enforce unique selection
        updatePanelistDropdowns();
    }

    // Optionally, update existing panelist entries to have unique indices
    $(document).ready(function () {
        $('#panelists .panelist').each(function (index) {
            $(this).find('select').attr('name', `panelist_id[${index}]`);
        });
    });

    // Define addNewTeamMember function globally
    function addNewTeamMember() {
        $.ajax({
            url: 'includes/get_users.php',
            method: 'GET',
            dataType: 'json',
            success: function (users) {
                var newMemberHtml = `
                        <div class="mb-3 row team-member">
                            <div class="col-sm-5">
                                <select class="form-select user-select" name="new_user_id[]" style="display:block;">
                                    <option value="">Select a user</option>
                                    ${users.map(user => `<option value="${user.id}">${user.first_name} ${user.last_name}</option>`).join('')}
                                </select>
                                <input type="text" class="form-control new-username-input" name="new_username[]" placeholder="Enter username" style="display:none;">
                                <a href="#" class="toggle-input">Switch to manual</a>
                            </div>
                            <div class="col-sm-5">
                                <select class="form-select role-select" name="new_role[]">
                                    <option value="adviser">Adviser/Professor</option>
                                    <option value="leader">Leader</option>
                                    <option value="member">Member</option>
                                </select>
                            </div>
                            <div class="col-sm-2">
                                <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                            </div>
                        </div>
                        `;
                $('#teamMembers').append(newMemberHtml);
                $('#teamMembers .toggle-input').last().on('click', function (e) {
                    e.preventDefault();
                    var $select = $(this).siblings('.user-select');
                    var $input = $(this).siblings('.new-username-input');
                    if ($select.is(':visible')) {
                        $select.hide();
                        $input.show();
                        $(this).text('Switch to select');
                    } else {
                        $input.hide();
                        $select.show();
                        $(this).text('Switch to manual');
                    }
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert('Error loading users');
            }
        });
    }

    // Remove team member functionality
    $(document).on('click', '.remove-member', function () {
        var teamMember = $(this).closest('.team-member');
        var userId = teamMember.data('user-id');
        var teamId = $('input[name="id"]').val();
        if (userId && teamId) {
            if (confirm('Are you sure you want to remove this team member? This action cannot be undone.')) {
                $.ajax({
                    url: 'includes/remove_team_member.php',
                    method: 'POST',
                    data: {
                        user_id: userId,
                        team_id: teamId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            teamMember.remove();
                            alert('Team member removed successfully');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('Error: Unable to remove team member');
                    }
                });
            }
        } else {
            // If it's a new member (not yet saved to database), just remove from form
            teamMember.remove();
        }
    });

    // Helper function for showing toasts
    function showToast(title, message, type = 'success') {
        // Create toast container if it doesn't exist
        if (!$('#toastContainer').length) {
            $('body').append(`
                <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
                </div>
                `);

        }

        // Generate unique ID for the toast
        const toastId = 'toast-' + Date.now();

        // Create toast HTML with more prominent styling
        const toast = `
                <div id="${toastId}" class="toast align-items-center border-0" 
                    role="alert" 
                    aria-live="assertive" 
                    aria-atomic="true"
                    style="min-width: 300px; opacity: 1; background-color: ${type === 'success' ? 'var(--main-accent)' : 'var(--main-btn-del)'};">
                    <div class="d-flex">
                        <div class="toast-body" style="font-size: 1rem; padding: 1rem; color:var(--main-bg-dark);">
                            <strong>${title}:</strong> ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
                `;

        // Add toast to container
        $('#toastContainer').append(toast);

        // Initialize and show the toast with modified options
        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: true,
            delay: 3000,
            animation: true
        });
        toastElement.show();

        // Remove toast element after it's hidden
        $(`#${toastId}`).on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }
</script>

<!-- AJAX Live Updates & Form Validation Functions -->
<script>
    // Comprehensive form validation functions
    const ValidationUtils = {
        // Check for emojis
        containsEmoji: function (text) {
            const emojiRegex = /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/u;
            return emojiRegex.test(text);
        },

        // Check if text is too long
        isTooLong: function (text, maxLength = 100) {
            return text.length > maxLength;
        },

        // Check if text is too short (single character/digit)
        isTooShort: function (text, minLength = 2) {
            return text.trim().length < minLength;
        },

        // Check for only numbers (for name fields)
        isOnlyNumbers: function (text) {
            return /^\d+$/.test(text.trim());
        },

        // Check for special characters (allow only letters, numbers, spaces, basic punctuation)
        hasInvalidCharacters: function (text) {
            const validPattern = /^[a-zA-Z0-9\s\.\,\-\_\@\(\)]+$/;
            return !validPattern.test(text);
        },

        // Check for HTML tags
        containsHTML: function (text) {
            const htmlRegex = /<[^>]*>/;
            return htmlRegex.test(text);
        },

        // Check if email is valid
        isValidEmail: function (email) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailPattern.test(email);
        },

        // Check username format (for student IDs like 20xx-x-xxxxx)
        isValidUsername: function (username, usertype) {
            if (usertype == 1) { // Student
                // Accept either 4digits-1digit-5digits or text.text
                const studentPattern = /^\d{4}-\d{1}-\d{5}$/;
                const altPattern = /^[a-zA-Z]+\.[a-zA-Z]+$/;
                return studentPattern.test(username) || altPattern.test(username);
            }
            // For admin/faculty, allow alphanumeric with basic characters
            const generalPattern = /^[a-zA-Z0-9\._-]{3,50}$/;
            return generalPattern.test(username);
        },

        // Validate field based on type
        validateField: function (fieldName, value, usertype = null) {
            const errors = [];

            if (!value || value.trim() === '') {
                return ['This field is required'];
            }

            const trimmedValue = value.trim();

            // Check for emojis
            if (this.containsEmoji(trimmedValue)) {
                errors.push('Emojis are not allowed');
            }

            // Field-specific validations
            switch (fieldName) {
                case 'username':
                    if (!this.isValidUsername(trimmedValue, usertype)) {
                        if (usertype == 1) {
                            errors.push('Student ID must be in format: 20XX-X-XXXXX or First.Last(old format)');
                        } else {
                            errors.push('Username must be 3-50 characters, alphanumeric, -, and . only');
                        }
                    }
                    break;

                case 'email':
                    if (!this.isValidEmail(trimmedValue)) {
                        errors.push('Please enter a valid email address');
                    }
                    break;

                case 'first_name':
                case 'last_name':
                    if (this.isTooShort(trimmedValue)) {
                        errors.push('Name must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 50)) {
                        errors.push('Name cannot exceed 50 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Name cannot be only numbers');
                    }
                    if (this.hasInvalidCharacters(trimmedValue)) {
                        errors.push('Name contains invalid characters');
                    }
                    break;

                case 'headline':
                    if (this.isTooLong(trimmedValue, 150)) {
                        errors.push('Headline cannot exceed 150 characters');
                    }
                    break;

                case 'bio':
                    if (this.isTooLong(trimmedValue, 500)) {
                        errors.push('Bio cannot exceed 500 characters');
                    }
                    // Check for symbols (non-alphanumeric, non-space, non-basic punctuation)
                    if (/[^a-zA-Z0-9\s\.\,\-\_\@\(\)\!\?]/.test(trimmedValue)) {
                        errors.push('Bio must be alphanumeric and basic punctuation only');
                    }
                    break;

                case 'room':
                    // Room name validation for defense schedules
                    if (this.isTooShort(trimmedValue, 2)) {
                        errors.push('Room name must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 50)) {
                        errors.push('Room name cannot exceed 50 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Room name cannot be only numbers');
                    }
                    // Allow alphanumeric, spaces, hyphens, and basic punctuation for room names
                    if (!/^[a-zA-Z0-9\s\-\.\,\_\(\)]+$/.test(trimmedValue)) {
                        errors.push('Room name contains invalid characters');
                    }
                    break;

                case 'rooms':
                    // Multiple rooms validation (comma-separated)
                    const roomsList = trimmedValue.split(',').map(room => room.trim()).filter(room => room !== '');
                    if (roomsList.length === 0) {
                        errors.push('At least one room is required');
                    }
                    for (let room of roomsList) {
                        if (room.length < 2) {
                            errors.push('Each room name must be at least 2 characters long');
                            break;
                        }
                        if (room.length > 50) {
                            errors.push('Each room name cannot exceed 50 characters');
                            break;
                        }
                        if (/^\d+$/.test(room)) {
                            errors.push('Room names cannot be only numbers');
                            break;
                        }
                        if (!/^[a-zA-Z0-9\s\-\.\,\_\(\)]+$/.test(room)) {
                            errors.push('Room names contain invalid characters');
                            break;
                        }
                    }
                    break;

                case 'password':
                    // Comprehensive password validation matching system requirements
                    if (this.containsHTML(trimmedValue)) {
                        errors.push('HTML tags and scripts are not allowed in passwords');
                    }

                    // Length validation
                    if (trimmedValue.length < 8) {
                        errors.push('Password must be at least 8 characters long');
                    }
                    if (trimmedValue.length > 128) {
                        errors.push('Password cannot exceed 128 characters');
                    }

                    // Character type requirements
                    if (!/[A-Z]/.test(trimmedValue)) {
                        errors.push('Password must contain at least one uppercase letter');
                    }
                    if (!/[a-z]/.test(trimmedValue)) {
                        errors.push('Password must contain at least one lowercase letter');
                    }
                    if (!/\d/.test(trimmedValue)) {
                        errors.push('Password must contain at least one number');
                    }
                    if (!/[!@#$%^&*\(\)\-_=+\[\]{};:'\",.<>\/?\\|~`]/.test(trimmedValue)) {
                        errors.push('Password must contain at least one special character');
                    }

                    // Pattern validation
                    if (/(.)\1{2,}/.test(trimmedValue)) {
                        errors.push('Password cannot contain 3 or more consecutive identical characters');
                    }

                    // Sequential character check
                    const sequences = ['abc', 'bcd', 'cde', 'def', 'efg', 'fgh', 'ghi', 'hij', 'ijk', 'jkl', 'klm', 'lmn', 'mno', 'nop', 'opq', 'pqr', 'qrs', 'rst', 'stu', 'tuv', 'uvw', 'vwx', 'wxy', 'xyz', '123', '234', '345', '456', '567', '678', '789'];
                    for (let seq of sequences) {
                        if (trimmedValue.toLowerCase().includes(seq)) {
                            errors.push('Password cannot contain sequential characters');
                            break;
                        }
                    }

                    // Common weak passwords
                    const commonPasswords = ['password', 'password123', '12345678', 'qwerty', 'admin', 'letmein'];
                    for (let common of commonPasswords) {
                        if (trimmedValue.toLowerCase().includes(common)) {
                            errors.push('Password contains common weak patterns');
                            break;
                        }
                    }
                    break;

                case 'area_of_expertise':
                    if (this.isTooLong(trimmedValue, 100)) {
                        errors.push('Area of expertise cannot exceed 100 characters');
                    }
                    break;

                // Teams-specific validations
                case 'name': // Team name
                    if (this.isTooShort(trimmedValue)) {
                        errors.push('Team name must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 80)) {
                        errors.push('Team name cannot exceed 80 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Team name cannot be only numbers');
                    }
                    break;

                case 'title': // Research title - allowing longer text
                    if (this.isTooShort(trimmedValue)) {
                        errors.push('Research title must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 250)) {
                        errors.push('Research title cannot exceed 250 characters');
                    }
                    break;

                // Programs-specific validations
                case 'college':
                    if (this.isTooShort(trimmedValue)) {
                        errors.push('College name must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 255)) {
                        errors.push('College name cannot exceed 255 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('College name cannot be only numbers');
                    }
                    break;

                case 'department':
                    if (trimmedValue && this.isTooLong(trimmedValue, 255)) {
                        errors.push('Department name cannot exceed 255 characters');
                    }
                    if (trimmedValue && this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Department name cannot be only numbers');
                    }
                    break;

                case 'program_name': // For program forms, we might use 'program_name' as field name
                case 'program': // Or just 'name' field in program context
                    if (this.isTooShort(trimmedValue)) {
                        errors.push('Program name must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 255)) {
                        errors.push('Program name cannot exceed 255 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Program name cannot be only numbers');
                    }
                    break;

                case 'specialization':
                    if (trimmedValue && this.isTooLong(trimmedValue, 255)) {
                        errors.push('Specialization cannot exceed 255 characters');
                    }
                    if (trimmedValue && this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Specialization cannot be only numbers');
                    }
                    break;

                // Environment variables validation
                case 'key':
                    if (this.isTooShort(trimmedValue)) {
                        errors.push('Key must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 100)) {
                        errors.push('Key cannot exceed 100 characters');
                    }
                    // Environment variable keys should be alphanumeric with underscores
                    if (!/^[a-zA-Z0-9_]+$/.test(trimmedValue)) {
                        errors.push('Key can only contain letters, numbers, and underscores');
                    }
                    break;

                case 'value':
                    if (this.isTooLong(trimmedValue, 500)) {
                        errors.push('Value cannot exceed 500 characters');
                    }
                    break;

                // Requirements validation
                case 'requirement_name':
                case 'req_name':
                    if (this.isTooShort(trimmedValue, 2)) {
                        errors.push('Requirement name must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 100)) {
                        errors.push('Requirement name cannot exceed 100 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Requirement name cannot be only numbers');
                    }
                    if (this.hasInvalidCharacters(trimmedValue)) {
                        errors.push('Requirement name contains invalid characters');
                    }
                    break;

                case 'specialization':
                    if (trimmedValue && this.isTooLong(trimmedValue, 255)) {
                        errors.push('Specialization cannot exceed 255 characters');
                    }
                    if (trimmedValue && this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Specialization cannot be only numbers');
                    }
                    break;

                // Rubric-specific validations
                case 'rubric_description':
                    if (trimmedValue && this.isTooLong(trimmedValue, 500)) {
                        errors.push('Rubric description cannot exceed 500 characters');
                    }
                    break;

                case 'description': // For requirements and other forms
                    if (this.isTooLong(trimmedValue, 1000)) {
                        errors.push('Description cannot exceed 1000 characters');
                    }
                    break;

                // Environment variables and other fields
                case 'key':
                    if (this.isTooShort(trimmedValue)) {
                        errors.push('Key must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 50)) {
                        errors.push('Key cannot exceed 50 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Key cannot be only numbers');
                    }
                    break;

                case 'value':
                    if (this.isTooLong(trimmedValue, 500)) {
                        errors.push('Value cannot exceed 500 characters');
                    }
                    break;

                case 'bio':
                    if (trimmedValue && this.isTooLong(trimmedValue, 1000)) {
                        errors.push('Bio cannot exceed 1000 characters');
                    }
                    break;

                case 'headline':
                    if (trimmedValue && this.isTooLong(trimmedValue, 100)) {
                        errors.push('Headline cannot exceed 100 characters');
                    }
                    break;

                // Research title form fields (for home page research title checker)
                case 'researchTitle':
                    if (this.isTooShort(trimmedValue, 10)) {
                        errors.push('Research title must be at least 10 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 250)) {
                        errors.push('Research title cannot exceed 250 characters');
                    }
                    break;

                case 'researchField':
                    if (this.isTooShort(trimmedValue, 2)) {
                        errors.push('Research field must be at least 2 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 100)) {
                        errors.push('Research field cannot exceed 100 characters');
                    }
                    if (this.isOnlyNumbers(trimmedValue)) {
                        errors.push('Research field cannot be only numbers');
                    }
                    break;

                case 'problem':
                    if (this.isTooShort(trimmedValue, 10)) {
                        errors.push('Problem statement must be at least 10 characters long');
                    }
                    if (this.isTooLong(trimmedValue, 500)) {
                        errors.push('Problem statement cannot exceed 500 characters');
                    }
                    break;
            }

            return errors;
        },

        // Validate password confirmation
        validatePasswordConfirmation: function (password, confirmPassword) {
            const errors = [];

            if (!confirmPassword || confirmPassword.trim() === '') {
                return ['Password confirmation is required'];
            }

            if (password !== confirmPassword) {
                errors.push('Passwords do not match');
            }

            return errors;
        },

        // Validate form data with password confirmation
        validatePasswordForm: function (formData) {
            const errors = {};
            const password = formData.get('password');
            const confirmPassword = formData.get('confirmpassword') || formData.get('password_confirm');

            // Validate password
            if (password) {
                const passwordErrors = this.validateField('password', password);
                if (passwordErrors.length > 0) {
                    errors.password = passwordErrors;
                }

                // Validate password confirmation
                if (confirmPassword) {
                    const confirmErrors = this.validatePasswordConfirmation(password, confirmPassword);
                    if (confirmErrors.length > 0) {
                        errors.confirmpassword = confirmErrors;
                    }
                }
            }

            return errors;
        },

        // Validate entire form
        validateUserForm: function (formElement) {
            const errors = {};
            const formData = new FormData(formElement);
            const usertype = formData.get('usertype');

            // Define fields to validate
            const fieldsToValidate = ['username', 'email', 'first_name', 'last_name'];

            // Add password field if it's an add form or if password is being changed
            if (formElement.id === 'addForm' || formData.get('password')) {
                fieldsToValidate.push('password');
            }

            // Add area_of_expertise if usertype is faculty (2)
            if (usertype == 2) {
                fieldsToValidate.push('area_of_expertise');
            }

            // Validate headline and bio only if not empty/null
            const optionalFields = ['headline', 'bio'];
            optionalFields.forEach(fieldName => {
                const value = formData.get(fieldName);
                if (value && value.trim() !== '') {
                    const fieldErrors = this.validateField(fieldName, value, usertype);
                    if (fieldErrors.length > 0) {
                        errors[fieldName] = fieldErrors;
                    }
                }
            });

            fieldsToValidate.forEach(fieldName => {
                const value = formData.get(fieldName);
                if (value !== null) { // Only validate if field exists
                    const fieldErrors = this.validateField(fieldName, value, usertype);
                    if (fieldErrors.length > 0) {
                        errors[fieldName] = fieldErrors;
                    }
                }
            });

            return errors;
        },

        // Validate teams form
        validateTeamsForm: function (formElement) {
            const errors = {};
            const formData = new FormData(formElement);

            // Define fields to validate for teams
            const fieldsToValidate = ['name', 'title', 'area_of_expertise'];

            fieldsToValidate.forEach(fieldName => {
                const value = formData.get(fieldName);
                if (value !== null) { // Only validate if field exists
                    const fieldErrors = this.validateField(fieldName, value);
                    if (fieldErrors.length > 0) {
                        errors[fieldName] = fieldErrors;
                    }
                }
            });

            return errors;
        },

        // Validate programs form
        validateProgramsForm: function (formElement) {
            const errors = {};
            const formData = new FormData(formElement);

            // Define fields to validate for programs
            const fieldsToValidate = ['college', 'name']; // Required fields
            const optionalFields = ['department', 'specialization']; // Optional fields to validate if present

            // Validate required fields
            fieldsToValidate.forEach(fieldName => {
                const value = formData.get(fieldName);
                if (!value || !value.trim()) {
                    errors[fieldName] = ['This field is required'];
                } else {
                    const fieldErrors = this.validateField(fieldName === 'name' ? 'program_name' : fieldName, value);
                    if (fieldErrors.length > 0) {
                        errors[fieldName] = fieldErrors;
                    }
                }
            });

            // Validate optional fields if they have values
            optionalFields.forEach(fieldName => {
                const value = formData.get(fieldName);
                if (value && value.trim()) { // Only validate if field has content
                    const fieldErrors = this.validateField(fieldName, value);
                    if (fieldErrors.length > 0) {
                        errors[fieldName] = fieldErrors;
                    }
                }
            });

            return errors;
        },

        // Validate requirements form
        validateRequirementsForm: function (formElement) {
            const errors = {};
            const formData = new FormData(formElement);

            // Define fields to validate for requirements
            const fieldsToValidate = ['name', 'description'];

            fieldsToValidate.forEach(fieldName => {
                const value = formData.get(fieldName);
                if (!value || !value.trim()) {
                    errors[fieldName] = ['This field is required'];
                } else {
                    // Use specific validation for requirement fields
                    let fieldErrors = [];
                    const trimmedValue = value.trim();

                    // Common validations for all text fields
                    if (this.containsEmoji(trimmedValue)) {
                        fieldErrors.push('Emojis are not allowed');
                    }
                    if (this.containsHTML(trimmedValue)) {
                        fieldErrors.push('HTML tags are not allowed');
                    }

                    if (fieldName === 'name') {
                        if (this.isTooShort(trimmedValue, 2)) {
                            fieldErrors.push('Requirement name must be at least 2 characters long');
                        }
                        if (this.isTooLong(trimmedValue, 100)) {
                            fieldErrors.push('Requirement name cannot exceed 100 characters');
                        }
                        if (this.isOnlyNumbers(trimmedValue)) {
                            fieldErrors.push('Requirement name cannot be only numbers');
                        }
                        if (this.hasInvalidCharacters(trimmedValue)) {
                            fieldErrors.push('Requirement name contains invalid characters');
                        }
                    } else if (fieldName === 'description') {
                        if (this.isTooShort(trimmedValue, 5)) {
                            fieldErrors.push('Description must be at least 5 characters long');
                        }
                        if (this.isTooLong(trimmedValue, 500)) {
                            fieldErrors.push('Description cannot exceed 500 characters');
                        }
                        // Allow more characters in description but still validate for emojis and basic structure
                        const descriptionPattern = /^[a-zA-Z0-9\s\.\,\-\_\@\(\)\:\;\!\?\"\']+$/;
                        if (!descriptionPattern.test(trimmedValue)) {
                            fieldErrors.push('Description contains invalid characters');
                        }
                    }

                    if (fieldErrors.length > 0) {
                        errors[fieldName] = fieldErrors;
                    }
                }
            });

            // Validate due_date
            const dueDate = formData.get('due_date');
            if (!dueDate) {
                errors['due_date'] = ['Due date is required'];
            } else {
                const selectedDate = new Date(dueDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time to start of day for comparison

                if (selectedDate < today) {
                    errors['due_date'] = ['Due date cannot be in the past'];
                }
            }

            // Validate file if provided
            const fileInput = formElement.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                const maxSize = 10 * 1024 * 1024; // 10MB

                if (!allowedTypes.includes(file.type)) {
                    errors['template_file'] = ['Invalid file type. Please upload PDF, DOC, DOCX, TXT, XLSX, or PPTX files only'];
                }
                if (file.size > maxSize) {
                    errors['template_file'] = ['File size too large. Maximum 10MB allowed'];
                }
            }

            return errors;
        },

        // Display validation errors
        displayErrors: function (errors) {
            // Clear previous errors
            $('.validation-error').remove();
            $('.is-invalid').removeClass('is-invalid');

            Object.keys(errors).forEach(fieldName => {
                const field = $(`[name="${fieldName}"]`);
                if (field.length > 0) {
                    field.addClass('is-invalid');
                    const errorHtml = `<div class="validation-error text-danger small mt-1">${errors[fieldName].join(', ')}</div>`;
                    field.closest('.mb-3').append(errorHtml);
                }
            });
        },

        // Real-time validation for individual fields
        setupRealTimeValidation: function (formSelector) {
            $(document).off('input.validation').on('input.validation', `${formSelector} input, ${formSelector} textarea`, function () {
                const field = $(this);
                const fieldName = field.attr('name');
                const value = field.val();
                const form = field.closest('form')[0];
                const formData = new FormData(form);
                const usertype = formData.get('usertype');

                // Remove previous error for this field
                field.removeClass('is-invalid');
                // Look for error container in both .mb-3 and .col-* structures
                const errorContainer = field.closest('.mb-3').length > 0 ? field.closest('.mb-3') : field.closest('.col-md-6, .col-md-4, .col-md-12, .col-12');
                errorContainer.find('.validation-error').remove();

                if (value && fieldName) {
                    const fieldErrors = ValidationUtils.validateField(fieldName, value, usertype);
                    if (fieldErrors.length > 0) {
                        field.addClass('is-invalid');
                        const errorHtml = `<div class="validation-error text-danger small mt-1">${fieldErrors.join(', ')}</div>`;
                        errorContainer.append(errorHtml);
                    }
                }
            });
        }
    };

    // Setup real-time validation when modals are shown
    $('#editModal, #addModal').on('shown.bs.modal', function () {
        const formSelector = $(this).find('form').length > 0 ? $(this).find('form').eq(0).attr('id') : null;
        if (formSelector) {
            ValidationUtils.setupRealTimeValidation(`#${formSelector}`);
        }
    });
</script>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form contents filled via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveEdit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 3rem; color: var(--main-black); margin-bottom: 1rem;">
                    <i class="fas fa-door-open"></i>
                </div>
                <h4 class="fw-bold mb-3" id="logoutConfirmModalLabel">Confirm Logout</h4>
                <p>Are you sure you want to log out?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="/logout/" class="btn btn-danger" id="confirmLogout">Logout</a>
            </div>
        </div>
    </div>
</div>
<script>
    // Add helper function to dynamically populate program dropdowns
    // function populateProgramDropdown(selectElement, selectedValue = null) {
    //     selectElement.html('<option value="">Loading programs...</option>');

    //     $.ajax({
    //         url: 'includes/get_programs_grouped.php',
    //         method: 'GET',
    //         dataType: 'json',
    //         success: function(response) {
    //             selectElement.empty();
    //             selectElement.append('<option value="">Select Program</option>');

    //             if (response.success && response.programs.length > 0) {
    //                 let currentCollege = null;
    //                 let optgroup = null;

    //                 $.each(response.programs, function(i, program) {
    //                     // Create new optgroup when college changes
    //                     if (program.college !== currentCollege) {
    //                         currentCollege = program.college;
    //                         optgroup = $('<optgroup>', {
    //                             label: currentCollege
    //                         });
    //                         selectElement.append(optgroup);
    //                     }

    //                     // Add program option to current optgroup
    //                     const option = $('<option>', {
    //                         value: program.display_name,
    //                         text: program.display_name
    //                     });



    //                     // Set selected if matches
    //                     if (selectedValue !== null && selectedValue === program.display_name) {
    //                         option.prop('selected', true);
    //                     }



    //                     optgroup.append(option);
    //                 });
    //             } else {
    //                 selectElement.html('<option value="">No programs available</option>');
    //             }
    //         },
    //         error: function() {
    //             selectElement.html('<option value="">Error loading programs</option>');
    //             console.error("Failed to load programs");
    //         }
    //     });
    // }

    $(document).ready(function () {
        // Create toast container if it doesn't exist
        if (!$('#toastContainer').length) {
            $('body').append(
                '<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>');
        }
        // EDIT START
        // Edit button functionality
        $(document).off('click.editBtn').on('click.editBtn', '.edit-btn', function (e) {
            e.preventDefault();

            var table = $(this).data('table');
            var id = $(this).data('id');

            console.log('Main app: Edit button clicked. Table:', table, 'ID:', id);

            // Special handling for rubrics
            if (table === 'rubrics') {
                console.log('Main app: Delegating rubric edit to rubrics_tab.php handler');
                // Let the edit-rubric-btn handler in rubrics_tab.php handle this
                $('.edit-rubric-btn[data-id="' + id + '"]').trigger('click');
                return true; // Allow event to bubble to other handlers
            }

            // Continue with normal edit handling for other tables
            $.ajax({
                url: 'includes/get_item_details.php',
                method: 'POST',
                data: {
                    table: table,
                    id: id
                },
                dataType: 'json',
                success: function (response) {
                    console.log('Response data:', response)
                    if (response.success) {
                        var form = $('#editForm');
                        form.empty();

                        // Add hidden inputs for table and id
                        form.append('<input type="hidden" name="table" value="' + table + '">');
                        form.append('<input type="hidden" name="id" value="' + id + '">');

                        if (table === 'users') {
                            console.log('Response programs:', response.programs);

                            var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="usertype" class="form-label">User Type ${response.data.program}</label>
                                    <select class="form-select" id="usertype" name="usertype" required>
                                        <option value="0"${response.data.usertype == 0 ? ' selected' : ''}>Admin</option>
                                        <option value="1"${response.data.usertype == 1 ? ' selected' : ''}>Student</option>
                                        <option value="2"${response.data.usertype == 2 ? ' selected' : ''}>Faculty</option>
                                    </select>
                                </div>
                            `;

                            var fieldsToShow = ['username', 'email', 'first_name', 'last_name',
                                'gender', 'headline', 'bio'
                            ];

                            fieldsToShow.forEach(function (key) {
                                var value = response.data[key] || '';
                                var inputType = (key === 'email') ? 'email' : 'text';
                                var label = key.replace('_', ' ').charAt(0)
                                    .toUpperCase() + key.slice(1);

                                if (key === 'bio') {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <textarea class="form-control" id="${key}" name="${key}" rows="3">${value}</textarea>
                                        </div>
                                    `;
                                } else if (key === 'gender') {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label class="form-label">${label}</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="${key}" id="${key}_m" value="m" ${value === 'm' ? 'checked' : ''}>
                                                    <label class="form-check-label" for="${key}_m">Male</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="${key}" id="${key}_f" value="f" ${value === 'f' ? 'checked' : ''}>
                                                    <label class="form-check-label" for="${key}_f">Female</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="${key}" id="${key}_o" value="o" ${value === 'o' ? 'checked' : ''}>
                                                    <label class="form-check-label" for="${key}_o">Other</label>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    formHtml += `
                                        <div class="mb-3">
                                            <label for="${key}" class="form-label">${label}</label>
                                            <input type="${inputType}" class="form-control" id="${key}" name="${key}" value="${value}">
                                        </div>
                                    `;
                                }
                            });

                            // Replace hardcoded program dropdown with select element
                            formHtml += `
                                <div class="mb-3" id="programField" 
                                    ${(id == 0 || response.data.usertype != 0) ? 'style="display:none;"' : ''}>
                                    <label for="program_id" class="form-label">Program</label>
                                    <select class="form-select" id="program_id" name="program_id" required>
                                    </select>
                                </div>
                            `;

                            // Area of expertise field
                            formHtml += `
                                <div class="mb-3 area-expertise-field" ${response.data.usertype != 2 ? 'style="display:none;"' : ''}>
                                    <label for="area_of_expertise" class="form-label">Area of Expertise</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" value="${response.data.area_of_expertise || ''}" required>
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                                            <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                                        </ul>
                                    </div>
                                </div>
                            `;

                            // Add Is Part Time radio buttons for faculty (usertype 2)
                            formHtml += `
                            <div class="mb-3 is-part-time-field" ${response.data.usertype != 2 ? 'style="display:none;"' : ''}>
                                <label class="form-label">Is Part Time</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_parttime" id="fullTime" value="0" ${response.data.is_parttime == 0 || response.data.is_parttime == null ? 'checked' : ''} required>
                                    <label class="form-check-label" for="fullTime">Full Time</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_parttime" id="partTime" value="1" ${response.data.is_parttime == 1 ? 'checked' : ''} required>
                                    <label class="form-check-label" for="partTime">Part Time</label>
                                </div>
                            </div>
                            `;
                            formHtml += `
                            <div class="mb-3 is-program-chair-field" 
                                ${(id == 0 || response.data.usertype != 0) ? 'style="display:none;"' : ''}>
                                <label class="form-label">Program Chair</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_program_chair" id="program_chair" value="0" ${response.data.is_program_chair == 0 || response.data.is_program_chair == null ? 'checked' : ''} required>
                                    <label class="form-check-label" for="program_chair">Program Chair</label>
                                </div>
                            </div>
                            `;

                            form.html(formHtml);
                            console.log(`Program data: ${response.data.program}`);


                            // Populate the programs dropdown
                            populateProgramDropdown($('#program_id'), response.data.program);

                            $('#usertype').on('change', function () {
                                if ($(this).val() == 2) {
                                    $('.area-expertise-field').show();
                                    $('.is-part-time-field').show();
                                } else {
                                    $('.area-expertise-field').hide();
                                    $('.is-part-time-field').hide();
                                }
                            });
                        } else if (table === 'teams') {
                            var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="name" class="form-label">Team Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="${response.data.name}">
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Research Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
                            </div>
                            <div class="mb-3">
                                <label for="area_of_expertise" class="form-label">Area of Expertise</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" value="${response.data.area_of_expertise || ''}">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                                        <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="program_id" class="form-label">Program</label>
                                <select class="form-select" id="program_id" name="program_id">
                                    // <option value="">Loading programs...</option>
                                </select>
                            </div>
                            <h5 class="mt-4">Team Members</h5>
                            <div id="teamMembers">
                        `;

                            response.data.members.forEach(function (member, index) {
                                formHtml += `
                                <div class="mb-3 row team-member" data-user-id="${member.id}">
                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" name="member_name[]" value="${member.name}" readonly>
                                        <input type="hidden" name="member_ids[]" value="${member.id}">
                                    </div>
                                    <div class="col-sm-5">
                                        <select class="form-select" name="member_role[]">
                                            <option value="adviser"${member.role === 'adviser' ? ' selected' : ''}>Adviser</option>
                                            <option value="leader"${member.role === 'leader' ? ' selected' : ''}>Leader</option>
                                            <option value="member"${member.role === 'member' ? ' selected' : ''}>Member</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                                    </div>
                                </div>
                            `;
                            });

                            formHtml += `
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
                        `;

                            form.html(formHtml);

                            // Populate the programs dropdown for the edit form, selecting the current value
                            populateProgramDropdown($('#editForm #program_id'), response.data.program);

                            // Add team member functionality
                            $('#addTeamMember').on('click', function () {
                                console.log('Add Team Member button clicked');
                                addNewTeamMember();
                            });

                        } else if (table === 'programs') {
                            var d = response.data;
                            var html = `
                          <input type="hidden" name="table" value="programs">
                          <input type="hidden" name="id" value="${id}">
                          <div class="mb-3">
                            <label class="form-label">College</label>
                            <input class="form-control" name="college" id="college" value="${d.college || ''}" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input class="form-control" name="department" id="department" value="${d.department || ''}">
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Program Name</label>
                            <input class="form-control" name="name" id="name" value="${d.name || ''}" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <input class="form-control" name="specialization" id="specialization" value="${d.specialization || ''}">
                          </div>`;
                            form.html(html);

                            // Add real-time validation for programs edit form using ValidationUtils
                            ValidationUtils.setupRealTimeValidation('#editForm');

                            $('#editModal').modal('show');
                        } else if (table === 'thesis_topics') {
                            var formHtml = `
                        <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="topic" class="form-label">Topic</label>
                                <input type="text" class="form-control" id="topic" name="topic" value="${response.data.topic}">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3">${response.data.description}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" value="${response.data.category}">
                            </div>
                        `;
                            form.html(formHtml);
                        } else if (table === 'research_titles') {
                            // Filter teams: include current team and teams without research titles
                            const availableTeams = response.teams.filter(team =>
                                team.id == response.data.team_id || !team.has_research_title
                            );

                            var teamOptions = availableTeams.map(team => {
                                const selected = team.id == response.data.team_id ? ' selected' : '';
                                const label = team.id == response.data.team_id ?
                                    `${team.name} (current)` : team.name;
                                return `<option value="${team.id}"${selected}>${label}</option>`;
                            }).join('');

                            var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                    <option value="">Select Team</option>
                                    ${teamOptions}
                                </select>
                                <div class="form-text text-muted">Only teams without existing research titles are shown (plus current team).</div>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="${response.data.title}">
                            </div>
                            <div class="mb-3">
                                <label for="program_id" class="form-label">Program</label>
                                <select class="form-select" id="program_id" name="program_id">
                                    <option value="">Loading programs...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="approved" name="approved_at" value="1" ${response.data.approved_at ? 'checked' : ''}>
                                    <label class="form-check-label" for="approved_at">Approved</label>
                                </div>
                            </div>
                        `;
                            form.html(formHtml);

                            // Populate the programs dropdown
                            populateProgramDropdown($('#program_id'), response.data.program_id);
                        } else if (table === 'env_variables') {

                            if (response.data.key === 'APP_LOGO_NAVBAR') {
                                var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="key" class="form-label">Key</label>
                                    <input type="text" class="form-control" id="key" name="key" value="${response.data.key}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="value" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" id="value" name="value" accept="image/*" required>
                                    <!-- Current image preview -->
                                    <?php
                                    if (defined('APP_LOGO_NAVBAR') && APP_LOGO_NAVBAR ) {
                                        $logoPath = '../assets/images/' . APP_LOGO_NAVBAR;
                                        echo '<img src="' . htmlspecialchars($logoPath) . '" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 200px;">';
                                    } else {
                                        echo '<img src="../assets/images/" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 200px;">';
                                    }
                                    ?>
                                    <p class="form-text text-muted">Image will be saved to /assets/images/ and renamed automatically.</p>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php if (!empty($response['data']['description'])): ?><?= htmlspecialchars($response['data']['description']) ?><?php endif; ?></textarea>
                                    
                                </div>
                            `;
                            } else if (response.data.key === 'APP_LOGO_FOOTER') {
                                var formHtml = `
                                <input type="hidden" name="table" value="${table}">
                                <input type="hidden" name="id" value="${id}">
                                <div class="mb-3">
                                    <label for="key" class="form-label">Key</label>
                                    <input type="text" class="form-control" id="key" name="key" value="${response.data.key}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="value" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" id="value" name="value" accept="image/*" required>
                                    <!-- Current image preview -->
                                    <?php
                                    if (defined('APP_LOGO_FOOTER') && APP_LOGO_FOOTER) {
                                        $logoPath = '../assets/images/' . APP_LOGO_FOOTER;
                                        echo '<img src="' . htmlspecialchars($logoPath) . '" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 200px;">';
                                    } else {
                                        echo '<img src="../assets/images/" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 200px;">';
                                    }
                                    ?>
                                    <p class="form-text text-muted">Image will be saved to /assets/images/ and renamed automatically.</p>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php if (!empty($response['data']['description'])): ?><?= htmlspecialchars($response['data']['description']) ?><?php endif; ?></textarea>
                                    
                                </div>
                            `;
                            }

                            // Handle special cases for MAIL_PASSWORD
                            else if (response.data.key === 'MAIL_PASSWORD') {
                                $('#value').val(''); // Clear the value field
                                form.html(`
                                    <input type="hidden" name="table" value="${table}">
                                    <input type="hidden" name="id" value="${id}">
                                    <div class="mb-3">
                                        <label for="key" class="form-label">Key</label>
                                        <input type="text" class="form-control" id="key" name="key" value="${response.data.key}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="value" class="form-label">Value</label>
                                        <input type="text" class="form-control" id="value" name="value" value="">
                                    </div>
                                    <div class="mb-3">
                                        <label for="old_value" class="form-label">Old Value</label>
                                        <input type="password" class="form-control" id="old_value" name="old_value" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php if (!empty($response['data']['description'])): ?><?= htmlspecialchars($response['data']['description']) ?><?php endif; ?></textarea>
                                    </div>

                                `);
                            } else {
                                var formHtml = `
                                    <input type="hidden" name="table" value="${table}">
                                    <input type="hidden" name="id" value="${id}">
                                    <div class="mb-3">
                                        <label for="key" class="form-label">Key</label>
                                        <input type="text" class="form-control" id="key" name="key" value="${response.data.key}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="value" class="form-label">Value</label>
                                        <input type="text" class="form-control" id="value" name="value" value="${response.data.value}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php if (!empty($response['data']['description'])): ?><?= htmlspecialchars($response['data']['description']) ?><?php endif; ?></textarea>
                                    </div>
                                `;
                            }
                            form.html(formHtml);
                        } else if (table === 'defense_schedules') {
                            var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="schedule_date" class="form-label">Schedule Date</label>
                                <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required value="${response.data.schedule_date || ''}">
                                <small class="form-text text-muted">Select date for the defense schedule.</small>
                            </div>
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required value="${response.data.start_time || ''}"
                                    onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                                <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                            </div>
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required value="${response.data.end_time || ''}"
                                    onchange="this.value = this.value.substr(0,3) + (this.value.substr(3,2) >= '30' ? '30' : '00')">
                                <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                            </div>
                            <div class="mb-3">
                                <label for="room" class="form-label">Room</label>
                                <input type="text" class="form-control" id="room" name="room" required value="${response.data.room || ''}">
                            </div>
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                <option value="">Select Team</option>
                                    ${response.teams.map(team => `<option value="${team.id}"${team.id === response.data.team_id ? ' selected' : ''}>${team.name}</option>`).join('')}
                                </select>
                            </div>
                            <h5 class="mt-4">Panelists (Max 3)</h5>
                            <div id="panelists">
                            `;

                            let panelistCount = 0; // Initialize panelist count
                            if (response.data.panelists && Array.isArray(response.data
                                .panelists)) {
                                panelistCount = response.data.panelists
                                    .length; // Get initial count
                                response.data.panelists.forEach(function (panelist, index) {
                                    // Ensure unique indices for names
                                    formHtml += `
                            <div class="mb-3 row panelist align-items-center" data-user-id="${panelist.id}">
                                <label class="col-sm-2 col-form-label">Panelist ${index + 1}</label>
                                <div class="col-sm-8">
                                    <select class="form-select" name="panelist_id[${index}]" required>
                                        ${response.staff.map(staff => `
                                            <option value="${staff.id}"${staff.id === panelist.id ? ' selected' : ''}>
                                                ${staff.name}
                                            </option>
                                        `).join('')}
                                    </select>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                                </div>
                            </div>
                            `;
                                });
                            } else {
                                console.warn(
                                    'Panelists data is missing or not an array in the response for edit form.'
                                );
                            }

                            formHtml += `
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
                            `;

                            form.html(formHtml);

                            // Initialize the date picker with same options as in defense_schedules_tab.php
                            $('.datepicker').datepicker({
                                format: 'yyyy-mm-dd', // Match MySQL date format
                                multidate: false, // Single date selection for defense schedule
                                startDate: new Date(), // Prevent selecting previous dates
                                todayHighlight: true, // Highlight today's date
                                autoclose: true // Close calendar after selection
                            });

                            // Add time picker validation
                            $('#start_time, #end_time').on('change', function () {
                                const startTime = $('#start_time').val();
                                const endTime = $('#end_time').val();

                                if (startTime && endTime && startTime >= endTime) {
                                    showToast('Error',
                                        'End time must be after start time', 'error'
                                    );
                                    $(this).val(''); // Clear the current field
                                }
                            });

                            // Store staff data for addNewPanelist function
                            window.staffData = response.staff;

                            // Function to update all panelist dropdowns with new data (for edit form)
                            function updatePanelistDropdowns(staffList) {
                                // Add validation to prevent errors
                                if (!staffList || !Array.isArray(staffList)) {
                                    console.warn('Invalid staffList provided to updatePanelistDropdowns:', staffList);
                                    return;
                                }
                                const optionsHtml = staffList.map(staff => `<option value="${staff.id}">${staff.name}</option>`).join('');
                                $('#panelists .panelist select').each(function () {
                                    const currentValue = $(this).val(); // Preserve current selection if possible
                                    $(this).html(optionsHtml);
                                    if (currentValue && staffList.find(staff => staff.id == currentValue)) {
                                        $(this).val(currentValue); // Restore selection if still available
                                    }
                                });
                            }

                            // Event listener for the team dropdown (for edit form)
                            $('#team_id').on('change', function () {
                                const teamId = $(this).val();
                                if (teamId) {
                                    $.ajax({
                                        url: 'includes/get_teams_and_staff.php',
                                        method: 'GET',
                                        dataType: 'json',
                                        data: {
                                            team_id: teamId
                                        },
                                        success: function (staffData) {
                                            if (staffData.success && staffData.staff && Array.isArray(staffData.staff)) {
                                                window.staffData = staffData.staff; // Update the global staff data
                                                updatePanelistDropdowns(staffData.staff);
                                            } else {
                                                console.warn('Invalid staff data received:', staffData);
                                                showToast('Error', 'Unable to fetch panelists for this team.', 'error');
                                            }
                                        },
                                        error: function () {
                                            showToast('Error', 'An error occurred while fetching panelists.', 'error');
                                        }
                                    });
                                } else {
                                    // If no team is selected, use the original staff data
                                    if (response.staff && Array.isArray(response.staff)) {
                                        updatePanelistDropdowns(response.staff);
                                    }
                                }
                            });

                            // Disable "Add Panelist" button initially if limit is reached
                            if (panelistCount >= 3) {
                                $('#addPanelist').prop('disabled', true);
                            }

                            // Add panelist functionality (uses global addNewPanelist function)
                            $('#addPanelist').on('click', function () {
                                console.log(
                                    'Add Panelist button clicked in edit modal');
                                addNewPanelist(window
                                    .staffData); // Call the global function
                            });

                            // Remove panelist functionality (Enable add button when removing)
                            // Use event delegation on the form for dynamically added elements
                            form.off('click.removePanelist').on('click.removePanelist',
                                '.remove-panelist',
                                function () {
                                    $(this).closest('.panelist').remove();
                                    // Check count and enable button if below limit
                                    if ($('#panelists .panelist').length < 3) {
                                        $('#addPanelist').prop('disabled', false);
                                    }
                                    // Re-index remaining panelists to ensure sequential names
                                    $('#panelists .panelist').each(function (index) {
                                        $(this).find('select').attr('name',
                                            `panelist_id[${index}]`);
                                        $(this).find('label.col-form-label').text(
                                            `Panelist ${index + 1}`);
                                    });
                                    updatePanelistDropdowns
                                        (); // Update dropdowns after removal
                                });

                            // Update dropdowns initially to disable selected options in other dropdowns
                            updatePanelistDropdowns();

                            // Add real-time validation for defense schedules edit form using ValidationUtils
                            ValidationUtils.setupRealTimeValidation('#editForm');
                        } else if (table === 'requirements') {
                            var formHtml = `
                            <input type="hidden" name="table" value="${table}">
                            <input type="hidden" name="id" value="${id}">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="${response.data.name || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required>${response.data.description || ''}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="${response.data.due_date || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label for="template_file" class="form-label">File Template</label>
                                <input type="file" class="form-control" id="template_file" name="template_file" accept=".pdf,.doc,.docx,.txt,.xlsx,.pptx">
                                <small class="form-text text-muted">Upload a new file template if needed. Current file: ${response.data.template_original_name || 'None'}</small>
                            </div>
                            ${response.data.template_file ? `
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remove_template" name="remove_template" value="1">
                                    <label class="form-check-label" for="remove_template">
                                        Remove current template file
                                    </label>
                                </div>
                            </div>
                            ` : ''}
                        `;
                            form.html(formHtml);

                            // Add real-time validation for requirements edit form using ValidationUtils
                            ValidationUtils.setupRealTimeValidation('#editForm');
                        } else if (table === 'rubrics') {
                            // Don't generate form fields - they're handled in rubrics_tab.php
                            return;
                        } else if (table === 'user_schedules') {
                            const d = response.data || {};
                            const formatTime = (time, part) => {
                                if (!time) return part === 'hour' ? '07' : '00';
                                const [h, m] = time.split(':');
                                if (part === 'hour') return ((parseInt(h) % 12) || 12).toString().padStart(2, '0');
                                if (part === 'minute') return m.padStart(2, '0');
                                return '';
                            };
                            const getMeridian = time => (time && parseInt(time.split(':')[0]) >= 12) ? 'PM' : 'AM';

                            // 🧠 Build program options
                            let programOptions = '<option value="">Select Program</option>';
                            if (Array.isArray(response.programs)) {
                                programOptions += response.programs.map(p => `
                                    <option value="${p.id}" data-name="${p.name}" data-specialization="${p.specialization || ''}"${p.id == d.program ? ' selected' : ''}>
                                        ${p.name}${p.specialization ? ' (' + p.specialization + ')' : ''}
                                    </option>
                                `).join('');
                            } else {
                                programOptions += '<option value="">No programs available</option>';
                            }

                            function loadPrograms(selectedProgramId = null) {
                                fetch('includes/tabs/load_programs.php')
                                    .then(res => res.text())
                                    .then(html => {
                                        const programSelect = document.getElementById('program');
                                        if (programSelect) {
                                            programSelect.innerHTML = '<option value="">Select Program</option>' + html;

                                            if (selectedProgramId) {
                                                programSelect.value = selectedProgramId;
                                            }
                                        }
                                    })
                                    .catch(err => console.error('Error loading programs:', err));
                            }


                            // JS: Call this when you need to load the instructors
                            function loadInstructors(selectedId) {
                                fetch('includes/tabs/load_instructors.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        user_id: selectedId
                                    })
                                })
                                    .then(res => res.text())
                                    .then(html => {
                                        document.getElementById('user_id').innerHTML = html;
                                    })
                                    .catch(err => console.error('Load error:', err));
                            }

                            // Example usage
                            loadInstructors(d.user_id);

                            loadPrograms(d.program);

                            const formHtml = `
                                <input type="hidden" name="table" value="user_schedules">
                                <input type="hidden" name="id" value="${id}">

                                <!-- Program -->
                                <div class="mb-3">
                                    <label for="program" class="form-label">Program</label>
                                    <select class="form-select" id="program" name="program" required>
                                        <option>Loading...</option>
                                    </select>
                                </div>

                                <!-- Course Code -->
                                <div class="mb-3">
                                    <label for="class_name" class="form-label">Course Code</label>
                                    <input type="text" class="form-control" id="class_name" name="class_name" value="${d.class_name || ''}" required>
                                </div>

                                <!-- Year -->
                                <div class="mb-3">
                                    <label for="year" class="form-label">Year</label>
                                    <input type="number" class="form-control" id="year" name="year" min="1" max="5" value="${d.year || ''}" required>
                                </div>

                                <!-- Section -->
                                <div class="mb-3">
                                    <label for="section" class="form-label">Section</label>
                                    <input type="text" class="form-control" id="section" name="section" value="${d.section || ''}" required>
                                </div>

                                <!-- Room -->
                                <div class="mb-3">
                                    <label for="room" class="form-label">Room</label>
                                    <input type="text" class="form-control" id="room" name="room" value="${d.room || ''}" required>
                                </div>

                                <!-- Instructor -->
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Instructor</label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option>Loading...</option>
                                    </select>
                                    

                                </div>

                                <!-- Day of Week -->
                                <div class="mb-3">
                                    <label for="day_of_week" class="form-label">Day of Week</label>
                                    <select class="form-select" id="day_of_week" name="day_of_week" required>
                                        <option value="">Select Day</option>
                                        ${['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'].map(day => `
                                            <option value="${day}"${d.day_of_week === day ? ' selected' : ''}>${day}</option>
                                        `).join('')}
                                    </select>
                                </div>

                                <!-- Start Time -->
                                <div class="mb-3">
                                    <label class="form-label">Start Time</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-center">
                                            <button type="button" class="btn btn-light btn-sm" onclick="adjustTime('start', 'hour', -1)">▲</button><br>
                                            <span id="start_hour">${formatTime(d.start_time, 'hour')}</span><br>
                                            <button type="button" class="btn btn-light btn-sm" onclick="adjustTime('start', 'hour', 1)">▼</button>
                                        </div>
                                        <span>:</span>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-light btn-sm" onclick="adjustTime('start', 'minute', -30)">▲</button><br>
                                            <span id="start_minute">${formatTime(d.start_time, 'minute')}</span><br>
                                            <button type="button" class="btn btn-light btn-sm" onclick="adjustTime('start', 'minute', 30)">▼</button>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-light btn-sm" onclick="toggleMeridian('start')">⇅</button><br>
                                            <span id="start_meridian">${getMeridian(d.start_time)}</span><br>
                                        </div>
                                        <input type="hidden" id="start_time" name="start_time" value="${d.start_time || '07:00'}" required>
                                    </div>
                                    <small class="form-text text-muted">Time must be between 7:00 AM and 9:00 PM (00 or 30 minutes only).</small>
                                </div>

                                <!-- End Time -->
                                <div class="mb-3">
                                    <label class="form-label">End Time</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-center">
                                            <button type="button" class="btn btn-light btn-sm" onclick="adjustTime('end', 'hour', -1)">▲</button><br>
                                            <span id="end_hour">${formatTime(d.end_time, 'hour')}</span><br>
                                            <button type="button" class="btn btn-light btn-sm" onclick="adjustTime('end', 'hour', 1)">▼</button>
                                        </div>
                                        <span>:</span>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-light btn-sm" onclick="adjustTime('end', 'minute', -30)">▲</button><br>
                                            <span id="end_minute">${formatTime(d.end_time, 'minute')}</span><br>
                                            <button type="button" class="btn btn-light btn-sm" onclick="adjustTime('end', 'minute', 30)">▼</button>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" class="btn btn-light btn-sm" onclick="toggleMeridian('end')">⇅</button><br>
                                            <span id="end_meridian">${getMeridian(d.end_time)}</span><br>
                                        </div>
                                        <input type="hidden" id="end_time" name="end_time" value="${d.end_time || '07:00'}" required>
                                    </div>
                                    <small class="form-text text-muted">Time must be between 7:00 AM and 9:00 PM (00 or 30 minutes only).</small>
                                </div>
                            `;

                            form.html(formHtml);

                            // 🎯 Now get the selected program in real-time (AFTER form is rendered)
                            const programSelect = document.getElementById('program');

                            if (programSelect) {
                                const selectedProgramId = programSelect.value;
                                const selectedOption = programSelect.options[programSelect.selectedIndex];
                                const selectedProgramName = selectedOption.dataset.name || '';
                                const selectedSpecialization = selectedOption.dataset.specialization || '';

                                console.log("📌 Selected Program ID:", selectedProgramId);
                                console.log("📌 Program Name:", selectedProgramName);
                                console.log("📌 Specialization:", selectedSpecialization);

                                // 💡 Optional: Watch for changes to program selection
                                programSelect.addEventListener('change', (e) => {
                                    const selectedOption = e.target.options[e.target.selectedIndex];
                                    console.log("🔄 Program changed to:", {
                                        id: e.target.value,
                                        name: selectedOption.dataset.name,
                                        specialization: selectedOption.dataset.specialization
                                    });
                                });
                            }
                        }

                        // Add more conditions for other tables as needed
                        $('#editModal').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('Error: Unable to fetch item details - ' + error);
                }
            });
        });

        // UPDATED: Edit form submission handler with debug logs
        $(document).on('submit', '#editForm', function (e) {
            e.preventDefault();
            // 🔹 Check native HTML5 validation first

            console.log('DEBUG: Edit form submit event triggered'); // <-- New debug log
            var formData = new FormData(this);
            var table = formData.get('table'); // Get table name from form data

            // Apply comprehensive validation for users table
            if (table === 'users') {
                const validationErrors = ValidationUtils.validateUserForm(this);
                if (Object.keys(validationErrors).length > 0) {
                    ValidationUtils.displayErrors(validationErrors);
                    showToast('Error', 'Please fix the validation errors before submitting', 'error');
                    return;
                }
            } else if (table === 'teams') {
                const validationErrors = ValidationUtils.validateTeamsForm(this);
                if (Object.keys(validationErrors).length > 0) {
                    ValidationUtils.displayErrors(validationErrors);
                    showToast('Error', 'Please fix the validation errors before submitting', 'error');
                    return;
                }
            } else if (table === 'programs') {
                const validationErrors = ValidationUtils.validateProgramsForm(this);
                if (Object.keys(validationErrors).length > 0) {
                    ValidationUtils.displayErrors(validationErrors);
                    showToast('Error', 'Please fix the validation errors before submitting', 'error');
                    return;
                }
            } else if (table === 'requirements') {
                const validationErrors = ValidationUtils.validateRequirementsForm(this);
                if (Object.keys(validationErrors).length > 0) {
                    ValidationUtils.displayErrors(validationErrors);
                    showToast('Error', 'Please fix the validation errors before submitting', 'error');
                    return;
                }
            }
            // if (table != 'user_schedules' && !form.checkValidity() ) {
            //     form.reportValidity(); // show browser validation messages
            //     return; // stop here if invalid
            // }
            // Add validation for defense schedule times
            if (table === 'defense_schedules') {
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                const minTime = '07:00';
                const maxTime = '20:30';

                if (startTime < minTime || startTime > maxTime) {
                    showToast('Error', 'Start time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (endTime < minTime || endTime > maxTime) {
                    showToast('Error', 'End time must be between 7:00 AM and 8:30 PM.', 'error');
                    return; // Prevent submission
                }
                if (startTime >= endTime) {
                    showToast('Error', 'End time must be after start time.', 'error');
                    return; // Prevent submission
                }
                // NOTE: Add server-side validation in includes/edit_items.php
                // to prevent rescheduling a team that already has a schedule on the selected date.
            }


            $.ajax({
                url: 'includes/edit_items.php', // Updated path for correct endpoint
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    console.log('DEBUG: Edit response received:', response);
                    if (response.success) {
                        showToast('Success', 'Item updated successfully', 'success');
                        $('#editModal').modal('hide');

                        // AJAX live update - refresh content without page reload
                        if (table === 'users') {
                            // For users tab, use the existing reloadCurrentView function
                            if (typeof window.reloadCurrentView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'programs') {
                            // For programs tab, use the programs-specific reload function
                            if (typeof window.reloadProgramsView === 'function') {
                                setTimeout(function () {
                                    window.reloadProgramsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'teams') {
                            // For teams tab, use the teams-specific reload function
                            if (typeof window.reloadCurrentTeamsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentTeamsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'research_titles') {
                            // For research titles tab, use the research titles-specific reload function
                            if (typeof window.reloadCurrentResearchTitlesView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentResearchTitlesView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'requirements') {
                            // For requirements tab, use the requirements-specific reload function
                            if (typeof window.reloadCurrentRequirementsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentRequirementsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'rubrics') {
                            // For rubrics tab, use the rubrics-specific reload function
                            if (typeof window.reloadCurrentRubricsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentRubricsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'defense_schedules') {
                            // For defense schedules tab, use the defense schedules-specific reload function
                            if (typeof window.reloadCurrentDefenseSchedulesView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentDefenseSchedulesView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'env_variables') {
                            // For environment variables tab, use the env variables-specific reload function
                            if (typeof window.reloadCurrentEnvVariablesView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentEnvVariablesView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else {
                            // For other tables, use their specific reload functions or fallback to page reload
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        showToast('Error', response.message || 'Update failed', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('DEBUG: AJAX error in edit form:', xhr.responseText);
                    showToast('Error', 'Unable to update item: ' + error, 'error');
                }
            }); // Close $.ajax call
        }); // Close $(document).on('submit', '#editForm', ...) handler

        // NEW: Connect the saveChanges button to trigger the edit form submission
        $(document).on('click', '#saveEdit', function () { // Changed ID to match button in modal
            console.log('DEBUG: Save changes button clicked, triggering edit form submission');
            $('#editForm').submit();
        });
        // EDIT END

        // ADD START
        // Add button functionality
        $(document).off('click.addBtn').on('click.addBtn', '.add-btn', function (e) {
            e.preventDefault();
            var table = $(this).data('table');
            console.log('Main app: Add button clicked for table:', table);

            // Special handling for rubrics
            if (table === 'rubrics') {
                console.log('Main app: Delegating rubric add to rubrics_tab.php handler');
                // Let the dedicated handler in rubrics_tab.php handle this
                return true; // Allow event to bubble to other handlers
            }

            // Special handling for thesis_topics
            if (table === 'thesis_topics') {
                console.log('Main app: Processing thesis topic add');
                // For thesis topics, we'll let the main handler show the add modal,
                // and the thesis_topics_tab.php handler will populate it
                var form = $('#addForm');
                form.empty();
                form.append('<input type="hidden" name="table" value="' + table + '">');

                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>'
                );

                $('#addModal').modal('show');
                return true;
            }



            // Standard handling for all other tables
            var form = $('#addForm');
            form.empty();
            form.append('<input type="hidden" name="table" value="' + table + '">');
            // user_schedules
            if (table === 'user_schedules') {
                form.append('<div class="mb-3">' +
                    '<label for="program" class="form-label">Program</label>' +
                    '<select class="form-select" id="program" name="program" required>' +
                    '<option value="">Select Program</option>' +
                    // Populate programs dynamically
                    <?php
                    try {
                        $stmt = $pdo->prepare("SELECT id, name, specialization FROM programs WHERE name IS NOT NULL ORDER BY name");
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "'<option value=\"" . htmlspecialchars($row['id']) . "\">" . htmlspecialchars($row['name']) . ($row['specialization'] ? " (" . htmlspecialchars($row['specialization']) . ")" : "") . "</option>' +";
                        }
                    } catch (PDOException $e) {
                        echo "'<option value=\"\">Error loading programs</option>' +";
                        error_log("Database error: " . $e->getMessage());
                    }
                    ?> '</select>' +
                    '</div>' +

                    '<div class="mb-3">' +
                    '<label for="class_name" class="form-label">Course Code</label>' +
                    '<input type="text" class="form-control" id="class_name" name=" class_name" required>' +
                    '</div>' +

                    '<div class="mb-3">' +
                    '<label for="year" class="form-label">Year</label>' +
                    '<input type="number" class="form-control" id="year" name="year" min="1" max="5" oninput="if(this.value > 5) this.value = 5; if(this.value < 1 && this.value !== \'\') this.value = 1;" required>' +
                    '</div>' +

                    '<div class="mb-3">' +
                    '<label for="section" class="form-label">Section</label>' +
                    '<input type="text" class="form-control" id="section" name="section" required>' +
                    '</div>' +

                    '<div class="mb-3">' +
                    '<label for="room" class="form-label">Room</label>' +
                    '<input type="text" class="form-control" id="room" name="room" required>' +
                    '</div>' +

                    '<div class="mb-3">' +
                    '<label for="user_id" class="form-label">Instructor</label>' +
                    '<select class="form-select" id="user_id" name="user_id" required>' +
                    '<option value="">Select Instructor</option>' +
                    <?php
                    try {
                        $stmt = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE usertype = 2 ORDER BY name");
                        $stmt->execute();
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "'<option value=\"" . htmlspecialchars($row['id']) . "\">" . htmlspecialchars($row['name']) . "</option>' +";
                        }
                    } catch (PDOException $e) {
                        echo "'<option value=\"\">Error loading instructors</option>' +";
                        error_log("Database error: " . $e->getMessage());
                    }
                    ?> '</select>' +
                    '</div>' +

                    '<div class="mb-3">' +
                    '<label for="day_of_week" class="form-label">Day of Week</label>' +
                    '<select class="form-select" id="day_of_week" name="day_of_week" required>' +
                    '<option value="">Select Day</option>' +
                    '<option value="Monday">Monday</option>' +
                    '<option value="Tuesday">Tuesday</option>' +
                    '<option value="Wednesday">Wednesday</option>' +
                    '<option value="Thursday">Thursday</option>' +
                    '<option value="Friday">Friday</option>' +
                    '<option value="Saturday">Saturday</option>' +
                    '</select>' +
                    '</div>' +

                    '<div class="mb-3">' +
                    '<label class="form-label">Start Time</label>' +
                    '<div class="d-flex align-items-center gap-2">' +
                    '<div class="text-center">' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="adjustTime(\'start\', \'hour\', -1)">▲</button><br>' +
                    '<span id="start_hour">07</span><br>' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="adjustTime(\'start\', \'hour\', 1)">▼</button>' +
                    '</div>' +
                    '<span>:</span>' +
                    '<div class="text-center">' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="adjustTime(\'start\', \'minute\', -30)">▲</button><br>' +
                    '<span id="start_minute">00</span><br>' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="adjustTime(\'start\', \'minute\', 30)">▼</button>' +
                    '</div>' +
                    '<div class="text-center">' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="toggleMeridian(\'start\')">⇅</button><br>' +
                    '<span id="start_meridian">AM</span><br>' +
                    '</div>' +
                    '<input type="hidden" id="start_time" name="start_time" value="07:00" required>' +
                    '</div>' +
                    '<small class="form-text text-muted">Time must be between 7:00 AM and 9:00 PM (00 or 30 minutes only).</small>' +
                    '</div>' +

                    '<div class="mb-3">' +
                    '<label class="form-label">End Time</label>' +
                    '<div class="d-flex align-items-center gap-2">' +
                    '<div class="text-center">' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="adjustTime(\'end\', \'hour\', -1)">▲</button><br>' +
                    '<span id="end_hour">07</span><br>' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="adjustTime(\'end\', \'hour\', 1)">▼</button>' +
                    '</div>' +
                    '<span>:</span>' +
                    '<div class="text-center">' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="adjustTime(\'end\', \'minute\', -30)">▲</button><br>' +
                    '<span id="end_minute">30</span><br>' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="adjustTime(\'end\', \'minute\', 30)">▼</button>' +
                    '</div>' +
                    '<div class="text-center">' +
                    '<button type="button" class="btn btn-light btn-sm" onclick="toggleMeridian(\'end\')">⇅</button><br>' +
                    '<span id="end_meridian">AM</span><br>' +
                    '</div>' +
                    '<input type="hidden" id="end_time" name="end_time" value="07:30" required>' +
                    '</div>' +
                    '<small class="form-text text-muted">Time must be between 7:30 AM and 9:00 PM (00 or 30 minutes only).</small>' +
                    '</div>'
                );
            } else if (table === 'programs') {
                var form = $('#addForm');
                form.empty();
                form.append('<input type="hidden" name="table" value="programs">');
                form.append(`
                  <div class="mb-3">
                    <label class="form-label">College</label>
                    <input class="form-control" name="college" id="college" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Department</label>
                    <input class="form-control" name="department" id="department">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Program Name</label>
                    <input class="form-control" name="name" id="name" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Specialization</label>
                    <input type="text" class="form-control" name="specialization" id="specialization">
                  </div>
                `);

                // Add real-time validation for programs form using ValidationUtils
                ValidationUtils.setupRealTimeValidation('#addForm');
                $('#addModal').modal('show');
            } else if (table === 'users') {
                form.append('<div class="mb-3">' +
                    '<label for="username" class="form-label">Username</label>' +
                    '<input type="text" class="form-control" id="username" name="username" placeholder="20xx-2-xxxxx" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="email" class="form-label">Email</label>' +
                    '<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="password" class="form-label">Password</label>' +
                    '<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="first_name" class="form-label">First Name</label>' +
                    '<input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="last_name" class="form-label">Last Name</label>' +
                    '<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="program_id" class="form-label">Program</label>' +
                    '<select class="form-select" id="program_id" name="program_id">' +
                    // '<option value="">Loading programs...</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="mb-3 area-expertise-field" style="display:none;">' +
                    '<label for="area_of_expertise" class="form-label">Area of Expertise</label>' +
                    '<div class="input-group">' +
                    '<input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">' +
                    '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>' +
                    '<ul class="dropdown-menu">' +
                    '<li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>' +
                    '<li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>' +
                    '<li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>' +
                    '<li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>' +
                    '</ul>' +
                    '</div>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="usertype" class="form-label">User Type</label>' +
                    '<select class="form-select" id="usertype" name="usertype" required>' +
                    '<option value="" disabled selected>Select User Type</option>' +
                    '<option value="0">Admin</option>' +
                    '<option value="1">Student</option>' +
                    '<option value="2">Faculty</option>' +
                    '</select>' +
                    '</div>' +
                    '<div class="mb-3 is-part-time-field" style="display:none;">' +
                    '<label class="form-label">Is Part Time</label>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="radio" name="is_parttime" id="addFullTime" value="0" checked>' +
                    '<label class="form-check-label" for="addFullTime">Full Time</label>' +
                    '</div>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="radio" name="is_parttime" id="addPartTime" value="1">' +
                    '<label class="form-check-label" for="addPartTime">Part Time</label>' +
                    '</div>' +
                    '</div>' +
                    // Program Chair field, hidden by default
                    '<div class="mb-3 is-program-chair-field" style="display:none;">' +
                    '<label class="form-label">Program Chair</label>' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="checkbox" name="is_program_chair" id="program_chair" value="0">' +
                    '<label class="form-check-label" for="program_chair">Program Chair</label>' +
                    '</div>' +
                    '</div>'
                );

                // Add real-time validation for users form using ValidationUtils
                ValidationUtils.setupRealTimeValidation('#addForm');

                // Populate the programs dropdown
                populateProgramDropdown($('#program_id'));

                // Add event listener for usertype change in add form
                $('#addForm').on('change', '#usertype', function () {
                    if ($(this).val() == 2) {
                        $('.area-expertise-field').show();
                        $('.is-part-time-field').show();
                        $('.is-program-chair-field').hide();
                    } else if ($(this).val() == 0) {
                        $('.area-expertise-field').hide();
                        $('.is-part-time-field').hide();
                        $('.is-program-chair-field').show();
                    } else {
                        $('.area-expertise-field').hide();
                        $('.is-part-time-field').hide();
                        $('.is-program-chair-field').hide();
                    }
                });
            } else if (table === 'thesis_topics') {
                form.append('<div class="mb-3">' +
                    '<label for="topic" class="form-label">Topic</label>' +
                    '<input type="text" class="form-control" id="topic" name="topic" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="category" class="form-label">Category</label>' +
                    '<input type="text" class="form-control" id="category" name="category" required>' +
                    '</div>'
                );
            } else if (table === 'research_titles') {
                // Fetch teams data to populate the dropdown
                $.ajax({
                    url: 'includes/get_teams_and_staff.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        // Filter out teams that already have research titles
                        const availableTeams = data.teams.filter(team => !team.has_research_title);

                        var teamOptions = '';
                        if (availableTeams.length === 0) {
                            teamOptions = '<option value="" disabled>No teams available (all teams already have research titles)</option>';
                        } else {
                            teamOptions = availableTeams.map(team => `<option value="${team.id}">${team.name}</option>`).join('');
                        }

                        var formHtml = `
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team Name</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                    <option value="">Select Team</option>
                                    ${teamOptions}
                                </select>
                                ${availableTeams.length === 0 ? '<div class="form-text text-muted">All teams already have research titles assigned. You can manage existing titles in the table below.</div>' : ''}
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="approved" name="approved">
                                <label class="form-check-label" for="approved">Approved</label>
                            </div>
                        `;
                        $('#addForm').append(formHtml);

                        // Add real-time validation for research titles form using ValidationUtils
                        ValidationUtils.setupRealTimeValidation('#addForm');
                    },
                    error: function () {
                        showToast('Error', 'Unable to fetch teams data', 'error');
                        // Fallback to simple input field if AJAX fails
                        form.append('<div class="mb-3">' +
                            '<label for="team_id" class="form-label">Team ID</label>' +
                            '<input type="number" class="form-control" id="team_id" name="team_id" required>' +
                            '</div>' +
                            '<div class="mb-3">' +
                            '<label for="title" class="form-label">Title</label>' +
                            '<input type="text" class="form-control" id="title" name="title" required>' +
                            '</div>' +
                            '<div class="mb-3 form-check">' +
                            '<input type="checkbox" class="form-check-input" id="approved" name="approved">' +
                            '<label class="form-check-label" for="approved">Approved</label>' +
                            '</div>');

                        // Add real-time validation for research titles form using ValidationUtils (fallback)
                        ValidationUtils.setupRealTimeValidation('#addForm');
                    }
                });
            } else if (table === 'teams') {
                var formHtml = `
                    <div class="mb-3">
                    <label for="name" class="form-label">Team Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                    <label for="title" class="form-label">Research Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                    <label for="area_of_expertise" class="form-label">Area of Expertise</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="area_of_expertise" name="area_of_expertise" placeholder="Enter area of expertise">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Preset</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item area-option" href="#" data-value="Mobile Dev">Mobile Dev</a></li>
                            <li><a class="dropdown-item area-option" href="#" data-value="Hybrid Dev">Hybrid Dev</a></li>
                            <li><a class="dropdown-item area-option" href="#" data-value="Web Dev">Web Dev</a></li>
                            <li><a class="dropdown-item area-option" href="#" data-value="Software Engineering">Software Engineering</a></li>
                        </ul>
                    </div>
                    </div>
                    <div class="mb-3">
                    <label for="program_id" class="form-label">Program</label>
                    <select class="form-select" id="program_id" name="program_id">
                        <option value="">Loading programs...</option>
                    </select>
                    </div>
                    <h5 class="mt-4">Team Members</h5>
                    <div id="teamMembers">
                    <!-- Team members will be added here -->
                    </div>
                    <button type="button" class="btn btn-secondary mt-2" id="addTeamMember">Add Team Member</button>
                `;
                form.append(formHtml);

                // Add real-time validation for teams form using ValidationUtils
                ValidationUtils.setupRealTimeValidation('#addForm');

                // Populate the programs dropdown for the add form
                populateProgramDropdown($('#addForm #program_id'));

                // Add team member functionality
                $('#addTeamMember').on('click', function () {
                    console.log('Add Team Member button clicked');
                    addNewTeamMember();
                });
            } else if (table === 'env_variables') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Key</label>' +
                    '<input type="text" class="form-control" id="key" name="key" required>' +
                    '</div>' +
                    '<label for="name" class="form-label">Value</label>' +
                    '<input type="text" class="form-control" id="value" name="value" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="created_by" class="form-label">Description</label>' +
                    '<input type="text" class="form-control" id="Description" name="description" required>' +
                    '</div>');

                // Add real-time validation for env_variables form using ValidationUtils
                ValidationUtils.setupRealTimeValidation('#addForm');
            } else if (table === 'requirements') {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="description" class="form-label">Description</label>' +
                    '<textarea class="form-control" id="description" name="description" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="template_file" class="form-label">File Template</label>' +
                    '<input type="file" class="form-control" id="template_file" name="template_file" accept=".pdf,.doc,.docx,.txt,.xlsx,.pptx">' +
                    '<small class="form-text text-muted">Upload a file template (Optional). Allowed types: PDF, DOC, DOCX, TXT, XLSX, PPTX</small>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="due_date" class="form-label">Due Date</label>' +
                    '<input type="date" class="form-control" id="due_date" name="due_date" required>' +
                    '</div>');

                // Add real-time validation for requirements form using ValidationUtils
                ValidationUtils.setupRealTimeValidation('#addForm');
            } else if (table === 'evaluations') {
                form.append('<div class="mb-3">' +
                    '<label for="defense_schedule_id" class="form-label">Defense Schedule ID</label>' +
                    '<input type="number" class="form-control" id="defense_schedule_id" name="defense_schedule_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="evaluator_id" class="form-label">Evaluator ID</label>' +
                    '<input type="number" class="form-control" id="evaluator_id" name="evaluator_id" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="total_score" class="form-label">Total Score</label>' +
                    '<input type="number" step="0.01" class="form-control" id="total_score" name="total_score" required>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="comments" class="form-label">Comments</label>' +
                    '<textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>' +
                    '</div>' +
                    '<div class="mb-3">' +
                    '<label for="recommendation" class="form-label">Recommendation</label>' +
                    '<input type="text" class="form-control" id="recommendation" name="recommendation" required>' +
                    '</div>');
            } else if (table === 'defense_schedules') {
                $.ajax({
                    url: 'includes/get_teams_and_staff.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        // Store initial staff data for fallback or initial population
                        window.staffData = data.staff;

                        var formHtml = `
                <input type="hidden" name="table" value="${table}">
                <div class="mb-3">
                    <label for="schedule_date" class="form-label">Schedule Date</label>
                    <input type="text" class="form-control datepicker" id="schedule_date" name="schedule_date" required>
                    <small class="form-text text-muted">Select date for the defense schedule.</small>
                </div>
                <div class="mb-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" min="07:00" max="20:30" step="1800" required>
                    <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                </div>
                <div class="mb-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" min="07:00" max="20:30" step="1800" required>
                    <small class="form-text text-muted">Time must be within working hours (7:00 AM to 8:30 PM).</small>
                </div>
                <div class="mb-3">
                    <label for="room" class="form-label">Room</label>
                    <input type="text" class="form-control" id="room" name="room" required>
                </div>
                <div class="mb-3">
                    <label for="team_id" class="form-label">Team</label>
                    <select class="form-select" id="team_id" name="team_id" required>
                    <option value="">Select Team</option>
                        ${data.teams.map(team => `
                            <option value="${team.id}" ${team.has_schedule ? 'disabled' : ''}>
                                ${team.name} ${team.has_schedule ? '(Already Scheduled)' : ''}
                            </option>
                        `).join('')}
                    </select>
                </div>
                <h5 class="mt-4">Panelists (Max 3)</h5>
                <div id="panelists">
                    <div class="mb-3 row panelist">
                    <label class="col-sm-2 col-form-label">Panelist 1</label>
                        <div class="col-sm-8">
                            <select class="form-select" name="panelist_id[0]">
                                </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mt-2" id="addPanelist">Add Panelist</button>
            `;

                        form.html(formHtml);

                        // Add real-time validation for defense schedules add form using ValidationUtils
                        ValidationUtils.setupRealTimeValidation('#addForm');

                        // Function to update all panelist dropdowns with new data
                        function updatePanelistDropdowns(staffList) {
                            const optionsHtml = staffList.map(staff => `<option value="${staff.id}">${staff.name}</option>`).join('');
                            $('#panelists .panelist select').each(function () {
                                $(this).html(optionsHtml);
                            });
                        }

                        // --- New Code for Dynamic Staff Loading ---
                        // Event listener for the team dropdown
                        $('#team_id').on('change', function () {
                            const teamId = $(this).val();
                            if (teamId) {
                                $.ajax({
                                    url: 'includes/get_teams_and_staff.php',
                                    method: 'GET',
                                    dataType: 'json',
                                    data: {
                                        team_id: teamId
                                    },
                                    success: function (staffData) {
                                        if (staffData.success) {
                                            window.staffData = staffData.staff; // Update the global staff data
                                            updatePanelistDropdowns(staffData.staff);
                                        } else {
                                            showToast('Error', 'Unable to fetch panelists for this team.', 'error');
                                        }
                                    },
                                    error: function () {
                                        showToast('Error', 'An error occurred while fetching panelists.', 'error');
                                    }
                                });
                            } else {
                                // If no team is selected, clear the panelist dropdowns
                                $('#panelists .panelist select').html('');
                            }
                        });

                        // Initialize the date picker
                        $('.datepicker').datepicker({
                            format: 'yyyy-mm-dd',
                            multidate: false,
                            startDate: new Date(),
                            todayHighlight: true,
                            autoclose: true
                        });

                        // Add time picker validation
                        $('#start_time, #end_time').on('change', function () {
                            const startTime = $('#start_time').val();
                            const endTime = $('#end_time').val();

                            if (startTime && endTime && startTime >= endTime) {
                                showToast('Error', 'End time must be after start time', 'error');
                                $(this).val('');
                            }
                        });

                        // Add panelist functionality
                        $('#addPanelist').on('click', function () {
                            addNewPanelist(window.staffData);
                        });

                        // Remove panelist functionality
                        $(document).on('click', '.remove-panelist', function () {
                            $(this).closest('.panelist').remove();
                            if ($('#panelists .panelist').length < 3) {
                                $('#addPanelist').prop('disabled', false);
                            }
                            $('#panelists .panelist').each(function (index) {
                                $(this).find('select').attr('name', `panelist_id[${index}]`);
                                $(this).find('label.col-form-label').text(`Panelist ${index + 1}`);
                            });
                            updatePanelistDropdowns(window.staffData);
                        });

                    },
                    error: function () {
                        showToast('Error', 'Unable to fetch teams and staff data', 'error');
                    }
                });
            } else {
                form.append('<div class="mb-3">' +
                    '<label for="name" class="form-label">Name</label>' +
                    '<input type="text" class="form-control" id="name" name="name" required>' +
                    '</div>');
            }

            // Show the modal for tables other than rubrics
            $('#addModal').modal('show');
        });

        // Add form submission handler
        $(document).off('click.addItem').on('click.addItem', '#addItem', function (e) {
            e.preventDefault();
            console.log('Add item button clicked');

            var form = $('#addForm')[0]; // get raw DOM element

            // Get table type to determine validation
            var $form = $(form);
            var table = $form.find('input[name="table"]').val();

            // Apply comprehensive validation for users table
            if (table === 'users') {
                const validationErrors = ValidationUtils.validateUserForm(form);
                if (Object.keys(validationErrors).length > 0) {
                    ValidationUtils.displayErrors(validationErrors);
                    showToast('Error', 'Please fix the validation errors before submitting', 'error');
                    return;
                }
            } else if (table === 'teams') {
                const validationErrors = ValidationUtils.validateTeamsForm(form);
                if (Object.keys(validationErrors).length > 0) {
                    ValidationUtils.displayErrors(validationErrors);
                    showToast('Error', 'Please fix the validation errors before submitting', 'error');
                    return;
                }
            } else if (table === 'programs') {
                const validationErrors = ValidationUtils.validateProgramsForm(form);
                if (Object.keys(validationErrors).length > 0) {
                    ValidationUtils.displayErrors(validationErrors);
                    showToast('Error', 'Please fix the validation errors before submitting', 'error');
                    return;
                }
            } else if (table === 'requirements') {
                const validationErrors = ValidationUtils.validateRequirementsForm(form);
                if (Object.keys(validationErrors).length > 0) {
                    ValidationUtils.displayErrors(validationErrors);
                    showToast('Error', 'Please fix the validation errors before submitting', 'error');
                    return;
                }
            }

            // 🔹 Check native HTML5 validation first
            if (!form.checkValidity()) {
                form.reportValidity(); // show browser validation messages
                return; // stop here if invalid
            }

            var formData = new FormData(form); // form is already DOM node

            // Your custom defense schedule validation
            if (table === 'defense_schedules') {
                const startTime = formData.get('start_time');
                const endTime = formData.get('end_time');
                const minTime = '07:00';
                const maxTime = '20:30';

                if (startTime < minTime || startTime > maxTime) {
                    showToast('Error', 'Start time must be between 7:00 AM and 8:30 PM.', 'error');
                    return;
                }
                if (endTime < minTime || endTime > maxTime) {
                    showToast('Error', 'End time must be between 7:00 AM and 8:30 PM.', 'error');
                    return;
                }
                if (startTime >= endTime) {
                    showToast('Error', 'End time must be after start time.', 'error');
                    return;
                }
            }

            $.ajax({
                url: 'includes/add_items.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showToast('Success', 'Added successfully', 'success');
                        $('#addModal').modal('hide');

                        // AJAX live update - refresh content without page reload
                        var table = $('#addForm input[name="table"]').val();
                        if (table === 'users') {
                            // For users tab, use the existing reloadCurrentView function
                            if (typeof window.reloadCurrentView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'programs') {
                            // For programs tab, use the programs-specific reload function
                            if (typeof window.reloadProgramsView === 'function') {
                                setTimeout(function () {
                                    window.reloadProgramsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'teams') {
                            // For teams tab, use the teams-specific reload function
                            if (typeof window.reloadCurrentTeamsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentTeamsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'research_titles') {
                            // For research titles tab, use the research titles-specific reload function
                            if (typeof window.reloadCurrentResearchTitlesView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentResearchTitlesView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'requirements') {
                            // For requirements tab, use the requirements-specific reload function
                            if (typeof window.reloadCurrentRequirementsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentRequirementsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'rubrics') {
                            // For rubrics tab, use the rubrics-specific reload function
                            if (typeof window.reloadCurrentRubricsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentRubricsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'defense_schedules') {
                            // For defense schedules tab, use the defense schedules-specific reload function
                            if (typeof window.reloadCurrentDefenseSchedulesView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentDefenseSchedulesView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'env_variables') {
                            // For environment variables tab, use the env variables-specific reload function
                            if (typeof window.reloadCurrentEnvVariablesView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentEnvVariablesView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else {
                            // For other tables, use their specific reload functions or fallback to page reload
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        showToast('Error', response.message || 'An unknown error occurred', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    let errorMessage = 'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            });
        });
        // Close $(document).on('click', '#addItem', ...) handler
        // ADD END

        // DELETE START
        // Delete button functionality
        $(document).off('click.deleteBtn').on('click.deleteBtn', '.delete-btn', function (e) {
            e.preventDefault();
            var table = $(this).data('table');
            var id = $(this).data('id');

            console.log('Delete button clicked. Table:', table, 'ID:', id);

            // Populate the modal
            $('#deleteTableName').text(table);
            $('#deleteItemId').text(id);

            // Store data on the confirm button for later use
            $('#confirmDelete').data('table', table);
            $('#confirmDelete').data('id', id);

            // Show the modal
            $('#deleteConfirmModal').modal('show');
        });

        // Confirm Delete button functionality
        $(document).off('click.confirmDelete').on('click.confirmDelete', '#confirmDelete', function () {
            var table = $(this).data('table');
            var id = $(this).data('id');

            console.log('Confirm delete clicked. Table:', table, 'ID:', id);

            $.ajax({
                url: 'includes/delete_item.php', // Ensure you have this endpoint
                method: 'POST',
                data: {
                    table: table,
                    id: id
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showToast('Success', 'Item deleted successfully', 'success');
                        $('#deleteConfirmModal').modal('hide');

                        // AJAX live update - refresh content without page reload
                        if (table === 'users') {
                            // For users tab, use the existing reloadCurrentView function
                            if (typeof window.reloadCurrentView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'programs') {
                            // For programs tab, use the programs-specific reload function
                            if (typeof window.reloadProgramsView === 'function') {
                                setTimeout(function () {
                                    window.reloadProgramsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'teams') {
                            // For teams tab, use the teams-specific reload function
                            if (typeof window.reloadCurrentTeamsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentTeamsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'research_titles') {
                            // For research titles tab, use the research titles-specific reload function
                            if (typeof window.reloadCurrentResearchTitlesView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentResearchTitlesView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'requirements') {
                            // For requirements tab, use the requirements-specific reload function
                            if (typeof window.reloadCurrentRequirementsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentRequirementsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'rubrics') {
                            // For rubrics tab, use the rubrics-specific reload function
                            if (typeof window.reloadCurrentRubricsView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentRubricsView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else if (table === 'defense_schedules') {
                            // For defense schedules tab, use the defense schedules-specific reload function
                            if (typeof window.reloadCurrentDefenseSchedulesView === 'function') {
                                setTimeout(function () {
                                    window.reloadCurrentDefenseSchedulesView(1);
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    location.reload();
                                }, 1000);
                            }
                        } else {
                            // For other tables, use their specific reload functions or fallback to page reload
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    } else {
                        showToast('Error', response.message || 'Deletion failed', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showToast('Error', 'Unable to delete item: ' + error, 'error');
                }
            });
        });
        // DELETE END

        // BULK ADD STUDENT START
        // NEW: Add bulk row functionality
        $(document).off('click.addBulkRow').on('click.addBulkRow', '#addBulkRow', function () {
            let count = parseInt($('#rowCountInput').val()) || 1;
            for (let i = 0; i < count; i++) {
                var rowCount = $('#bulkAddTable tbody tr').length;
                var newRow = `
                  <tr>
                    <td><input type="text" class="form-control" name="users[${rowCount}][id]"></td>
                    <td><input type="text" class="form-control" name="users[${rowCount}][name]"></td>
                    <td><input type="text" class="form-control" name="users[${rowCount}][program]"></td>
                    <td class="text-center">
                      <input type="checkbox" name="users[${rowCount}][no_username]">
                    </td>
                  </tr>
                `;
                $('#bulkAddTable tbody').append(newRow);
            }
        });

        // When the Bulk Add Modal is shown, insert the CSV download link if not already present
        $('#bulkAddModal').on('shown.bs.modal', function () {
            if (!$(this).find('#downloadCsvTemplate').length) {
                $(this).find('.modal-body').prepend(`
                    <div class="mb-3">
                        <a href="#" id="downloadCsvTemplate" class="btn btn-sm btn-secondary">Download CSV Template</a>
                    </div>
                `);
            }
        });

        // Update Bulk Add Users CSV download handler
        $(document).off('click.downloadCsvTemplate').on('click.downloadCsvTemplate', '#downloadCsvTemplate',
            function (e) {
                e.preventDefault();
                const csvContent = 'ID,Name,Program,No Username\\n';
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'users_template.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });

        // NEW: Bulk Add Students functionality
        $(document).off('click.bulkAddBtn').on('click.bulkAddBtn', '.bulk-add-btn', function (e) {
            e.preventDefault();
            console.log('Bulk Add User button clicked');
            $('#bulkAddModal').modal('show');
        });

        // Handle Bulk Add Form Submission
        $(document).off('submit.bulkAddForm').on('submit.bulkAddForm', '#bulkAddForm', function (e) {
            e.preventDefault();

            let formData = new FormData(this); // Grab all form inputs

            $.ajax({
                url: 'includes/bulk_add_users.php', // Path to backend PHP
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                beforeSend: function () {
                    $('#bulkAddStatus').html('<div class="alert alert-info">Processing...</div>');
                },
                success: function (response) {
                    if (response.success) {
                        $('#bulkAddStatus').html(`
                        <div class="alert alert-success">
                            ${response.message}<br>
                            Processed: ${response.processed_count} | Inserted: ${response.inserted_count}
                        </div>
                    `);
                        $('#bulkAddModal').modal('hide');
                        location.reload(); // Reload to reflect changes
                    } else {
                        $('#bulkAddStatus').html(`
                        <div class="alert alert-danger">
                            ${response.message}
                        </div>
                    `);
                    }
                },
                error: function (xhr, status, error) {
                    $('#bulkAddStatus').html(`
                    <div class="alert alert-danger">
                        🚨 An error occurred: ${error}
                    </div>
                `);
                }
            });
        });

        // BULK ADD STUDENT END

        // NEW: Bulk Add Teams functionality


        // BULK ADD TEAMS START
        $(document).off('click.addBulkTeamsRow').on('click.addBulkTeamsRow', '#addBulkTeamsRow', function () {
            let count = parseInt($('#teamsRowCountInput').val()) || 1;
            for (let i = 0; i < count; i++) {
                var rowCount = $('#bulkAddTeamsTable tbody tr').length;
                var newRow = `
                  <tr>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][name]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][title]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][area_of_expertise]"></td>
                    <td><input type="text" class="form-control" name="teams[${rowCount}][program]"></td>
                  </tr>
                `;
                $('#bulkAddTeamsTable tbody').append(newRow);
            }
        });

        $(document).off('submit.bulkAddTeamsForm').on('submit.bulkAddTeamsForm', '#bulkAddTeamsForm', function (e) {
            e.preventDefault();
            console.log('Bulk Add Teams form submitted');

            var form = $('#bulkAddTeamsForm');
            var formData = new FormData(form[0]);

            // If pasted bulk text is provided, append it
            var bulkText = $('#bulkTeamsTextInput').val().trim();
            if (bulkText !== "") {
                formData.append('bulk_teams', bulkText);
            }

            $.ajax({
                url: 'includes/bulk_add_teams.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        showToast('Success', 'Teams added successfully', 'success');
                        $('#bulkAddTeamsModal').modal('hide');
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        // More descriptive error message
                        showToast('Error', response.message ||
                            'Failed to add teams. Please check your data and try again.',
                            'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    // User-friendly error message
                    let errorMessage =
                        'Unable to process your request. Please try again later.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                    showToast('Error', errorMessage, 'error');
                }
            });
        });

        $(document).off('click.bulkAddTeamsBtn').on('click.bulkAddTeamsBtn', '.bulk-add-teams-btn', function (e) {
            e.preventDefault();
            console.log('Bulk Add Teams button clicked');
            $('#bulkAddTeamsModal').modal('show');
        });

        // BULK ADD TEAMS END

        // Event handler for preset buttons in area of expertise fields
        $(document).on('click', '.area-option', function (e) {
            e.preventDefault();
            var presetValue = $(this).data('value');
            $(this).closest('.input-group').find('input[name="area_of_expertise"]').val(presetValue);
        });

        // Event handler for preset buttons in program fields
        $(document).on('click', '.program-option', function (e) {
            e.preventDefault();
            var presetValue = $(this).data('value');
            $(this).closest('.input-group').find('input[name="program"]').val(presetValue);
        });

        const sidebarContainer = $('#sidebarContainer');
        const mainContent = $('#mainContent');
        const toggleButton = $('#toggleSidebar');

        toggleButton.on('click', function () {
            sidebarContainer.toggleClass('collapsed');
            mainContent.toggleClass('expanded');
            toggleButton.toggleClass('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebarContainer.hasClass('collapsed'));
        });

        // Check localStorage for saved sidebar state on page load
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            sidebarContainer.addClass('collapsed');
            mainContent.addClass('expanded');
            toggleButton.addClass('collapsed');
        }

        // Handle window resize
        $(window).on('resize', function () {
            if (window.innerWidth <= 768) {
                mainContent.addClass('expanded');
            } else {
                if (!sidebarContainer.hasClass('collapsed')) {
                    mainContent.removeClass('expanded');
                }
            }
        });
    });

    function updatePanelistDropdowns() {
        // Collect all selected panelist IDs from the current modal context
        var $modal = $('.modal.show'); // Target only the currently visible modal
        var $panelistContainer = $modal.find('#panelists');
        if (!$panelistContainer.length) return; // Exit if no panelist container found

        var selectedIds = [];
        $panelistContainer.find('.panelist select').each(function () {
            var val = $(this).val();
            if (val) selectedIds.push(val);
        });

        $panelistContainer.find('.panelist select').each(function () {
            var $select = $(this);
            var currentVal = $select.val();
            $select.find('option').each(function () {
                var $opt = $(this);
                if ($opt.val() && $opt.val() !== currentVal && selectedIds.includes($opt.val())) {
                    $opt.prop('disabled', true);
                } else {
                    $opt.prop('disabled', false);
                }
            });
        });
    }


    function addNewPanelist(staff) {
        var $modal = $('.modal.show'); // Target the currently visible modal
        var $panelistContainer = $modal.find('#panelists');
        var $addPanelistButton = $modal.find('#addPanelist');

        // Check current number of panelists within the specific modal
        var currentPanelistCount = $panelistContainer.find('.panelist').length;
        if (currentPanelistCount >= 3) {
            showToast('Warning', 'Maximum of 3 panelists allowed.', 'warning');
            return; // Stop if limit is reached
        }

        // Determine the next index based on existing panelists in this modal
        var currentIndices = $panelistContainer.find('.panelist select').map(function () {
            var name = $(this).attr('name');
            var match = name.match(/\[(\d+)\]/);
            return match ? parseInt(match[1], 10) : -1;
        }).get();
        var nextIndex = currentIndices.length > 0 ? Math.max(...currentIndices) + 1 : 0;

        var newPanelistHtml = `
        <div class="mb-3 row panelist">
            <label class="col-sm-2 col-form-label">Panelist ${nextIndex + 1}</label>
            <div class="col-sm-8">
                <select class="form-select" name="panelist_id[${nextIndex}]">
                    ${staff.map(member => `<option value="${member.id}">${member.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-sm remove-panelist">Remove</button>
            </div>
        </div>
`;

        $panelistContainer.append(newPanelistHtml);
        console.log('New panelist added to DOM with name:', `panelist_id[${nextIndex}]`);

        // Disable button if limit is now reached
        if ($panelistContainer.find('.panelist').length >= 3) {
            $addPanelistButton.prop('disabled', true);
        }
        // Update dropdowns to enforce unique selection
        updatePanelistDropdowns();

        // Add change listener to the new dropdown
        $panelistContainer.find(`select[name="panelist_id[${nextIndex}]"]`).on('change', updatePanelistDropdowns);
    }


    // Optionally, update existing panelist entries to have unique indices on modal show
    $('#editModal, #addModal').on('shown.bs.modal', function () {
        var $panelistContainer = $(this).find('#panelists');
        if ($panelistContainer.length) {
            $panelistContainer.find('.panelist').each(function (index) {
                $(this).find('select').attr('name', `panelist_id[${index}]`);
                $(this).find('label.col-form-label').text(`Panelist ${index + 1}`);
            });
            // Add change listener to all panelist dropdowns within this modal
            $panelistContainer.find('select').off('change.updateDropdowns').on('change.updateDropdowns',
                updatePanelistDropdowns);
            updatePanelistDropdowns(); // Initial update on show
        }
    });


    // Define addNewTeamMember function globally
    function addNewTeamMember() {
        // Check total team member count (including existing ones)
        var $modal = $('.modal.show');
        var currentMemberCount = $modal.find('#teamMembers .team-member').length;

        if (currentMemberCount >= 6) {
            showToast('Warning', 'Maximum of 6 team members allowed.', 'warning');
            return;
        }

        // Get the current team ID if editing (for excluding current members)
        var teamId = $modal.find('input[name="id"]').val() || 0;

        $.ajax({
            url: 'includes/get_available_users.php?team_id=' + teamId,
            method: 'GET',
            dataType: 'json',
            success: function (users) {
                var $modal = $('.modal.show'); // Target the current modal
                var $teamMembersContainer = $modal.find('#teamMembers');

                // Check current role counts
                var roleCount = getRoleCount($teamMembersContainer);

                // Determine available roles
                var availableRoles = getAvailableRoles(roleCount);

                if (availableRoles.length === 0) {
                    showToast('Warning', 'All required roles are filled. You can only add more members (max 4 total).', 'warning');
                    return;
                }

                var newMemberHtml = `
                <div class="mb-3 row team-member">
                    <div class="col-sm-5">
                        <select class="form-select role-select" name="new_role[]" onchange="filterUsersByRole(this)">
                            ${generateRoleOptions(availableRoles)}
                        </select>
                    </div>
                    <div class="col-sm-5">
                        <select class="form-select user-select" name="new_user_id[]" style="display:block;">
                            <option value="">Select a user</option>
                            ${users.map(user => {
                    // Add usertype indicator and filter logic
                    let userTypeLabel = '';
                    let isAppropriateForRole = true;

                    if (user.usertype == 0) {
                        userTypeLabel = ' (Program Chair)';
                    } else if (user.usertype == 1) {
                        userTypeLabel = ' (Student)';
                    } else if (user.usertype == 2) {
                        userTypeLabel = ' (Staff/Professor)';
                    } else {
                        userTypeLabel = ' (Other)';
                        isAppropriateForRole = false;
                    }

                    // Only show appropriate users
                    if (isAppropriateForRole) {
                        return `<option value="${user.id}" data-usertype="${user.usertype}">${user.first_name} ${user.last_name}${userTypeLabel}</option>`;
                    }
                    return '';
                }).filter(option => option !== '').join('')}
                        </select>
                        <input type="text" class="form-control new-username-input" name="new_username[]" placeholder="Enter username" style="display:none;">
                        <a href="#" class="toggle-input">Switch to manual</a>
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                    </div>
                </div>
                `;
                $teamMembersContainer.append(newMemberHtml);

                // Add event listeners
                var $newMember = $teamMembersContainer.find('.team-member').last();

                $newMember.find('.toggle-input').on('click', function (e) {
                    e.preventDefault();
                    var $select = $(this).siblings('.user-select');
                    var $input = $(this).siblings('.new-username-input');
                    if ($select.is(':visible')) {
                        $select.hide().prop('disabled', true); // Disable select when hidden
                        $input.show().prop('disabled', false); // Enable input when shown
                        $(this).text('Switch to select');
                    } else {
                        $input.hide().prop('disabled', true); // Disable input when hidden
                        $select.show().prop('disabled', false); // Enable select when shown
                        $(this).text('Switch to manual');
                    }
                });

                // Initially disable the input field
                $newMember.find('.new-username-input').prop('disabled', true);

                // Add change listeners for role and user selection
                $newMember.find('.role-select').on('change', function () {
                    updateUserDropdownForRole($(this));
                    updateTeamMemberDropdowns();
                });

                $newMember.find('.user-select').on('change', function () {
                    updateTeamMemberDropdowns();
                });

                // Initial setup for the new member
                updateUserDropdownForRole($newMember.find('.role-select'));
                updateTeamMemberDropdowns();

                // Check if we should disable the add button
                if ($teamMembersContainer.find('.team-member').length >= 6) {
                    $modal.find('#addTeamMember').prop('disabled', true);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                showToast('Error', 'Error loading users', 'error');
            }
        });
    }

    // Helper function to count current roles
    function getRoleCount($container) {
        var count = {
            adviser: 0,
            leader: 0,
            member: 0
        };

        $container.find('.team-member').each(function () {
            var role = $(this).find('select[name*="role"]').val() || $(this).find('select[name*="member_role"]').val();
            if (role && count.hasOwnProperty(role)) {
                count[role]++;
            }
        });

        return count;
    }

    // Helper function to determine available roles
    function getAvailableRoles(roleCount) {
        var available = [];

        if (roleCount.adviser < 1) {
            available.push('adviser');
        }
        if (roleCount.leader < 1) {
            available.push('leader');
        }
        if (roleCount.member < 4) {
            available.push('member');
        }

        return available;
    }

    // Helper function to generate role options
    function generateRoleOptions(availableRoles) {
        var options = '<option value="">Select Role</option>';

        // Order: adviser first, leader second, member last
        if (availableRoles.includes('adviser')) {
            options += '<option value="adviser">Adviser/Professor</option>';
        }
        if (availableRoles.includes('leader')) {
            options += '<option value="leader">Leader</option>';
        }
        if (availableRoles.includes('member')) {
            options += '<option value="member">Member</option>';
        }

        return options;
    }

    // Helper function to filter users based on selected role
    function filterUsersByRole(roleSelect) {
        const selectedRole = roleSelect.value;
        const userSelect = $(roleSelect).closest('.team-member').find('.user-select');

        // Show/hide options based on role requirements
        userSelect.find('option').each(function () {
            const option = $(this);
            const usertype = option.data('usertype');
            let shouldShow = true;

            if (selectedRole === 'adviser') {
                // For adviser role, only show staff (2) and program chairs (0)
                shouldShow = usertype == 2 || usertype == 0;
            } else if (selectedRole === 'leader' || selectedRole === 'member') {
                // For leader/member roles, primarily show students (1), but allow others too
                shouldShow = true; // Allow all for flexibility
            }

            if (shouldShow) {
                option.show();
            } else {
                option.hide();
                // If this option was selected and now hidden, deselect it
                if (option.is(':selected')) {
                    option.prop('selected', false);
                    userSelect.val('');
                }
            }
        });
    }

    // Helper function to update user dropdown based on selected role
    function updateUserDropdownForRole($roleSelect) {
        var role = $roleSelect.val();
        var $userSelect = $roleSelect.closest('.team-member').find('.user-select');
        var $options = $userSelect.find('option');

        $options.each(function () {
            var $option = $(this);
            var usertype = $option.data('usertype');

            if ($option.val() === '') {
                // Keep the empty option
                $option.show();
                return;
            }

            if (role === 'adviser') {
                // Only show faculty (usertype 2) and admin (usertype 0) for adviser role
                if (usertype == 2 || usertype == 0) {
                    $option.show();
                } else {
                    $option.hide();
                }
            } else if (role === 'leader') {
                // Show only usertype 1 (student) for leader role
                if (usertype == 1) {
                    $option.show();
                } else {
                    $option.hide();
                }
            } else if (role === 'member') {
                // Show only usertype 1 (student) for member role
                if (usertype == 1) {
                    $option.show();
                } else {
                    $option.hide();
                }
            } else {
                // Hide all options if no valid role is selected
                $option.hide();
            }
        });

        // Clear selection if current selection is now hidden
        if ($userSelect.val() && $userSelect.find('option:selected').is(':hidden')) {
            $userSelect.val('');
        }
    }

    // Helper function to update all team member dropdowns to prevent duplicates
    function updateTeamMemberDropdowns() {
        var $modal = $('.modal.show');
        var $container = $modal.find('#teamMembers');
        var selectedUserIds = [];

        // Collect all selected user IDs
        $container.find('.team-member').each(function () {
            var userId = $(this).find('.user-select').val();
            if (userId) {
                selectedUserIds.push(userId);
            }
        });

        // Update each dropdown
        $container.find('.team-member').each(function () {
            var $currentSelect = $(this).find('.user-select');
            var currentValue = $currentSelect.val();

            $currentSelect.find('option').each(function () {
                var $option = $(this);
                var optionValue = $option.val();

                if (optionValue && optionValue !== currentValue && selectedUserIds.includes(optionValue)) {
                    $option.prop('disabled', true);
                } else {
                    $option.prop('disabled', false);
                }
            });
        });
    }

    // Remove team member functionality (works for both edit and add modals)
    $(document).on('click', '.remove-member', function () {
        var teamMember = $(this).closest('.team-member');
        var userId = teamMember.data('user-id'); // Only set for existing members in edit form
        var teamId = $('.modal.show').find('input[name="id"]').val(); // Get team ID from the current modal

        if (userId && teamId) {
            if (confirm('Are you sure you want to remove this team member from the team? This action cannot be undone.')) {
                $.ajax({
                    url: 'includes/remove_team_member.php',
                    method: 'POST',
                    data: {
                        user_id: userId,
                        team_id: teamId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            teamMember.remove();
                            showToast('Success', 'Team member removed successfully', 'success');
                            // Re-enable add button and update dropdowns
                            var $modal = $('.modal.show');
                            if ($modal.find('#teamMembers .team-member').length < 6) {
                                $modal.find('#addTeamMember').prop('disabled', false);
                            }
                            updateTeamMemberDropdowns();
                        } else {
                            showToast('Error', response.message || 'Failed to remove member', 'error');
                        }
                    },
                    error: function () {
                        showToast('Error', 'Unable to remove team member via AJAX', 'error');
                    }
                });
            }
        } else {
            // If it's a new member, just remove from form
            teamMember.remove();
            // Re-enable add button and update dropdowns
            var $modal = $('.modal.show');
            if ($modal.find('#teamMembers .team-member').length < 6) {
                $modal.find('#addTeamMember').prop('disabled', false);
            }
            updateTeamMemberDropdowns();
        }
    });


    // Helper function for showing toasts
    function showToast(title, message, type = 'success') {
        // Create toast container if it doesn't exist
        if (!$('#toastContainer').length) {
            $('body').append(`
    <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 9999">
    </div>
`);

        }

        // Generate unique ID for the toast
        const toastId = 'toast-' + Date.now();

        // Modern universal toast styling and structure
        const icon = type === 'success' ?
            `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#eaf0fe;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#1304ee"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>` :
            type === 'error' ?
                `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#fbeaea;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#dc3545"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>` :
                `<span style="display:inline-flex;align-items:center;justify-content:center;width:2.2rem;height:2.2rem;background:#fffbe6;border-radius:50%;margin-right:1rem;"><svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#ffc107"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/></svg></span>`;

        const bgColor = type === 'success' ? '#f6fffa' : (type === 'error' ? '#fff6f6' : '#fffbe6');
        const borderColor = type === 'success' ? '#1304ee' : (type === 'error' ? '#dc3545' : '#ffc107');
        const textColor = '#222';
        const toast = `
<div id="${toastId}" class="toast align-items-center border-0 shadow-lg"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
    style="min-width:320px;max-width:400px;opacity:1;background:${bgColor};border-left:5px solid ${borderColor};border-radius:12px;margin-bottom:1rem;box-shadow:0 4px 24px 0 rgba(0,0,0,0.10);">
    <div class="d-flex align-items-center" style="padding:1rem 1.25rem;">
        ${icon}
        <div class="toast-body p-0" style="font-size:1rem;color:${textColor};line-height:1.5;">
            <div style="font-weight:600;font-size:1.08rem;margin-bottom:2px;">${title}</div>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close" style="margin-left:1.5rem;"></button>
    </div>
</div>
`;

        // Add toast to container
        $('#toastContainer').append(toast);

        // Initialize and show the toast with modified options
        const toastElement = new bootstrap.Toast(document.getElementById(toastId), {
            autohide: true,
            delay: 3000,
            animation: true
        });
        toastElement.show();

        // Remove toast element after it's hidden
        $(`#${toastId}`).on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }
    // Logout confirmation modal trigger for dashboard and home sidebars
    $(document).ready(function () {
        // Dashboard sidebar logout
        $(document).on('click', '#dashboardLogoutBtn', function (e) {
            e.preventDefault();
            $('#logoutConfirmModal').modal('show');
        });
        // Home sidebar logout
        $(document).on('click', '#homeLogoutBtn', function (e) {
            e.preventDefault();
            $('#logoutConfirmModal').modal('show');
        });

        // Profile dropdown menu functionality
        $(document).on('click', '.profile-dropdown-menu .dropdown-item[href="../profile"]', function (e) {
            e.preventDefault();
            window.location.href = '../profile';
        });

        $(document).on('click', '.profile-dropdown-menu .dropdown-item[href="../profile-edit"]', function (e) {
            e.preventDefault();
            window.location.href = '../profile-edit';
        });

        // dropdown closes properly after clicking links
        $(document).on('click', '.profile-dropdown-menu .dropdown-item', function () {
            $('.profile-dropdown-container').removeClass('show');
            $('.profile-dropdown-menu').removeClass('show');
        });
    });
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0" style="display: flex; justify-content: flex-end;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
                    </svg>
                </div>
                <h4 class="fw-bold mb-3" id="deleteConfirmModalLabel">Confirm Deletion</h4>
                <p>Are you sure you want to delete this item?</p>
                <p class="mb-0"><strong>Table:</strong> <span id="deleteTableName"></span></p>
                <p class="mb-0"><strong>ID:</strong> <span id="deleteItemId"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>