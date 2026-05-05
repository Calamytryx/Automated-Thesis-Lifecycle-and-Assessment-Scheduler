# Scheduler Class Conflict Resolution - Final Fix Summary

**Issue**: Defense schedules were being generated with class conflicts despite occupancy map and calendar context fixes.

**Root Causes Identified**:
1. DefenseSchedule constructor had fallback logic that created chromosomes with null time_slot when no valid slots existed
2. Fallback populated from raw `$_POST['days']` and `date('n/j/Y')`, bypassing validated slot filtering
3. Crossover function could change day to an eligible day but keep an old time_slot that might not be valid for that day
4. GA mutations could introduce unvalidated slots if fallback logic didn't guard properly

## Fixes Applied

### Fix 1: DefenseSchedule Constructor (lines 3874-3916)
**Problem**: If a team had no valid slots on any eligible day, it would:
- Pick a day from `$_POST['days']` (raw, unfiltered)
- Set `time_slot = null` 
- Create chromosome with invalid state

**Solution**: 
- Only schedule teams that have at least one valid slot on an eligible day
- Build `$eligibleWithSlots` by checking each eligible day for valid slots
- Only create chromosome if `$eligibleWithSlots` is non-empty
- Teams with no valid slots remain unscheduled (will be marked unresolved)
- Removed all fallbacks to `$_POST['days']` and raw date formats

**Code Pattern**:
```php
if (!empty($eligibleWithSlots)) {
    // Only reach here if we have at least one valid slot
    $dayPick = $eligibleWithSlots[array_rand($eligibleWithSlots)];
    $slotList = scheduler_slots_for_team_day($slotsByTeamDay, $tidConst, $dayPick);
    $slotPick = !empty($slotList) ? $slotList[array_rand($slotList)] : null;
    
    if ($slotPick === null) {
        continue; // Skip this team; it will be unresolved
    }
    // Create chromosome with valid slot
} else {
    // No valid slots for this team
    error_log("Team has no valid slots; will remain unscheduled");
}
```

### Fix 2: Crossover Function (lines 3007-3028)
**Problem**: If crossover changed day but that day had no valid slots, it would fall back to old time_slot:
```php
$defense['day'] = $eligible[array_rand($eligible)];
$slots = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $defense['day']);
$defense['time_slot'] = !empty($slots) ? $slots[array_rand($slots)] : $defense['time_slot']; // ❌ Old slot might not match new day
```

**Solution**: 
- When changing day, verify the new day actually has valid slots
- Try eligible days (shuffled) until finding one with slots
- Only use that day if it has valid slots
- If NO eligible day has slots, force repair attempt to fail

**Code Pattern**:
```php
$dayToUse = $defense['day'];
if (!empty($eligible)) {
    $eligibleShuffled = $eligible;
    shuffle($eligibleShuffled);
    foreach ($eligibleShuffled as $candidateDay) {
        $slotsForDay = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $candidateDay);
        if (!empty($slotsForDay)) {
            $dayToUse = $candidateDay;
            break;
        }
    }
}

$defense['day'] = $dayToUse;
$slots = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $defense['day']);
if (empty($slots)) {
    $attempts = $maxAttempts; // Force exit of repair loop
    break;
}
$defense['time_slot'] = $slots[array_rand($slots)]; // Now guaranteed valid for this day
```

### Fix 3: Fallback Scheduling (two locations: lines ~3338, ~3567)
**Problem**: In fallback scheduling for unresolved teams, if no slots were available:
```php
$pickTimeFb = !empty($slotOpts) ? $slotOpts[array_rand($slotOpts)] : null;
if ($pickTimeFb === null) {
    continue;
}
// But then pickTimeFb is used even if null in some cases
```

**Solution**:
- Check for empty slots BEFORE using them
- Skip the entire fallback scheduling if no slots available
- Same pattern applied to both fallback locations

**Code Pattern**:
```php
$slotOpts = scheduler_slots_for_team_day($slotMapFb, (int) $missingTeamId, $pickDayFb);
if (empty($slotOpts)) {
    error_log("No candidate slot available for fallback team; skipping");
    continue;
}
$pickTimeFb = $slotOpts[array_rand($slotOpts)];
// Now guaranteed to have valid time_slot
```

## Validation Flow

With these fixes, the complete validation flow is:

1. **Slot Computation** (`computeSchedulerSlotContext`):
   - For each team/day/time, check class conflicts
   - Populate `slotsByTeamDay` with ONLY valid slots
   - Filter eligible days to only those with at least one valid slot

2. **Pre-GA Validation** (`buildPreGACandidatePool`):
   - For each (team, room, slot, day), check room/panelist/existing conflicts
   - Populate `validCandidatePool` with conflict-free candidates

3. **GA Initialization** (DefenseSchedule constructor):
   - ✅ Only pick from `slotsByTeamDay` (class-conflict-free)
   - ✅ Only schedule if team has at least one valid slot
   - ✅ NO fallback to raw POST data

4. **GA Evolution** (mutation, crossover):
   - ✅ All operations use only `slotsByTeamDay` slots
   - ✅ Day changes ensure new day has valid slots
   - ✅ Final conflicts checked with `hasConflicts()`

5. **Final Validation** (`validateScheduleOutput`):
   - Double-check all scheduled times against class/room/panelist constraints

## Expected Behavior After Fix

**Scenario**: Team 23 with panelist 269, 2026-05-08, Cloud Computing class 08:00-10:00

- **Before**: 07:00-09:00 slot was generated despite conflict
- **After**: 07:00 slot is rejected in `computeSchedulerSlotContext`, never added to `slotsByTeamDay`, GA cannot select it

## Testing Recommendations

1. **Unit Test**: Verify `computeSchedulerSlotContext` correctly rejects 07:00-09:00 for teams with 08:00-10:00 class
2. **Integration Test**: Run scheduler with conflicting class times, verify no conflicted schedules generated
3. **Regression Test**: Verify teams with no valid slots are correctly marked unresolved
4. **Edge Case**: Test with overlapping class periods, multiple panelists, room conflicts

## Files Modified
- `/opt/lampp/htdocs/dashboard/includes/run_scheduler.php`
  - Lines 3874-3916: DefenseSchedule constructor
  - Lines 3007-3028: Crossover function
  - Lines ~3338, ~3567: Fallback scheduling
