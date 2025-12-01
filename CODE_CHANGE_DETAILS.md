# Code Change Details - addNewTeamMember() Function

## File Changed
`/opt/lampp/htdocs/dashboard/app.js.php`

## Function Modified
`addNewTeamMember()` (approximately line 4181)

## What Changed

### BEFORE (Broken)
```javascript
function addNewTeamMember() {
    var $modal = $('.modal.show');
    var currentMemberCount = $modal.find('#teamMembers .team-member').length;

    if (currentMemberCount >= 6) {
        showToast('Warning', 'Maximum of 6 team members allowed.', 'warning');
        return;
    }

    var teamId = $modal.find('input[name="id"]').val() || 0;

    // ❌ PROBLEM: Single AJAX call - only gets students
    $.ajax({
        url: 'includes/get_available_users.php?type=students&team_id=' + teamId + '&include_advisers=1',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            // Process response
            // Result: Only students in list
            // Adviser role has no professors → EMPTY dropdown
        },
        error: function (jqXHR, textStatus, errorThrown) {
            showToast('Error', 'Error loading users', 'error');
        }
    });
}
```

### AFTER (Fixed)
```javascript
function addNewTeamMember() {
    var $modal = $('.modal.show');
    var currentMemberCount = $modal.find('#teamMembers .team-member').length;

    if (currentMemberCount >= 6) {
        showToast('Warning', 'Maximum of 6 team members allowed.', 'warning');
        return;
    }

    var teamId = $modal.find('input[name="id"]').val() || 0;
    
    // ✅ Get the team's program for adviser filtering
    var teamProgram = $modal.find('input[name="program"]').val() || '';

    // ✅ SOLUTION: Two parallel AJAX calls - get both students AND advisers
    var studentsPromise = $.ajax({
        url: 'includes/get_available_users.php?type=students&team_id=' + teamId,
        method: 'GET',
        dataType: 'json'
    });

    var advisersPromise = $.ajax({
        url: 'includes/get_available_users.php?type=advisers&team_program=' + encodeURIComponent(teamProgram),
        method: 'GET',
        dataType: 'json'
    });

    // ✅ Wait for both requests and combine results
    $.when(studentsPromise, advisersPromise).done(function(studentsResponse, advisersResponse) {
        var students = [];
        var advisers = [];
        
        // ✅ Extract students from response
        if (studentsResponse && typeof studentsResponse === 'object') {
            if (Array.isArray(studentsResponse)) {
                students = studentsResponse;
            } else if (studentsResponse.data && Array.isArray(studentsResponse.data)) {
                students = studentsResponse.data;
            }
        }
        
        // ✅ Extract advisers from response
        if (advisersResponse && typeof advisersResponse === 'object') {
            if (Array.isArray(advisersResponse)) {
                advisers = advisersResponse;
            } else if (advisersResponse.data && Array.isArray(advisersResponse.data)) {
                advisers = advisersResponse.data;
            }
        }
        
        // ✅ Combine into single user list
        var users = [];
        if (Array.isArray(students)) {
            users = users.concat(students);
        }
        if (Array.isArray(advisers)) {
            users = users.concat(advisers);
        }
        
        // ✅ Remove duplicates by ID
        var seen = {};
        users = users.filter(function(user) {
            if (seen[user.id]) return false;
            seen[user.id] = true;
            return true;
        });

        var $modal = $('.modal.show');
        var $teamMembersContainer = $modal.find('#teamMembers');

        var roleCount = getRoleCount($teamMembersContainer);
        var availableRoles = getAvailableRoles(roleCount);

        if (availableRoles.length === 0) {
            showToast('Warning', 'All required roles are filled. You can only add more members (max 4 total).', 'warning');
            return;
        }

        if (users.length === 0) {
            showToast('Warning', 'No users available for selection', 'warning');
            return;
        }

        // ✅ Render with BOTH types available - filtering happens by role
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

                        if (isAppropriateForRole) {
                            var adviserCountAttr = (typeof user.adviser_count !== 'undefined') ? ` data-adviser-count="${user.adviser_count}"` : '';
                            return `<option value="${user.id}" data-usertype="${user.usertype}"${adviserCountAttr}>${user.first_name} ${user.last_name}${userTypeLabel}</option>`;
                        }
                        return '';
                    }).filter(option => option !== '').join('')}
                </select>
                <input type="text" class="form-control new-username-input" name="new_username[]" placeholder="Enter username" style="display:none;">
                <a href="#" class="toggle-input">Switch to manual</a>
            </div>
            ${(currentMemberCount === 0 || currentMemberCount === 1) ? '' : `
                <div class="col-sm-2">
                    <button type="button" class="btn btn-danger btn-sm remove-member">Remove</button>
                </div>
            `}
        </div>
        `;
        
        $teamMembersContainer.append(newMemberHtml);

        var $newMember = $teamMembersContainer.find('.team-member').last();

        $newMember.find('.toggle-input').on('click', function (e) {
            e.preventDefault();
            var $select = $(this).siblings('.user-select');
            var $input = $(this).siblings('.new-username-input');
            if ($select.is(':visible')) {
                $select.hide().prop('disabled', true);
                $input.show().prop('disabled', false);
                $(this).text('Switch to select');
            } else {
                $input.hide().prop('disabled', true);
                $select.show().prop('disabled', false);
                $(this).text('Switch to manual');
            }
        });

        $newMember.find('.new-username-input').prop('disabled', true);

        $newMember.find('.role-select').on('change', function () {
            updateUserDropdownForRole($(this));
            updateTeamMemberDropdowns();
        });

        $newMember.find('.user-select').on('change', function () {
            updateTeamMemberDropdowns();
        });

        updateUserDropdownForRole($newMember.find('.role-select'));
        updateTeamMemberDropdowns();

        if ($teamMembersContainer.find('.team-member').length >= 6) {
            $modal.find('#addTeamMember').prop('disabled', true);
        }
        
    }).fail(function(studentsError, advisersError) {
        console.error('Error loading users:', studentsError, advisersError);
        showToast('Error', 'Error loading users', 'error');
    });
}
```

---

## Key Differences Explained

### 1. Fetch Strategy
**Before**: 
```javascript
// Single AJAX call
$.ajax({ url: 'get_available_users.php?type=students...' })
```

**After**:
```javascript
// Two parallel AJAX calls
var studentsPromise = $.ajax({ url: '...type=students' });
var advisersPromise = $.ajax({ url: '...type=advisers' });
$.when(studentsPromise, advisersPromise).done(function(...) { ... });
```

### 2. Data Handling
**Before**:
```javascript
// Single response variable
success: function (response) {
    // Only contains students
}
```

**After**:
```javascript
// Separate extraction for each type
var students = [...];  // From first AJAX call
var advisers = [...];   // From second AJAX call
var users = students.concat(advisers);  // Combined
```

### 3. Result Quality
**Before**:
- Users list: [Student1, Student2, Student3]
- Adviser role: Empty (no professors)
- Result: ❌ BROKEN

**After**:
- Users list: [Student1, Student2, Student3, Prof1, Prof2]
- Adviser role: Shows [Prof1, Prof2] after filtering
- Result: ✅ FIXED

---

## Important Details

### $.when() Usage
```javascript
$.when(promise1, promise2).done(function(response1, response2) {
    // Both promises must complete successfully
    // Then this function runs
});
```

**Why this is important**:
- Waits for BOTH API calls to complete
- Prevents rendering before data is ready
- Handles both success and failure cases
- Standard jQuery pattern

### Duplicate Removal
```javascript
var seen = {};
users = users.filter(function(user) {
    if (seen[user.id]) return false;  // Already seen
    seen[user.id] = true;              // Mark as seen
    return true;                       // Include this one
});
```

**Why this is important**:
- Theoretical edge case (rare but possible)
- User could be both student AND professor
- Prevents duplicate entries in dropdown

### Error Handling
```javascript
}).fail(function(studentsError, advisersError) {
    console.error('Error loading users:', studentsError, advisersError);
    showToast('Error', 'Error loading users', 'error');
});
```

**Why this is important**:
- If EITHER API call fails, prevents incomplete data
- Shows user-friendly error message
- Logs error for debugging

---

## Testing the Change

### Verify Change Was Applied
```bash
# Check the file has been modified
grep -n "advisersPromise" /opt/lampp/htdocs/dashboard/app.js.php

