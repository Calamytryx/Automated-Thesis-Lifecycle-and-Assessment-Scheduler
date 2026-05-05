#!/bin/bash
# Quick diagnostic script to analyze scheduler logs
# Usage: bash /opt/lampp/htdocs/run_diagnostics.sh

echo "=================================================="
echo "SCHEDULER CLASS OVERLAP DIAGNOSTICS"
echo "=================================================="
echo ""

LOG_FILE="/opt/lampp/logs/error_log"

if [ ! -f "$LOG_FILE" ]; then
    echo "ERROR: Log file not found at $LOG_FILE"
    exit 1
fi

echo "STEP 1: USER SCHEDULES LOADED"
echo "---"
grep "COMPUTE SLOT CONTEXT START" "$LOG_FILE" | tail -1
grep "Total users with schedules:" "$LOG_FILE" | tail -1
grep "Total schedule entries across all users:" "$LOG_FILE" | tail -1
echo ""

echo "STEP 2: ROOM OCCUPANCY MAP"
echo "---"
ROOM_COUNT=$(grep "^Room Occupancy:" "$LOG_FILE" | wc -l)
echo "Room occupancy entries: $ROOM_COUNT"
if [ $ROOM_COUNT -gt 0 ]; then
    grep "^Room Occupancy:" "$LOG_FILE" | tail -5
    echo "... (showing last 5)"
fi
echo ""

echo "STEP 3: TEAM MEMBERS WITH SCHEDULE STATUS"
echo "---"
grep "CLASS CONFLICT: Team member" "$LOG_FILE" | head -20
echo ""

echo "STEP 4: SLOTS STATUS"
echo "---"
ADDED=$(grep "Slot ADDED" "$LOG_FILE" | wc -l)
REJECTED_CLASS=$(grep "Slot REJECTED (class conflict)" "$LOG_FILE" | wc -l)
REJECTED_ROOM=$(grep "Slot REJECTED (no room)" "$LOG_FILE" | wc -l)

echo "Slots ADDED to valid pool: $ADDED"
echo "Slots REJECTED due to class conflict: $REJECTED_CLASS"
echo "Slots REJECTED due to no available room: $REJECTED_ROOM"
echo ""

echo "STEP 5: FINAL SUMMARY BY TEAM/DAY"
echo "---"
grep "Final slots for team" "$LOG_FILE" | tail -20
echo ""

echo "STEP 6: CLASS CONFLICTS DETECTED"
echo "---"
CONFLICTS=$(grep "CLASS CONFLICT DETECTED:" "$LOG_FILE" | wc -l)
echo "Total class conflicts detected: $CONFLICTS"
if [ $CONFLICTS -gt 0 ]; then
    grep "CLASS CONFLICT DETECTED:" "$LOG_FILE" | head -10
    if [ $CONFLICTS -gt 10 ]; then
        echo "... and $(($CONFLICTS - 10)) more"
    fi
fi
echo ""

echo "=================================================="
echo "INTERPRETATION GUIDE:"
echo "=================================================="
echo "1. If 'Total users with schedules: 0' → No user schedules loaded at all"
echo "2. If 'Total schedule entries: 0' → Classes not in database or not loading"
echo "3. If 'Team member has NO schedules loaded' → Member not in user_schedules table"
echo "4. If many 'Slot REJECTED (class conflict)' → Classes ARE blocking (working!)"
echo "5. If 0 slots added for team/day → Everything is being filtered (possibly too strict)"
echo "6. If 'Final slots for team X: 0 valid times' → No feasible times for that team"
echo ""
