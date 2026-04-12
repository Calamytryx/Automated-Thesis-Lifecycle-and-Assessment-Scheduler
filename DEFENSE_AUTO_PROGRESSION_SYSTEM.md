# Defense Type Auto-Progression System

## Overview
The genetic algorithm scheduler now includes automatic defense type progression based on past defense results. This eliminates manual intervention and ensures teams automatically advance through defense stages.

## How It Works

### 1. Schedule Date Check
When generating a new schedule, the system checks all existing schedules:
- **Past schedules** (date < current date): Processed for auto-progression
- **Future schedules** (date >= current date): Requires user confirmation to overwrite

### 2. Defense Status Evaluation
For past schedules, the system checks the `defense_status` field:

#### If Status = "passed"
The team progresses to the next defense level:
```
title_proposal  →  title_defense
title_defense   →  final_defense
final_defense   →  [completed, no new schedule]
```

#### If Status = "failed"
The team gets a re-defense:
```
[any defense type]  →  re_defense
```

### 3. Automatic Schedule Generation
- Old schedules are **preserved** for historical records
- New schedules are created with the progressed defense type
- The `teams.next_defense_type` column tracks the progression

## Database Schema Changes

### New Column: `teams.next_defense_type`
```sql
ALTER TABLE teams 
ADD COLUMN next_defense_type VARCHAR(50) NULL 
DEFAULT 'title_proposal';
```

**Values:**
- `title_proposal` (default)
- `title_defense`
- `final_defense`
- `re_defense`

### Required Existing Column: `defense_schedules.defense_status`
Should contain: `pending`, `passed`, `failed`

## User Experience

### Scenario 1: Past Schedule with Result
```
Team A completed title_proposal on 2026-01-15
Status: passed

→ When scheduler runs:
  - Old schedule remains in database
  - Team A automatically scheduled for title_defense
  - No user confirmation needed
```

### Scenario 2: Past Schedule, Failed Result
```
Team B completed title_defense on 2026-01-20
Status: failed

→ When scheduler runs:
  - Old schedule remains in database
  - Team B automatically scheduled for re_defense
  - No user confirmation needed
```

### Scenario 3: Future Schedule
```
Team C has title_proposal scheduled for 2026-03-01
Current date: 2026-02-05

→ When scheduler runs:
  - System asks: "Team C already has an UPCOMING schedule. Overwrite?"
  - User can confirm or cancel
  - If confirmed, only future schedules are removed
```

### Scenario 4: Mixed Schedules
```
Team D: 
  - Passed title_proposal on 2026-01-10 (kept)
  - Has title_defense scheduled for 2026-03-05 (requires confirmation)

→ When scheduler runs:
  - Past schedule processed automatically
  - User asked about future schedule only
```

## Implementation Files

### Modified Files
1. **`/dashboard/includes/run_scheduler.php`**
   - Added date comparison logic
   - Implemented `handleDefenseProgression()` function
   - Modified `checkExistingSchedules()` to return detailed info
   - Updated `fetchTeams()` to include `next_defense_type`
   - Modified `DefenseSchedule::__construct()` to use team's defense type
   - Updated database INSERT to include defense_type

### New Files
2. **`add_next_defense_type_column.sql`**
   - Migration script to add the new column

## Functions Added

### `handleDefenseProgression($pdo, $teamsToProgress)`
Processes automatic defense type progression for teams with evaluated past defenses.

**Parameters:**
- `$teamsToProgress`: Array of teams with their old schedule and status

**Returns:**
- Array of progressed teams with old and new defense types

**Logic:**
```php
if (status === 'passed') {
    switch (current_defense_type) {
        case 'title_proposal': → 'title_defense'
        case 'title_defense':  → 'final_defense'
        case 'final_defense':  → skip (already complete)
    }
} else if (status === 'failed') {
    → 're_defense'
}
```

## Configuration

No configuration needed. The system automatically:
- Detects past vs future schedules
- Reads defense_status from database
- Updates next_defense_type
- Generates new schedules with correct type

## Testing Checklist

- [ ] Run scheduler with only future schedules → should ask for confirmation
- [ ] Run scheduler with past schedule (passed) → should auto-progress
- [ ] Run scheduler with past schedule (failed) → should create re_defense
- [ ] Run scheduler with mixed past/future → should auto-progress past, ask about future
- [ ] Verify old schedules are not deleted
- [ ] Verify notifications include correct defense type name
- [ ] Check that defense_type appears correctly in database
- [ ] Verify teams.next_defense_type updates correctly

## Migration Steps

1. **Add database column:**
   ```bash
   mysql -u root -p your_database < add_next_defense_type_column.sql
   ```

2. **Test on staging environment**

3. **Deploy to production**

4. **Verify existing teams have default value:**
   ```sql
   SELECT id, name, next_defense_type FROM teams LIMIT 10;
   ```

## Rollback Plan

If issues occur:
```sql
-- Remove the column
ALTER TABLE teams DROP COLUMN next_defense_type;

-- Restore previous run_scheduler.php from git
git checkout HEAD~1 dashboard/includes/run_scheduler.php
```

## Benefits

✅ **Automatic progression** - No manual defense type updates needed
✅ **Historical data** - Past schedules preserved for audit trail
✅ **Smart confirmation** - Only asks about future schedules
✅ **Handles failures** - Automatically creates re-defense schedules
✅ **Clear notifications** - Users see specific defense type in alerts

## Future Enhancements

- Add UI to manually override defense progression
- Dashboard widget showing progression statistics
- Email notifications for automatic progressions
- Bulk defense status updates for batch processing