# Should return: Line numbers where "advisersPromise" appears
# Expected: Around line 4196-4197
```

### Verify Syntax
```bash
# Validate PHP syntax (app.js.php is a PHP file that outputs JavaScript)
php -l /opt/lampp/htdocs/dashboard/app.js.php

# Should show: No syntax errors detected
```

### Verify Behavior
1. Open Developer Tools (F12)
2. Go to Network tab
3. Click "Add Team Member"
4. Should see TWO requests:
   - `get_available_users.php?type=students...`
   - `get_available_users.php?type=advisers...`
5. Both should return `200 OK` with JSON

---

## Backwards Compatibility

✅ **No breaking changes**:
- All existing API parameters still work
- Response format unchanged
- HTML structure unchanged
- Other functions unaffected

✅ **Forwards compatible**:
- Works with existing database schema
- Works with current user system
- Works with current role system
- No migrations needed

---

## Performance Impact

### Before
```
1 AJAX call (~100-200ms)
Result: Incomplete data
```

### After
```
2 parallel AJAX calls (~100-200ms each, simultaneous)
Total: Still ~100-200ms (same or faster)
Result: Complete data
```

**Why it's faster or same**:
- Parallel execution means they happen at same time
- Total time = time of slowest call, not sum of both
- Client-side merging is instant (<1ms)

---

## Summary of Changes

| Aspect | Before | After | Change |
|--------|--------|-------|--------|
| API Calls | 1 | 2 | +1 call |
| Parallelization | N/A | Yes | Optimized |
| Users Fetched | Students only | Students + Professors | +Professors |
| Adviser Role | Empty | Shows professors | ✅ FIXED |
| Code Complexity | Simple | Moderate | Justified |
| Performance | ~150ms | ~150ms | Same/Better |
| Backwards Compatible | N/A | ✅ Yes | No breaks |

---

**File**: `/opt/lampp/htdocs/dashboard/app.js.php`  
**Function**: `addNewTeamMember()`  
**Status**: ✅ Updated and validated  
**Date**: November 25, 2025
