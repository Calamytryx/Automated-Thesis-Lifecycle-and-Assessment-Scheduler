# 🎯 DEFENSE TYPE SYSTEM - QUICK REFERENCE CARD

## YOUR 4 MAIN FEATURES

### 1️⃣ DYNAMIC REQUIREMENT TYPES
**Location:** Dashboard → Requirements  
**What:** Each requirement is linked to a defense stage  
**How to use:**
```
Requirements tab → Add/Edit → Choose Defense Type dropdown
Options: Title Proposal / Title Defense / Final Defense / General
```
**Benefit:** Different requirements for different stages (not hardcoded)

---

### 2️⃣ MULTIPLE SUBMISSIONS (1-3 FILES)
**Location:** Dashboard → Requirements form  
**What:** Allow students to submit multiple versions of a requirement  
**How to use:**
```
Requirements form → Check "Allow Multiple Submissions" checkbox
→ Set max submissions (1, 2, or 3) → Save
```
**Benefit:** Teams can submit 3 title proposals, system selects best one

---

### 3️⃣ OVERRIDE DEFENSE TYPE
**Location:** Dashboard → Team Overrides & Panelists tab  
**What:** Change a team's defense stage manually (admin only)  
**How to use:**
```
Click Team name → Click "Override" button
→ Select new defense type → Enter reason → Save
```
**Benefit:** Handle special cases (medical leave, early approval, etc.)

---

### 4️⃣ LOCK PANELISTS
**Location:** Dashboard → Team Overrides & Panelists tab  
**What:** Prevent panelist changes between defense stages  
**How to use:**
```
Click team name → Click "Panelists" button
→ See current panelists → Click "Lock Panelists"
```
**Benefit:** Same panelists for consistency across stages

---

## FIND IT IN 5 SECONDS

| What You Need | Where to Find It |
|:---|:---|
| **Create requirement with type** | Dashboard → Requirements → Add → Defense Type dropdown |
| **Allow multiple submissions** | Dashboard → Requirements → Add → Allow Multiple Submissions ☐ |
| **Override defense type** | Dashboard → Team Overrides & Panelists → Override button |
| **Lock panelists** | Dashboard → Team Overrides & Panelists → Panelists button |
| **See defense type in eval** | Any Evaluation page → Look at colored badge at top |

---

## COLOR CODES (BADGES)

| Color | Meaning | Where |
|:---:|:---|:---|
| 🔵 **Blue** | Title Defense | Requirements table, Evaluation page |
| 🔵 **Cyan** | Title Proposal | Requirements table |
| 🟢 **Green** | Final Defense | Requirements table, Evaluation page |
| 🟠 **Gray** | General | Requirements table |

---

## STEP-BY-STEP: COMMON TASKS

### TASK: Set up a requirement that needs 3 title proposals

```
1. Go: Dashboard → Requirements → Add Requirement
2. Enter: Name, Description, Due Date, Template (as normal)
3. Select: Defense Type = "Title Proposal"
4. Check: ☐ Allow Multiple Submissions
5. Set: Maximum Submissions = 3
6. Click: Save
✓ Done! Students can now submit up to 3 titles
```

### TASK: Override a team to Final Defense (special case)

```
1. Go: Dashboard → Team Overrides & Panelists
2. Find: Your team in the list
3. Click: "Override" button
4. Select: Defense Type = "Final Defense"
5. Type: Reason (e.g., "Early approval from thesis advisor")
6. Optional: Set expiration date
7. Click: Save Override
✓ Done! Team is now in Final Defense stage
```

### TASK: Lock panelists so they don't change next stage

```
1. Go: Dashboard → Team Overrides & Panelists
2. Find: Your team
3. Click: "Panelists" button
4. See: Current panelists for each stage
5. Click: "Lock Panelists" button
6. Confirm: Yes, lock them
✓ Done! Scheduler will use same panelists next time
```

---

## WHAT'S NEW VS BEFORE

### Before This System
```
❌ All requirements the same
❌ Only 1 file per requirement
❌ Panelists could change anytime
❌ No override option
❌ Hardcoded defense stages
```

### After This System
```
✅ Requirements linked to stages
✅ 1-3 files per requirement (your choice)
✅ Panelists locked once assigned
✅ Override system for special cases
✅ Dynamic - controlled by you
```

