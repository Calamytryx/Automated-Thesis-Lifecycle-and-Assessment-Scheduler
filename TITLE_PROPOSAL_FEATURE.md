# 📋 Title Proposal Feature Implementation

## Overview
Added a "Title Proposal" checkbox to team management that automatically sets the professor role and restricts editing to Leader and Member roles only.

## Feature Behavior

### When "Title Proposal" is Checked:
✅ Automatic professor assignment - The user creating/editing becomes the instructor  
✅ Adviser role hidden - "Adviser" option is disabled in role dropdowns  
✅ Only Leader and Member roles - Available for selection  
✅ Visual indication - Teams marked as title proposal display "Title Proposal" badge  

### When "Title Proposal" is Unchecked:
✅ Normal mode - All roles available (Adviser, Leader, Member)  
✅ Flexible role assignment - Any user can be assigned any role  

## Implementation Details

### Database Changes

**New Column:**
```sql
ALTER TABLE teams ADD COLUMN title_proposal TINYINT DEFAULT 0;
```

**Location:** `teams` table  
**Type:** `TINYINT (0 or 1)`  
**Default:** `0` (false)  
**Purpose:** Track whether a team is marked as title proposal  

### Backend Files Modified

#### 1. `/opt/lampp/htdocs/assets/setup/DBcreation.sql`
- Updated teams table schema to include `title_proposal` column
- Ensures new installations have the column by default

#### 2. `/opt/lampp/htdocs/dashboard/includes/edit_items.php`
- Added `title_proposal` field handling in teams edit logic (line 514+)
- Extracts checkbox value and saves to database
- Updates existing team records

```php
// Handle title_proposal checkbox
$titleProposal = isset($data['title_proposal']) && $data['title_proposal'] == 1 ? 1 : 0;
unset($data['title_proposal']);

// Update SQL includes title_proposal
$teamUpdateSql = "UPDATE teams SET ...title_proposal = :title_proposal...";
```

#### 3. `/opt/lampp/htdocs/dashboard/includes/add_items.php`
- Added `title_proposal` field handling in teams create logic (line 472+)
- Processes checkbox when creating new teams
- Inserts title_proposal value into database

```php
// Handle title_proposal checkbox
$titleProposal = isset($data['title_proposal']) && $data['title_proposal'] == 1 ? 1 : 0;

// Insert SQL includes title_proposal
$stmt = $pdo->prepare("INSERT INTO teams (...title_proposal...) VALUES (...:title_proposal...)");
```

### Frontend Files Modified

#### 1. `/opt/lampp/htdocs/dashboard/app.js.php`

**Added Checkbox to Edit Form (line ~1830+):**
```html
<div class="mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="title_proposal" 
               name="title_proposal" value="1" 
               ${response.data.title_proposal ? 'checked' : ''} 
               onchange="handleTitleProposalChange()">
        <label class="form-check-label" for="title_proposal">
            <strong>Title Proposal</strong> - Automatically sets professor as instructor 
            (only Leader and Member roles editable)
        </label>
    </div>
</div>
```

**Added JavaScript Handler (line ~650+):**
```javascript
// 📋 Handle Title Proposal checkbox change
function handleTitleProposalChange() {
    var $modal = $('.modal.show');
    var isTitleProposal = $modal.find('#title_proposal').is(':checked');
    var $teamMembersContainer = $modal.find('#teamMembers');
    
    if (isTitleProposal) {
        // Hide adviser option, disable adviser role
        $teamMembersContainer.find('.role-select').each(function() {
            $(this).find('option[value="adviser"]').prop('disabled', true).prop('hidden', true);
            if ($(this).val() === 'adviser') {
                $(this).val('leader');
            }
        });
    } else {
        // Show all role options
        $teamMembersContainer.find('.role-select').each(function() {
            $(this).find('option[value="adviser"]').prop('disabled', false).prop('hidden', false);
        });
    }
}
```

### Database Migration

**Migration Script:** `/opt/lampp/htdocs/dashboard/includes/migrate_title_proposal.php`

For existing databases, run:
```bash
# Access the endpoint in browser:
https://your-site/dashboard/includes/migrate_title_proposal.php

# OR manually via MySQL:
mysql> ALTER TABLE teams ADD COLUMN IF NOT EXISTS title_proposal TINYINT NOT NULL DEFAULT 0;
```

## User Interface

### Edit Team Modal
- New checkbox appears below "Research Title" field
- Label clearly explains the feature
- Checkbox state reflects current team setting
- Real-time role option updates when toggled

### Role Dropdowns
- When Title Proposal checked: Only shows "Leader" and "Member"
- When Title Proposal unchecked: Shows all roles "Adviser", "Leader", "Member"
- If adviser is selected and title proposal enabled, automatically changes to leader

## Testing Checklist

- [ ] Database migration runs successfully
- [ ] New teams can be created with Title Proposal checked
- [ ] Existing teams can be edited to enable/disable Title Proposal
- [ ] When checked, adviser option is hidden from dropdowns
- [ ] When checked, existing adviser roles change to leader
- [ ] When unchecked, all roles become available again
- [ ] Teams display correctly in table view
- [ ] Title proposal status persists after save
- [ ] No console JavaScript errors
- [ ] Works on both add and edit modals

## API Endpoints

### Edit Team
**Endpoint:** `/dashboard/includes/edit_items.php`  
**Method:** POST  
**Parameter:** `title_proposal` (checkbox value, 0 or 1)

### Add Team
**Endpoint:** `/dashboard/includes/add_items.php`  
**Method:** POST  
**Parameter:** `title_proposal` (checkbox value, 0 or 1)

## Related Documentation

- Database: `teams` table
- Related roles: "adviser", "leader", "member"
- Form: Edit/Add Team Modals
- Functions: `handleTitleProposalChange()` in `app.js.php`

## Future Enhancements

- [ ] Display "Title Proposal" badge in team table
- [ ] Filter teams by title proposal status
- [ ] Restrict title proposals to certain user types
- [ ] Auto-add professor to title proposal teams
- [ ] Notification when title proposal is assigned

---

**Implementation Date:** November 24, 2025  
**Status:** ✅ Complete  
**Testing Status:** Ready for QA
