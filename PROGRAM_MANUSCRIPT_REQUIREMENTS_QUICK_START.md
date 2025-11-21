# Program-Specific Manuscript Requirements - Quick Start

## 5-Minute Setup

### Your Goal
Make "Final Manuscript" required ONLY for WebDev and Mobile programs at Final Defense stage.

### 1. Run Database Migration (30 seconds)
```bash
mysql -h 127.0.0.1 -u root --ssl=false icei_38697196_coecsathesis < /opt/lampp/htdocs/assets/setup/20251122_program_manuscript_mapping.sql
```

Verify:
```bash
mysql icei_38697196_coecsathesis -e "SHOW TABLES LIKE '%manuscript%';"
```
Expected output: `program_manuscript_requirements` table exists ✓

### 2. Mark Manuscript in Requirements Table (10 seconds)
```bash
mysql icei_38697196_coecsathesis -e "UPDATE requirements SET is_defense_manuscript=1 WHERE name='Final Manuscript';"
```

Verify:
```bash
mysql icei_38697196_coecsathesis -e "SELECT id, name, is_defense_manuscript FROM requirements WHERE is_defense_manuscript=1;"
```
Expected output: Shows requirement #5 "Final Manuscript" with is_defense_manuscript=1 ✓

### 3. Get Program IDs (20 seconds)
```bash
mysql icei_38697196_coecsathesis -e "SELECT id, name FROM programs;"
```

Find:
- WebDevelopment program ID: `___`
- Mobile program ID: `___`

### 4. Add Mapping - WebDevelopment (20 seconds)
```bash
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=2&defense_type=final_defense&is_required=1&submission_stage=before_defense"
```

Expected response:
```json
{"success": true, "message": "Manuscript requirement added"}
```

### 5. Add Mapping - Mobile (20 seconds)
```bash
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=3&defense_type=final_defense&is_required=1&submission_stage=before_defense"
```

Expected response:
```json
{"success": true, "message": "Manuscript requirement added"}
```

### 6. Verify Mappings (20 seconds)
```bash
mysql icei_38697196_coecsathesis -e "SELECT r.name, p.name, pmr.defense_type FROM program_manuscript_requirements pmr JOIN requirements r ON pmr.requirement_id=r.id JOIN programs p ON pmr.program_id=p.id WHERE pmr.requirement_id=5;"
```

Expected output:
```
Final Manuscript | WebDevelopment | final_defense
Final Manuscript | Mobile         | final_defense
```
✓ Success!

### 7. Test with API (30 seconds)

**For a WebDev team (should see Final Manuscript):**
```bash
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=10"
```

Expected: `manuscripts` array contains "Final Manuscript"

**For a DataScience team (should NOT see Final Manuscript):**
```bash
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=20"
```

Expected: `manuscripts` array is empty or doesn't contain "Final Manuscript"

---

## What Happened?

Your system now has:

1. **Database** - Stores which programs need which manuscripts at which defense stages
2. **API** - 8 endpoints to manage and query manuscript requirements
3. **Helper Functions** - Automatically check if a manuscript applies to a team

## Next Steps

1. **Display in Requirements Tab** → Add UI to configure manuscripts per program
2. **Filter Decision-Support** → Show only applicable manuscripts
3. **Filter Home Tab** → Show only applicable manuscripts to teams

---

## Common Tasks

### Add Manuscript for More Programs
```bash
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=4&defense_type=final_defense"
# Now DataScience also needs it
```

### Remove Manuscript from a Program
```bash
curl -X POST "http://localhost/api/manuscript_requirements.php?action=remove_manuscript_requirement" \
  -d "requirement_id=5&program_id=3&defense_type=final_defense"
# Mobile no longer needs it
```

### Check All Mappings for a Manuscript
```bash
curl "http://localhost/api/manuscript_requirements.php?action=get_requirement_manuscripts&requirement_id=5"
# Shows which programs have this manuscript
```

### Bulk Add for Multiple Programs
```bash
curl -X POST "http://localhost/api/manuscript_requirements.php?action=bulk_update_manuscripts" \
  -d "requirement_id=5&defense_type=final_defense" \
  -d "program_ids[]=2" \
  -d "program_ids[]=3" \
  -d "program_ids[]=4"
# Adds Final Manuscript for WebDev, Mobile, and DataScience
```

---

## Testing Scenarios

### Scenario 1: Different Programs See Different Manuscripts
1. Team 10 (WebDev) @ Final Defense → Should see "Final Manuscript"
2. Team 20 (DataScience) @ Final Defense → Should NOT see "Final Manuscript"

**Test:**
```bash
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=10"
curl "http://localhost/api/manuscript_requirements.php?action=get_team_manuscripts&team_id=20"
```

### Scenario 2: Manuscript Not Visible at Other Defense Stages
1. WebDev Team @ Title Proposal → Should NOT see "Final Manuscript"
2. WebDev Team @ Final Defense → Should see "Final Manuscript"

**Test:** Modify schedule to change defense type, then call API

### Scenario 3: Re-Defense Also Gets Manuscript
```bash
curl -X POST "http://localhost/api/manuscript_requirements.php?action=add_manuscript_requirement" \
  -d "requirement_id=5&program_id=2&defense_type=re-defense"
# Now WebDev also needs final manuscript at re-defense
```

---

## Troubleshooting

### Migration Fails
**Error:** `Table 'program_manuscript_requirements' already exists`
- Table is already created from previous run - safe to ignore

### API Returns Error
**Error:** `{"success": false, "error": "Invalid requirement ID"}`
- Check requirement exists: `SELECT * FROM requirements WHERE id=5;`

**Error:** `{"success": false, "error": "Invalid program ID"}`
- Check program exists: `SELECT * FROM programs;`

### No Manuscripts Returned for Team
**Check:**
1. Is manuscript marked as is_defense_manuscript? 
   ```bash
   mysql icei_38697196_coecsathesis -e "SELECT * FROM requirements WHERE id=5;"
   ```
2. Is mapping created for this program?
   ```bash
   mysql icei_38697196_coecsathesis -e "SELECT * FROM program_manuscript_requirements WHERE requirement_id=5;"
   ```
3. Is team at correct defense stage?
   ```bash
   mysql icei_38697196_coecsathesis -e "SELECT * FROM defense_schedules WHERE team_id=10 ORDER BY schedule_date DESC LIMIT 1;"
   ```

---

## Key Files

| File | Purpose |
|------|---------|
| `/assets/setup/20251122_program_manuscript_mapping.sql` | Database migration |
| `/api/manuscript_requirements.php` | REST API endpoints |
| `/dashboard/includes/manuscript_requirements_functions.php` | Helper functions |
| `PROGRAM_MANUSCRIPT_REQUIREMENTS.md` | Full documentation |

---

## Next Integration Points

After setup, you need to integrate filtering in three places:

### 1. Decision Support Page
Show only manuscripts applicable to team's program + stage

### 2. Requirements Tab  
Configuration UI to select which programs need which manuscripts

### 3. Home Page
Filter team's requirement checklist to applicable manuscripts only

See `PROGRAM_MANUSCRIPT_REQUIREMENTS.md` for implementation details.