---

## EVALUATOR'S VIEW

When evaluating, you'll see:

```
┌─────────────────────────────────┐
│ Team Name │ Date │ Time │ 🚩 Final Defense │
│                                 │
│ [Evaluation Form Below]         │
└─────────────────────────────────┘

The badge tells you:
- Which defense stage you're evaluating
- What rubric to apply
- Context for scoring
```

---

## ADMIN DASHBOARD NAVIGATION

```
DASHBOARD SIDEBAR
├─ Dashboard Home
├─ Defenses
│  ├─ Rubrics
│  ├─ Defense Schedules
│  ├─ Evaluations
│  ├─ Requirements ────────────── ✅ Edit requirement types here
│  └─ Team Overrides & Panelists  ✅ Override & lock panelists here (NEW!)
├─ Files
├─ Announcements
└─ Admin Settings
```

---

## IF SOMETHING ISN'T SHOWING...

### "I don't see the Defense Type column"
```
→ Requirements table may be loading old data
→ Try: F5 (refresh page) or Ctrl+Shift+R (hard refresh)
→ If still no: Make sure you added a requirement with defense type
```

### "The Override button isn't working"
```
→ Check: Are you an admin? (only admins can override)
→ Check: Is the button visible? (refresh if needed)
→ Check: Did you get an error? (check browser console F12)
```

### "I can't select multiple submissions"
```
→ Requirement must be type "Title Proposal"
→ If general type: System won't allow multiple
→ Try: Delete requirement and recreate with Title Proposal type
```

---

## DATABASE FIELDS (FOR ADMINS)

**Requirements Table - New Columns:**
```
requirement_type: The defense stage (title_proposal, title_defense, final_defense, general)
allow_multiple_submissions: Yes/No (1/0)
max_submissions: 1-3 (how many files allowed)
```

**Defense Type Options:**
```
title_proposal: For title proposal stage
title_defense: For title defense stage
final_defense: For final defense stage
general: For requirements needed at all stages
```

---

## KEYBOARD SHORTCUTS

| Action | How |
|:---|:---|
| Edit requirement | Dashboard → Requirements → Click pencil icon |
| Set override | Dashboard → Team Overrides → Click Override |
| Search team | Dashboard → Team Overrides → Type in search box |
| Close modal | Press Escape or click X button |
| Save form | Press Ctrl+S or click Save button |

---

## SUPPORT: COMMON ISSUES

**Q: How many files can a team submit?**  
A: 1-3, you set it when creating the requirement

**Q: Can I change panelists after locking?**  
A: Only by clicking unlock in the same modal

**Q: Does override override the panelists?**  
A: No, they're separate. Override changes stage, lock affects panelists.

**Q: Can students see overrides?**  
A: No, only admins and evaluators see them

**Q: What happens if I don't set an override?**  
A: System automatically determines defense type (no manual action needed)

---

## QUICK TEST CHECKLIST

✅ Can I see "Team Overrides & Panelists" in sidebar?  
✅ Can I create a requirement with defense type?  
✅ Can I check "Allow Multiple Submissions"?  
✅ Can I see the new columns in requirements table?  
✅ Can I set an override for a team?  
✅ Can I lock panelists for a team?  
✅ Do I see the defense type badge on evaluation?  
✅ Are all badges the right color?  

*If all ✅, the system is working perfectly!*

---

## NEED MORE HELP?

📖 **Detailed Guide:** Read `FRONTEND_CHANGES_GUIDE.md`  
📍 **Where Things Are:** Read `WHERE_TO_FIND_OVERRIDES.md`  
🗺️ **Visual Diagrams:** Read `VISUAL_NAVIGATION_MAP.md`  
⚙️ **Technical Details:** Read `README_DEFENSE_TYPE_SYSTEM.md`  
✔️ **Full Checklist:** Read `IMPLEMENTATION_COMPLETE.md`  

---

## 🚀 YOU'RE ALL SET!

All features are implemented, documented, and ready to use.

**Start here:** Dashboard → Team Overrides & Panelists

**Questions?** Check the 6 documentation files (listed above)

**Found a bug?** Check browser console (F12) for error messages

---

**Last Updated:** November 21, 2025  
**System Status:** ✅ FULLY IMPLEMENTED  
**User Ready:** ✅ YES  

