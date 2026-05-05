# Trace: How Fixes Prevent Class Conflict Generation

## Scenario Setup
- **Team 23** with panelist 269
- **Defense Date**: Friday, 2026-05-08
- **Panelist Schedule**: Cloud Computing class, Friday 08:00-10:00
- **Problem**: System was generating 07:00-09:00 defense slot despite class overlap

## Trace: Before Fixes ❌

### Phase 1: computeSchedulerSlotContext
```
1. For team 23, day "05-08-2026", time "07:00":
   - Call validateCandidateClassConflicts({team_id: 23, panelist_ids: [269], day: "05-08-2026", time_slot: "07:00"})
   - Conflict DETECTED: 07:00-09:00 overlaps with 08:00-10:00 ✓
   - Return conflicts array
2. Since conflicts non-empty:
   - Log: "Slot REJECTED (class conflict)"
   - Don't add to slotsByTeamDay[23]["2026-05-08"] ✓
   - Result: slotsByTeamDay[23]["2026-05-08"] = ["08:00", "09:00", ...]  // No 07:00
```

### Phase 2: DefenseSchedule Constructor ❌ (BEFORE FIX)
```
1. eligibleDaysByTeam[23] = ["2026-05-08"]
2. For each eligible day:
   - Check scheduler_slots_for_team_day(slotsByTeamDay, 23, "2026-05-08")
   - Result: ["08:00", "09:00", ...] (non-empty)
   - Add to eligibleWithSlots
3. eligibleWithSlots = ["2026-05-08"] (non-empty)
4. dayPick = "2026-05-08"
5. slotList = ["08:00", "09:00", ...]
6. slotPick = "08:00" (randomly chosen)
7. Create chromosome ✓
```
Result: CORRECT - only valid slots used

### Phase 3: BUT... Problem Path ❌ (BEFORE FIX)
```
WAIT - what if eligibleWithSlots WAS empty? (earlier version had fallback)

OLD CODE:
  if (!empty($eligibleWithSlots)) {
    // ... pick valid slot
  } else {
    $dayPick = !empty($eligibleConst) ? $eligibleConst[...] : $_POST['days'][...]; // ❌ Raw data!
    $slotPick = null; // ❌ Null slot!
  }
  
  $defense = [
    'time_slot' => $slotPick, // Could be null!
    'day' => $dayPick,  // Could be raw $_POST data!
  ];
```

If for some reason `slotsByTeamDay[23]["2026-05-08"]` was empty, the old code would:
- Pick day from `$_POST['days']` (unfiltered)
- Set time_slot to null
- Create chromosome with invalid state

This chromosome would then be used by GA, and when time_slot was accessed later, it might:
- Remain null (causing errors)
- Get filled in by mutation (potentially with unvalidated slot)
- Cause GA to generate invalid schedules

## Trace: After Fixes ✅

### Phase 1: computeSchedulerSlotContext (UNCHANGED)
```
Same as before - correctly rejects conflicting slots
Result: slotsByTeamDay[23]["2026-05-08"] = ["08:00", "09:00", ...]
```

### Phase 2: DefenseSchedule Constructor ✅ (AFTER FIX)
```
NEW CODE:
  $eligibleWithSlots = [];
  foreach ($eligibleConst as $dayCandidate) {
    $slotCandidateList = scheduler_slots_for_team_day($slotsByTeamDay, 23, $dayCandidate);
    if (!empty($slotCandidateList)) {  // ✓ Only add days with valid slots
      $eligibleWithSlots[] = $dayCandidate;
    }
  }
  
  if (!empty($eligibleWithSlots)) {
    // ... pick valid slot from pre-validated pool
  } else {
    // No valid slots for this team - SKIP IT (don't create chromosome)
    error_log("Team 23 has no valid slots; will remain unscheduled");
    continue; // ✓ Skip this team entirely
  }
```

Guarantee: NO chromosome created with unvalidated slot ✓
```

### Phase 3: GA Initialization
```
DefenseSchedule constructor creates chromosome with:
  - time_slot: "08:00" (from valid pool)
  - day: "2026-05-08"
  - No null values
  - No raw POST data
  - Guaranteed conflict-free for this team/time/day combo
