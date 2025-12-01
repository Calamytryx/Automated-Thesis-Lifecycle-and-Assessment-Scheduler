# Quick Reference: Using Dynamic Requirements & Panelist Management

## For Admins Setting Up Requirements

### Creating a "Title Proposal" Requirement
1. Go to Admin Dashboard → Requirements
2. Click "Add Requirement"
3. Fill in:
   - **Name**: "Title Proposals"
   - **Defense Type**: Select "Title Proposal"
   - **Allow Multiple Submissions**: ✓ Check this
   - **Max Submissions**: 3
   - **Template**: Upload a PDF template for students
4. Click Save

**Result**: Teams without approved titles can upload 3 different proposals. Each upload tracked separately.

### Creating a "Title Defense" Requirement
1. Go to Admin Dashboard → Requirements
2. Click "Add Requirement"
3. Fill in:
   - **Name**: "Title Defense Manuscript"
   - **Defense Type**: Select "Title Defense"
   - **Allow Multiple Submissions**: ☐ Leave unchecked
   - **Max Submissions**: 1
   - **Template**: Upload template if needed
4. Click Save

**Result**: Teams with approved titles use this requirement for their title defense evaluation.

### Creating a "Final Defense" Requirement
1. Go to Admin Dashboard → Requirements
2. Click "Add Requirement"
3. Fill in:
   - **Name**: "Final Manuscript"
   - **Defense Type**: Select "Final Defense"
   - **Allow Multiple Submissions**: ☐ Leave unchecked
   - **Max Submissions**: 1
   - **Template**: Upload template
4. Click Save

**Result**: This requirement shows up only for teams ready for final defense (approved title + 2 evaluations).

## For Admins Managing Team Defense Types

### Check a Team's Current Defense Type
1. Go to Admin Dashboard → Teams
2. Find the team in the list
3. Look for **Defense Type** status
   - **Title Proposal**: No approved title yet
   - **Title Defense**: Has approved title, < 2 evaluations
   - **Final Defense**: Has approved title + ≥ 2 evaluations

### Force a Team to Different Defense Stage (Override)
*Use this for special cases: extended timelines, medical leaves, etc.*

1. Find the team in Admin Dashboard
2. Click team name to open details
3. Scroll to **Defense Type Override** section
4. Select override type from dropdown:
   - "Title Proposal" - Even though they have an approved title
   - "Title Defense" - Even though they have no title yet
   - "Final Defense" - Even though they don't have 2 evaluations
5. Enter **Reason** (for audit): "Extended deadline per department request"
6. Leave **Expires** blank for permanent override, or set date for temporary
7. Click **Set Override**

**Result**: System will treat this team as the overridden type regardless of actual status. Override appears in logs.

### Remove a Defense Type Override
1. Open team details
2. Scroll to **Defense Type Override** section
3. If override is active, click **Remove Override**
4. Confirm

**Result**: System returns to automatic detection based on team status.

## For Admins Managing Panelists

### Lock Panelists for a Team
*Do this after scheduling to prevent algorithm from changing them in future runs*

1. After running scheduler and assigning panelists to a team
2. Go to Admin Dashboard → Teams
3. Find the team and click to open
4. Scroll to **Panelist Assignment** section
5. You'll see the current panelists for each defense stage
6. Click **Lock Panelists** button

**Result**: 
- These panelists will remain the same for this team through all future scheduling
- Next time scheduler runs, this team's panelists won't change
- Shows as "LOCKED" in the interface

### Unlock Panelists for Reassignment
*Do this if you want to allow the scheduling algorithm to reassign panelists*

1. Open team details
2. Scroll to **Panelist Assignment** section
3. Find the defense stage you want to unlock (Title Defense, Final Defense, etc.)
4. Click **Unlock** button

**Result**:
- Panelists can be changed by next scheduler run
- Team's panelists return to automatic assignment based on program/expertise
- Shows as "UNLOCKED" in the interface

### Manually Set Specific Panelists
*Use if you need to assign specific staff members to a team*

1. Open team details
2. Scroll to **Panelist Assignment** section
3. For the defense stage you want to set:
   - Primary: Click dropdown, select panelist
   - Secondary: Click dropdown, select panelist
   - Tertiary: Click dropdown, select panelist
