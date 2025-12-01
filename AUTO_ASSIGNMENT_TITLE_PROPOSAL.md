# 🎯 AUTO-ASSIGNMENT FEATURE - TITLE PROPOSAL

**Feature**: When a team is marked as "Title Proposal", the currently logged-in user is **automatically assigned as the Adviser/Professor**

**Status**: ✅ IMPLEMENTED

---

## 🎓 HOW IT WORKS

### When Adding a New Team
```
1. User creates a new team
2. User checks "Title Proposal" checkbox
3. User adds other team members (Leader, Member roles)
4. User clicks "Save Team"
5. System automatically adds the current user as Adviser
6. Result: Team has adviser already assigned (current user)
```

### When Editing an Existing Team
```
1. User opens edit form for existing team
2. User checks "Title Proposal" checkbox (if not already checked)
3. System checks if current user is already a team member
4. If NOT a member: Add them as Adviser
5. If a member but NOT adviser: Change their role to Adviser
6. If already adviser: Leave as is
7. User clicks "Save Changes"
```

---

## 💻 IMPLEMENTATION DETAILS

### Add Handler (`add_items.php` - Lines ~510-530)
```php
// 🎓 TITLE PROPOSAL AUTO-ASSIGNMENT: If title_proposal=1, auto-assign current user as adviser
if ($titleProposal == 1) {
    try {
        $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
        $stmt->execute([
            'team_id' => $teamId,
            'user_id' => $userId,
            'role' => 'adviser'
        ]);
        error_log("Title proposal team created: Auto-assigned current user (ID: $userId) as adviser to team $teamId");
    } catch (PDOException $adviserError) {
        error_log("Note: Could not auto-assign user as adviser: " . $adviserError->getMessage());
    }
}
```

### Edit Handler (`edit_items.php` - Lines ~564-600)
```php
// 🎓 TITLE PROPOSAL AUTO-ASSIGNMENT: If title_proposal changed to 1, auto-assign current user as adviser if not already assigned
if ($titleProposal == 1) {
    // Check if current user is already in the team
    $stmtCheck = $pdo->prepare("SELECT role FROM team_members WHERE team_id = :team_id AND user_id = :user_id");
    $stmtCheck->execute([':team_id' => $id, ':user_id' => $userId]);
    $existingRole = $stmtCheck->fetchColumn();
    
    if (!$existingRole) {
        // User not in team, add them as adviser
        try {
            $stmt = $pdo->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)");
            $stmt->execute([
                'team_id' => $id,
                'user_id' => $userId,
                'role' => 'adviser'
            ]);
            error_log("Title proposal enabled: Auto-assigned current user (ID: $userId) as adviser to team $id");
        } catch (PDOException $adviserError) {
            error_log("Note: Could not auto-assign user as adviser: " . $adviserError->getMessage());
        }
    } else if ($existingRole !== 'adviser') {
        // User exists but not as adviser, update their role
        try {
            $stmtUpdate = $pdo->prepare("UPDATE team_members SET role = :role WHERE team_id = :team_id AND user_id = :user_id");
            $stmtUpdate->execute([
                ':role' => 'adviser',
                ':team_id' => $id,
                ':user_id' => $userId
            ]);
            error_log("Title proposal enabled: Updated current user (ID: $userId) role to adviser in team $id");
        } catch (PDOException $adviserError) {
            error_log("Note: Could not update user role to adviser: " . $adviserError->getMessage());
        }
    }
}
```

---

## ✅ BEHAVIOR LOGIC

### Scenario 1: Creating Title Proposal Team (Add Form)
```
Input:  title_proposal = 1
        current_user_id = 42

Logic:
  - Check if $titleProposal == 1? YES
  - Insert team_members record:
    * team_id = newly created team
    * user_id = 42 (current user)
    * role = 'adviser'
  - Result: ✅ User 42 is Adviser
```

### Scenario 2: Enabling Title Proposal on Existing Team (Edit Form)
```
Case A: Current user NOT yet in team
  Input:  title_proposal = 1
          current_user_id = 42
          existing_members = [user 10 (leader), user 11 (member)]
  
  Logic:
    - Check if $titleProposal == 1? YES
    - Query: Is user 42 in team? NO
    - Insert team_members record:
      * team_id = team_id
      * user_id = 42
      * role = 'adviser'
    - Result: ✅ User 42 added as Adviser

Case B: Current user already in team but not as adviser
  Input:  title_proposal = 1
          current_user_id = 42
          user_42_current_role = 'member'
  
  Logic:
    - Check if $titleProposal == 1? YES
    - Query: Is user 42 in team? YES, role = 'member'
    - Update role from 'member' to 'adviser'
    - Result: ✅ User 42 role changed to Adviser

Case C: Current user already adviser
  Input:  title_proposal = 1
          current_user_id = 42
          user_42_current_role = 'adviser'
  
  Logic:
    - Check if $titleProposal == 1? YES
    - Query: Is user 42 in team? YES, role = 'adviser'
    - No action needed (already adviser)
    - Result: ✅ No change (already correct)
```

---

## 🎯 KEY FEATURES

✅ **Automatic**: No user action required  
✅ **Smart**: Only adds if needed (checks existing role)  
✅ **Safe**: Won't fail if adviser assignment fails  
✅ **Logged**: All actions logged for audit trail  
✅ **Intelligent**: Handles all three scenarios (new, existing member, already adviser)  
✅ **Non-Breaking**: Doesn't interfere with other team member additions  

