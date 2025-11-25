# 🔐 Professor Access Control & Permission Model

## Overview
Comprehensive access control system for professors managing research teams with section-based and college-based filtering.

## Permission Hierarchy

### 1. **STUDENT SELECTION (Team Members: Leader & Member)**
**Rule**: Professors can ONLY select students from their assigned section(s)

- **Visibility**: Professors see ALL teams and schedules (no section filter)
- **Selection**: When adding leader/member roles, only students from their assigned section(s) appear in dropdown
- **Creation**: Professor cannot create teams with students outside their section(s)
- **Multiple Sections**: One professor can be assigned to multiple sections → can select students from any of those sections

**Examples**:
- Prof A assigned to Section A → can select only Section A students
- Prof B assigned to Sections B & C → can select from both Section B and C students
- Prof X with no section assignment → can select from all students

### 2. **ADVISER SELECTION**
**Rule**: Professors can ONLY select advisers from their SAME COLLEGE, regardless of section

- **College-Based**: Not section-based
- **Filtering**: When assigning an adviser role, only professors from same college appear in dropdown
- **Multiple Advisers**: Different teams can have different advisers (no limit on who can advise what)
- **Cross-Section**: Advisers can be from different sections within same college

**Examples**:
- Team in College A, Program X → adviser dropdown shows only professors from College A
- Team in College A, Section 1 → adviser can be from Section 1, 2, 3, or any section in College A
- Team in College B → adviser dropdown shows only professors from College B (different college advisers excluded)

### 3. **TITLE PROPOSAL TEAMS**
**Rule**: Professors automatically assigned as adviser when creating title proposal teams

- **Detection**: Title containing "title proposal" (case-insensitive)
- **Auto-Assignment**: Professor is instantly added as adviser (`role='adviser'`)
- **College Check**: Still respects college requirement for adviser role
- **Multiple Sections**: Works correctly with professors assigned to multiple sections

### 4. **BECOME ADVISER (Non-Title Proposal)**
**Rule**: "Become Adviser" button shows for eligible non-title-proposal teams

**Shows when**:
- Professor is not already an adviser on the team
- Team doesn't have an adviser yet
- Team title does NOT contain "title proposal"
- Professor is viewing from same college as team's program

**Hidden when**:
- Professor is already an adviser
- Team has an adviser
- Team is a title proposal
- Different college

## Implementation Details

### Database Structure
```
section_professors table:
  - professor_id (USER ID)
  - section (Section Name)
  
  One professor can have MULTIPLE rows for multiple sections
  Example: Prof ID 5 → Section A
           Prof ID 5 → Section B
           Prof ID 5 → Section C
```

### Permission Functions (section_access.php)

#### `getProfessorSections($pdo, $professor_id)` → Array
Returns all sections assigned to professor (supports multiple)

#### `canProfessorCreateTeam($pdo, $professor_id, $memberIds)` → Array
Validates that ALL member IDs are from professor's assigned section(s)
Returns: `['canCreate' => bool, 'message' => string]`

#### `getAvailableStudentsForProfessor($pdo, $professor_id)` → Array
Returns students only from professor's assigned section(s)

#### `getAvailableAdvisersForTeam($pdo, $teamProgram)` → Array
Returns professors only from same college as team's program

#### `getProfessorCollege($pdo, $professor_id)` → String
Returns college name from professor's program assignment

#### `isTitleProposal($title)` → Boolean
Returns true if title contains "title proposal"

### API Endpoints

#### `/includes/get_available_users.php`
```
GET Parameters:
  type=students            → Returns students from professor's section(s)
  type=advisers            → Returns professors from same college
  team_program=NAME        → Required for type=advisers
  team_id=ID              → Optional, for exclusion checks

Response Format:
{
  "success": true,
  "data": [ { id, first_name, last_name, username, [section/college] } ]
}
```

### Frontend Integration

#### JavaScript Variables (app.js.php)
```javascript
const currentUserType = <?php echo $_SESSION['usertype']; ?>;  // 0=admin, 1=student, 2=professor
const currentUserId = <?php echo $_SESSION['id']; ?>;
```

#### Team Member Dropdown (addNewTeamMember)
- Professors: Calls `get_available_users.php?type=students`
- Shows students with section label: "John Doe (Section A)"
- Non-professors: Fallback to legacy endpoint

#### Adviser Selection (Become Adviser)
- Professors: Calls `get_available_users.php?type=advisers&team_program=PROGRAM_NAME`
- Shows professors from same college

## Data Validation

### Team Creation (add_items.php)
```
1. Check professor has section assignment(s)
2. Validate all members from assigned section(s)
3. Reject if any member outside section(s)
4. Auto-assign as adviser if title proposal
```

### Bulk Team Creation (bulk_add_teams.php)
```
1. For each team in bulk:
   a. Extract member usernames
   b. Lookup their IDs
   c. Validate all from professor's section(s)
   d. Reject entire team if validation fails
   e. Auto-assign adviser if title proposal
```

### Adviser Assignment (add_items.php team_members)
```
1. Get team's program college
2. Verify adviser is from same college
3. Add team_member with role='adviser'
4. No section check for adviser role
```

## Example Scenarios

### Scenario 1: Professor with Multiple Sections
- Prof ID 10 assigned to: Section A, Section B
- Creating team:
  - ✅ Can add students from Section A
  - ✅ Can add students from Section B
  - ❌ Cannot add students from Section C
  - ✅ Can select any professor from same college as adviser

### Scenario 2: Title Proposal Team
- Prof ID 20 creates team named "Title Proposal Research"
- System automatically:
  - ✅ Adds Prof ID 20 as adviser (role='adviser')
  - ✅ Respects college requirement
  - ✅ Works with multiple sections

### Scenario 3: Become Adviser Button
- Team "AI Research" (no adviser) in College A
- Prof ID 30 (College A, Section A) views team
- ✅ "Become Adviser" button shows
- Prof ID 30 clicks, becomes adviser
- ✅ Prof added to team_members with role='adviser'

### Scenario 4: Cross-Section Team
- Team has students from Section A and B
- Prof assigned only to Section A
- ❌ Cannot view/edit team (would violate section rules)
- ✅ But can still become adviser (college-based)

## Security Notes

- ✅ Section filtering enforced server-side (add_items.php)
- ✅ College filtering enforced server-side (get_available_users.php)
- ✅ Multi-section support through array queries
- ✅ Title proposal detection case-insensitive
- ✅ All permissions checked before database writes
- ✅ Error messages returned to user
- ✅ Comprehensive logging for debugging

## Testing Checklist

- [ ] Professor with 1 section can select students from that section
- [ ] Professor with multiple sections can select from all assigned sections
- [ ] Professor CANNOT select students from non-assigned sections
- [ ] Professor CANNOT create teams mixing sections
- [ ] Title proposal auto-assigns professor as adviser
- [ ] "Become Adviser" button appears for non-title-proposal teams without adviser
- [ ] "Become Adviser" only shows professors from same college
- [ ] Cross-college adviser selection blocked
- [ ] Permissions enforced on both single and bulk team creation
- [ ] API endpoints return correct filtered data
- [ ] Section labels appear in student dropdown
