# Class Overlap Diagnostics - How to Debug

## Problem
Defenses are still being scheduled on time slots occupied by classes, even though class-blocking logic is in place.

## Root Cause Investigation
The issue is likely one of:
1. Team members' class schedules are not being loaded into `$userSchedules`
2. Day-of-week format mismatch between class data and scheduler
3. Time parsing issues (format, timezone)
4. Class records have `allow_overlap=1` or `is_research_class=1` set to 1

## Diagnostic Changes Made

I've added detailed logging to trace the issue:

### In `buildRoomOccupancyMap()` (line ~212)
- Logs all room occupancy entries being built
- Shows: room, day, class time, class name

### In `validateCandidateClassConflicts()` (line ~379)
- Logs team members found
- Logs for each member: whether they have schedules loaded
- Logs the first 3 schedules per member (class name, day, time, room, overlap flags)
- Logs when a class conflict is detected

### In `computeSchedulerSlotContext()` (line ~599)
- Logs total users and total schedule entries loaded
- For each slot, logs if it's ADDED or REJECTED
- Shows the final count of valid slots per team/day

### In `validateCandidateSlot()` (line ~274)
- Logs room occupancy checks showing what's in the map

## How to Debug

### Step 1: Clear the Error Log
```bash
echo "" > /opt/lampp/logs/error_log
```

### Step 2: Trigger a Schedule Generation
1. Go to the dashboard
2. Navigate to Defense Schedules tab
3. Click "Generate Schedules" button
4. Wait for completion

### Step 3: View Diagnostics
```bash
tail -500 /opt/lampp/logs/error_log | grep -E "(ROOM OCCUPANCY|CLASS CONFLICT|COMPUTE SLOT|Slot |Team member|Final slots)"
```

### Step 4: Analyze Output
Look for these patterns:

**Issue: No schedules loaded**
```
Total users with schedules: 2
Total schedule entries across all users: 0
```
→ If count is 0, team members don't have schedules in user_schedules table

**Issue: Team member has no schedules**
```
CLASS CONFLICT: Team member 123 has NO schedules loaded (team_id=45)
```
→ The member doesn't have an entry in user_schedules

**Issue: Class schedules not being used for conflicts**
```
CLASS CONFLICT: Team member 123 has 0 schedules loaded
```
→ Even though they should have 3-hour laboratory or other classes

**Issue: Slot being accepted despite class conflict**
```
Slot ADDED to pool: team=45 day=Monday time=3:00 PM
```
But there's a class at 3:00 PM → validation not working

**Good: Slot being properly rejected**
```
Slot REJECTED (class conflict): team=45 day=Monday time=3:00 PM
```
→ This is correct behavior

## Checking user_schedules Table

To verify what's in the database:
```sql
-- Check total schedules
SELECT COUNT(*) FROM user_schedules;

-- Check schedules for a specific team's members
SELECT us.user_id, us.class_name, us.day_of_week, us.start_time, us.end_time, 
       us.room, us.allow_overlap, us.is_research_class
FROM user_schedules us
INNER JOIN team_members tm ON us.user_id = tm.user_id
WHERE tm.team_id = 45;  -- Replace 45 with actual team ID

-- Check if overlap exception columns exist
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME='user_schedules' 
AND COLUMN_NAME IN ('allow_overlap', 'is_research_class');
```

## Key Diagnostic Variables

After running with logging, check these key things:

1. **User Schedules Loaded**: `Total users with schedules: X`
   - Should be > 0
   - Should include team members and panelists

2. **Schedule Entries**: `Total schedule entries across all users: X`
   - Should match number of class records in user_schedules table
   - Each student should have 3-5 classes, each panelist may have 0-2

3. **Team Members Found**: `Team %d member validation: found X members`
   - Should match actual team member count
   - If 0, the team lookup is broken

4. **Member Schedule Status**: `CLASS CONFLICT: Team member %d has %d schedules loaded`
   - Should show each member and their schedule count
   - If ANY shows "NO schedules loaded", that's the problem

5. **Final Slot Count**: `Final slots for team %d day %s: X valid times`
   - Should be non-zero for most teams/days
   - If all are zero, something is filtering everything out

## Next Steps After Analysis

Once logs show the issue:
- If members have no schedules → Add them to user_schedules table
- If classes have allow_overlap=1 → Set to 0 if they should block
- If day format mismatch → Check normalize_user_schedule_day_to_week_int() function
- If time parsing fails → Check time format (should be HH:MM:SS)