```

### Phase 4: GA Mutations/Crossovers ✅ (AFTER FIX)

**Crossover Example - Before**: ❌
```
parent1: {team: 23, day: "2026-05-08", time_slot: "08:00", ...}
parent2: {team: 23, day: "2026-05-09", time_slot: "09:00", ...}

After crossover point cut:
  child: {team: 23, day: "2026-05-09", time_slot: "08:00", ...}  // ❌ Mismatch!
  
Then old code:
  $defense['day'] = $eligible[array_rand($eligible)];  // Pick random eligible day
  $slots = scheduler_slots_for_team_day($slotsByTeamDay, 23, $defense['day']);
  $defense['time_slot'] = !empty($slots) ? $slots[...] : $defense['time_slot'];  // Keep old if no slots!
  
  If $defense['day'] changed to "2026-05-07" but that day has no valid slots:
    - time_slot stays "08:00" (from different day!)
    - Potential mismatch: ❌
```

**Crossover Example - After**: ✅
```
Same setup, but NEW CODE in crossover:

  $dayToUse = $defense['day'];  // Start with current day
  if (!empty($eligible)) {
    $eligibleShuffled = $eligible;
    shuffle($eligibleShuffled);
    foreach ($eligibleShuffled as $candidateDay) {
      $slotsForDay = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $candidateDay);
      if (!empty($slotsForDay)) {  // ✓ Only pick day if it has slots
        $dayToUse = $candidateDay;
        break;
      }
    }
  }
  
  $defense['day'] = $dayToUse;  // Now guaranteed to have valid slots
  $slots = scheduler_slots_for_team_day($slotsByTeamDay, $tid, $defense['day']);
  if (empty($slots)) {  // ✓ Sanity check
    error_log("Crossover: team has no valid slots even on eligible days");
    $attempts = $maxAttempts;  // Force exit, repair failed
    break;
  }
  $defense['time_slot'] = $slots[array_rand($slots)];  // ✓ Pick from validated pool
  
  Result: Both day and time_slot guaranteed valid for each other
```

### Phase 5: GA Output
```
All chromosomes created and modified using ONLY slots from:
  - slotsByTeamDay (pre-filtered class conflicts)
  - validCandidatePool (additional room/panelist validation)

Guarantee: NO 07:00-09:00 slot in output for Team 23 on 2026-05-08
Because:
  1. ✓ Class conflict detection worked (rejects 07:00-09:00)
  2. ✓ Slot not in slotsByTeamDay
  3. ✓ DefenseSchedule only picks from slotsByTeamDay
  4. ✓ GA only modifies using valid slots
  5. ✓ No fallback to raw data or null slots
```

## Edge Cases Handled

### Edge Case 1: Team With No Valid Slots
```
BEFORE: Picked day from $_POST, set time_slot=null, created invalid chromosome
AFTER: Skipped in DefenseSchedule constructor, marked unresolved
Result: User notified of unresolved teams; scheduler doesn't create invalid chromosomes
```

### Edge Case 2: Crossover Creates Day/Slot Mismatch
```
BEFORE: Changed day but kept old time_slot (fallback if no slots)
AFTER: Ensures new day has valid slots before using it; force repair fail if not
Result: Day and time_slot always match valid combinations from slotsByTeamDay
```

### Edge Case 3: Fallback Scheduling With No Slots
```
BEFORE: Set time_slot=null if no slots available
AFTER: Skip entire fallback scheduling if no slots (continue to next team)
Result: Only valid schedules created in fallback logic
```

## Conclusion

The fixes ensure that:
1. ✅ All slots in `slotsByTeamDay` are pre-validated for class conflicts
2. ✅ All chromosomes created use only slots from `slotsByTeamDay`
3. ✅ All chromosome modifications (mutations, crossovers) maintain valid slot usage
4. ✅ No fallback to raw, unvalidated data or null values
5. ✅ Teams with no valid slots are handled gracefully (not scheduled)

**Result**: The scheduler will NEVER generate a 07:00-09:00 slot for a team with an 08:00-10:00 class conflict, because that slot is rejected before GA runs and never available for selection.
