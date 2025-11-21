# File Visibility System - Visual Guide

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                   FILE VISIBILITY SYSTEM                        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ ADMIN CONFIGURES (Once per requirement)                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ 1. Mark as Defense Manuscript?                                  │
│    ☐ No (regular requirement)                                   │
│    ☑ Yes (main file to be defended)                            │
│                                                                 │
│ 2. Set Visibility Scope:                                       │
│    ○ all_stages (visible everywhere)                           │
│    ○ specific_stages (configure below)                         │
│    ○ current_stage_only (disappears after)                     │
│    ○ hidden (admin only)                                       │
│                                                                 │
│ 3. Configure Per-Defense-Type Rules:                           │
│                                                                 │
│    Defense Type     │ Can View │ Can Download │                 │
│    ────────────────┼──────────┼──────────────┤                 │
│    Title Proposal   │    ✓     │      ✓       │ (submit here)  │
│    Title Defense    │    ✓     │      ✗       │ (view only)    │
│    Final Defense    │    ✓     │      ✗       │ (view only)    │
│    Re-Defense       │    ✓     │      ✗       │ (view only)    │
│    General          │    ✓     │      ✓       │ (always)       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                            ↓↓↓
┌─────────────────────────────────────────────────────────────────┐
│ SYSTEM STORES IN DATABASE                                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ requirements table:                                             │
│  - is_defense_manuscript: 1                                     │
│  - visibility_scope: 'specific_stages'                          │
│                                                                 │
│ file_visibility_rules table:                                    │
│  - (req_id=1, defense_type='title_proposal'): can_view=1,      │
│    can_download=1                                              │
│  - (req_id=1, defense_type='title_defense'): can_view=1,       │
│    can_download=0                                              │
│  - (req_id=1, defense_type='final_defense'): can_view=1,       │
│    can_download=0                                              │
│  - (req_id=1, defense_type='re-defense'): can_view=1,          │
│    can_download=0                                              │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                            ↓↓↓
┌─────────────────────────────────────────────────────────────────┐
│ TEAM VIEWS FILES (Dynamic based on current defense type)        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ TEAM AT TITLE PROPOSAL STAGE:                                   │
│                                                                 │
│  📁 Files Visible:                                              │
│     🎯 Thesis Manuscript (Defense File) - [Download] [Upload]  │
│     📄 General Info - [Download]                                │
│     📄 Templates - [Download]                                   │
│                                                                 │
│  ────────────────────────────────────────────────────────────   │
│                                                                 │
│ TEAM AT TITLE DEFENSE STAGE:                                    │
│                                                                 │
│  📁 Files Visible:                                              │
│     🎯 Thesis Manuscript (Defense File) - [View Only]          │
│     📄 General Info - [Download]                                │
│     📄 Templates - [Download]                                   │
│                                                                 │
│  (Note: Can't upload/download manuscript - read-only)          │
│                                                                 │
│  ────────────────────────────────────────────────────────────   │
│                                                                 │
│ TEAM AT FINAL DEFENSE STAGE:                                    │
│                                                                 │
│  📁 Files Visible:                                              │
│     🎯 Thesis Manuscript (Defense File) - [View Only]          │
│     📄 General Info - [Download]                                │
│     📄 Templates - [Download]                                   │
│                                                                 │
│  (Same as Title Defense - can review manuscript)               │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                            ↓↓↓
┌─────────────────────────────────────────────────────────────────┐
│ AUDIT TRAIL (logged automatically)                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ Team 5, Title Proposal - 2025-11-22 10:00 - download manuscript │
│ Team 5, Title Proposal - 2025-11-22 10:15 - view templates      │
│ Team 5, Title Defense  - 2025-11-22 14:30 - view manuscript     │
│ Team 5, Title Defense  - 2025-11-22 14:45 - view general_info   │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Visibility Scope Decision Tree

```
Does requirement need to be visible?
│
├─ No, never show to teams
│  └─→ visibility_scope = 'hidden' ✓
│
└─ Yes, show to teams
   │
   ├─ Should it be visible everywhere?
   │  │
   │  ├─ Yes (general files, templates, etc.)
   │  │  └─→ visibility_scope = 'all_stages' ✓
   │  │
   │  └─ No (stage-specific files)
   │     │
   │     ├─ Should it disappear after the stage?
   │     │  │
   │     │  ├─ Yes (e.g., Title Proposal only)
   │     │  │  └─→ visibility_scope = 'current_stage_only' ✓
   │     │  │
   │     │  └─ No (e.g., thesis used across stages)
   │     │     └─→ visibility_scope = 'specific_stages'
   │     │        then configure per defense type ✓
   │     │
```

---

## Defense Manuscript vs Regular File

```
DEFENSE MANUSCRIPT                    │  REGULAR REQUIREMENT
(is_defense_manuscript = TRUE)         │  (is_defense_manuscript = FALSE)
                                      │
✓ Main file to be defended            │  Supporting documents
✓ Shown with special 🎯 badge         │  Shown normally
✓ Progress tracked carefully          │  Treated as checklist items
✓ Visibility carefully controlled     │  May be visible to all
✓ Usually marked read-only            │  May allow re-uploads
   during later stages                │
                                      │
Example: Thesis, Research Paper       │  Example: CV, Budget Sheet,
         Project Report                │           Literature Review
```

---

## Common Configurations

### Configuration 1: Thesis at All Stages (Most Common)

```
Requirement: "Thesis Manuscript"
is_defense_manuscript: ✓ TRUE
visibility_scope: specific_stages

Rules:
┌──────────────┬──────────┬────────────┐
│ Defense Type │ View     │ Download   │
├──────────────┼──────────┼────────────┤
│ Title Prop   │ ✓ (edit) │ ✓          │
│ Title Def    │ ✓ (read) │ ✗          │
│ Final Def    │ ✓ (read) │ ✗          │
│ Re-Defense   │ ✓ (read) │ ✓          │
│ General      │ ✓        │ ✓          │
└──────────────┴──────────┴────────────┘

Result:
  Title Proposal:  Can modify thesis
  Title Defense:   Read-only for evaluation
  Final Defense:   Read-only for evaluation
  Re-Defense:      Can modify (revisions)
```

### Configuration 2: Proposal Form (Stage-Specific)

```
Requirement: "Title Proposal Form"
is_defense_manuscript: ✗ FALSE
visibility_scope: current_stage_only

Rules:
┌──────────────┬──────────┬────────────┐
│ Defense Type │ View     │ Download   │
├──────────────┼──────────┼────────────┤
│ Title Prop   │ ✓        │ ✓          │
│ Title Def    │ ✗ HIDDEN │ ✗          │
│ Final Def    │ ✗ HIDDEN │ ✗          │
│ Re-Defense   │ ✗ HIDDEN │ ✗          │
│ General      │ ✓        │ ✓          │
└──────────────┴──────────┴────────────┘

Result:
  Title Proposal:  See form, can submit
  Title Defense:   Form GONE (not visible)
  Final Defense:   Form GONE (not visible)
  Re-Defense:      Form GONE (not visible)
```

### Configuration 3: Template (Always Visible)

```
Requirement: "Document Template"
is_defense_manuscript: ✗ FALSE
visibility_scope: all_stages

Rules:
(No need to configure per-stage, visible everywhere)

Result:
  All stages:      Can download template
  (Simple, no restrictions)
```

### Configuration 4: Admin Only (Hidden)

```
Requirement: "Internal Review Notes"
is_defense_manuscript: ✗ FALSE
visibility_scope: hidden

Result:
  All stages:      Cannot see requirement
  Admin only:      Can manage in database
```

---

## File Access Flow

```
TEAM REQUESTS FILE
│
├─ Get team's current defense type
│  └─ Query: SELECT defense_type FROM defense_schedules 
│            WHERE team_id = ? AND schedule_date <= NOW()
│
├─ Get requirement's visibility rules
│  └─ Query: SELECT visibility_scope FROM requirements WHERE id = ?
│            + SELECT can_view, can_download FROM file_visibility_rules...
│
├─ Check visibility:
│  │
│  ├─ IF visibility_scope = 'hidden'
│  │  └─ DENY: File is hidden
│  │
│  ├─ ELSE IF visibility_scope = 'all_stages'
│  │  └─ ALLOW: File visible to all
│  │
│  ├─ ELSE IF visibility_scope = 'current_stage_only'
│  │  └─ IF current_defense_type matches rule
│  │     ├─ ALLOW: Visible during this stage
│  │     └─ DENY: Stage doesn't match
│  │
│  └─ ELSE IF visibility_scope = 'specific_stages'
│     └─ IF rule for current_defense_type exists AND can_view = 1
│        ├─ ALLOW: File visible to this stage
│        └─ DENY: No rule or can_view = 0
│
├─ Log access attempt
│  └─ INSERT defense_file_access_log (team_id, action='view'/'download')
│
└─ Return file or error message
```

---

## API Usage Flow

### Mark Defense Manuscript (Admin)

```
Admin clicks "Is Defense Manuscript" checkbox
                     ↓
Form submission
                     ↓
POST /api/file_visibility_management.php?action=mark_defense_manuscript
                     ↓
UPDATE requirements SET is_defense_manuscript = 1 WHERE id = 5
                     ↓
✓ Requirement marked as defense manuscript
```

### Check File Access (Team/API)

```
Team requests file
                     ↓
GET /api/file_visibility_management.php?action=can_access_file
    &team_id=10&requirement_id=5
                     ↓
1. Get team's current defense_type → 'title_defense'
2. Get requirement's visibility_scope → 'specific_stages'
3. Get rule for (req=5, type='title_defense') → can_view=1, can_download=0
4. Log access
5. Return: {'can_access': true, 'can_download': false, 'reason': '...'}
                     ↓
✓ File accessible (view only)
  or ✗ File not accessible
```

---

## Query Examples

### Get All Files Visible to a Team

```sql
SELECT r.id, r.name, r.is_defense_manuscript, tr.file_name
FROM requirements r
LEFT JOIN team_requirements tr ON r.id = tr.requirement_id
LEFT JOIN file_visibility_rules fvr ON r.id = fvr.requirement_id
WHERE 
    -- Team has this requirement
    tr.team_id = 10
    -- File is submitted
    AND tr.file_name IS NOT NULL
    -- Visibility allows it
    AND (
        r.visibility_scope = 'all_stages'
        OR (r.visibility_scope = 'specific_stages' AND fvr.can_view = 1)
        OR (r.visibility_scope = 'current_stage_only' AND fvr.defense_type = 'title_defense')
    )
    -- Not hidden
    AND r.visibility_scope != 'hidden'
ORDER BY r.is_defense_manuscript DESC, r.name ASC;
```

### Get Audit Log (Who Accessed What)

```sql
SELECT 
    t.name as team_name,
    r.name as file_name,
    dfal.action,
    dfal.access_timestamp,
    CONCAT(u.first_name, ' ', u.last_name) as user
FROM defense_file_access_log dfal
LEFT JOIN teams t ON dfal.team_id = t.id
LEFT JOIN requirements r ON dfal.requirement_id = r.id
LEFT JOIN users u ON dfal.user_id = u.id
ORDER BY dfal.access_timestamp DESC
LIMIT 100;
```

---

## Status Indicators in UI (Future)

```
Defense Manuscript Files:
  🎯 Thesis Manuscript [View] [Download]     ← Can download (editing phase)
  🎯 Thesis Manuscript [View Only]           ← Read-only (review phase)
  🎯 Thesis Manuscript [Not Available]       ← Hidden from this stage

Regular Files:
  📄 Templates [Download]
  📄 General Info [Download]
  📄 Archive [Hidden]                        ← Not visible

Badges:
  [Manuscript]  - Blue badge for defense files
  [Read-Only]   - Gray badge for view-only files
  [Download OK] - Green checkmark if downloadable
  [Restricted]  - Red icon if inaccessible
```

---

## Error Scenarios

| Scenario | System Response |
|----------|-----------------|
| Team at Title Proposal tries to download read-only file | ERROR: "This file is read-only at your current stage" |
| Team at Title Defense tries to access Title Proposal only file | ERROR: "This file is not available at your current stage" |
| Team tries to access hidden file | ERROR: "This file is not available" |
| File hasn't been submitted yet | INFO: "No file uploaded yet" |
| Team finished all stages, tries to access stage-only file | INFO: "File was only available during [stage]" |

---

## Performance Notes

✅ **Fast Queries:**
- Indexed on: (requirement_id, defense_type)
- Simple enum comparisons
- Usually <10ms response time

✅ **Scalable:**
- Works with thousands of requirements
- Works with hundreds of teams
- Audit log can be archived/pruned

✅ **Efficient Joins:**
- Requirements → file_visibility_rules (indexed)
- team_requirements → files (direct lookup)
- defense_schedules → current_defense_type (one per team)

---

## Implementation Checklist

- [ ] Run database migration
- [ ] Verify tables created
- [ ] Mark first requirement as defense manuscript
- [ ] Set visibility scope
- [ ] Configure per-stage visibility rules
- [ ] Test API: get_visible_files
- [ ] Test API: can_access_file
- [ ] Verify audit logging
- [ ] Test with multiple defense types
- [ ] Review audit log for accuracy

✅ **You're ready to go!**
