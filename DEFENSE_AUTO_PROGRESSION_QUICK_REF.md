# Defense Auto-Progression Quick Reference

## Decision Tree

```
Existing Schedule Found
    ↓
Is defense_date < current_date?
    ↓
YES (Past) ──────────────────────────────── NO (Future)
    ↓                                            ↓
Has defense_status?                        Ask user confirmation
    ↓                                            ↓
YES ─────────────── NO                     User confirms?
    ↓                  ↓                         ↓
Status?          Skip team              YES ──────────── NO
    ↓                                        ↓              ↓
passed ─── failed                    Delete future    Cancel
    ↓         ↓                        schedule      scheduler
    ↓         ↓                            ↓
Progress  re_defense                Create new
    ↓         ↓                        schedule
    ↓         ↓                            ↓
Update next_defense_type              Done
    ↓
Create new schedule
    ↓
Done
```

## Defense Type Progression

| Current Status | Current Defense | Next Defense Type |
|----------------|-----------------|-------------------|
| ✅ passed      | title_proposal  | title_defense     |
| ✅ passed      | title_defense   | final_defense     |
| ✅ passed      | final_defense   | _(no new schedule)_ |
| ❌ failed      | _(any)_         | re_defense        |
| ⏳ pending     | _(any)_         | _(wait for result)_ |

## Code Flow

```php
// 1. Check existing schedules
$scheduledTeams = checkExistingSchedules($pdo, $selectedSections);

// 2. Separate by date
foreach ($scheduledTeams as $schedule) {
    if ($schedule['defense_date'] < date('Y-m-d')) {
        // Past schedule
        if (in_array($schedule['defense_status'], ['passed', 'failed'])) {
            $teamsToAutoProgress[] = $schedule;
        }
    } else {
        // Future schedule - needs confirmation
        $futureSchedules[] = $schedule;
    }
}

// 3. Auto-progress past schedules
if (!empty($teamsToAutoProgress)) {
    handleDefenseProgression($pdo, $teamsToAutoProgress);
}

// 4. Ask about future schedules
if (!empty($futureSchedules) && !confirm_overwrite) {
    return ['requireConfirmation' => true];
}

// 5. Generate new schedules
$teams = fetchTeams($pdo, $sections); // includes next_defense_type
geneticAlgorithm(...); // uses team's defense_type
```

## Database Updates

### On Past Schedule (Passed)
```sql
-- System automatically runs:
UPDATE teams 
SET next_defense_type = 'title_defense' 
WHERE id = 123;
```

### On Schedule Creation
```sql
-- System automatically runs:
INSERT INTO defense_schedules 
(team_id, defense_date, defense_type, ...) 
VALUES (123, '2026-03-15', 'title_defense', ...);
```

## API Response Examples

### Past Schedule (Auto-processed)
```json
{
  "success": true,
  "message": "Schedule generated successfully",
  "teams_progressed": [
    {
      "team_id": 123,
      "old_defense_type": "title_proposal",
      "new_defense_type": "title_defense",
      "status": "passed"
    }
  ]
}
```

### Future Schedule (Needs Confirmation)
```json
{
  "success": false,
  "requireConfirmation": true,
  "message": "Teams have UPCOMING schedules. Overwrite?",
  "scheduledTeams": {
    "123": "Team Alpha",
    "124": "Team Beta"
  }
}
```

## Testing Commands

### Check team's next defense type
```sql
SELECT id, name, next_defense_type FROM teams WHERE id = 123;
```

### Check past schedules with status
```sql
SELECT team_id, defense_date, defense_type, defense_status 
FROM defense_schedules 
WHERE defense_date < CURDATE() 
AND status = 'scheduled';
```

### Manually set a team's next defense type
```sql
UPDATE teams 
SET next_defense_type = 'title_defense' 
WHERE id = 123;
```

### Check schedule history for a team
```sql
SELECT defense_date, defense_type, defense_status, status
FROM defense_schedules 
WHERE team_id = 123 
ORDER BY defense_date DESC;
```

## Common Issues & Solutions

### Issue: Team not progressing
**Check:**
```sql
SELECT ds.defense_date, ds.defense_status, ds.defense_type
FROM defense_schedules ds
WHERE ds.team_id = 123 
AND ds.defense_date < CURDATE();
```
**Solution:** Ensure `defense_status` is set to 'passed' or 'failed'

### Issue: Wrong defense type in new schedule
**Check:**
```sql
SELECT id, next_defense_type FROM teams WHERE id = 123;
```
**Solution:** Run `handleDefenseProgression()` manually or update the column

### Issue: Confirmation prompt for old schedules
**Check:** System clock and database dates
```sql
SELECT NOW() as current_time, 
       defense_date, 
       (defense_date < CURDATE()) as is_past
FROM defense_schedules WHERE team_id = 123;
```

## Log Messages to Look For

```log
✅ Found X teams with existing schedules
✅ Team 123 passed title_proposal, progressing to title_defense
✅ Auto-progressed X teams based on past defense results
⚠️  Could not update next_defense_type for team 123
❌ Team 123 failed title_defense, scheduling re_defense
```

## One-Liner Checks

```bash
# Check if column exists
mysql -e "SHOW COLUMNS FROM teams LIKE 'next_defense_type';" your_db

# Count teams needing progression
mysql -e "SELECT COUNT(*) FROM defense_schedules WHERE defense_date < CURDATE() AND defense_status IN ('passed','failed');" your_db

# List all defense progressions
mysql -e "SELECT t.id, t.name, t.next_defense_type, ds.defense_type as current FROM teams t LEFT JOIN defense_schedules ds ON t.id=ds.team_id ORDER BY t.id;" your_db
```