4. Click **Save Panelist Assignment**
5. Check **Lock after saving** to prevent changes

**Result**: Selected panelists are assigned and optionally locked.

## For Students Using Multiple Title Submissions

### Submitting 3 Title Proposals

1. Go to Home Dashboard → Requirements
2. Find "Title Proposals" requirement (allows multiple submissions)
3. Click **Submit** for first time
4. Upload PDF of first title proposal
5. Click **Submit**
   - Shows: "Submission 1 of 3"
6. Go back to Requirements
7. Click **Submit** again for second proposal
8. Upload PDF of second title
9. Click **Submit**
   - Shows: "Submission 2 of 3"
10. Repeat for third proposal
    - Shows: "Submission 3 of 3"

**Result**: All 3 proposals submitted and tracked. Can see all submissions in requirement details.

### Checking Submission Status

1. Go to Home Dashboard → Requirements
2. Find "Title Proposals" requirement
3. See status: "3 of 3 submissions complete"
4. Click **View Details** to see each proposal's status

## For Admins Reviewing Multiple Proposals

### During Evaluation

When evaluators open the evaluation form for a team with 3 title proposals:

1. All 3 proposals are displayed in a tab or carousel:
   - Tab 1: First Proposal
   - Tab 2: Second Proposal  
   - Tab 3: Third Proposal
2. Evaluators can switch between proposals while filling rubric
3. Single evaluation form covers all 3 proposals
4. Recommendation applies to all 3 (accept one → accept all)

## Common Questions

**Q: Can I upload multiple files for non-proposal requirements?**
A: No. Only requirements marked with "Allow Multiple Submissions" allow multiple files. Title Defense and Final Defense requirements typically only allow 1 file.

**Q: What happens if I unlock panelists but don't run the scheduler?**
A: Panelists stay the same. They only change when the scheduler algorithm runs. You can manually reassign them or run scheduler again.

**Q: Can a team be in multiple defense stages at once?**
A: No. Each team has exactly one current defense type. However, they can be scheduled for multiple defenses (title defense, then later final defense). Panelists lock per stage.

**Q: If I override a team to "Final Defense" but they have no approved title, what happens?**
A: The system will treat them as final defense (use final defense requirement, show final defense rubric) even though they technically have no approved title. Use this for special cases only.

**Q: How long does a panelist lock last?**
A: Until you manually unlock them. Locks persist across scheduling runs. Only the admin can unlock.

**Q: What if a panelist calls in sick for a title defense?**
A: Unlock the panelists, then either manually reassign or run scheduler again to auto-assign new ones.

## API Endpoints (For Technical Users)

### Get Team Defense Info
```bash
GET /api/admin_overrides.php?action=get_team_defense_info&team_id=5

Response:
{
  "defense_type": "title_defense",
  "panelists": [271, 270, 272],
  "override": null
}
```

### Set Defense Override
```bash
POST /api/admin_overrides.php?action=set_defense_type_override

Params:
  team_id: 5
  override_type: "final_defense"
  reason: "Medical leave extension"
  expires_at: null (or "2025-12-31")

Response:
{
  "success": true,
  "message": "Defense type override set to final_defense for team 5"
}
```

### Lock Panelists
```bash
POST /api/admin_overrides.php?action=lock_panelists

Params:
  team_id: 5
  defense_type: "title_defense"
  panelist_ids: [271, 270, 272]

Response:
{
  "success": true,
  "message": "Panelists locked for team 5, title_defense"
}
```

### Get All Submissions for Requirement
```bash
GET /api/admin_overrides.php?action=get_team_requirement_submissions&team_id=5&requirement_id=41

Response:
{
  "submissions": [
    {
      "id": 1,
      "submission_number": 1,
      "original_file_name": "AI_Research.pdf",
      "status": "submitted",
      "submitted_at": "2025-11-21 10:30:00"
    },
    {
      "id": 2,
      "submission_number": 2,
      "original_file_name": "ML_Application.pdf",
      "status": "submitted",
      "submitted_at": "2025-11-21 11:15:00"
    },
    {
      "id": 3,
      "submission_number": 3,
      "original_file_name": "Data_Science.pdf",
      "status": "submitted",
      "submitted_at": "2025-11-21 12:00:00"
    }
  ],
  "count": 3
}
```