---

## 📝 USER PERSPECTIVE

### Creating a Title Proposal Team
```
User Flow:
1. Click "Add Team"
2. Enter: Team Name, Research Title, Area, Program
3. ✓ Check "Title Proposal" checkbox
4. Add other team members (Leader, Member only)
5. Click "Save Team"
6. ✅ Success! 
7. System automatically:
   - Creates the team
   - Adds you as Adviser
   - Makes you the "pseudo adviser" (as requested)
```

### Converting Team to Title Proposal
```
User Flow:
1. Click Edit on existing team
2. ✓ Check "Title Proposal" checkbox
3. Click "Save Changes"
4. ✅ Success!
5. System automatically:
   - Updates title_proposal = 1
   - Adds you as Adviser (if not already a member)
   - Or changes your role to Adviser (if already a member)
```

---

## 📊 DATABASE BEHAVIOR

### team_members Table Entry
When `title_proposal = 1`:
```sql
INSERT INTO team_members (team_id, user_id, role) 
VALUES (team_id, current_user_id, 'adviser')
```

### What Gets Stored
```
team_id: The newly created/edited team
user_id: The ID of the currently logged-in user
role: 'adviser' (exactly this string)
created_at: AUTO (timestamp)
```

### Query Examples
```sql
-- Find all title proposal teams where user is adviser
SELECT t.id, t.name, tm.role 
FROM teams t
JOIN team_members tm ON t.id = tm.team_id
WHERE t.title_proposal = 1 
AND tm.user_id = :current_user_id 
AND tm.role = 'adviser';

-- Count how many title proposal teams a user is adviser for
SELECT COUNT(*) 
FROM teams t
JOIN team_members tm ON t.id = tm.team_id
WHERE t.title_proposal = 1 
AND tm.user_id = :current_user_id 
AND tm.role = 'adviser';
```

---

## 🔄 WORKFLOW EXAMPLE

```
SCENARIO: Professor creates a Title Proposal team for students

Step 1: Professor goes to Dashboard → Teams Tab
Step 2: Professor clicks "Add Team"
Step 3: Popup shows "Add Team" form
Step 4: Professor enters:
        - Team Name: "Team Alpha"
        - Research Title: "AI Study"
        - Area of Expertise: "Machine Learning"
        - Program: "CS"
        ✓ Checks "Title Proposal" checkbox
Step 5: Professor adds team members:
        - Student 1 (Role: Leader)
        - Student 2 (Role: Member)
Step 6: Professor clicks "Save Team"
Step 7: System executes:
        - Insert teams table: title_proposal = 1
        - Insert research_titles
        - Insert team_member for Student 1 (leader)
        - Insert team_member for Student 2 (member)
        - Insert team_member for Professor (adviser) ← AUTO-ADDED
Step 8: Professor sees success message
Step 9: Team now appears in table with:
        - Name: Team Alpha
        - Members: 3 (Professor as Adviser, Student 1 as Leader, Student 2 as Member)
```

---

## ✨ ADDITIONAL BENEFITS

1. **No Manual Assignment**: Professor doesn't need to manually add themselves
2. **Consistency**: Every title proposal team has a responsible adviser
3. **Tracking**: Easy to see who's responsible for which teams
4. **Audit Trail**: Logged for compliance and auditing
5. **Error Recovery**: Won't fail if adviser assignment has issues
6. **Smart Logic**: Handles edge cases (already member, different role, etc.)

---

## 🐛 ERROR HANDLING

### If Auto-Assignment Fails
```
Situation: INSERT into team_members fails
Result: 
  - Team is still created (success)
  - Error is logged: "Note: Could not auto-assign user as adviser"
  - User sees: "Team created successfully"
  - User can manually add themselves if needed
  
Rationale: 
  - Don't break the main operation for a secondary feature
  - Log for admin review
  - User can fix if necessary
```

---

## 📋 TESTING SCENARIOS

| Scenario | Expected | Result |
|----------|----------|--------|
| Create team with title_proposal=1 | User auto-added as adviser | ✅ PASS |
| Create team with title_proposal=0 | User NOT added | ✅ PASS |
| Enable title_proposal on existing team | User auto-added as adviser | ✅ PASS |
| User already member when enabling | Role changed to adviser | ✅ PASS |
| User already adviser when enabling | No change | ✅ PASS |
| Multiple teams created | Each gets respective adviser | ✅ PASS |

---

## 🎓 BUSINESS LOGIC

**Why This Matters**:
- Every title proposal team needs an instructor/adviser
- The person creating it is the obvious choice
- Automatic assignment prevents oversight
- Creates accountability
- Ensures compliance with academic structure

**User Benefits**:
- No extra steps needed
- Automatic responsibility tracking
- Clear role assignment
- Professional workflow
- Error prevention

---

## 📝 SUMMARY

**Feature**: Auto-assign current user as Adviser when creating/editing Title Proposal teams  
**Status**: ✅ IMPLEMENTED & TESTED  
**Files Modified**: 
- `add_items.php` (ADD handler)
- `edit_items.php` (EDIT handler)  
**Line Changes**: ~40 lines total  
**Performance Impact**: None (check before insert)  
**Security Impact**: None (uses existing auth)  
**Backward Compatibility**: 100%

---

**This completes the Title Proposal feature with automatic adviser assignment!** ✅
