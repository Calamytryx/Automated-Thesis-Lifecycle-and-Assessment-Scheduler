# File Visibility System - Quick Start

## What This Does

Control **which files teams see based on which defense stage they're at** (Title Proposal, Title Defense, Final Defense, Re-Defense).

Mark files as "**defense manuscripts**" - the main files to be defended.

Example:
- **Team in Title Proposal stage** → Sees only Title Proposal requirements
- **Team in Final Defense stage** → Sees all previous + current requirements
- **File marked as manuscript** → Shows with special indicator
- **File set to "current_stage_only"** → Disappears after defense ends

---

## Quick Setup (5 minutes)

### 1. Enable the Feature (Admin)

Run the database migration:

```bash
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/20251122_add_file_visibility.sql
```

✅ Done! Two new tables created, columns added to requirements.

### 2. Mark Your First Defense Manuscript

```bash
curl -X POST "http://localhost/api/file_visibility_management.php?action=mark_defense_manuscript" \
  -d "requirement_id=1&is_manuscript=1"
```

Replace `1` with your actual requirement ID.

### 3. Set Visibility for Each Defense Stage

```bash
# Title Proposal can see and download
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=1&defense_type=title_proposal&can_view=1&can_download=1"

# Title Defense can view but not download
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=1&defense_type=title_defense&can_view=1&can_download=0"

# Final Defense can view but not download
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=1&defense_type=final_defense&can_view=1&can_download=0"
```

### 4. Test with a Team

```bash
# Get files visible to Team #5 (at their current defense stage)
curl "http://localhost/api/file_visibility_management.php?action=get_visible_files&team_id=5"
```

You should see only files applicable to their current stage!

---

## 4 Visibility Modes

| Mode | Use Case | Example |
|------|----------|---------|
| **all_stages** | Visible everywhere | "General Information" |
| **specific_stages** | Fine control | Manuscript visible only to certain stages |
| **current_stage_only** | Hide before/after | "Title Proposal requirement" disappears after stage ends |
| **hidden** | Admin only | "Draft materials" never visible to teams |

---

## Set Visibility Scope

```bash
# Option 1: Visible to all stages (default)
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=1&visibility_scope=all_stages"

# Option 2: Specific stages (configure per stage above)
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=1&visibility_scope=specific_stages"

# Option 3: Only visible during current stage
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=1&visibility_scope=current_stage_only"

# Option 4: Hidden from all teams
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=1&visibility_scope=hidden"
```

---

## Common Workflows

### Workflow 1: Thesis Submitted at Title Proposal, Used at All Stages

```bash
# Mark as manuscript
curl -X POST "http://localhost/api/file_visibility_management.php?action=mark_defense_manuscript" \
  -d "requirement_id=5&is_manuscript=1"

# Set to visible at all stages
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=5&visibility_scope=all_stages"

# Allow download at Title Proposal only
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=5&defense_type=title_proposal&can_view=1&can_download=1"

# Read-only at later stages
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=5&defense_type=title_defense&can_view=1&can_download=0"

curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=5&defense_type=final_defense&can_view=1&can_download=0"
```

### Workflow 2: Requirement Only for Title Proposal

```bash
# Set to only visible during current stage
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=6&visibility_scope=current_stage_only"

# Make it visible only to title_proposal
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_defense_type_visibility" \
  -d "requirement_id=6&defense_type=title_proposal&can_view=1&can_download=1"
```

### Workflow 3: Hide Admin Materials

```bash
curl -X POST "http://localhost/api/file_visibility_management.php?action=set_visibility_scope" \
  -d "requirement_id=99&visibility_scope=hidden"
```

---

## Check What's Visible

### For Admin: See All Manuscripts
```bash
curl "http://localhost/api/file_visibility_management.php?action=list_defense_manuscripts"
```

### For Team: See Visible Files
```bash
curl "http://localhost/api/file_visibility_management.php?action=get_visible_files&team_id=5"
```

### Check Single File Access
```bash
curl "http://localhost/api/file_visibility_management.php?action=can_access_file&team_id=5&requirement_id=1"
```

---

## View Audit Log

See which teams accessed which files:

```bash
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis -e "
SELECT 
  dfal.team_id,
  t.name as team_name,
  r.name as file_name,
  dfal.action,
  dfal.access_timestamp
FROM defense_file_access_log dfal
LEFT JOIN teams t ON dfal.team_id = t.id
LEFT JOIN requirements r ON dfal.requirement_id = r.id
ORDER BY dfal.access_timestamp DESC
LIMIT 20;
"
```

---

## Files Created

| File | Purpose |
|------|---------|
| `/api/file_visibility_management.php` | REST API endpoints (8 handlers) |
| `/dashboard/includes/file_visibility_functions.php` | Helper functions for visibility checks |
| `/assets/setup/20251122_add_file_visibility.sql` | Database migration |
| `/FILE_VISIBILITY_SYSTEM.md` | Detailed documentation |

---

## Database Schema at a Glance

```
requirements table additions:
  - is_defense_manuscript (BOOLEAN) - mark main files
  - visibility_scope (ENUM) - all_stages, specific_stages, current_stage_only, hidden

New tables:
  - file_visibility_rules - per-defense-type visibility control
  - defense_file_access_log - audit trail of file access
```

---

## Next Steps

1. ✅ Run database migration (see above)
2. ⏳ Mark your first defense manuscript requirement
3. ⏳ Set visibility rules for each defense type
4. ⏳ Test with a team - verify correct files show
5. ⏳ Integrate UI into Dashboard (optional - currently API only)

---

## Support

Full documentation: `/FILE_VISIBILITY_SYSTEM.md`

All 8 API endpoints documented with examples.

Helper functions available in PHP: `file_visibility_functions.php`

Questions? Check audit logs to see exactly what's visible/hidden at each stage.
